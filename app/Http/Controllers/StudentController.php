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

        // 2. Últimas 10 marcaciones de clase: cargamos session → section → subject
        $historial = Attendance::where('student_id', $user->id)
            ->with(['session.section.subject', 'session'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // 3. Últimas 10 visitas voluntarias a laboratorios
        $visitasLab = LabVisit::where('student_id', $user->id)
            ->with('laboratory')
            ->orderByDesc('entry_time')
            ->take(10)
            ->get();

        // 4. Avisos de salidas no marcadas
        $avisosSinSalida = LabVisit::where('student_id', $user->id)
            ->where('no_exit_warning', true)
            ->count();

        return view('estudiante.perfil', compact('user', 'inscripciones', 'historial', 'visitasLab', 'avisosSinSalida'));
    }
}

