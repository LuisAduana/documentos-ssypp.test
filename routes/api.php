<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\CoordinadorController;
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\DocumentoController;
use App\http\Controllers\InscripcionController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\ProfesorController;
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
  $usuario = $request->user();
  if ($usuario->rol_usuario == "ALUMNO") {
    return response()->json(User::with("alumno")->where("id", $usuario->id)->first(), 200);
  } else {
    return response()->json(User::with("profesor")->where("id", $usuario->id)->first(), 200);
  }
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

    Route::post('obtener-proyectos-practica', [ProyectoPracticaController::class, 'obtenerProyectosPractica']);
    Route::post('registrar-proyecto-practica', [ProyectoPracticaController::class, 'registrarProyectoPractica']);
    Route::put('modificar-proyecto-practica', [ProyectoPracticaController::class, 'modificarProyectoPractica']);
    Route::put('modificar-estado-proyecto-practica', [ProyectoPracticaController::class, 'cambiarEstadoProyecto']);

    Route::get('obtener-alumnos-inscritos', [AlumnoController::class, 'consultarAlumnosInscritos']);
    Route::get('obtener-alumnos-con-proyecto', [AlumnoController::class, 'consultarAlumnosConProyectos']);
    Route::post('asignar-alumno-proyecto', [AlumnoController::class, 'asignarProyecto']);

    Route::get('obtener-inscripciones', [InscripcionController::class, 'obtenerInscripciones']);
    Route::post('registrar-inscripcion', [InscripcionController::class, 'registrarInscripcion']);
    Route::put('terminar-inscripcion', [InscripcionController::class, 'terminarInscripcion']);
    Route::put('cancelar-inscripcion', [InscripcionController::class, 'cancelarInscripcion']);

    Route::get('obtener-nombres-dependencias', [UtilidadesController::class, 'obtenerNombresDependencias']);
    Route::post('obtener-responsables-por-dependencia', [UtilidadesController::class, 'obtenerNombresResponsablesPorDependencia']);
    Route::post('obtener-proyectos-seleccionador', [UtilidadesController::class, 'obtenerProyectosSeleccionadosAlumno']);

    Route::get("obtener-profesores", [ProfesorController::class, "consultarProfesores"]);
    Route::post("validar-registro", [ProfesorController::class, "validarRegistroProfesor"]);
    Route::post("registrar-profesor", [ProfesorController::class, "registrarProfesor"]);
    Route::put("modificar-profesor", [ProfesorController::class, "modificarProfesor"]);
    Route::put("modificar-alumnos-asignados", [ProfesorController::class, "modificarAlumnosAsignados"]);
    Route::put("activar-desactivar-profesor", [ProfesorController::class, "cambiarEstadoProfesor"]);

    Route::post("obtener-documentos-aceptados-alumno", [DocumentoController::class, "obtenerDocumentosAceptadosAlumno"]);
});

Route::prefix('profesor')->group(function () {
  Route::post('consulta-alumnos', [AlumnoController::class, 'consultarAlumnos']);
  Route::post('obtener-documentos-alumno', [DocumentoController::class, 'obtenerDocumentosAlumno']);
  Route::post('descargar-documento-practica', [DocumentoController::class, 'descargarDocumentoPractica']);
  Route::post('descargar-documento-servicio', [DocumentoController::class, 'descargarDocumentoServicio']);
  Route::put('modificar-estado-documento', [DocumentoController::class, 'modificarEstadoDocumento']);
  Route::post('obtener-mensajes', [DocumentoController::class, 'obtenerMensajes']);
});

Route::prefix('alumno')->group(function() {
    Route::post('registrar-alumno', [AlumnoController::class, 'crearRegistro']);
    Route::post('comprobar-registro', [AlumnoController::class, 'comprobarExistencia']);
    Route::post('validar-registro', [AlumnoController::class, 'validarRegistro']);
    Route::put('modificar-alumno', [AlumnoController::class, 'modificarAlumno']);

    Route::post('obtener-documentos', [DocumentoController::class, 'obtenerDocumentos']);
    Route::post('registrar-documento-practica', [DocumentoController::class, 'registrarDocumentoPractica']);
    Route::post('registrar-documento-servicio', [DocumentoController::class, 'registrarDocumentoServicio']);
    Route::post('modificar-documento-practica', [DocumentoController::class, 'modificarDocumentoPractica']);
    Route::post('modificar-documento-servicio', [DocumentoController::class, 'modificarDocumentoServicio']);
    
});

Route::prefix('utilidades')->group(function() {
    Route::get('obtener-proyectos-inscripcion', [UtilidadesController::class, 'obtenerProyectosInscripcion']);
    Route::get('obtener-alumnos-asignados-activos', [UtilidadesController::class, 'obtenerAlumnosAsignadosActivos']);
    Route::put('cambiar-password', [UtilidadesController::class, 'cambiarPassword']);
    Route::post('obtener-informacion-proyecto', [UtilidadesController::class, 'obtenerInformacionProyecto']);
});

Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout']);
Route::post("obtener-informacion-alumno", [LoginController::class, "obtenerInformacionAlumno"]);
Route::post('obtener-informacion-profesor', [LoginController::class, "obtenerInformacionProfesor"]);