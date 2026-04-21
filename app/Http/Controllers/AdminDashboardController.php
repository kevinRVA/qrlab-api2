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

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'students'          => User::where('role', User::ROLE_STUDENT)->count(),
            'teachers'          => User::where('role', User::ROLE_TEACHER)->count(),
            'active_sessions'   => Session::where('is_active', true)->count(),
            'total_attendances' => Attendance::count(),
        ];

        return view('admin.index', compact('stats'));
    }

    public function asistencia()
    {
        return view('admin.asistencia');
    }

    public function practicasLibres()
    {
        return view('admin.practicas-libres');
    }

    public function alertasCierre()
    {
        return view('admin.alertas-cierre');
    }

    public function getSesionesApi()
    {
        $sesiones = Session::with(['section.teacher', 'section.subject'])
            ->withCount('attendances')
            ->orderBy('created_at', 'desc')
            ->get();

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

    public function descargarReporte($id, \App\Services\ReportService $reportService)
    {
        $sesion = Session::with(['section.subject', 'section.teacher'])->findOrFail($id);
        return $reportService->downloadSessionAttendanceCsv($sesion);
    }

    public function getLabVisitasApi(Request $request)
    {
        $query = LabVisit::with(['student', 'laboratory'])->orderByDesc('entry_time');

        if ($request->filled('lab_id') && $request->lab_id !== 'TODOS') {
            $query->where('laboratory_id', $request->lab_id);
        }

        $desde = $request->filled('fecha_desde') ? Carbon::parse($request->fecha_desde)->startOfDay() : Carbon::today()->subDays(6)->startOfDay();
        $hasta = $request->filled('fecha_hasta') ? Carbon::parse($request->fecha_hasta)->endOfDay() : Carbon::today()->endOfDay();

        $query->whereBetween('entry_time', [$desde, $hasta]);

        $visitas = $query->get()->map(function ($v) {
            $duracion = null;
            if ($v->entry_time && $v->exit_time) {
                $diff = $v->entry_time->diff($v->exit_time);
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

    public function getLabsApi()
    {
        $labs = Laboratory::all()->map(function ($lab) {
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

    public function printLabQr($id)
    {
        $lab = Laboratory::findOrFail($id);
        if (empty($lab->qr_token)) {
            return back()->with('error', 'Este laboratorio aún no tiene un QR generado.');
        }
        $qrUrl = url('/lab-qr/' . $lab->qr_token);
        return view('lab.imprimir-qr', compact('lab', 'qrUrl'));
    }

    public function finalizarVisita($id)
    {
        $visita = LabVisit::find($id);
        if (!$visita) return response()->json(['ok' => false, 'mensaje' => 'Visita no encontrada.'], 404);
        if ($visita->exit_time) return response()->json(['ok' => false, 'mensaje' => 'Esta visita ya fue finalizada.'], 422);

        $visita->update(['exit_time' => Carbon::now(), 'auto_closed' => true, 'no_exit_warning' => true]);
        return response()->json(['ok' => true, 'mensaje' => 'Visita finalizada correctamente.']);
    }

    public function getAlertasCierreAutoApi()
    {
        $alertas = DB::table('lab_visits')
            ->join('users', 'lab_visits.student_id', '=', 'users.id')
            ->select('users.id as student_id', 'users.name as nombre', 'users.user_code as carnet',
                DB::raw('COUNT(*) as total_cierres'),
                DB::raw('SUM(CASE WHEN lab_visits.no_exit_warning = true THEN 1 ELSE 0 END) as alertas_activas'))
            ->where('lab_visits.auto_closed', true)
            ->groupBy('users.id', 'users.name', 'users.user_code')
            ->having('total_cierres', '>=', 3)
            ->having('alertas_activas', '>', 0)
            ->orderByDesc('total_cierres')
            ->get();
        return response()->json($alertas);
    }

    public function getHistorialAlertasApi()
    {
        $historial = DB::table('lab_visits')
            ->join('users', 'lab_visits.student_id', '=', 'users.id')
            ->join('laboratories', 'lab_visits.laboratory_id', '=', 'laboratories.id')
            ->select('users.id as student_id', 'users.name as nombre', 'users.user_code as carnet',
                DB::raw('COUNT(*) as total_cierres'),
                DB::raw('SUM(CASE WHEN lab_visits.no_exit_warning = true THEN 1 ELSE 0 END) as alertas_activas'),
                DB::raw('MAX(lab_visits.entry_time) as ultima_visita'))
            ->where('lab_visits.auto_closed', true)
            ->groupBy('users.id', 'users.name', 'users.user_code')
            ->orderByDesc('total_cierres')
            ->limit(20)
            ->get();
        return response()->json($historial);
    }
}
