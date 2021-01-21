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
        $request->validate(ReglasValidaciones::getValidacionesResponsable($request, true));

        DB::transaction(function () use ($request) {
            $dependencia = DB::table('dependencia')->where('nombre_dependencia', $request->nombre_dependencia)->first();
            DB::insert(
                'insert into responsable (nombre_responsable, cargo, correo, 
                num_contacto, estado, dependencia_id) values (?, ?, ?, ?, ?, ?)', [
                $request->nombre_responsable, $request->cargo, $request->correo,
                $request->num_contacto, $request->estado, $dependencia->id]
            );
        });
    }

    public function modificarResponsable(Request $request) {
        $rules = ReglasValidaciones::getValidacionesResponsable($request, false);
        $customMessages = ReglasValidaciones::getMensajesPersonalizados();

        $this->validate($request, $rules, $customMessages);

        DB::transaction(function () use ($request) {
            $dependencia = DB::table('dependencia')->where('nombre_dependencia', $request->nombre_dependencia)->first();
            DB::update('update responsable set nombre_responsable = ?, cargo = ?, correo = ?, num_contacto = ?, estado = ?, dependencia_id = ? where id = ?', [
                $request->nombre_responsable, $request->cargo, $request->correo, $request->num_contacto, $request->estado, $dependencia->id, $request->id
            ]);
        });
    }

    public function obtenerResponsables(Request $request) {
        $query = Responsable::all();

        $responsables = array();
        foreach ($query as $responsable) {
            $localArray = array(
                'id' => $responsable->id,
                'nombre_responsable' => $responsable->nombre_responsable,
                'cargo' => $responsable->cargo,
                'correo' => $responsable->correo,
                'num_contacto' => $responsable->num_contacto,
                'estado' => $responsable->estado,
                'dependencia_id' => $responsable->dependencia_id,
                'nombre_dependencia' => $responsable->dependencia->nombre_dependencia
            );
            array_push($responsables, $localArray);
        }

        return response()->json($responsables, 200);
    }

    public function activarDesactivarResponsable(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesCambioEstado());
        DB::transaction(function () use ($request) {
            $responsable = DB::table('responsable')->find($request->id);
            $dependencia = DB::table('dependencia')->find($responsable->dependencia_id);
            if ($dependencia->estado == "INACTIVO") {
                throw ValidationException::withMessages([
                    'estado' => ['La dependencia a la que pertenece el responsable está desactivada. Por favor actívela.']
                ]);
            } else {
                DB::update('update responsable set estado = ? where id = ?', [$request->estado, $request->id]);
            }
        });
    }
}
