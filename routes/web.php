<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ComputadoraController;
use App\Http\Controllers\SystemStatsController;

// ==================== AUTH ====================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::redirect('/', '/login');
Route::post('/logout', [AuthController::class, 'logout'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('logout');

// ==================== GESTIÓN DE SESIÓN ====================
// Ping de actividad — reinicia el contador server-side (requiere sesión activa)
Route::post('/session/ping', [AuthController::class, 'sessionPing'])
    ->middleware('verificar.sesion')
    ->name('session.ping');

// Cierre silencioso por pestaña cerrada — enviado via sendBeacon
// No aplica CSRF middleware para que el beacon lo alcance siempre
Route::post('/session/close', [AuthController::class, 'sessionClose'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('session.close');

// ==================== ACCESO DENEGADO ====================
Route::get('/acceso-denegado', function () {
    if (!session('loggedin')) return redirect('/');
    return view('acceso_denegado');
})->name('acceso.denegado');

// ==================== DASHBOARD ====================
// Todos los roles autenticados pueden ver el dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('dashboard');

// ==================== EXPLORADOR ====================
// Todos los roles pueden navegar el explorador
Route::get('/explorador/datos', [FileController::class, 'explorador'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('explorador.datos');

// ==================== DESCARGAR ====================
// Todos los roles pueden descargar
Route::get('/descargar', [FileController::class, 'descargar'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('descargar');

// ==================== RENOMBRAR ====================
// Administrador, supervisor y secretario pueden renombrar (renombrar carpetas es función básica)
Route::post('/explorador/renombrar', [FileController::class, 'renombrar'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('explorador.renombrar');

// ==================== ELIMINAR ====================
// Solo el administrador puede eliminar (mover a papelera)
Route::post('/explorador/eliminar', [FileController::class, 'eliminar'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador'])
    ->name('explorador.eliminar');

// ==================== PAPELERA ====================
// Solo el administrador tiene acceso a la papelera
Route::get('/explorador/papelera', [FileController::class, 'listarPapelera'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador'])
    ->name('explorador.papelera');

Route::post('/explorador/restaurar', [FileController::class, 'restaurar'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador'])
    ->name('explorador.restaurar');

Route::post('/explorador/eliminar-permanente', [FileController::class, 'eliminarPermanente'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador'])
    ->name('explorador.eliminarPermanente');

Route::post('/explorador/vaciar-papelera', [FileController::class, 'vaciarPapelera'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador'])
    ->name('explorador.vaciarPapelera');

// ==================== USUARIOS (ADMINISTRADOR) ====================
Route::post('/usuarios/store', [\App\Http\Controllers\UserController::class, 'store'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador'])
    ->name('usuarios.store');

Route::put('/usuarios/{id}', [\App\Http\Controllers\UserController::class, 'update'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador'])
    ->name('usuarios.update');

Route::delete('/usuarios/{id}', [\App\Http\Controllers\UserController::class, 'destroy'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador'])
    ->name('usuarios.destroy');

// ── Perfil propio (cualquier rol autenticado) ──
Route::put('/perfil', [\App\Http\Controllers\UserController::class, 'updatePerfil'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('perfil.update');

// ==================== SERVIDORES EXTERNOS ====================
// Administrador gestiona
Route::post('/servidores/store', [\App\Http\Controllers\ServerController::class, 'store'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador'])
    ->name('servidores.store');

Route::put('/servidores/{id}', [\App\Http\Controllers\ServerController::class, 'update'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador'])
    ->name('servidores.update');

Route::delete('/servidores/{id}', [\App\Http\Controllers\ServerController::class, 'destroy'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador'])
    ->name('servidores.destroy');

// ==================== EXPLORADOR REMOTO ====================
Route::get('/remoto/{servidor_id}', [\App\Http\Controllers\RemoteDashboardController::class, 'index'])
    ->middleware(['verificar.sesion'])
    ->name('remoto.index');

Route::get('/remoto/{servidor_id}/datos', [\App\Http\Controllers\RemoteDashboardController::class, 'obtenerDatos'])
    ->middleware(['verificar.sesion']);

Route::get('/remoto/{servidor_id}/descargar', [\App\Http\Controllers\RemoteDashboardController::class, 'descargar'])
    ->middleware(['verificar.sesion']);

Route::post('/remoto/{servidor_id}/renombrar', [\App\Http\Controllers\RemoteDashboardController::class, 'renombrar'])
    ->middleware(['verificar.sesion']);

Route::post('/remoto/{servidor_id}/eliminar', [\App\Http\Controllers\RemoteDashboardController::class, 'eliminar'])
    ->middleware(['verificar.sesion']);

Route::post('/remoto/{servidor_id}/crear-carpeta', [\App\Http\Controllers\RemoteDashboardController::class, 'crearCarpeta'])
    ->middleware(['verificar.sesion']);

Route::post('/remoto/{servidor_id}/subir', [\App\Http\Controllers\RemoteDashboardController::class, 'subir'])
    ->middleware(['verificar.sesion']);

// ==================== SUBIR ARCHIVOS ====================
// Todos los roles pueden subir archivos
Route::get('/subir', [FileController::class, 'vistaSubir'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('subir');

Route::post('/subir/chunk', [FileController::class, 'subirChunk'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('subir.chunk');

// ==================== CREAR CARPETA ====================
// Solo administrador y supervisor pueden crear carpetas
Route::post('/explorador/crear-carpeta', [FileController::class, 'crearCarpeta'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor'])
    ->name('explorador.crearCarpeta');

// ==================== CREAR / EDITAR ARCHIVO ====================
// Todos los roles pueden crear y editar archivos
Route::post('/explorador/crear-archivo', [FileController::class, 'crearArchivo'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('explorador.crearArchivo');

Route::get('/explorador/contenido-archivo', [FileController::class, 'contenidoArchivo'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('explorador.contenidoArchivo');

Route::get('/explorador/previsualizar', [FileController::class, 'previsualizar'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('explorador.previsualizar');

Route::post('/explorador/guardar-archivo', [FileController::class, 'guardarArchivo'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('explorador.guardarArchivo');

// ==================== COPIAR / MOVER ====================
// Solo administrador y supervisor pueden copiar/mover
Route::post('/explorador/copiar', [FileController::class, 'copiar'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor'])
    ->name('explorador.copiar');

Route::post('/explorador/mover', [FileController::class, 'mover'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor'])
    ->name('explorador.mover');

// ==================== ESTADO DEL SERVIDOR (widgets) ====================
Route::get('/sistema/overview', [SystemStatsController::class, 'overview'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('sistema.overview');

// ==================== OBTENER SUBCARPETAS ====================
// Todos los roles pueden ver subcarpetas (es navegación)
Route::get('/explorador/subcarpetas', [FileController::class, 'obtenerSubcarpetas'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('explorador.subcarpetas');

// ==================== BUSCAR ====================
// Todos los roles pueden buscar
Route::get('/explorador/buscar', [FileController::class, 'buscar'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor,secretario'])
    ->name('explorador.buscar');

// ==================== DETECTAR PC CLIENTE ====================
Route::get('/computadoras/detectar-info', function (\Illuminate\Http\Request $request) {
    if (!session('loggedin')) return response()->json(['error' => 'No autorizado'], 401);
    return response()->json([
        'ip' => $request->ip(),
    ]);
})->middleware(['verificar.sesion'])->name('computadoras.detectarInfo');

// ==================== COMPUTADORAS ====================
// Todos los roles autenticados pueden ver el módulo de computadoras
Route::post('/computadoras', [ComputadoraController::class, 'store'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor'])
    ->name('computadoras.store');

Route::put('/computadoras/{id}', [ComputadoraController::class, 'update'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador,supervisor'])
    ->name('computadoras.update');

Route::delete('/computadoras/{id}', [ComputadoraController::class, 'destroy'])
    ->middleware(['verificar.sesion', 'verificar.rol:administrador'])
    ->name('computadoras.destroy');
