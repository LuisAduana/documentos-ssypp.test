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

        $cuenta = Inscripcion::where('estado_inscripcion', 'ACTIVO')->count();
        if ($cuenta !== 0) {
            throw ValidationException::withMessages([
                'inscripcion' => ['Ya existe una inscripción activa.']
            ]);
        }

        DB::transaction(function () use ($request) {
            $id = DB::table('inscripcion')->insertGetId([
                'token_inscripcion' => $this->generarRandomString(),
                'inscripcion_inicio' => $request->inscripcion_inicio,
                'fin_inscripcion' => $request->fin_inscripcion,
                'tipo_inscripcion' => $request->tipo_inscripcion,
                'estado_inscripcion' => "ACTIVO"
            ]);
            $inscripcion = DB::table('inscripcion')->where('estado_inscripcion', 'DEFAULT')->first();
            foreach($request->proyectos as $proyecto) {
                DB::update("update proyecto set estado = 'INSCRIPCION', inscripcion_id = ? where id = ?", [$id, $proyecto["id_proyecto"]]);
            }
            $idString = strval($id);
            $idInscripcion = strval($inscripcion->id);
            DB::unprepared("create event cambiar_estado_inscripcion on schedule at '".$request->fin_inscripcion."' do begin update inscripcion set estado_inscripcion = 'INACTIVO' where id = ".$idString."; update proyecto set estado = 'NO ASIGNADO', inscripcion_id = ".$idInscripcion." where estado = 'INSCRIPCION' and inscripcion_id = ".$idString."; END");
        });
    }

    public function terminarInscripcion(Request $request) {
        DB::transaction(function () use ($request) {
            $proyectos = Proyecto::where("inscripcion_id", $request->id)->get();
            $inscripcion = Inscripcion::where("estado_inscripcion", "DEFAULT")->first();
            foreach($proyectos as $proyecto) {
                Proyecto::where("estado", "INSCRIPCION")->where("id", $proyecto->id)->update([
                    "estado" => "NO ASIGNADO",
                    "inscripcion_id" => $inscripcion->id
                ]);
            }
            Inscripcion::where("id", $request->id)->update(["estado_inscripcion" => "INACTIVO"]);
            DB::unprepared("DROP EVENT cambiar_estado_inscripcion");
        });
    }

    public function cancelarInscripcion(Request $request) {
        DB::transaction(function () use ($request) {
            $proyectos = Proyecto::where("inscripcion_id", $request->id)->get();
            $users = User::where("estado", "INSCRIPCION")->get();
            $inscripcion = Inscripcion::where("estado_inscripcion", "DEFAULT")->first();
            foreach($proyectos as $proyecto) {
                Proyecto::where("id", $proyecto->id)->update([
                    "estado" => "NO ASIGNADO",
                    "inscripcion_id" => $inscripcion->id
                ]);
            }
            foreach($users as $user) {
                User::where("id", $user->id)->update([
                    "estado" => "ACTIVO"
                ]);
            }
            Inscripcion::where("id", $request->id)->update(["estado_inscripcion" => "INACTIVO"]);
            DB::unprepared("DROP EVENT cambiar_estado_inscripcion");
        });
    }

    public function obtenerInscripciones(Request $request) {
        $inscripciones = DB::table('inscripcion')
            ->where('estado_inscripcion', 'ACTIVO')
            ->orWhere('estado_inscripcion', 'INACTIVO')
            ->get();

        return response()->json($inscripciones, 200);
    }

    function generarRandomString() {
        $length = 5;
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
