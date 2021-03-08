<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use App\Models\Proyecto;
use App\Models\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DependenciaController extends Controller
{
    public function registrarDependencia(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesDependencia($request, true));

        Dependencia::create([
            "nombre_dependencia" => $request->nombre_dependencia,
            "nombre_contacto" => $request->nombre_contacto,
            "direccion" => $request->direccion,
            "ciudad" => $request->ciudad,
            "correo" => $request->correo,
            "num_contacto" => $request->num_contacto,
            "sector" => $request->sector,
            "num_us_directos" => $request->num_us_directos,
            "num_us_indirectos" => $request->num_us_indirectos,
            "estado" => $request->estado
        ]);
    }

    public function modificarDependencia(Request $request) {
        $this->validate(
            $request,
            ReglasValidaciones::getValidacionesDependencia($request, false),
            ReglasValidaciones::getMensajesPersonalizados()
        );

        Dependencia::where("id", $request->id)->update([
            "nombre_dependencia" => $request->nombre_dependencia,
            "nombre_contacto" => $request->nombre_contacto,
            "direccion" => $request->direccion,
            "ciudad" => $request->ciudad,
            "correo" => $request->correo,
            "num_contacto" => $request->num_contacto,
            "sector" => $request->sector,
            "num_us_directos" => $request->num_us_directos,
            "num_us_indirectos" => $request->num_us_indirectos,
            "estado" => $request->estado
        ]);;
    }

    public function obtenerDependencias(Request $request) {
        return response()->json(Dependencia::get(), 200);
    }

    public function activarDesactivarDependencia(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesCambioEstado());
        DB::transaction(function () use ($request) {
            Dependencia::where("id", $request->id)->update(["estado" => $request->estado]);
            if ($request->estado == "INACTIVO") {
                Responsable::where("dependencia_id", $request->id)->update(["estado" => "INACTIVO"]);
                Proyecto::where("dependencia_id", $request->id)->update(["estado" => "INACTIVO"]);;
            }
        });
    }
}
