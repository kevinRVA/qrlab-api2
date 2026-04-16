<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\AttendanceWebController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\LabQrController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
| Accesibles sin autenticación.
*/

Route::get('/', fn () => redirect()->route('login'));
Route::get('/login',  [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (requieren autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Cerrar sesión
    Route::match(['get', 'post'], '/logout', [WebAuthController::class, 'logout'])->name('logout');

    // ── Asistencia por QR de clase (docente genera QR, estudiante escanea) ─
    Route::get('/asistencia/{token}', [AttendanceWebController::class, 'registrar'])
         ->name('asistencia.registrar');

    // ── Asistencia voluntaria por laboratorio ─────────────────────────────
    Route::get('/lab-qr/{token}', [LabQrController::class, 'scan'])
         ->name('lab.qr.scan');

    /*
    |----------------------------------------------------------------------
    | Panel Administrador  (rol: admin)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {

        Route::get('/admin', [AdminController::class, 'index'])
             ->name('admin.index');

        // Sub-módulos del panel admin
        Route::get('/admin/asistencia', [AdminController::class, 'asistencia'])
             ->name('admin.asistencia');
        Route::get('/admin/practicas-libres', [AdminController::class, 'practicasLibres'])
             ->name('admin.practicas-libres');

        // Imprimir QR estático de un laboratorio
        Route::get('/admin/lab-qr/{id}/imprimir', [AdminController::class, 'printLabQr'])
             ->name('admin.lab.imprimir');

        // APIs JSON del dashboard
        Route::get('/api/admin/sesiones',               [AdminController::class, 'getSesionesApi']);
        Route::get('/api/admin/descargar-reporte/{id}', [AdminController::class, 'descargarReporte']);
        Route::get('/api/admin/lab-visitas',            [AdminController::class, 'getLabVisitasApi']);
        Route::get('/api/admin/labs',                   [AdminController::class, 'getLabsApi']);
    });

    /*
    |----------------------------------------------------------------------
    | Panel Docente  (rol: teacher)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:teacher')->group(function () {

        Route::get('/docente',                   [TeacherController::class, 'index'])
             ->name('docente.index');
        Route::post('/docente/sesion',           [TeacherController::class, 'createSession']);
        Route::post('/docente/sesion/finalizar', [TeacherController::class, 'finishSession']);
    });

    /*
    |----------------------------------------------------------------------
    | Panel Estudiante  (rol: student)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:student')->group(function () {

        Route::get('/perfil', [StudentController::class, 'index'])
             ->name('student.perfil');
    });
});
