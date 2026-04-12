<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Session;
use App\Models\Attendance;
use App\Models\Laboratory;
use App\Models\LabVisit;
use Carbon\Carbon;

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

    // =====================================================================
    // MÉTODOS PARA ASISTENCIA VOLUNTARIA POR LABORATORIO
    // =====================================================================

    /**
     * API JSON: Retorna las visitas a laboratorios.
     * Parámetros GET opcionales: lab_id, fecha (Y-m-d)
     */
    public function getLabVisitasApi(Request $request)
    {
        $query = LabVisit::with(['student', 'laboratory'])
            ->orderByDesc('entry_time');

        if ($request->filled('lab_id') && $request->lab_id !== 'TODOS') {
            $query->where('laboratory_id', $request->lab_id);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('entry_time', $request->fecha);
        } else {
            // Por defecto: hoy
            $query->whereDate('entry_time', Carbon::today());
        }

        $visitas = $query->get()->map(function ($v) {
            $duracion = null;
            if ($v->entry_time && $v->exit_time) {
                $diff = $v->entry_time->diff($v->exit_time);
                $duracion = $diff->h . 'h ' . $diff->i . 'min';
            }

            return [
                'id'               => $v->id,
                'carnet'           => $v->student->user_code ?? 'N/A',
                'nombre'           => $v->student->name ?? 'Desconocido',
                'laboratorio'      => $v->laboratory->name ?? 'Desconocido',
                'laboratory_id'    => $v->laboratory_id,
                'entry_time'       => $v->entry_time?->format('H:i:s'),
                'exit_time'        => $v->exit_time?->format('H:i:s'),
                'duracion'         => $duracion,
                'auto_closed'      => $v->auto_closed,
                'no_exit_warning'  => $v->no_exit_warning,
                'fecha'            => $v->entry_time?->format('d/m/Y'),
            ];
        });

        return response()->json($visitas);
    }

    /**
     * API JSON: Lista todos los laboratorios con su token QR y URL de impresión.
     */
    public function getLabsApi()
    {
        $labs = Laboratory::all()->map(function ($lab) {
            return [
                'id'          => $lab->id,
                'name'        => $lab->name,
                'qr_token'    => $lab->qr_token,
                'print_url'   => route('admin.lab.imprimir', $lab->id),
                'scan_url'    => $lab->qr_token ? url('/lab-qr/' . $lab->qr_token) : null,
            ];
        });

        return response()->json($labs);
    }

    /**
     * Vista de impresión del QR estático de un laboratorio.
     */
    public function printLabQr($id)
    {
        $lab = Laboratory::findOrFail($id);

        if (empty($lab->qr_token)) {
            return back()->with('error', 'Este laboratorio aún no tiene un QR generado. Ejecuta: php artisan lab:generar-qr');
        }

        $qrUrl = url('/lab-qr/' . $lab->qr_token);

        return view('lab.imprimir-qr', compact('lab', 'qrUrl'));
    }
}