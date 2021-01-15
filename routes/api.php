<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\CoordinadorController;
use App\Http\Controllers\LoginController;



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
    Route::get('obtener-dependencias', [CoordinadorController::class, 'obtenerDependencias']);
    Route::post('registrar-dependencia', [CoordinadorController::class, 'registrarDependencia']);
    Route::put('activar-desactivar-dependencia', [CoordinadorController::class, 'activarDesactivarDependencia']);
    Route::put('modificar-dependencia', [CoordinadorController::class, 'modificarDependencia']);
    Route::get('obtener-responsables', [CoordinadorController::class, 'obtenerResponsables']);
    Route::post('registrar-responsable', [CoordinadorController::class, 'registrarResponsable']);
    Route::put('modificar-responsable', [CoordinadorController::class, 'modificarResponsable']);

    Route::get('obtener-nombres-dependencias', [CoordinadorController::class, 'obtenerNombresDependencias']);
});

Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout']);