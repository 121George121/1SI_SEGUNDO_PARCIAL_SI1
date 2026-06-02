<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Usuario_Seguridad_y_Auditoria\autenticacionController;
use App\Http\Controllers\Usuario_Seguridad_y_Auditoria\gestionarUsuariosyRolesController;

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