<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Laboratory;
use Illuminate\Support\Str;

class GenerarQrLaboratorios extends Command
{
    protected $signature   = 'lab:generar-qr';
    protected $description = 'Genera tokens QR estáticos para los laboratorios que aún no los tienen';

    public function handle(): int
    {
        $labs = Laboratory::all();

        if ($labs->isEmpty()) {
            $this->warn('No existen laboratorios en la base de datos. Crea los labs primero.');
            return 1;
        }

        $generados = 0;

        foreach ($labs as $lab) {
            if (empty($lab->qr_token)) {
                $lab->update(['qr_token' => 'LAB-' . strtoupper(Str::random(12))]);
                $this->line("  ✓ Lab [{$lab->id}] {$lab->name} → token generado.");
                $generados++;
            } else {
                $this->line("  — Lab [{$lab->id}] {$lab->name} → ya tiene token, se omite.");
            }
        }

        $this->info("Proceso finalizado. {$generados} token(s) generado(s).");
        return 0;
    }
}
