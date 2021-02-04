<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\CoordinadorController;
use App\Http\Controllers\DependenciaController;
use App\http\Controllers\InscripcionController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\ProyectoPracticaController;
use App\Http\Controllers\ProyectoServicioController;
use App\Http\Controllers\UtilidadesController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('administrador')->group(function() { 
    // Por seguridad esta ruta está inhabilitada y no se pueda acceder al registro de un nuevo administrador
    // si desea registrar un nuevo administrador descomente esta ruta
    // Route::post('registro', [AdministradorController::class, 'registro']);
    Route::get('obtener-coordinadores', [AdministradorController::class, 'obtenerCoordinadores']);
    Route::post('registrar-coordinador', [AdministradorController::class, 'registrarCoordinador']);
    Route::put('modificar-coordinador', [AdministradorController::class, 'modificarCoordinador']);
    Route::put('activar-desactivar-coordinador', [AdministradorController::class, 'activarDesactivarCoordinador']);
});

Route::prefix('coordinador')->group(function() {
    Route::get('obtener-dependencias', [DependenciaController::class, 'obtenerDependencias']);
    Route::post('registrar-dependencia', [DependenciaController::class, 'registrarDependencia']);
    Route::put('modificar-dependencia', [DependenciaController::class, 'modificarDependencia']);
    Route::put('activar-desactivar-dependencia', [DependenciaController::class, 'activarDesactivarDependencia']);


    Route::get('obtener-responsables', [ResponsableController::class, 'obtenerResponsables']);
    Route::post('registrar-responsable', [ResponsableController::class, 'registrarResponsable']);
    Route::put('modificar-responsable', [ResponsableController::class, 'modificarResponsable']);
    Route::put('activar-desactivar-responsable', [ResponsableController::class, 'activarDesactivarResponsable']);

    Route::post('obtener-proyectos-servicio', [ProyectoServicioController::class, 'obtenerProyectosServicio']);
    Route::post('registrar-proyecto-servicio', [ProyectoServicioController::class, 'registrarProyectoServicio']);
    Route::put('modificar-proyecto-servicio', [ProyectoServicioController::class, 'modificarProyectoServicio']);
    Route::put('modificar-estado-proyecto', [ProyectoServicioController::class, 'cambiarEstadoProyecto']);

    Route::get('obtener-proyectos-practica', [ProyectoPracticaController::class, 'obtenerProyectosPractica']);
    Route::post('registrar-proyecto-practica', [ProyectoPracticaController::class, 'registrarProyectoPractica']);
    Route::put('modificar-proyecto-practica', [ProyectoPracticaController::class, 'modificarProyectoPractica']);
    Route::put('modificar-estado-proyecto-practica', [ProyectoPracticaController::class, 'cambiarEstadoProyecto']);

    Route::get('obtener-inscripciones', [InscripcionController::class, 'obtenerInscripciones']);
    Route::post('registrar-inscripcion-servicio', [InscripcionController::class, 'registrarInscripcionServicio']);
    Route::put('cancelar-inscripciones-servicio', [InscripcionController::class, 'cancelarInscripciones']);

    Route::get('obtener-nombres-dependencias', [UtilidadesController::class, 'obtenerNombresDependencias']);
    Route::post('obtener-responsables-por-dependencia', [UtilidadesController::class, 'obtenerNombresResponsablesPorDependencia']);
});

Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout']);