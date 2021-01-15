<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use App\Models\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CoordinadorController extends Controller
{
    public function modificarResponsable(Request $request) {
        $rules = [
            'nombre_responsable' => ['required', 'max:120', Rule::unique('responsable')->ignore($request->id)],
            'cargo' => ['required', 'max:100', 'min:1'],
            'correo' => ['required', 'email', 'max:130'],
            'num_contacto' => ['required', 'max:20', 'min:10'],
            'estado' => ['required', 'max:15', 'min:1'],
            'nombre_dependencia' => ['required', 'max:230', 'min:1']
        ];

        $customMessages = [
            'nombre_responsable.unique' => 'El nombre del responsable ya ha sido registrado.',
        ];

        $this->validate($request, $rules, $customMessages);

        DB::transaction(function () use ($request) {
            $dependencia = DB::table('dependencia')->where('nombre_dependencia', $request->nombre_dependencia)->first();
            DB::update('update responsable set nombre_responsable = ?, cargo = ?, correo = ?, num_contacto = ?, estado = ?, dependencia_id = ? where id = ?', [
                $request->nombre_responsable, $request->cargo, $request->correo, $request->num_contacto, $request->estado, $dependencia->id, $request->id
            ]);
        });
    }

    public function registrarResponsable(Request $request) {
        $request->validate([
            'nombre_responsable' => ['required', 'max:120', 'min:1', 'unique:responsable'],
            'cargo' => ['required', 'max:100', 'min:1'],
            'correo' => ['required', 'email', 'max:130'],
            'num_contacto' => ['required', 'max:20', 'min:10'],
            'estado' => ['required', 'max:11', 'min:1'],
            'nombre_dependencia' => ['required', 'max:230', 'min:1']
        ]);

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

    public function registrarDependencia(Request $request) {
        $request->validate([
            'nombre_dependencia' => ['required', 'max:230', 'min:1', 'unique:dependencia'],
            'nombre_contacto' => ['required', 'max:200', 'min:1'],
            'direccion' => ['required', 'max:250', 'min:1'],
            'ciudad' => ['required', 'max:120', 'min:1'],
            'correo' => ['required', 'email', 'max:130', 'min:1'],
            'num_contacto' => ['required', 'max:20', 'min:1'],
            'sector' => ['required', 'max:50', 'min:1'],
            'num_us_directos' => ['required', 'max:30', 'min:1'],
            'num_us_indirectos' => ['required', 'max:30', 'min:1'],
            'estado' => ['required', 'max:11', 'min:1']
        ]);

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
        $rules = [
            'nombre_dependencia' => ['required', 'max:230', Rule::unique('dependencia')->ignore($request->id)],
            'nombre_contacto' => ['required', 'max:200', 'min:1'],
            'direccion' => ['required', 'max:250', 'min:1'],
            'ciudad' => ['required', 'max:120', 'min:1'],
            'correo' => ['required', 'max:130', 'email'],
            'num_contacto' => ['required', 'max:20', 'min:10'],
            'sector' => ['required', 'max:50', 'min:1'],
            'num_us_directos' => ['required', 'max:30', 'min:1'],
            'num_us_indirectos' => ['required', 'max:30', 'min:1'],
            'estado' => ['required', 'max:15', 'min:1']
        ];

        $customMessages = [
            'nombre_dependencia.unique' => 'El nombre de la dependencia ya ha sido registrado.',
        ];

        $this->validate($request, $rules, $customMessages);

        DB::update('update dependencia set nombre_dependencia = ?, nombre_contacto = ?, 
        direccion = ?, ciudad = ?, correo = ?, num_contacto = ?, sector = ?, num_us_directos = ?, 
        num_us_indirectos = ?, estado = ? where id = ?', [
            $request->nombre_dependencia, $request->nombre_contacto, $request->direccion, $request->ciudad,
            $request->correo, $request->num_contacto, $request->sector, $request->num_us_directos,
            $request->num_us_indirectos, $request->estado, $request->id
        ]);
    }

    public function activarDesactivarDependencia(Request $request) {
        DB::update('update dependencia set estado = ? where id = ?', [$request->estado, $request->id]);
    }

    public function obtenerNombresDependencias(Request $request) {
        $resultado = DB::select('select nombre_dependencia from dependencia where estado = ?', ["ACTIVO"]);
        $nombres = array();
        foreach ($resultado as $dependencia) {
            array_push($nombres, $dependencia->nombre_dependencia);
        }
        return response()->json($nombres, 200);
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
}
