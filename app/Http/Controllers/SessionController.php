<?php

namespace App\Http\Controllers;

use App\Models\Session; // Usamos tu modelo original
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    /**
     * INICIAR CLASE: Genera el registro y devuelve el token para el QR
     */
    public function start(Request $request)
    {
        // 1. Validamos los datos que envía el docente desde la app web
        $request->validate([
            'teacher_name' => 'required|string|max:255',
            'teacher_code' => 'required|string|max:50',
            'subject' => 'required|string|max:255',
            'section' => 'required|string|max:50',
        ]);

        // 2. Creamos la sesión en tu base de datos QRLAB
        $session = Session::create([
            'teacher_name' => $request->teacher_name,
            'teacher_code' => $request->teacher_code,
            'subject' => $request->subject,
            'section' => $request->section,
            'qr_token' => Str::uuid()->toString(), // Genera el token único para el QR
            'is_active' => true,
        ]);

        // 3. Respondemos con el token
        return response()->json([
            'message' => 'Clase iniciada correctamente',
            'qr_token' => $session->qr_token,
            'session_id' => $session->id
        ], 201);
    }

    /**
     * FINALIZAR CLASE: Cierra la sesión y cuenta los estudiantes
     */
    public function end($token)
    {
        // 1. Buscamos la sesión activa usando el token del QR
        $session = Session::where('qr_token', $token)
            ->where('is_active', true)
            ->first();

        // Si no existe o ya se cerró, devolvemos un error
        if (!$session) {
            return response()->json([
                'error' => 'La sesión no existe o ya fue finalizada'
            ], 404);
        }

        // 2. Cambiamos el estado a inactivo
        $session->update([
            'is_active' => false
        ]);

        // 3. Contamos cuántos alumnos se registraron (usando la relación que definimos)
        $studentCount = $session->attendances()->count();

        // 4. Devolvemos la data final
        return response()->json([
            'message' => 'Clase finalizada exitosamente',
            'total_students' => $studentCount,
            'download_url' => url("/api/admin/descargar-reporte/{$session->id}")
        ], 200);
    }
}