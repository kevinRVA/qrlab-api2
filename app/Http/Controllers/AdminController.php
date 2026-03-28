<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Session;
use App\Models\Attendance;

class AdminController extends Controller
{
    // Esta es la función que ya tenías
    public function index()
    {
        $stats = [
            'students' => User::where('role', 'student')->count(),
            'teachers' => User::where('role', 'teacher')->count(),
            'active_sessions' => Session::where('is_active', true)->count(),
            'total_attendances' => Attendance::count()
        ];

        return view('admin', compact('stats'));
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
}