<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Section;
use App\Models\Laboratory;
use App\Models\Session;

class InstructorController extends Controller
{
    // Carga la pantalla principal del Instructor
    public function index()
    {
        $instructor = Auth::user();

        // Validar que sea instructor
        if (!$instructor->is_instructor) {
            return redirect('/perfil')->with('error', 'No tienes permisos de instructor.');
        }
        
        // Buscamos solo las secciones asignadas a este instructor
        $sections = $instructor->instructorSections()->with('subject')->get();
        $laboratories = Laboratory::all();

        // Buscamos sesiones activas creadas en secciones de este instructor
        $sectionIds = $sections->pluck('id')->toArray();

        $activeSessions = Session::with(['section.subject'])
            ->whereIn('section_id', $sectionIds)
            ->where('is_active', true)
            ->get();

        // Últimas 10 clases finalizadas (historial)
        $recentSessions = Session::with(['section.subject'])
            ->withCount('attendances')
            ->whereIn('section_id', $sectionIds)
            ->where('is_active', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('estudiante.instructor', compact('instructor', 'sections', 'laboratories', 'activeSessions', 'recentSessions'));
    }

    // Crea la sesión en la base de datos
    public function createSession(Request $request)
    {
        $request->validate([
            'section_id'      => 'required|exists:sections,id',
            'laboratory_name' => 'required|string',
            'class_type'      => 'required|in:Clase,Parcial,Reposicion',
        ]);

        $instructor = Auth::user();

        // Validar que el instructor esté asignado a esta sección
        if (!$instructor->instructorSections()->where('section_id', $request->section_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'No estás asignado a esta clase'], 403);
        }

        $token = Str::random(10); // Creamos el código único

        $session = Session::create([
            'section_id'      => $request->section_id,
            'laboratory_name' => $request->laboratory_name,
            'class_type'      => $request->class_type,
            'qr_token'        => $token,
            'is_active'       => true,
        ]);

        $qrUrl = url('/asistencia/' . $token);

        return response()->json([
            'success' => true,
            'qr_url' => $qrUrl,
            'session_id' => $session->id
        ]);
    }

    // Finaliza la sesión actual
    public function finishSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:sessions,id'
        ]);

        $session = Session::find($request->session_id);
        $instructor = Auth::user();

        // Validar que la sesión pertenezca a una clase de este instructor
        if (!$instructor->instructorSections()->where('section_id', $session->section_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        // Apagamos el QR
        $session->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }

    // Descarga la lista de asistencia
    public function descargarReporte($id, \App\Services\ReportService $reportService)
    {
        $instructor = Auth::user();

        $sesion = Session::with(['section.subject', 'section.teacher'])
            ->whereIn('section_id', $instructor->instructorSections()->pluck('sections.id'))
            ->findOrFail($id);

        return $reportService->downloadSessionAttendanceCsv($sesion);
    }
}
