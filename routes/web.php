<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Usuario_Seguridad_y_Auditoria\autenticacionController;
use App\Http\Controllers\Usuario_Seguridad_y_Auditoria\gestionarUsuariosyRolesController;
use App\Http\Controllers\Inscripcion_y_Documentacion\documentosController;
use App\Http\Controllers\Gestion_Academica\gestionarCarrerasYCuposController;
use App\Http\Controllers\Logistica_Recursos_y_Reportes\gestionarAulasController;
use App\Http\Controllers\Gestion_Academica\gestionarGruposController;
use App\Http\Controllers\Logistica_Recursos_y_Reportes\gestionarDocentesController;
use App\Http\Controllers\Inscripcion_y_Documentacion\gestionarInscripcionController;
use App\Http\Controllers\Gestion_Financiera\gestionarPagosController;
use App\Http\Controllers\Gestion_Academica\gestionarMateriasYHorariosController;
use App\Http\Controllers\Gestion_Academica\asignarDocentesAGruposYMateriasController;

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
    Route::delete('/carreras-cupos/{id}', [gestionarCarrerasYCuposController::class, 'destroy'])
    ->name('carreras-cupos.destroy');
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
    Route::delete('/grupos/{id}', [gestionarGruposController::class, 'destroy'])
    ->name('grupos.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/docentes', [gestionarDocentesController::class, 'index'])
        ->name('docentes.index');
    Route::post('/docentes', [gestionarDocentesController::class, 'store'])
        ->name('docentes.store');
    Route::put('/docentes/{id}', [gestionarDocentesController::class, 'update'])
        ->name('docentes.update');
    Route::put('/docentes/{id}/documentos', [gestionarDocentesController::class, 'validarDocumentos'])
        ->name('docentes.documentos');
    Route::put('/docentes/{id}/deshabilitar', [gestionarDocentesController::class, 'deshabilitar'])
        ->name('docentes.deshabilitar');
    Route::put('/docentes/{id}/habilitar', [gestionarDocentesController::class, 'habilitar'])
        ->name('docentes.habilitar');
    Route::delete('/docentes/{id}', [gestionarDocentesController::class, 'destroy'])
    ->name('docentes.destroy');
    Route::get('/docentes/{id}/documentos', [gestionarDocentesController::class, 'documentos'])
    ->name('docentes.documentos.form');
    Route::post('/docentes/{id}/documentos', [gestionarDocentesController::class, 'guardarDocumentos'])
    ->name('docentes.documentos.guardar');
});

Route::middleware('auth')->group(function () {
    Route::get('/inscripcion', [gestionarInscripcionController::class, 'index'])
        ->name('inscripcion.index');
    Route::get('/inscripcion/buscar-ci/{ci}', [gestionarInscripcionController::class, 'buscarPorCi'])
        ->name('inscripcion.buscarCi');
    Route::post('/inscripcion', [gestionarInscripcionController::class, 'store'])
        ->name('inscripcion.store');
    Route::put('/inscripcion/{id}', [gestionarInscripcionController::class, 'update'])
        ->name('inscripcion.update');
    Route::delete('/inscripcion/{id}', [gestionarInscripcionController::class, 'destroy'])
        ->name('inscripcion.destroy');
    Route::get('/inscripcion/{codigo}/documentos', [gestionarInscripcionController::class, 'documentos'])
    ->name('inscripcion.documentos.form');
    Route::post('/inscripcion/{codigo}/documentos', [gestionarInscripcionController::class, 'guardarDocumentos'])
    ->name('inscripcion.documentos.guardar');
});

Route::middleware('auth')->group(function () {
    Route::get('/pagos', [gestionarPagosController::class, 'index'])
        ->name('pagos.index');
    Route::post('/pagos', [gestionarPagosController::class, 'store'])
        ->name('pagos.store');
    Route::put('/pagos/{id}', [gestionarPagosController::class, 'update'])
        ->name('pagos.update');
    Route::delete('/pagos/{id}', [gestionarPagosController::class, 'destroy'])
        ->name('pagos.destroy');
    Route::post('/pagos/inscripcion/guardar', [gestionarPagosController::class, 'guardarPagoInscripcion'])
        ->name('pagos.inscripcion.guardar');
});

Route::middleware('auth')->group(function () {

    // CU14 - Gestionar Materias
Route::get('/materias', [gestionarMateriasYHorariosController::class, 'indexMaterias'])
    ->name('materias.index');

Route::post('/materias', [gestionarMateriasYHorariosController::class, 'storeMateria'])
    ->name('materias.store');

Route::put('/materias/{id}', [gestionarMateriasYHorariosController::class, 'updateMateria'])
    ->name('materias.update');

Route::delete('/materias/{id}', [gestionarMateriasYHorariosController::class, 'destroyMateria'])
    ->name('materias.destroy');


// CU14 - Gestionar Horarios
Route::get('/horarios', [gestionarMateriasYHorariosController::class, 'indexHorarios'])
    ->name('horarios.index');

Route::post('/horarios', [gestionarMateriasYHorariosController::class, 'storeHorario'])
    ->name('horarios.store');

Route::put('/horarios/{id}', [gestionarMateriasYHorariosController::class, 'updateHorario'])
    ->name('horarios.update');

Route::delete('/horarios/{id}', [gestionarMateriasYHorariosController::class, 'destroyHorario'])
    ->name('horarios.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/asignaciones-docentes', [asignarDocentesAGruposYMateriasController::class, 'index'])
        ->name('asignaciones-docentes.index');

    Route::post('/asignaciones-docentes', [asignarDocentesAGruposYMateriasController::class, 'store'])
        ->name('asignaciones-docentes.store');

    Route::put('/asignaciones-docentes/{idGrupo}/{idMateria}', [asignarDocentesAGruposYMateriasController::class, 'update'])
        ->name('asignaciones-docentes.update');

    Route::delete('/asignaciones-docentes/{idGrupo}/{idMateria}', [asignarDocentesAGruposYMateriasController::class, 'destroy'])
        ->name('asignaciones-docentes.destroy');
});