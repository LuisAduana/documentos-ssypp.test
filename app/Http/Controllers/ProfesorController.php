<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfesorController extends Controller
{
    public function cambiarEstadoProfesor(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesCambioEstado());
        User::where("id", $request->id)->update(["estado" => $request->estado]);
    }

    public function registrarProfesor(Request $request) {
        DB::transaction(function () use ($request) {
            $idUser = DB::table("users")->insertGetId([
                "correo" => $request->correo,
                "password" => Hash::make($request->password),
                "nombres" => $request->nombres,
                "apellido_paterno" => $request->apellido_paterno,
                "apellido_materno" => $request->apellido_materno,
                "estado" => $request->estado,
                "num_contacto" => $request->num_contacto,
                "rol_usuario" => $request->rol_usuario
            ]);
            $idProfesor = DB::table("profesor")->insertGetId([
                "num_personal" => $request->num_personal,
                "users_id" => $idUser
            ]);

            foreach ($request->alumnos as $alumno) {
                User::where("id", $alumno["id"])->update([
                    "estado" => "ACTIVO"
                ]);
                Alumno::where("id", $alumno["alumno_id"])->update([
                    "profesor_id" => $idProfesor
                ]);
            }
        });
    }

    public function modificarAlumnosAsignados(Request $request) {
      DB::transaction(function () use ($request) {
        $alumnos = Alumno::where("profesor_id", $request->profesor_id)->get();
        $default = User::where("estado", "DEFAULT")->first();
        foreach ($alumnos as $alumno) {
          Alumno::where("id", $alumno["id"])->update([
            "profesor_id" => $default->profesor->id
          ]);
          User::where("id", $alumno->users_id)->update(["estado" => "ASIGNADO"]);
        }
        foreach ($request->alumnos as $alumno) {
          User::where("id", $alumno["id"])->update([
              "estado" => "ACTIVO"
          ]);
          Alumno::where("id", $alumno["alumno_id"])->update([
              "profesor_id" => $request->profesor_id
          ]);
        }
      });
    }

    public function consultarProfesores(Request $request) {
        $query = User::where("rol_usuario", "PROFESOR")
            ->where("estado", "ACTIVO")
            ->orWhere("estado", "INACTIVO")
            ->get();
        $profesores = array();
        foreach($query as $profesor) {
            $localArray = array(
                "id" => $profesor->id,
                "correo" => $profesor->correo,
                "nombres" => $profesor->nombres,
                "apellido_paterno" => $profesor->apellido_paterno,
                "apellido_materno" => $profesor->apellido_materno,
                "estado" => $profesor->estado,
                "num_contacto" => $profesor->num_contacto,
                "rol_usuario" => $profesor->rol_usuario,
                "profesor_id" => $profesor->profesor->id,
                "num_personal" => $profesor->profesor->num_personal
            );
            array_push($profesores, $localArray);
        }

        return response()->json($profesores, 200);
    }

    public function validarRegistroProfesor(Request $request) {
      $this->validate(
          $request,
          ReglasValidaciones::getValidacionesProfesor($request),
          ReglasValidaciones::getMensajesPersonalizados()
      );
    }

    public function modificarProfesor(Request $request) {
        $this->validate(
            $request,
            ReglasValidaciones::getValidacionesModificarProfesor($request),
            ReglasValidaciones::getMensajesPersonalizados()
        );

        DB::transaction(function () use ($request) {
            User::where("id", $request->id)->update([
                "correo" => $request->correo,
                "nombres" => $request->nombres,
                "apellido_paterno" => $request->apellido_paterno,
                "apellido_materno" => $request->apellido_materno,
                "num_contacto" => $request->num_contacto,
            ]);
            Profesor::where("id", $request->profesor_id)->update([
                "num_personal" => $request->num_personal
            ]);
        });
    }
}
