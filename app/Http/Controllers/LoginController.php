<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoProyecto;
use App\Models\Documento;
use App\Models\Profesor;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class LoginController extends Controller
{

    /**
     * Crea una nueva autenticación del SPA.
     */
    public function login(Request $request)
    {
      $request->validate([
          "correo" => ["required"],
          "password" => ["required"]
      ]);

      $usuario = User::where("correo", $request->correo)->first();

      if (Auth::attempt(["correo" => $request->correo, "password" => $request->password, "estado" => "ACTIVO"])) {
        return response()->json((object) array("estado" => $usuario->estado, "rol_usuario" => $usuario->rol_usuario), 200);
      } else {
        if ($usuario == null || !Hash::check($request->password, $usuario->password)) {
          throw ValidationException::withMessages([
            "correo" => ["Las credenciales son inválidas"]
          ]);
        } else {
          return response()->json((object) array("estado" => $usuario->estado), 200);
        }
      }
    }

    public function obtenerInformacionAlumno(Request $request) {
      $request->validate(["id" => ["required"]]);

      return response()->json(
        DB::transaction(function () use ($request) {
          $user = User::with("alumno")->where("id", $request->id)->first();
          $lastProyect = DB::table("alumno_proyecto")->where("alumno_id", $user->alumno->id)->orderByRaw("created_at DESC")->limit(1)->first();
          if ($lastProyect->tipo_proyecto == "practicas") {
            $proyecto = Proyecto::with("proyectoPractica")->with("dependencia")->with("responsable")->where("id", $lastProyect->proyecto_id)->first();
          } else {
            $proyecto = Proyecto::with("proyectoServicio")->with("dependencia")->with("responsable")->where("id", $lastProyect->proyecto_id)->first();
          }
          // $coordinador = User::where([["rol_usuario", "COORDINADOR"], ["estado", "ACTIVO"]])->first();
          return array(
            "proyecto" => $proyecto,
            // "coordinador" => $coordinador,
            "tipo_proyecto" => $lastProyect->tipo_proyecto
          );
        })
      , 200);
    }

    public function obtenerInformacionProfesor(Request $request) {
      return response()->json(Profesor::where("users_id", $request->id)->first(), 200);
    }

    /**
     * Cierra la autenticación SPA en el servidor.
     */
    public function logout() 
    {
        Auth::logout();
    }
}
