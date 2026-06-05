<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Usuario_Seguridad_y_Auditoria\autenticacionController;
use App\Http\Controllers\Usuario_Seguridad_y_Auditoria\gestionarUsuariosyRolesController;
use App\Http\Controllers\Inscripcion_y_Documentacion\documentosController;
use App\Http\Controllers\Gestion_Academica\gestionarCarrerasYCuposController;
use App\Http\Controllers\Logistica_Recursos_y_Reportes\gestionarAulasController;
use App\Http\Controllers\Gestion_Academica\gestionarGruposController;
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [autenticacionController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [autenticacionController::class, 'login'])->name('login.post');
    Route::get('/password/forgot', [autenticacionController::class, 'mostrarOlvidoContrasena'])->name('password.forgot');
    Route::post('/password/forgot', [autenticacionController::class, 'enviarCodigoRecuperacion'])->name('password.forgot.send');
    Route::get('/password/reset', [autenticacionController::class, 'mostrarFormularioCambioContrasena'])->name('password.reset.form');
    Route::post('/password/reset', [autenticacionController::class, 'cambiarContrasena'])->name('password.reset.update');
    Route::post('/password/resend', [autenticacionController::class, 'reenviarCodigoRecuperacion'])->name('password.resend');
});

Route::middleware('auth')->group(function () {
    Route::view('/menu', 'Menu')->name('menu');
    Route::post('/logout', [autenticacionController::class, 'logout'])->name('logout');
});



Route::prefix('usuarios')->middleware('auth')->group(function () {
    Route::get('/', [gestionarUsuariosyRolesController::class, 'index'])->name('usuarios.index');
    Route::get('/create', [gestionarUsuariosyRolesController::class, 'create'])->name('usuarios.create');
    Route::post('/store', [gestionarUsuariosyRolesController::class, 'store'])->name('usuarios.store');
    Route::get('/{id}/edit', [gestionarUsuariosyRolesController::class, 'edit'])->name('usuarios.edit');
    Route::put('/{id}', [gestionarUsuariosyRolesController::class, 'update'])->name('usuarios.update');
    Route::delete('/{id}', [gestionarUsuariosyRolesController::class, 'destroy'])->name('usuarios.destroy');
    Route::get('/{id}/roles', [gestionarUsuariosyRolesController::class, 'mostrarAsignarRoles'])->name('usuarios.roles');
    Route::post('/{id}/roles', [gestionarUsuariosyRolesController::class, 'assignRoles'])->name('usuarios.roles.update');
});

Route::prefix('documentos')->middleware('auth')->group(function () {
    Route::get('/', [documentosController::class, 'index'])->name('documentos.index');
    Route::post('/', [documentosController::class, 'store'])->name('documentos.store');
    Route::put('/{id}', [documentosController::class, 'update'])->name('documentos.update');
    Route::put('/{persona}/{documento}/validar', [documentosController::class, 'validarDocumento'])->name('documentos.validar');
    Route::put('/{persona}/{documento}/observar', [documentosController::class, 'observarDocumento'])->name('documentos.observar');
    Route::delete('/{id}', [documentosController::class, 'destroyRequisito'])->name('documentos.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/carreras-cupos', [gestionarCarrerasYCuposController::class, 'index'])
        ->name('carreras-cupos.index');
    Route::post('/carreras-cupos', [gestionarCarrerasYCuposController::class, 'store'])
        ->name('carreras-cupos.store');
    Route::put('/carreras-cupos/{id}', [gestionarCarrerasYCuposController::class, 'update'])
        ->name('carreras-cupos.update');
    Route::put('/carreras-cupos/{id}/cupos', [gestionarCarrerasYCuposController::class, 'actualizarCupos'])
        ->name('carreras-cupos.cupos');
    Route::put('/carreras-cupos/{id}/deshabilitar', [gestionarCarrerasYCuposController::class, 'deshabilitar'])
        ->name('carreras-cupos.deshabilitar');
    Route::put('/carreras-cupos/{id}/habilitar', [gestionarCarrerasYCuposController::class, 'habilitar'])
        ->name('carreras-cupos.habilitar');
});

Route::middleware('auth')->group(function () {
    Route::get('/gestion-academica', function () {
        return redirect()->route('carreras-cupos.index');
    })->name('gestion-academica.menu');
});

Route::middleware('auth')->group(function () {
    Route::get('/logistica-recursos', function () {
        return redirect()->route('aulas.index');
    })->name('logistica-recursos.menu');
    Route::get('/aulas', [gestionarAulasController::class, 'index'])
        ->name('aulas.index');
    Route::post('/aulas', [gestionarAulasController::class, 'store'])
        ->name('aulas.store');
    Route::put('/aulas/{id}', [gestionarAulasController::class, 'update'])
        ->name('aulas.update');
    Route::put('/aulas/{id}/capacidad', [gestionarAulasController::class, 'actualizarCapacidad'])
        ->name('aulas.capacidad');
    Route::put('/aulas/{id}/deshabilitar', [gestionarAulasController::class, 'deshabilitar'])
        ->name('aulas.deshabilitar');
    Route::put('/aulas/{id}/habilitar', [gestionarAulasController::class, 'habilitar'])
        ->name('aulas.habilitar');
});

Route::middleware('auth')->group(function () {
    Route::get('/grupos', [gestionarGruposController::class, 'index'])
        ->name('grupos.index');
    Route::post('/grupos', [gestionarGruposController::class, 'store'])
        ->name('grupos.store');
    Route::put('/grupos/{id}', [gestionarGruposController::class, 'update'])
        ->name('grupos.update');
    Route::put('/grupos/{id}/deshabilitar', [gestionarGruposController::class, 'deshabilitar'])
        ->name('grupos.deshabilitar');
    Route::put('/grupos/{id}/habilitar', [gestionarGruposController::class, 'habilitar'])
        ->name('grupos.habilitar');
});