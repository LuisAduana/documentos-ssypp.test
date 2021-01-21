<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use App\Models\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UtilidadesController extends Controller
{
    public function obtenerNombresResponsablesPorDependencia(Request $request) {
        $request->validate(['nombre_dependencia' => ['required', 'max:230', 'min:1']]);

        $dependencia = DB::table('dependencia')->where("nombre_dependencia", $request->nombre_dependencia)->first();
        $responsables = DB::table('responsable')
            ->where('dependencia_id', $dependencia->id)
            ->where('estado', 'ACTIVO')
            ->get();

        $nombres = array();
        foreach ($responsables as $responsable) {
            array_push($nombres, $responsable->nombre_responsable);
        }

        return response()->json($nombres, 200);
    }

    public function obtenerNombresDependencias(Request $request) {
        $dependencias = DB::table('dependencia')->where("estado", "ACTIVO")->get();

        $nombres = array();
        foreach ($dependencias as $dependencia) {
            array_push($nombres, $dependencia->nombre_dependencia);
        }

        return response()->json($nombres, 200);
    }
}
