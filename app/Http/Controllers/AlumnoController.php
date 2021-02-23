<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoProyecto;
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
        DB::transaction(function () use ($request) {
            AlumnoProyecto::create([
                "tipo_proyecto" => $request->tipo_proyecto,
                "alumno_id" => $request->alumno_id,
                "proyecto_id" => $request->proyecto_id
            ]);
            Proyecto::where("id", $request->proyecto_id)->update(["estado" => "ASIGNADO"]);
            User::where("id", $request->id)->update(["estado" => "INSCRITO"]);
    });
    }

    public function crearRegistro(Request $request) 
    {
        $this->validate(
            $request,
            ReglasValidaciones::getValidacionesRegistroAlumno(),
            ReglasValidaciones::getMensajesPersonalizados()
        );

        $inscripcion = Inscripcion::where("token_inscripcion", $request->token_inscripcion)->first();

        if ($inscripcion == null || $inscripcion->token_inscripcion != $request->token_inscripcion) {
            return response()->json("El token de inscripción no es válido.", 401);
        } else if ($inscripcion->estado_inscripcion == "INACTIVO") {
            return response()->json("El tiempo de inscripción ha finalizado.", 401);
        }

        DB::transaction(function () use ($request) {
            $id = DB::table('users')->insertGetId([
                'correo' => $request->correo,
                'password' => Hash::make($request->password),
                'nombres' => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'estado' => "INSCRIPCION",
                'num_contacto' => $request->num_contacto,
                'rol_usuario' => $request->rol_usuario
            ]);

            $usuario = User::where("estado", "DEFAULT")->first();
            
            Alumno::create([
                "matricula" => $request->matricula,
                "bloque" => $request->bloque,
                "seccion" => $request->seccion,
                "proyectos" => $request->proyectos,
                "users_id" => $id,
                "profesor_id" => $usuario->profesor->id
            ]);
        });
    }

    public function validarRegistro(Request $request) {
        if ($request->es_registro) {
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
        } else if ($usuario->estado == 'ASIGNACION') {
            return response()->json('Ya te has registrado y te encuentras en proceso de asignación.', 201);
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
            'id_alumno' => $usuario->alumno->id,
            'matricula' => $usuario->alumno->matricula,
            'bloque' => $usuario->alumno->bloque,
            'seccion' => $usuario->alumno->seccion,
            'proyectos' => $usuario->alumno->proyecto
        );
        
        return response()->json($resultado, 200);
    }
}
