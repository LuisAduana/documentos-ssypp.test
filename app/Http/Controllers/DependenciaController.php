<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DependenciaController extends Controller
{
    public function registrarDependencia(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesDependencia($request, true));

        Dependencia::create([
            'nombre_dependencia' => $request->nombre_dependencia,
            'nombre_contacto' => $request->nombre_contacto,
            'direccion' => $request->direccion,
            'ciudad' => $request->ciudad,
            'correo' => $request->correo,
            'num_contacto' => $request->num_contacto,
            'sector' => $request->sector,
            'num_us_directos' => $request->num_us_directos,
            'num_us_indirectos' => $request->num_us_indirectos,
            'estado' => $request->estado
        ]);
    }

    public function modificarDependencia(Request $request) {
        $rules = ReglasValidaciones::getValidacionesDependencia($request, false);
        $customMessages = ReglasValidaciones::getMensajesPersonalizados();

        $this->validate($request, $rules, $customMessages);

        DB::update('update dependencia set nombre_dependencia = ?, nombre_contacto = ?, 
        direccion = ?, ciudad = ?, correo = ?, num_contacto = ?, sector = ?, num_us_directos = ?, 
        num_us_indirectos = ?, estado = ? where id = ?', [
            $request->nombre_dependencia, $request->nombre_contacto, $request->direccion, $request->ciudad,
            $request->correo, $request->num_contacto, $request->sector, $request->num_us_directos,
            $request->num_us_indirectos, $request->estado, $request->id
        ]);
    }

    public function obtenerDependencias(Request $request) {
        $query = Dependencia::all();

        $dependencias = array();

        foreach ($query as $dependencia) {
            $localArray = array(
                'id' => $dependencia->id,
                'nombre_dependencia' => $dependencia->nombre_dependencia,
                'nombre_contacto' => $dependencia->nombre_contacto,
                'direccion' => $dependencia->direccion,
                'ciudad' => $dependencia->ciudad,
                'correo' => $dependencia->correo,
                'num_contacto' => $dependencia->num_contacto,
                'sector' => $dependencia->sector,
                'num_us_directos' => $dependencia->num_us_directos,
                'num_us_indirectos' => $dependencia->num_us_indirectos,
                'estado' => $dependencia->estado
            );
            array_push($dependencias, $localArray);
        }

        return response()->json($dependencias, 200);
    }

    public function activarDesactivarDependencia(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesCambioEstado());
        DB::transaction(function () use ($request) {
            DB::update('update dependencia set estado = ? where id = ?', [$request->estado, $request->id]);
            if ($request->estado == "INACTIVO") {
                DB::update('update responsable set estado = ? where dependencia_id = ?', [$request->estado, $request->id]);
            }
        });
    }
}
