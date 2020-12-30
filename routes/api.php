<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdministradorController;



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
    
});

Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout']);