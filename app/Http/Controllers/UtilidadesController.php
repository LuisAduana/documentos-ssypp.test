<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use App\Models\Inscripcion;
use App\Models\Responsable;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UtilidadesController extends Controller
{

  public function cambiarPassword(Request $request) {
    $request->validate([
      "id" => ["required"],
      "password" => ["required", "min:7", "max:120"]
    ]);
    
    User::where("id", $request->id)->update(["password" => Hash::make($request->password)]);
  }

    public function obtenerAlumnosAsignadosActivos(Request $request) {
        $users = User::where([["rol_usuario", "ALUMNO"], ["estado", "ASIGNADO"]])
            ->orWhere([["rol_usuario", "ALUMNO"], ["estado", "ACTIVO"]])->get();
        $alumnos = array();
        foreach($users as $user) {
            $localArray = array(
                "id" => $user->id,
                "correo" => $user->correo,
                "nombres" => $user->nombres,
                "apellido_paterno" => $user->apellido_paterno,
                "apellido_materno" => $user->apellido_materno,
                "estado" => $user->estado,
                "num_contacto" => $user->num_contacto,
                "rol_usuario" => $user->rol_usuario,
                "alumno_id" => $user->alumno->id,
                "matricula" => $user->alumno->matricula,
                "bloque" => $user->alumno->bloque,
                "seccion" => $user->alumno->seccion,
                "proyectos" => $user->alumno->proyectos
            );
            array_push($alumnos, $localArray);
        }

        return response()->json($alumnos, 200);
    }

    public function obtenerProyectosSeleccionadosAlumno(Request $request) {

        $inscripcion = DB::table("inscripcion")->orderByRaw("id DESC")->limit(1)->first();
        
        $proyectos_seleccionados = array();
        for($i = 0; $i < count($request->proyectos); $i++) {
            $query = Proyecto::where("id", $request->proyectos[$i])->first();
            array_push($proyectos_seleccionados, $query);
        }
        
        $proyectos= array();
        if ($inscripcion->tipo_inscripcion == "servicio") {
            foreach($proyectos_seleccionados as $proyecto) {
                $localArray = array(
                    "id" => $proyecto->id,
                    "estado" => $proyecto->estado,
                    "nombre_dependencia" => $proyecto->dependencia->nombre_dependencia,
                    "nombre_responsable" => $proyecto->responsable->nombre_responsable,
                    "num_alumnos" => $proyecto->proyectoServicio->num_alumnos,
                    "actividades" => $proyecto->proyectoServicio->actividades,
                    "horario" => $proyecto->proyectoServicio->horario,
                    "requisitos" => $proyecto->proyectoServicio->requisitos
                );
                array_push($proyectos, $localArray);
            }
        } else {
            foreach($proyectos_seleccionados as $proyecto) {
                $localArray = array(
                    "id" => $proyecto->id,
                    "estado" => $proyecto->estado,
                    "nombre_dependencia" => $proyecto->dependencia->nombre_dependencia,
                    "nombre_responsable" => $proyecto->responsable->nombre_responsable,
                    "nombre_proyecto" => $proyecto->proyectoPractica->nombre_proyecto,
                    "descripcion_general" => $proyecto->proyectoPractica->descripcion_general,
                    "objetivo_general" => $proyecto->proyectoPractica->objetivo_general,
                    "objetivos_inmediatos" => $proyecto->proyectoPractica->objetivos_inmediatos,
                    "objetivos_mediatos" => $proyecto->proyectoPractica->objetivos_mediatos,
                    "metodologia" => $proyecto->proyectoPractica->metodologia,
                    "recursos" => $proyecto->proyectoPractica->recursos,
                    "actividades_funcionales" => $proyecto->proyectoPractica->actividades_funcionales,
                    "responsabilidades" => $proyecto->proyectoPractica->responsabilidades,
                    "duracion" => $proyecto->proyectoPractica->duracion,
                    "horario" => $proyecto->proyectoPractica->horario
                );
                array_push($proyectos, $localArray);
            }
        }

        $respuesta = array(
            "proyectos" => $proyectos,
            "tipo_inscripcion" => $inscripcion->tipo_inscripcion
        );

        return response()->json($respuesta, 200);
    }

    public function obtenerProyectosInscripcion(Request $request) {
    
      $proyectos = array();
      $tipo_inscripcion = "";

      DB::transaction(function () use (&$proyectos, &$tipo_inscripcion) {
          $cuenta = Inscripcion::where("estado", "ACTIVO")->count();
          if ($cuenta === 0) {
            throw ValidationException::withMessages(["inscripcion" => "No existe una inscripción activa."]);
          }
          $inscripcion = Inscripcion::where("estado", "ACTIVO")->first();
          $query = Proyecto::where("estado", "INSCRIPCION")->get();
          $tipo_inscripcion = $inscripcion->tipo_inscripcion;
          if ($tipo_inscripcion == "servicio") {
            foreach ($query as $proyecto) {
                $localArray = array(
                    "id" => $proyecto->id,
                    "estado" => $proyecto->estado,
                    "nombre_dependencia" => $proyecto->dependencia->nombre_dependencia,
                    "direccion" => $proyecto->dependencia->direccion,
                    "nombre_responsable" => $proyecto->responsable->nombre_responsable,
                    "actividades" => $proyecto->proyectoServicio->actividades,
                    "horario" => $proyecto->proyectoServicio->horario,
                    "requisitos" => $proyecto->proyectoServicio->requisitos,
                    "num_alumnos" => $proyecto->proyectoServicio->num_alumnos
                );
                array_push($proyectos, $localArray);
            }
          } else {
            foreach ($query as $proyecto) {
                $localArray = array(
                    "id" => $proyecto->id,
                    "estado" => $proyecto->estado,
                    "nombre_dependencia" => $proyecto->dependencia->nombre_dependencia,
                    "direccion" => $proyecto->dependencia->direccion,
                    "nombre_responsable" => $proyecto->responsable->nombre_responsable,
                    "nombre_proyecto" => $proyecto->proyectoPractica->nombre_proyecto,
                    "descripcion_general" => $proyecto->proyectoPractica->descripcion_general,
                    "objetivo_general" => $proyecto->proyectoPractica->objetivo_general,
                    "objetivos_inmediatos" => $proyecto->proyectoPractica->objetivos_inmediatos,
                    "objetivos_mediatos" => $proyecto->proyectoPractica->objetivos_mediatos,
                    "metodologia" => $proyecto->proyectoPractica->metodologia,
                    "recursos" => $proyecto->proyectoPractica->recursos,
                    "actividades_funcionales" => $proyecto->proyectoPractica->actividades_funcionales,
                    "responsabilidades" => $proyecto->proyectoPractica->responsabilidades,
                    "duracion" => $proyecto->proyectoPractica->duracion,
                    "horario" => $proyecto->proyectoPractica->horario
                );
                array_push($proyectos, $localArray);
            }
          }
      });
      $respuesta = array(
          "tipo_inscripcion" => $tipo_inscripcion,
          "proyectos" => $proyectos
      );
      return response()->json($respuesta, 200);
    }

    public function obtenerNombresResponsablesPorDependencia(Request $request) {
        $request->validate(["nombre_dependencia" => ["max:230", "min:1"]]);
        if (!$request->has("nombre_dependencia")) {
            return response()->json("true", 200);
        }
        $dependencia = Dependencia::where("nombre_dependencia", $request->nombre_dependencia)->first();
        $responsables = Responsable::where("dependencia_id", $dependencia->id)->where("estado", "ACTIVO")->get();

        $nombres = array();
        foreach ($responsables as $responsable) {
            array_push($nombres, $responsable->nombre_responsable);
        }

        return response()->json($nombres, 200);
    }

    public function obtenerNombresDependencias(Request $request) {
        $dependencias = Dependencia::where("estado", "ACTIVO")->get();

        $nombres = array();
        foreach ($dependencias as $dependencia) {
            array_push($nombres, $dependencia->nombre_dependencia);
        }

        return response()->json($nombres, 200);
    }
}
