<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CoordinatorDashboardController;
use App\Models\User;

// Ruta Pública
Route::post('/login', [AuthController::class, 'login']);
Route::post('/estudiante/escanear-qr', [AttendanceController::class, 'scan']); // Podría protegerse en el futuro

// Rutas Protegidas por Sanctum / Sesión Web
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Docente
    Route::post('/docente/iniciar-clase', [SessionController::class, 'start']);
    Route::post('/docente/finalizar-clase/{token}', [SessionController::class, 'end']);

});