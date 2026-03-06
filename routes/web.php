<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\AttendanceWebController;
use App\Http\Controllers\TeacherController;

// Rutas Públicas
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);


// Rutas Protegidas (Solo usuarios logueados)
Route::middleware('auth')->group(function () {
    
    // Cerrar Sesión
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // Paneles (Asumiré que ya tienes estas vistas o las crearemos luego)
    Route::get('/admin', function () { return view('admin'); });
    Route::get('/docente', function () { return view('docente'); });
    Route::get('/perfil', function () { return "Bienvenido Estudiante. Escanea un QR para tu asistencia."; });
    Route::get('/docente', [TeacherController::class, 'index'])->name('docente.index');
    Route::post('/docente/sesion', [TeacherController::class, 'createSession']);

    // LA RUTA MÁGICA DEL CÓDIGO QR
    Route::get('/asistencia/{token}', [AttendanceWebController::class, 'registrar'])->name('asistencia.registrar');

});