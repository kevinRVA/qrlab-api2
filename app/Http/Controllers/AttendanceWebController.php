<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Session;
use App\Models\Enrollment;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceWebController extends Controller
{
    public function registrar($token)
    {
        $user = Auth::user();

        // 1. Validar que sea estudiante
        if ($user->role !== 'student') {
            return view('asistencia.resultado', [
                'tipo' => 'error', 
                'mensaje' => 'Solo los estudiantes pueden registrar asistencia.'
            ]);
        }

        // 2. Buscar la sesión por el Token del QR
        $session = Session::where('qr_token', $token)->first();

        if (!$session) {
            return view('asistencia.resultado', ['tipo' => 'error', 'mensaje' => 'Código QR inválido o clase no encontrada.']);
        }

        if (!$session->is_active) {
            return view('asistencia.resultado', ['tipo' => 'error', 'mensaje' => 'Esta clase ya ha finalizado.']);
        }

        // 3. Validar que el estudiante esté inscrito en esa materia/sección
        $inscrito = Enrollment::where('student_id', $user->id)
                              ->where('section_id', $session->section_id)
                              ->exists();

        if (!$inscrito) {
            return view('asistencia.resultado', ['tipo' => 'error', 'mensaje' => 'No estás inscrito en esta sección.']);
        }

        // 4. Registrar la asistencia (firstOrCreate evita duplicados si escanea dos veces)
        Attendance::firstOrCreate([
            'session_id' => $session->id,
            'student_id' => $user->id
        ]);

        // 5. Mostrar pantalla de éxito
        return view('asistencia.resultado', [
            'tipo' => 'exito', 
            'mensaje' => '¡Asistencia registrada exitosamente!',
            'clase' => $session->section->subject->name
        ]);
    }
}