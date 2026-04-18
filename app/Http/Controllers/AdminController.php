<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Session;
use App\Models\Attendance;
use App\Models\Laboratory;
use App\Models\LabVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Hub principal del panel de administración.
     */
    public function index()
    {
        $stats = [
            'students'          => User::where('role', 'student')->count(),
            'teachers'          => User::where('role', 'teacher')->count(),
            'active_sessions'   => Session::where('is_active', true)->count(),
            'total_attendances' => Attendance::count(),
        ];

        return view('admin.index', compact('stats'));
    }

    /**
     * Vista: Asistencia de Clases.
     */
    public function asistencia()
    {
        return view('admin.asistencia');
    }

    /**
     * Vista: Prácticas Libres (acceso voluntario a laboratorios).
     */
    public function practicasLibres()
    {
        return view('admin.practicas-libres');
    }

    /**
     * Vista: Detalle de alertas de estudiantes con 3+ cierres automáticos.
     */
    public function alertasCierre()
    {
        return view('admin.alertas-cierre');
    }

    // 1. NUEVA FUNCIÓN: Alimenta la tabla y las gráficas con datos relacionales
    public function getSesionesApi()
    {
        // Traemos las sesiones incluyendo la sección, el maestro y la materia
        $sesiones = Session::with(['section.teacher', 'section.subject'])
            ->withCount('attendances')
            ->orderBy('created_at', 'desc')
            ->get();

        // Traducimos los datos para que tu JavaScript los entienda a la perfección
        $data = $sesiones->map(function ($sesion) {
            return [
                'id' => $sesion->id,
                'created_at' => $sesion->created_at,
                'is_active' => $sesion->is_active,
                'laboratory_name' => $sesion->laboratory_name,
                'attendances_count' => $sesion->attendances_count,
                
                // Mapeamos las relaciones (si no existe, ponemos 'Desconocido')
                'teacher_name' => $sesion->section->teacher->name ?? 'Desconocido',
                'subject' => $sesion->section->subject->name ?? 'Desconocida',
                'section' => $sesion->section->section_code ?? 'N/A',
            ];
        });

        return response()->json($data);
    }

    // 2. NUEVA FUNCIÓN: Genera el Excel detallado con los nombres de los alumnos
    public function descargarReporte($id)
    {
        $sesion = Session::with(['section.subject', 'section.teacher'])->findOrFail($id);
        
        // Traemos las asistencias y con ellas, los datos del estudiante
        $asistencias = Attendance::with('student')->where('session_id', $id)->get();

        $materia = $sesion->section->subject->name ?? 'Materia';
        $fecha = $sesion->created_at->format('d-m-Y');
        $fileName = "Asistencia_{$materia}_{$fecha}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($asistencias) {
            $file = fopen('php://output', 'w');
            
            // Esto asegura que Excel lea bien las tildes y las ñ (UTF-8 BOM)
            fputs($file, "\xEF\xBB\xBF"); 
            
            // Encabezados del Excel (separados por punto y coma para el Excel en español)
            fputcsv($file, ['Carnet / Codigo', 'Nombre del Estudiante', 'Carrera', 'Hora de Registro'], ';');

            foreach ($asistencias as $asistencia) {
                $estudiante = $asistencia->student;
                fputcsv($file, [
                    $estudiante->user_code ?? 'N/A',
                    $estudiante->name ?? 'Desconocido',
                    $estudiante->career ?? 'N/A',
                    $asistencia->created_at->format('H:i:s')
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =====================================================================
    // MÉTODOS PARA ASISTENCIA VOLUNTARIA POR LABORATORIO
    // =====================================================================

    /**
     * API JSON: Retorna las visitas a laboratorios.
     * Parámetros GET opcionales:
     *   - lab_id       : ID del laboratorio (o 'TODOS')
     *   - fecha_desde  : fecha inicio del rango (Y-m-d)
     *   - fecha_hasta  : fecha fin del rango   (Y-m-d)
     * Por defecto devuelve los últimos 7 días (incluyendo hoy).
     */
    public function getLabVisitasApi(Request $request)
    {
        $query = LabVisit::with(['student', 'laboratory'])
            ->orderByDesc('entry_time');

        if ($request->filled('lab_id') && $request->lab_id !== 'TODOS') {
            $query->where('laboratory_id', $request->lab_id);
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
                $diff = $v->entry_time->diff($v->exit_time);
                $duracion = $diff->h . 'h ' . $diff->i . 'min';
            }

            return [
                'id'               => $v->id,
                'carnet'           => $v->student->user_code ?? 'N/A',
                'nombre'           => $v->student->name ?? 'Desconocido',
                'laboratorio'      => $v->laboratory->name ?? 'Desconocido',
                'laboratory_id'    => $v->laboratory_id,
                'entry_time'       => $v->entry_time?->format('H:i:s'),
                'exit_time'        => $v->exit_time?->format('H:i:s'),
                'duracion'         => $duracion,
                'auto_closed'      => $v->auto_closed,
                'no_exit_warning'  => $v->no_exit_warning,
                'fecha'            => $v->entry_time?->format('d/m/Y'),
            ];
        });

        return response()->json($visitas);
    }

    /**
     * API JSON: Lista todos los laboratorios con su token QR y URL de impresión.
     */
    public function getLabsApi()
    {
        $labs = Laboratory::all()->map(function ($lab) {
            return [
                'id'          => $lab->id,
                'name'        => $lab->name,
                'qr_token'    => $lab->qr_token,
                'print_url'   => route('admin.lab.imprimir', $lab->id),
                'scan_url'    => $lab->qr_token ? url('/lab-qr/' . $lab->qr_token) : null,
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

        if (empty($lab->qr_token)) {
            return back()->with('error', 'Este laboratorio aún no tiene un QR generado. Ejecuta: php artisan lab:generar-qr');
        }

        $qrUrl = url('/lab-qr/' . $lab->qr_token);

        return view('lab.imprimir-qr', compact('lab', 'qrUrl'));
    }

    // =====================================================================
    // FINALIZAR VISITA (admin cierra una práctica libre abierta)
    // =====================================================================

    /**
     * Finaliza una visita abierta: registra exit_time = ahora y auto_closed = true.
     * POST /api/admin/lab-visitas/{id}/finalizar
     */
    public function finalizarVisita($id)
    {
        $visita = LabVisit::find($id);

        if (!$visita) {
            return response()->json(['ok' => false, 'mensaje' => 'Visita no encontrada.'], 404);
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

    // =====================================================================
    // ALERTAS: ESTUDIANTES CON 3+ CIERRES AUTOMÁTICOS
    // =====================================================================

    /**
     * Alertas ACTIVAS: estudiantes con 3+ visitas auto-cerradas SIN perdonán (no_exit_warning=true).
     * Cuando el estudiante escanea salida correctamente, no_exit_warning se limpia
     * y este estudiante deja de aparecer en las alertas.
     * GET /api/admin/alertas-cierre-auto
     */
    public function getAlertasCierreAutoApi()
    {
        $alertas = DB::table('lab_visits')
            ->join('users', 'lab_visits.student_id', '=', 'users.id')
            ->select(
                'users.id as student_id',
                'users.name as nombre',
                'users.user_code as carnet',
                DB::raw('COUNT(*) as total_cierres'),
                DB::raw('SUM(CASE WHEN lab_visits.no_exit_warning = true THEN 1 ELSE 0 END) as alertas_activas')
            )
            ->where('lab_visits.auto_closed', true)
            ->groupBy('users.id', 'users.name', 'users.user_code')
            ->having('total_cierres', '>=', 3)
            ->having('alertas_activas', '>', 0)
            ->orderByDesc('total_cierres')
            ->get();

        return response()->json($alertas);
    }

    /**
     * Historial completo: ranking de estudiantes que ALGUNA VEZ tuvieron cierres automáticos,
     * incluyendo los que ya fueron perdonados (para métricas históricas).
     * GET /api/admin/historial-alertas
     */
    public function getHistorialAlertasApi()
    {
        $historial = DB::table('lab_visits')
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
            ->where('lab_visits.auto_closed', true)
            ->groupBy('users.id', 'users.name', 'users.user_code')
            ->orderByDesc('total_cierres')
            ->limit(20)
            ->get();

        return response()->json($historial);
    }
}