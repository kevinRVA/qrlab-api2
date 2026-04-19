<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Session;
use App\Models\Attendance;
use App\Models\Laboratory;
use App\Models\LabVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // =========================================================================
    // HELPER PRIVADO: Filtro según el rol del usuario autenticado
    // -------------------------------------------------------------------------
    // Devuelve un array con ['ids' => [...], 'names' => [...]] si es coordinador,
    // o null si es admin (sin restricción).
    // =========================================================================
    private function getLabFilter(): ?array
    {
        $user = Auth::user();

        if ($user->role !== 'coordinador') {
            return null; // Admin: sin filtro
        }

        return [
            'ids'   => $user->getAssignedLabIds(),
            'names' => $user->getAssignedLabNames(),
        ];
    }

    // =========================================================================
    // VISTAS
    // =========================================================================

    /**
     * Hub principal del panel de administración.
     */
    public function index()
    {
        $filter = $this->getLabFilter();

        if ($filter) {
            // Coordinador: estadísticas limitadas a sus labs
            $labNames = $filter['names'];
            $labIds   = $filter['ids'];

            $activeSessions   = Session::where('is_active', true)
                ->whereIn('laboratory_name', $labNames)->count();
            $totalAttendances = Attendance::whereHas('session', function ($q) use ($labNames) {
                $q->whereIn('laboratory_name', $labNames);
            })->count();

            $stats = [
                'students'          => User::where('role', 'student')->count(),
                'teachers'          => User::where('role', 'teacher')->count(),
                'active_sessions'   => $activeSessions,
                'total_attendances' => $totalAttendances,
            ];
        } else {
            // Admin: estadísticas globales
            $stats = [
                'students'          => User::where('role', 'student')->count(),
                'teachers'          => User::where('role', 'teacher')->count(),
                'active_sessions'   => Session::where('is_active', true)->count(),
                'total_attendances' => Attendance::count(),
            ];
        }

        return view('admin.index', compact('stats'));
    }

    /** Vista: Asistencia de Clases. */
    public function asistencia()
    {
        return view('admin.asistencia');
    }

    /** Vista: Prácticas Libres (acceso voluntario a laboratorios). */
    public function practicasLibres()
    {
        return view('admin.practicas-libres');
    }

    /** Vista: Detalle de alertas de estudiantes con 3+ cierres automáticos. */
    public function alertasCierre()
    {
        return view('admin.alertas-cierre');
    }

    // =========================================================================
    // API JSON — SESIONES DE CLASE
    // =========================================================================

    /**
     * Alimenta la tabla y las gráficas de Asistencia de Clases.
     * Si el usuario es coordinador, sólo devuelve sesiones de sus labs.
     */
    public function getSesionesApi()
    {
        $filter = $this->getLabFilter();

        $query = Session::with(['section.teacher', 'section.subject'])
            ->withCount('attendances')
            ->orderBy('created_at', 'desc');

        if ($filter) {
            $query->whereIn('laboratory_name', $filter['names']);
        }

        $sesiones = $query->get();

        $data = $sesiones->map(function ($sesion) {
            return [
                'id'                => $sesion->id,
                'created_at'        => $sesion->created_at,
                'is_active'         => $sesion->is_active,
                'laboratory_name'   => $sesion->laboratory_name,
                'class_type'        => $sesion->class_type ?? 'Clase',
                'attendances_count' => $sesion->attendances_count,
                'teacher_name'      => $sesion->section->teacher->name ?? 'Desconocido',
                'subject'           => $sesion->section->subject->name ?? 'Desconocida',
                'section'           => $sesion->section->section_code ?? 'N/A',
            ];
        });

        return response()->json($data);
    }

    /**
     * Genera el CSV detallado con los nombres de los alumnos de una sesión.
     */
    public function descargarReporte($id)
    {
        $sesion = Session::with(['section.subject', 'section.teacher'])->findOrFail($id);

        // Si es coordinador, verificar que la sesión pertenezca a sus labs
        $filter = $this->getLabFilter();
        if ($filter && !in_array($sesion->laboratory_name, $filter['names'])) {
            abort(403, 'No tienes permiso para descargar este reporte.');
        }

        $asistencias = Attendance::with('student')->where('session_id', $id)->get();

        $materia  = $sesion->section->subject->name ?? 'Materia';
        $tipo     = $sesion->class_type ?? 'Clase';
        $fecha    = $sesion->created_at->format('d-m-Y');
        $fileName = "Asistencia_{$materia}_{$tipo}_{$fecha}.csv";

        $headers = [
            'Content-type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($asistencias, $sesion) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM para Excel
            fputcsv($file, ['Carnet / Codigo', 'Nombre del Estudiante', 'Carrera', 'Tipo de Clase', 'Hora de Registro'], ';');
            foreach ($asistencias as $asistencia) {
                $estudiante = $asistencia->student;
                fputcsv($file, [
                    $estudiante->user_code ?? 'N/A',
                    $estudiante->name      ?? 'Desconocido',
                    $estudiante->career    ?? 'N/A',
                    $sesion->class_type    ?? 'Clase',
                    $asistencia->created_at->format('H:i:s'),
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================================================================
    // API JSON — PRÁCTICAS LIBRES (VISITAS A LABORATORIOS)
    // =========================================================================

    /**
     * Retorna las visitas a laboratorios.
     * Si el usuario es coordinador, filtra por sus labs asignados.
     */
    public function getLabVisitasApi(Request $request)
    {
        $filter = $this->getLabFilter();

        $query = LabVisit::with(['student', 'laboratory'])
            ->orderByDesc('entry_time');

        // Filtro por coordinador (siempre aplicado primero)
        if ($filter) {
            $query->whereIn('laboratory_id', $filter['ids']);
        }

        // Filtro por lab específico (del selector del usuario)
        if ($request->filled('lab_id') && $request->lab_id !== 'TODOS') {
            // Si es coordinador, asegurarse de que el lab seleccionado es suyo
            if (!$filter || in_array($request->lab_id, $filter['ids'])) {
                $query->where('laboratory_id', $request->lab_id);
            }
        }

        // Rango de fechas: si no se envía ninguno, últimos 7 días
        $desde = $request->filled('fecha_desde')
            ? Carbon::parse($request->fecha_desde)->startOfDay()
            : Carbon::today()->subDays(6)->startOfDay();

        $hasta = $request->filled('fecha_hasta')
            ? Carbon::parse($request->fecha_hasta)->endOfDay()
            : Carbon::today()->endOfDay();

        $query->whereBetween('entry_time', [$desde, $hasta]);

        $visitas = $query->get()->map(function ($v) {
            $duracion = null;
            if ($v->entry_time && $v->exit_time) {
                $diff     = $v->entry_time->diff($v->exit_time);
                $duracion = $diff->h . 'h ' . $diff->i . 'min';
            }
            return [
                'id'              => $v->id,
                'carnet'          => $v->student->user_code ?? 'N/A',
                'nombre'          => $v->student->name ?? 'Desconocido',
                'laboratorio'     => $v->laboratory->name ?? 'Desconocido',
                'laboratory_id'   => $v->laboratory_id,
                'entry_time'      => $v->entry_time?->format('H:i:s'),
                'exit_time'       => $v->exit_time?->format('H:i:s'),
                'duracion'        => $duracion,
                'auto_closed'     => $v->auto_closed,
                'no_exit_warning' => $v->no_exit_warning,
                'fecha'           => $v->entry_time?->format('d/m/Y'),
            ];
        });

        return response()->json($visitas);
    }

    /**
     * Lista los laboratorios disponibles.
     * Si el usuario es coordinador, sólo devuelve sus labs asignados.
     */
    public function getLabsApi()
    {
        $filter = $this->getLabFilter();

        $query = Laboratory::query();
        if ($filter) {
            $query->whereIn('id', $filter['ids']);
        }

        $labs = $query->get()->map(function ($lab) {
            return [
                'id'        => $lab->id,
                'name'      => $lab->name,
                'qr_token'  => $lab->qr_token,
                'print_url' => route('admin.lab.imprimir', $lab->id),
                'scan_url'  => $lab->qr_token ? url('/lab-qr/' . $lab->qr_token) : null,
            ];
        });

        return response()->json($labs);
    }

    /**
     * Vista de impresión del QR estático de un laboratorio.
     */
    public function printLabQr($id)
    {
        $lab = Laboratory::findOrFail($id);

        // Verificar acceso del coordinador
        $filter = $this->getLabFilter();
        if ($filter && !in_array($lab->id, $filter['ids'])) {
            abort(403, 'No tienes acceso a este laboratorio.');
        }

        if (empty($lab->qr_token)) {
            return back()->with('error', 'Este laboratorio aún no tiene un QR generado. Ejecuta: php artisan lab:generar-qr');
        }

        $qrUrl = url('/lab-qr/' . $lab->qr_token);
        return view('lab.imprimir-qr', compact('lab', 'qrUrl'));
    }

    // =========================================================================
    // FINALIZAR VISITA (admin / coordinador cierra una práctica libre abierta)
    // =========================================================================

    public function finalizarVisita($id)
    {
        $visita = LabVisit::find($id);

        if (!$visita) {
            return response()->json(['ok' => false, 'mensaje' => 'Visita no encontrada.'], 404);
        }

        // Verificar acceso del coordinador
        $filter = $this->getLabFilter();
        if ($filter && !in_array($visita->laboratory_id, $filter['ids'])) {
            return response()->json(['ok' => false, 'mensaje' => 'No tienes acceso a este laboratorio.'], 403);
        }

        if ($visita->exit_time) {
            return response()->json(['ok' => false, 'mensaje' => 'Esta visita ya fue finalizada.'], 422);
        }

        $visita->update([
            'exit_time'       => Carbon::now(),
            'auto_closed'     => true,
            'no_exit_warning' => true,
        ]);

        return response()->json(['ok' => true, 'mensaje' => 'Visita finalizada correctamente.']);
    }

    // =========================================================================
    // ALERTAS: ESTUDIANTES CON 3+ CIERRES AUTOMÁTICOS
    // =========================================================================

    /**
     * Alertas ACTIVAS: estudiantes con 3+ visitas auto-cerradas sin perdón.
     * Si el usuario es coordinador, sólo incluye visitas de sus labs.
     */
    public function getAlertasCierreAutoApi()
    {
        $filter = $this->getLabFilter();

        $query = DB::table('lab_visits')
            ->join('users', 'lab_visits.student_id', '=', 'users.id')
            ->select(
                'users.id as student_id',
                'users.name as nombre',
                'users.user_code as carnet',
                DB::raw('COUNT(*) as total_cierres'),
                DB::raw('SUM(CASE WHEN lab_visits.no_exit_warning = true THEN 1 ELSE 0 END) as alertas_activas')
            )
            ->where('lab_visits.auto_closed', true);

        if ($filter) {
            $query->whereIn('lab_visits.laboratory_id', $filter['ids']);
        }

        $alertas = $query
            ->groupBy('users.id', 'users.name', 'users.user_code')
            ->having('total_cierres', '>=', 3)
            ->having('alertas_activas', '>', 0)
            ->orderByDesc('total_cierres')
            ->get();

        return response()->json($alertas);
    }

    /**
     * Historial completo de cierres automáticos (incluyendo perdonados).
     * Si el usuario es coordinador, sólo incluye visitas de sus labs.
     */
    public function getHistorialAlertasApi()
    {
        $filter = $this->getLabFilter();

        $query = DB::table('lab_visits')
            ->join('users', 'lab_visits.student_id', '=', 'users.id')
            ->join('laboratories', 'lab_visits.laboratory_id', '=', 'laboratories.id')
            ->select(
                'users.id as student_id',
                'users.name as nombre',
                'users.user_code as carnet',
                DB::raw('COUNT(*) as total_cierres'),
                DB::raw('SUM(CASE WHEN lab_visits.no_exit_warning = true THEN 1 ELSE 0 END) as alertas_activas'),
                DB::raw('MAX(lab_visits.entry_time) as ultima_visita')
            )
            ->where('lab_visits.auto_closed', true);

        if ($filter) {
            $query->whereIn('lab_visits.laboratory_id', $filter['ids']);
        }

        $historial = $query
            ->groupBy('users.id', 'users.name', 'users.user_code')
            ->orderByDesc('total_cierres')
            ->limit(20)
            ->get();

        return response()->json($historial);
    }
}