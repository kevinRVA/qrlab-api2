<?php

namespace App\Services;

use App\Models\Session;
use App\Models\Attendance;

class ReportService
{
    /**
     * Generate a CSV download response for a session's attendance.
     *
     * @param Session $sesion
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadSessionAttendanceCsv(Session $sesion)
    {
        $asistencias = Attendance::with('student')->where('session_id', $sesion->id)->get();

        $materia = $sesion->section->subject->name ?? 'Materia';
        $tipo = $sesion->class_type ?? 'Clase';
        $fecha = $sesion->created_at->format('d-m-Y');
        $fileName = "Asistencia_{$materia}_{$tipo}_{$fecha}.csv";

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($asistencias, $sesion) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($file, ['Carnet / Codigo', 'Nombre del Estudiante', 'Carrera', 'Tipo de Clase', 'Hora de Registro'], ';');

            foreach ($asistencias as $asistencia) {
                $estudiante = $asistencia->student;
                fputcsv($file, [
                    $estudiante->user_code ?? 'N/A',
                    $estudiante->name ?? 'Desconocido',
                    $estudiante->career ?? 'N/A',
                    $sesion->class_type ?? 'Clase',
                    $asistencia->created_at->format('H:i:s'),
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
