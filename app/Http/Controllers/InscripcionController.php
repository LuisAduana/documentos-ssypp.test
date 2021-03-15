<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InscripcionController extends Controller
{
  public function registrarInscripcion(Request $request) {
    $request->validate(ReglasValidaciones::getValidacionesInscripcion());

    DB::transaction(function () use ($request) {

      $cuenta = Inscripcion::where("estado", "ACTIVO")->count();
      if ($cuenta !== 0) {
          throw ValidationException::withMessages([
            "inscripcion" => "Ya existe una inscripción activa."
          ]);
      }

      $usuario = User::where("estado", "INSCRIPCION")->count();
      if ($usuario !== 0) {
        throw ValidationException::withMessages([
          "inscripcion" => "Primero asigne los proyectos a los alumnos antes de registrar otra inscripción."
        ]);
      }

      $id = DB::table("inscripcion")->insertGetId([
          "token_inscripcion" => $this->generarRandomString(),
          "inscripcion_inicio" => $request->inscripcion_inicio,
          "fin_inscripcion" => $request->fin_inscripcion,
          "tipo_inscripcion" => $request->tipo_inscripcion,
          "estado" => "ACTIVO"
      ]);
      $inscripcion = Inscripcion::where("estado", "DEFAULT")->first();

      foreach($request->proyectos as $proyecto) {
        Proyecto::where("id", $proyecto["proyecto_id"])->update([
          "estado" => "INSCRIPCION",
          "inscripcion_id" => $id
        ]);
      }
      $idString = strval($id);
      $idInscripcion = strval($inscripcion->id);
      DB::unprepared("create event cambiar_estado_inscripcion on schedule at '".$request->fin_inscripcion."' do begin update inscripcion set estado = 'INACTIVO' where id = ".$idString."; update proyecto set estado = 'ACTIVO', inscripcion_id = ".$idInscripcion." where estado = 'INSCRIPCION' and inscripcion_id = ".$idString."; END");
    });
  }

    public function terminarInscripcion(Request $request) {
      $request->validate(ReglasValidaciones::getValidacionesCambioEstado());
        DB::transaction(function () use ($request) {
            $proyectos = Proyecto::where("inscripcion_id", $request->id)->get();
            $inscripcion = Inscripcion::where("estado", "DEFAULT")->first();
            foreach($proyectos as $proyecto) {
                Proyecto::where("estado", "INSCRIPCION")->where("id", $proyecto->id)->update([
                    "estado" => "ACTIVO",
                    "inscripcion_id" => $inscripcion->id
                ]);
            } 
            Inscripcion::where("id", $request->id)->update(["estado" => "INACTIVO"]);
            DB::unprepared("DROP EVENT cambiar_estado_inscripcion");
        });
    }

    public function cancelarInscripcion(Request $request) {
        DB::transaction(function () use ($request) {
            $proyectos = Proyecto::where("inscripcion_id", $request->id)->get();
            $users = User::where("estado", "INSCRIPCION")->get();
            $inscripcion = Inscripcion::where("estado", "DEFAULT")->first();
            foreach($proyectos as $proyecto) {
                Proyecto::where("id", $proyecto->id)->update([
                    "estado" => "ACTIVO",
                    "inscripcion_id" => $inscripcion->id
                ]);
            }
            foreach($users as $user) {
                User::where("id", $user->id)->update([
                    "estado" => "CANCELADO"
                ]);
            }
            Inscripcion::where("id", $request->id)->update(["estado" => "INACTIVO"]);
            DB::unprepared("DROP EVENT cambiar_estado_inscripcion");
        });
    }

    public function obtenerInscripciones(Request $request) {
        return response()->json(
            Inscripcion::where("estado", "ACTIVO")
              ->orWhere("estado", "INACTIVO")
              ->get(),
            200);
    }

    function generarRandomString() {
        $length = 5;
        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charactersLength = strlen($characters);
        $randomString = "";
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
