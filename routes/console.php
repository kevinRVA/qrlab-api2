<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cierra automáticamente las visitas de laboratorio sin salida, cada día a las 00:00
Schedule::command('visitas:cerrar')->dailyAt('00:00');
