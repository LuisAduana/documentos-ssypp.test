<?php

namespace App\Http\Controllers;

use App\Models\Responsable;
use App\Models\Dependencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ResponsableController extends Controller
{
    public function registrarResponsable(Request $request) {
        $request->validate(
            ReglasValidaciones::getValidacionesResponsable($request, true)
        );

        DB::transaction(function () use ($request) {
            $dependencia = Dependencia::where("nombre_dependencia", $request->nombre_dependencia)->first();
            Responsable::create([
                "nombre_responsable" => $request->nombre_responsable,
                "cargo" => $request->cargo,
                "correo" => $request->correo,
                "num_contacto" => $request->num_contacto,
                "estado" => $request->estado,
                "dependencia_id" => $dependencia->id
            ]);
        });
    }

    public function modificarResponsable(Request $request) {
        $this->validate(
            $request,
            ReglasValidaciones::getValidacionesResponsable($request, false),
            ReglasValidaciones::getMensajesPersonalizados()
        );

        DB::transaction(function () use ($request) {
            $dependencia = Dependencia::where("nombre_dependencia", $request->nombre_dependencia)->first();

            Responsable::where("id", $request->id)->update([
                "nombre_responsable" => $request->nombre_responsable,
                "cargo" => $request->cargo,
                "correo" => $request->correo,
                "num_contacto" => $request->num_contacto,
                "estado" => $request->estado,
                "dependencia_id"  => $dependencia->id
            ]);
        });
    }

    public function obtenerResponsables(Request $request) {
        $query = Responsable::get();

        $responsables = array();
        foreach ($query as $responsable) {
            $localArray = array(
                "id" => $responsable->id,
                "nombre_responsable" => $responsable->nombre_responsable,
                "cargo" => $responsable->cargo,
                "correo" => $responsable->correo,
                "num_contacto" => $responsable->num_contacto,
                "estado" => $responsable->estado,
                "dependencia_id" => $responsable->dependencia_id,
                "nombre_dependencia" => $responsable->dependencia->nombre_dependencia
            );
            array_push($responsables, $localArray);
        }

        return response()->json($responsables, 200);
    }

    public function activarDesactivarResponsable(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesCambioEstado());
        DB::transaction(function () use ($request) {
            $dependencia = Dependencia::where("id", $request->id)->first();
            if ($dependencia->estado == "INACTIVO") {
                throw ValidationException::withMessages([
                    "estado" => ["La dependencia a la que pertenece el responsable está desactivada. Por favor actívela."]
                ]);
            } else {
                Responsable::where("id", $request->id)->update(["estado" => $request->estado]);
            }
        });
    }
}
