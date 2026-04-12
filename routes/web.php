<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\AttendanceWebController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\LabQrController;

// Rutas Públicas
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
// PANEL ADMIN: Solo entra si el rol es 'admin'
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index')->middleware('role:admin');
    
    // Rutas API para el Dashboard del Admin (También protegidas)
    Route::get('/api/admin/sesiones', [AdminController::class, 'getSesionesApi'])->middleware('role:admin');
    Route::get('/api/admin/descargar-reporte/{id}', [AdminController::class, 'descargarReporte'])->middleware('role:admin');

// Rutas Protegidas (Solo usuarios logueados)
Route::middleware('auth')->group(function () {

    // Cerrar Sesión
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // Paneles (Asumiré que ya tienes estas vistas o las crearemos luego)
    Route::get('/admin', function () {
        return view('admin');
    });
    Route::get('/docente', function () {
        return view('docente');
    });
    Route::get('/perfil', function () {
        return "Bienvenido Estudiante. Escanea un QR para tu asistencia.";
    });
    Route::get('/docente', [TeacherController::class, 'index'])->name('docente.index');
    Route::post('/docente/sesion', [TeacherController::class, 'createSession']);

    // LA RUTA MÁGICA DEL CÓDIGO QR
    Route::get('/asistencia/{token}', [AttendanceWebController::class, 'registrar'])->name('asistencia.registrar');

    // Rutas Protegidas (Solo usuarios logueados)
    Route::middleware('auth')->group(function () {

        Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

        // PANEL ADMIN: Solo entra si el rol es 'admin'
        Route::get('/admin', function () {
            return view('admin');
        })->middleware('role:admin');

        // PANEL DOCENTE: Solo entra si el rol es 'teacher'
        Route::get('/docente', [TeacherController::class, 'index'])->name('docente.index')->middleware('role:teacher');
        Route::post('/docente/sesion', [TeacherController::class, 'createSession'])->middleware('role:teacher');

        // PANEL ESTUDIANTE: Solo entra si el rol es 'student'
        Route::get('/perfil', [StudentController::class, 'index'])->name('student.perfil')->middleware('role:student');

        // LA RUTA MÁGICA DEL CÓDIGO QR (Esta la dejamos solo con 'auth' porque el controlador ya valida que sea estudiante)
        Route::get('/asistencia/{token}', [AttendanceWebController::class, 'registrar'])->name('asistencia.registrar');

        // === ASISTENCIA VOLUNTARIA POR LABORATORIO ===
        // QR estático de cada laboratorio (estudiante escanea para entrada/salida)
        Route::get('/lab-qr/{token}', [LabQrController::class, 'scan'])->name('lab.qr.scan');

        // API Admin: listado de visitas voluntarias (JSON)
        Route::get('/api/admin/lab-visitas', [AdminController::class, 'getLabVisitasApi'])->middleware('role:admin');

        // API Admin: listado de laboratorios con sus tokens
        Route::get('/api/admin/labs', [AdminController::class, 'getLabsApi'])->middleware('role:admin');

        // Admin: página de impresión del QR de un laboratorio
        Route::get('/admin/lab-qr/{id}/imprimir', [AdminController::class, 'printLabQr'])->name('admin.lab.imprimir')->middleware('role:admin');
    });
    // PANEL DOCENTE
    Route::get('/docente', [TeacherController::class, 'index'])->name('docente.index')->middleware('role:teacher');
    Route::post('/docente/sesion', [TeacherController::class, 'createSession'])->middleware('role:teacher');
    Route::post('/docente/sesion/finalizar', [TeacherController::class, 'finishSession'])->middleware('role:teacher'); // <-- NUEVA RUTA
});

