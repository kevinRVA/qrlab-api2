<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LabVisit;
use Carbon\Carbon;

class CerrarVisitasAbiertas extends Command
{
    /**
     * Nombre del comando artisan.
     * Ejecutar manualmente: php artisan visitas:cerrar
     * Se programa automáticamente cada día a las 00:00.
     */
    protected $signature   = 'visitas:cerrar';
    protected $description = 'Cierra las visitas de laboratorio que no tienen salida registrada (cron diario 00:00)';

    public function handle(): int
    {
        // Buscamos visitas del día ANTERIOR que no tienen exit_time
        $ayer = Carbon::yesterday();

        $visitasAbiertas = LabVisit::whereDate('entry_time', $ayer)
            ->whereNull('exit_time')
            ->get();

        if ($visitasAbiertas->isEmpty()) {
            $this->info('No hay visitas abiertas de ayer. Todo en orden.');
            return 0;
        }

        foreach ($visitasAbiertas as $visita) {
            // Cerramos la visita a las 23:59:59 del día de entrada
            $cierreAutomatico = Carbon::parse($visita->entry_time)
                ->endOfDay(); // = 23:59:59 del mismo día

            $visita->update([
                'exit_time'        => $cierreAutomatico,
                'auto_closed'      => true,
                'no_exit_warning'  => true,
            ]);
        }

        $this->info("Se cerraron {$visitasAbiertas->count()} visita(s) abiertas de ayer.");
        return 0;
    }
}
