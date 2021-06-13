<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoProyecto;
use App\Models\Documento;
use App\Models\User;
use App\Models\Inscripcion;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AlumnoController extends Controller
{
  public function asignarProyecto(Request $request) {
    $request->validate(ReglasValidaciones::getValidacionesAsignarProyecto());

    if (Inscripcion::where("estado", "ACTIVO")->count() > 0) {
      throw ValidationException::withMessages([
        "asignacion" => "No se pueden asignar proyectos porque existe una inscripción activa"
      ]);
    }

    DB::transaction(function () use ($request) {
      AlumnoProyecto::create([
          "tipo_proyecto" => $request->tipo_proyecto,
          "alumno_id" => $request->alumno_id,
          "proyecto_id" => $request->proyecto_id
      ]);
      User::where("id", $request->id)->update(["estado" => "ASIGNADO"]);
    });
  }

    public function crearRegistro(Request $request) 
    {
      
      $request->validate(["esRegistro" => ["required"]]);
      if ($request->esRegistro) {
        $this->validate(
            $request,
            ReglasValidaciones::getValidacionesValidarRegistro(),
            ReglasValidaciones::getMensajesPersonalizados()
        );
      } else {
        $this->validate(
            $request,
            ReglasValidaciones::getValidacionesValidarActualizacion($request),
            ReglasValidaciones::getMensajesPersonalizados()
        );
      }

      $inscripcion = Inscripcion::where("token_inscripcion", $request->token_inscripcion)->first();

      if ($inscripcion == null || $inscripcion->token_inscripcion != $request->token_inscripcion) {
          return response()->json("El token de inscripción no es válido.", 401);
      } else if ($inscripcion->estado == "INACTIVO") {
          return response()->json("El tiempo de inscripción ha finalizado.", 401);
      }

      DB::transaction(function () use ($request) {
          $usuario = User::updateOrCreate(["id" => $request->id], [
            "correo" => $request->correo,
            "password" => Hash::make($request->password),
            "nombres" => $request->nombres,
            "apellido_paterno" => $request->apellido_paterno,
            "apellido_materno" => $request->apellido_materno,
            "estado" => "INSCRIPCION",
            "num_contacto" => $request->num_contacto,
            "rol_usuario" => $request->rol_usuario
          ]);

          $profesor = User::where("estado", "DEFAULT")->first();
          
          Alumno::updateOrCreate(["id" => $request->alumno_id], [
              "matricula" => $request->matricula,
              "bloque" => $request->bloque,
              "seccion" => $request->seccion,
              "proyectos" => $request->proyectos,
              "users_id" => $usuario->id,
              "profesor_id" => $profesor->profesor->id
          ]);
      });
    }

    public function validarRegistro(Request $request) {
        if ($request->esRegistro) {
            $this->validate(
                $request,
                ReglasValidaciones::getValidacionesValidarRegistro(),
                ReglasValidaciones::getMensajesPersonalizados()
            );
        } else {
            $this->validate(
                $request,
                ReglasValidaciones::getValidacionesValidarActualizacion($request),
                ReglasValidaciones::getMensajesPersonalizados()
            );
        }
    }

    public function consultarAlumnosInscritos(Request $request) {
      $users = User::where("estado", "INSCRIPCION")->get();
      if (sizeof($users) == 0) {
        throw ValidationException::withMessages([
          "alumnos" => ["No existen alumnos inscritos."]
        ]);
      }
      $alumnos = array();
      foreach ($users as $user) {
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

    public function consultarAlumnosConProyectos(Request $request) {
      // DB::table('users')
      //       ->where('votes', '>', 100)
      //       ->orWhere(function($query) {
      //           $query->where('name', 'Abigail')
      //                 ->where('votes', '>', 50);
      //       })
      //       ->get();
      //$users = User::where("estado", "ACTIVO")->where("estado", "ASIGNADO")->where("rol_usuario", "ALUMNO")->get();
      $users = User::where("rol_usuario", "ALUMNO")->where(
        function($query) {
          $query->where("estado", "ACTIVO")
          ->orWhere("estado", "ASIGNADO");
        })
        ->get();
      $alumnos = array();
      foreach ($users as $user) {
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

    public function comprobarExistencia(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesComprobarExistencia());

        $usuario = User::where('correo', $request->correo)->first();
        if($usuario == null || !Hash::check($request->password, $usuario->password)) {
            return response()->json('Las credenciales son inválidas', 401);
        } else if ($usuario->rol_usuario != 'ALUMNO') {
            return response()->json('Esas credenciales no pertenecen a un alumno', 401);
        }

        $resultado = array(
            'id' => $usuario->id,
            'correo' => $usuario->correo,
            'nombres' => $usuario->nombres,
            'apellido_paterno' => $usuario->apellido_paterno,
            'apellido_materno' => $usuario->apellido_materno,
            'estado' => $usuario->estado,
            'num_contacto' => $usuario->num_contacto,
            'rol_usuario' => $usuario->rol_usuario,
            'alumno_id' => $usuario->alumno->id,
            'matricula' => $usuario->alumno->matricula,
            'bloque' => $usuario->alumno->bloque,
            'seccion' => $usuario->alumno->seccion,
            'proyectos' => $usuario->alumno->proyecto
        );
        
        return response()->json($resultado, 200);
    }

    public function modificarAlumno(Request $request) {
      $this->validate(
        $request,
        ReglasValidaciones::getValidacionesModificarAlumno($request),
        ReglasValidaciones::getMensajesPersonalizados()
      );

      if (Alumno::where("matricula", $request->matricula)->count() > 0) {
        $alumno = Alumno::where("matricula", $request->matricula)->first();
        if ($alumno->id != $request->alumno_id) {
          throw ValidationException::withMessages([
            "matricula" => ["La matrícula ya ha sido registrada"]
          ]);
        }
      }
      DB::transaction(function () use ($request) {
        User::where("id", $request->id)->update([
          "correo" => $request->correo,
          "nombres" => $request->nombres,
          "apellido_paterno" => $request->apellido_paterno,
          "apellido_materno" => $request->apellido_materno,
          "num_contacto" => $request->num_contacto
        ]);
        Alumno::where("id", $request->alumno_id)->update([
          "matricula" => $request->matricula,
          "bloque" => $request->bloque,
          "seccion" => $request->seccion
        ]);
      });
    }

    public function consultarAlumnos(Request $request) {
      $request->validate(["id" => ["required"]]);

      $query = Alumno::where("profesor_id", $request->id)->get();
      $alumnos = array();
      foreach ($query as $usuario) {
        $notificacion = false;
        if (Documento::where("estado", "ENVIADO")->where("alumno_id", $usuario->id)->count() > 0) {
          $notificacion = true;
        }
        $localArray = array(
          "id" => $usuario->user->id,
          "correo" => $usuario->user->correo,
          "nombres" => $usuario->user->nombres,
          "apellido_paterno" => $usuario->user->apellido_paterno,
          "apellido_materno" => $usuario->user->apellido_materno,
          "num_contacto" => $usuario->user->num_contacto,
          "alumno_id" => $usuario->id,
          "matricula" => $usuario->matricula,
          "notificacion" => $notificacion
        );
        array_push($alumnos, $localArray);
      }
      return response()->json($alumnos,  200);
    }
}
