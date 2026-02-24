use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\AttendanceController;

// ENTORNOS DOCENTE Y ADMIN (App Web)
Route::post('/docente/iniciar-clase', [SessionController::class, 'start']);
Route::post('/docente/finalizar-clase/{token}', [SessionController::class, 'end']);
Route::get('/admin/sesiones', [SessionController::class, 'index']); // Para el dashboard del admin

// ENTORNO ESTUDIANTE (App React Native)
Route::post('/estudiante/escanear-qr', [AttendanceController::class, 'scan']);