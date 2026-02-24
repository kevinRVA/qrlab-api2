<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Session;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * ESCANEAR QR: Registra la asistencia del alumno en la sesión activa
     */
    public function scan(Request $request)
    {
        // 1. Validamos los datos que envía la app React Native
        $request->validate([
            'qr_token' => 'required|string', // El token que leyó la cámara
            'student_name' => 'required|string|max:255',
            'student_code' => 'required|string|max:50', // El carné
            'career' => 'required|string|max:255',
        ]);

        // 2. Buscamos si existe una sesión ACTIVA con ese token exacto
        $session = Session::where('qr_token', $request->qr_token)
            ->where('is_active', true)
            ->first();

        // Si no se encuentra o ya la cerró el docente, rechazamos el registro
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Error: El código QR es inválido o la clase ya finalizó.'
            ], 404);
        }

        // OPCIONAL: Verificar si el alumno ya se registró antes en esta misma clase
        $alreadyRegistered = Attendance::where('session_id', $session->id)
            ->where('student_code', $request->student_code)
            ->exists();

        if ($alreadyRegistered) {
            return response()->json([
                'success' => false,
                'message' => 'Ya estás registrado en esta clase.'
            ], 400); // 400 Bad Request
        }

        // 3. Guardamos la asistencia vinculada al ID de esa sesión
        $attendance = Attendance::create([
            'session_id' => $session->id,
            'student_name' => $request->student_name,
            'student_code' => $request->student_code,
            'career' => $request->career,
        ]);

        // 4. Respondemos a la app móvil con éxito
        return response()->json([
            'success' => true,
            'message' => 'Asistencia registrada correctamente en ' . $session->subject,
            'data' => $attendance
        ], 201);
    }
}