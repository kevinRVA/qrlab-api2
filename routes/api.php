<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;    

// ENTORNOS DOCENTE Y ADMIN (App Web)
Route::post('/docente/iniciar-clase', [SessionController::class, 'start']);
Route::post('/docente/finalizar-clase/{token}', [SessionController::class, 'end']);
Route::get('/admin/sesiones', [SessionController::class, 'index']); // Para el dashboard del admin

// Ruta para que la app móvil inicie sesión
Route::post('/login', [AuthController::class, 'login']);
Route::get('/admin/descargar-reporte/{id}', [SessionController::class, 'downloadReport']);
// ENTORNO ESTUDIANTE (App React Native)
Route::post('/estudiante/escanear-qr', [AttendanceController::class, 'scan']);