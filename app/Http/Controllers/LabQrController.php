<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Laboratory;
use App\Models\LabVisit;
use Carbon\Carbon;

class LabQrController extends Controller
{
    /**
     * Procesa el escaneo del QR estático de un laboratorio.
     * Si hay visita abierta → registra SALIDA
     * Si no hay visita abierta → registra nueva ENTRADA (sin restricción de visitas previas)
     */
    public function scan($token)
    {
        $user = Auth::user();

        // 1. Solo estudiantes pueden marcar asistencia voluntaria
        if ($user->role !== 'student') {
            return view('lab.resultado', [
                'tipo'    => 'error',
                'mensaje' => 'Solo los estudiantes pueden registrar su acceso al laboratorio.',
            ]);
        }

        // 2. Buscar el laboratorio por su token estático
        $lab = Laboratory::where('qr_token', $token)->first();

        if (!$lab) {
            return view('lab.resultado', [
                'tipo'    => 'error',
                'mensaje' => 'Código QR no reconocido. Contacta al administrador.',
            ]);
        }

        $hoy   = Carbon::today();
        $ahora = Carbon::now();

        // 3. ¿Tiene ya una visita ABIERTA en este laboratorio?
        $visitaAbierta = LabVisit::where('student_id', $user->id)
            ->where('laboratory_id', $lab->id)
            ->whereDate('entry_time', $hoy)
            ->whereNull('exit_time')
            ->first();

        if ($visitaAbierta) {
            // — Registrar SALIDA —
            $visitaAbierta->update(['exit_time' => $ahora]);

            $duracion    = $visitaAbierta->entry_time->diff($ahora);
            $duracionStr = $duracion->h . 'h ' . $duracion->i . 'min';

            return view('lab.resultado', [
                'tipo'        => 'salida',
                'mensaje'     => '¡Salida registrada correctamente!',
                'laboratorio' => $lab->name,
                'hora'        => $ahora->format('H:i'),
                'duracion'    => $duracionStr,
                'student'     => $user->name,
            ]);
        }

        // 4. Registrar nueva ENTRADA — múltiples visitas al mismo lab permitidas
        LabVisit::create([
            'laboratory_id' => $lab->id,
            'student_id'    => $user->id,
            'entry_time'    => $ahora,
        ]);

        return view('lab.resultado', [
            'tipo'        => 'entrada',
            'mensaje'     => '¡Entrada registrada! Recuerda escanear de nuevo al salir.',
            'laboratorio' => $lab->name,
            'hora'        => $ahora->format('H:i'),
            'student'     => $user->name,
        ]);
    }
}
