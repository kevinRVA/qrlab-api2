<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\AttendanceWebController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\LabQrController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CoordinatorDashboardController;
use App\Models\User;

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
Route::middleware(['auth', \App\Http\Middleware\NoCacheHeaders::class])->group(function () {

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
    | Panel Administrador / Coordinador
    |----------------------------------------------------------------------
    */
    Route::middleware('role:' . User::ROLE_ADMIN . '|' . User::ROLE_COORDINATOR)->group(function () {

        Route::get('/admin', function(Request $request) {
            return $request->user()->role === User::ROLE_ADMIN 
                ? app(AdminDashboardController::class)->index() 
                : app(CoordinatorDashboardController::class)->index();
        })->name('admin.index');

        // Sub-módulos del panel
        Route::get('/admin/asistencia', function(Request $request) {
            return $request->user()->role === User::ROLE_ADMIN 
                ? app(AdminDashboardController::class)->asistencia() 
                : app(CoordinatorDashboardController::class)->asistencia();
        })->name('admin.asistencia');

        Route::get('/admin/practicas-libres', function(Request $request) {
            return $request->user()->role === User::ROLE_ADMIN 
                ? app(AdminDashboardController::class)->practicasLibres() 
                : app(CoordinatorDashboardController::class)->practicasLibres();
        })->name('admin.practicas-libres');

        Route::get('/admin/alertas-cierre', function(Request $request) {
            return $request->user()->role === User::ROLE_ADMIN 
                ? app(AdminDashboardController::class)->alertasCierre() 
                : app(CoordinatorDashboardController::class)->alertasCierre();
        })->name('admin.alertas-cierre');

        Route::get('/admin/lab-qr/{id}/imprimir', function(Request $request, $id) {
            return $request->user()->role === User::ROLE_ADMIN 
                ? app(AdminDashboardController::class)->printLabQr($id) 
                : app(CoordinatorDashboardController::class)->printLabQr($id);
        })->name('admin.lab.imprimir');

        // APIs JSON del dashboard consumidas por las vistas Blade
        Route::get('/api/admin/sesiones', function(Request $request) {
            return $request->user()->role === User::ROLE_ADMIN 
                ? app(AdminDashboardController::class)->getSesionesApi() 
                : app(CoordinatorDashboardController::class)->getSesionesApi();
        });

        Route::get('/api/admin/descargar-reporte/{id}', function(Request $request, $id, \App\Services\ReportService $reportService) {
            return $request->user()->role === User::ROLE_ADMIN 
                ? app(AdminDashboardController::class)->descargarReporte($id, $reportService) 
                : app(CoordinatorDashboardController::class)->descargarReporte($id, $reportService);
        });

        Route::get('/api/admin/lab-visitas', function(Request $request) {
            return $request->user()->role === User::ROLE_ADMIN 
                ? app(AdminDashboardController::class)->getLabVisitasApi($request) 
                : app(CoordinatorDashboardController::class)->getLabVisitasApi($request);
        });

        Route::post('/api/admin/lab-visitas/{id}/finalizar', function(Request $request, $id) {
            return $request->user()->role === User::ROLE_ADMIN 
                ? app(AdminDashboardController::class)->finalizarVisita($id) 
                : app(CoordinatorDashboardController::class)->finalizarVisita($id);
        });

        Route::get('/api/admin/labs', function(Request $request) {
            return $request->user()->role === User::ROLE_ADMIN 
                ? app(AdminDashboardController::class)->getLabsApi() 
                : app(CoordinatorDashboardController::class)->getLabsApi();
        });

        Route::get('/api/admin/alertas-cierre-auto', function(Request $request) {
            return $request->user()->role === User::ROLE_ADMIN 
                ? app(AdminDashboardController::class)->getAlertasCierreAutoApi() 
                : app(CoordinatorDashboardController::class)->getAlertasCierreAutoApi();
        });

        Route::get('/api/admin/historial-alertas', function(Request $request) {
            return $request->user()->role === User::ROLE_ADMIN 
                ? app(AdminDashboardController::class)->getHistorialAlertasApi() 
                : app(CoordinatorDashboardController::class)->getHistorialAlertasApi();
        });

    });

    /*
    |----------------------------------------------------------------------
    | Panel Administrador Exclusivo (rol: admin)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:' . User::ROLE_ADMIN)->group(function () {
        // Vista de instructores
        Route::get('/admin/instructors', [AdminDashboardController::class, 'instructorsIndex'])->name('admin.instructors');
        
        // Rutas de Configuración del Sistema (Coordinadores y Labs)
        Route::get('/admin/configuracion', [\App\Http\Controllers\ConfiguracionSistemaController::class, 'index'])->name('admin.configuracion');

        // Instructores
        Route::get('/api/admin/system/instructors', [AdminDashboardController::class, 'getInstructorsApi']);
        Route::post('/api/admin/system/instructors/assign', [AdminDashboardController::class, 'assignInstructor']);
        Route::post('/api/admin/system/instructors/remove', [AdminDashboardController::class, 'removeInstructorAssignment']);

        // CRUD Coordinadores
        Route::get('/api/admin/system/coordinators', [\App\Http\Controllers\ConfiguracionSistemaController::class, 'getCoordinators']);
        Route::post('/api/admin/system/coordinators', [\App\Http\Controllers\ConfiguracionSistemaController::class, 'storeCoordinator']);
        Route::put('/api/admin/system/coordinators/{id}', [\App\Http\Controllers\ConfiguracionSistemaController::class, 'updateCoordinator']);
        Route::delete('/api/admin/system/coordinators/{id}', [\App\Http\Controllers\ConfiguracionSistemaController::class, 'destroyCoordinator']);

        // CRUD Laboratorios
        Route::get('/api/admin/system/labs', [\App\Http\Controllers\ConfiguracionSistemaController::class, 'getLabs']);
        Route::post('/api/admin/system/labs', [\App\Http\Controllers\ConfiguracionSistemaController::class, 'storeLab']);
        Route::put('/api/admin/system/labs/{id}', [\App\Http\Controllers\ConfiguracionSistemaController::class, 'updateLab']);
        Route::delete('/api/admin/system/labs/{id}', [\App\Http\Controllers\ConfiguracionSistemaController::class, 'destroyLab']);

        // Asignación de Laboratorios
        Route::post('/api/admin/system/coordinators/{id}/labs', [\App\Http\Controllers\ConfiguracionSistemaController::class, 'assignLabs']);
    });

    /*
    |----------------------------------------------------------------------
    | Panel Docente  (rol: teacher)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:' . User::ROLE_TEACHER)->group(function () {

        Route::get('/docente',                              [TeacherController::class, 'index'])->name('docente.index');
        Route::post('/docente/sesion',                      [TeacherController::class, 'createSession']);
        Route::post('/docente/sesion/finalizar',            [TeacherController::class, 'finishSession']);
        Route::get('/docente/sesion/{id}/descargar',        [TeacherController::class, 'descargarReporte'])->name('docente.sesion.descargar');
    });

    /*
    |----------------------------------------------------------------------
    | Panel Estudiante  (rol: student)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:' . User::ROLE_STUDENT)->group(function () {
        Route::get('/perfil', [StudentController::class, 'index'])->name('student.perfil');
        
        // Rutas de Instructor (Tutor)
        Route::get('/estudiante/instructor', [\App\Http\Controllers\InstructorController::class, 'index'])->name('instructor.index');
        Route::post('/estudiante/instructor/sesion', [\App\Http\Controllers\InstructorController::class, 'createSession']);
        Route::post('/estudiante/instructor/sesion/finalizar', [\App\Http\Controllers\InstructorController::class, 'finishSession']);
        Route::get('/estudiante/instructor/sesion/{id}/descargar', [\App\Http\Controllers\InstructorController::class, 'descargarReporte'])->name('instructor.sesion.descargar');
    });
});
