<?php

use Illuminate\Support\Facades\Route;
use App\Models\Laboratory; // No olvides importar el modelo

Route::get('/', function () {
    return view('welcome');
});

// Ruta modificada para enviar los laboratorios
Route::get('/docente', function () {
    $laboratorios = Laboratory::all();
    return view('docente', compact('laboratorios'));
});
// Ruta para el Dashboard del Administrador de Laboratorios
Route::get('/admin', function () {
    return view('admin');
});