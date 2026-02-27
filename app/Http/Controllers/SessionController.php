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
            'laboratory_name' => 'required|string',
        ]);

        // 2. Creamos la sesión en tu base de datos QRLAB
        $session = Session::create([
            'teacher_name' => $request->teacher_name,
            'teacher_code' => $request->teacher_code,
            'subject' => $request->subject,
            'section' => $request->section,
            'laboratory_name' => $request->laboratory_name,
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
    /**
     * DESCARGAR REPORTE: Genera un archivo CSV (Excel) con la asistencia
     */
    public function downloadReport($id)
    {
        // Buscamos la sesión y traemos a todos los estudiantes registrados
        $session = Session::with('attendances')->findOrFail($id);

        $fileName = "Reporte_Lab_{$session->subject}.csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Nombre del Estudiante', 'Carnet', 'Carrera', 'Hora de Escaneo'];

        $callback = function () use ($session, $columns) {
            $file = fopen('php://output', 'w');
            // Esto arregla los caracteres especiales (tildes, ñ)
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // CAMBIO 1: Agregamos el ';' al final
            fputcsv($file, $columns, ';');

            foreach ($session->attendances as $attendance) {
                // CAMBIO 2: Agregamos el ';' al final del arreglo
                fputcsv($file, [
                    $attendance->student_name,
                    $attendance->student_code,
                    $attendance->career,
                    $attendance->created_at->format('d/m/Y H:i A')
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    /**
     * DASHBOARD ADMIN: Devuelve todas las sesiones con su conteo de alumnos
     */
    public function index()
    {
        // Traemos todas las sesiones, de la más nueva a la más antigua, 
        // y usamos 'withCount' para que Laravel cuente los alumnos por nosotros automáticamente.
        $sessions = Session::withCount('attendances')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($sessions);
    }
}