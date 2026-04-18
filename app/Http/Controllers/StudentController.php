<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\LabVisit;

class StudentController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Materias inscritas: cargamos section → subject y section → teacher
        $inscripciones = Enrollment::where('student_id', $user->id)
            ->with(['section.subject', 'section.teacher'])
            ->get();

        // 2. Últimas 5 marcaciones de clase
        $historial = Attendance::where('student_id', $user->id)
            ->with(['session.section.subject', 'session'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // 3. Últimas 5 visitas voluntarias a laboratorios
        $visitasLab = LabVisit::where('student_id', $user->id)
            ->with('laboratory')
            ->orderByDesc('entry_time')
            ->take(5)
            ->get();

        // 4. Avisos de salidas no marcadas (auto_closed por el sistema)
        $avisosSinSalida = LabVisit::where('student_id', $user->id)
            ->where('no_exit_warning', true)
            ->count();

        // 5. Cierres automáticos acumulados (históricos)
        $cierresTotales = LabVisit::where('student_id', $user->id)
            ->where('auto_closed', true)
            ->count();

        // 6. Cierres automáticos aún activos (no perdonados por escaneo correcto)
        $cierresActivos = LabVisit::where('student_id', $user->id)
            ->where('auto_closed', true)
            ->where('no_exit_warning', true)
            ->count();

        // 7. Alerta a mostrar si es reincidente y tiene una alerta activa nueva
        $cierresAutoCerrados = ($cierresTotales >= 3 && $cierresActivos > 0) ? $cierresTotales : 0;

        return view('estudiante.perfil', compact(
            'user', 'inscripciones', 'historial',
            'visitasLab', 'avisosSinSalida', 'cierresAutoCerrados'
        ));
    }
}

