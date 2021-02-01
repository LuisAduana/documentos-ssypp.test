<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
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
                'estado_inscripcion' => $request->estado_inscripcion
            ]);
            $idString = strval($id);
            DB::unprepared("create event cambiar_estado_inscripcion on schedule at '".$request->fin_inscripcion."' do update inscripcion set estado_inscripcion = 'INACTIVO' where id = ".$idString);
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
