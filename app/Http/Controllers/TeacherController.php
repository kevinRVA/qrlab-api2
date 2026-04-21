<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Section;
use App\Models\Laboratory;
use App\Models\Session;
use App\Models\Attendance;

class TeacherController extends Controller
{
    // Carga la pantalla principal del Docente
    public function index()
    {
        $teacher = Auth::user();
        
        // Buscamos solo las secciones asignadas a este profesor, e incluimos el nombre de la materia
        $sections = Section::with('subject')->where('teacher_id', $teacher->id)->get();
        $laboratories = Laboratory::all();

        // Buscamos sesiones activas que este profesor haya olvidado cerrar
        $activeSessions = Session::with(['section.subject'])
            ->whereHas('section', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('is_active', true)
            ->get();

        // Últimas 10 clases finalizadas (historial del docente)
        $recentSessions = Session::with(['section.subject'])
            ->withCount('attendances')
            ->whereHas('section', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('is_active', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('docente.index', compact('teacher', 'sections', 'laboratories', 'activeSessions', 'recentSessions'));
    }

    // Crea la sesión en la base de datos y devuelve la URL para el QR
    public function createSession(Request $request)
    {
        $request->validate([
            'section_id'      => 'required|exists:sections,id',
            'laboratory_name' => 'required|string',
            'class_type'      => 'required|in:Clase,Parcial,Reposicion',
        ]);

        $token = Str::random(10); // Creamos el código único

        $session = Session::create([
            'section_id'      => $request->section_id,
            'laboratory_name' => $request->laboratory_name,
            'class_type'      => $request->class_type,
            'qr_token'        => $token,
            'is_active'       => true,
        ]);

        // Generamos la URL COMPLETA apuntando a tu IP
        $qrUrl = url('/asistencia/' . $token);

        return response()->json([
            'success' => true,
            'qr_url' => $qrUrl,
            'session_id' => $session->id
        ]);
    }
    // Finaliza la sesión actual (Invalida el QR)
    public function finishSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:sessions,id'
        ]);

        $session = Session::find($request->session_id);

        // Medida de seguridad: Validar que la sesión pertenezca a una clase de este maestro
        $teacher = Auth::user();
        if ($session->section->teacher_id !== $teacher->id) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        // Apagamos el QR
        $session->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }

    // Descarga la lista de asistencia de una sesión propia del docente
    public function descargarReporte($id, \App\Services\ReportService $reportService)
    {
        $teacher = Auth::user();

        // Sólo puede descargar sesiones de sus propias secciones
        $sesion = Session::with(['section.subject', 'section.teacher'])
            ->whereHas('section', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->findOrFail($id);

        return $reportService->downloadSessionAttendanceCsv($sesion);
    }
}