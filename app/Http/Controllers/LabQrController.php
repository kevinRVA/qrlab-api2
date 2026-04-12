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
     * Primer escaneo del día  → registra ENTRADA
     * Segundo escaneo del día → registra SALIDA
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

        $hoy = Carbon::today();
        $ahora = Carbon::now();

        // 3. ¿Tiene ya una visita ABIERTA hoy en este laboratorio?
        $visitaAbierta = LabVisit::where('student_id', $user->id)
            ->where('laboratory_id', $lab->id)
            ->whereDate('entry_time', $hoy)
            ->whereNull('exit_time')
            ->first();

        if ($visitaAbierta) {
            // — SEGUNDA MARCA: registrar SALIDA —
            $visitaAbierta->update([
                'exit_time' => $ahora,
            ]);

            $duracion = $visitaAbierta->entry_time->diff($ahora);
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

        // 4. ¿Ya marcó salida hoy en este laboratorio? (visita cerrada hoy)
        $visitaCerradaHoy = LabVisit::where('student_id', $user->id)
            ->where('laboratory_id', $lab->id)
            ->whereDate('entry_time', $hoy)
            ->whereNotNull('exit_time')
            ->exists();

        if ($visitaCerradaHoy) {
            return view('lab.resultado', [
                'tipo'        => 'error',
                'mensaje'     => 'Ya registraste tu entrada y salida en este laboratorio hoy. Puedes volver mañana.',
                'laboratorio' => $lab->name,
            ]);
        }

        // — PRIMERA MARCA: registrar ENTRADA —
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
