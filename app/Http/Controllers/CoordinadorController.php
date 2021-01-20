<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use App\Models\Responsable;
use App\Models\Proyecto;
use App\Models\ProyectoServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CoordinadorController extends Controller
{
    public function modificarProyectoServicio(Request $request) {
        $request->validate([
            'id' => ['required', 'min:1'],
            'estado' => ['required', 'max:11', 'min:1'],
            'nombre_responsable' => ['required', 'max:120', 'min:1'],
            'nombre_dependencia' => ['required', 'max:230', 'min:1'],
            'num_alumnos' => ['required', 'max:45', 'min:1'],
            'actividades' => ['required', 'max:250', 'min:1'],
            'horario' => ['required', 'max:100', 'min:1'],
            'requisitos' => ['required', 'max:250', 'min:1']
        ]);
        DB::transaction(function () use ($request) {
            $responsable = DB::table('responsable')->where('nombre_responsable', $request->nombre_responsable)->first();
            $dependencia = DB::table('dependencia')->where('nombre_dependencia', $request->nombre_dependencia)->first();
            DB::update('update proyecto set estado = ?, responsable_id = ?, dependencia_id = ? where id = ?', [
                $request->estado, $responsable->id, $dependencia->id, $request->id
            ]);
            DB::update('update proyecto_servicio set num_alumnos = ?, actividades = ?, horario = ?, requisitos = ? where id = ?', [
                $request->num_alumnos, $request->actividades, $request->horario, $request->requisitos, $request->id_proyecto_servicio
            ]);
        });
    }

    public function cambiarEstadoProyecto(Request $request) {
        $request->validate([
            'id' => ['required'],
            'estado' => ['required', 'max:11', 'min:1']
        ]);
        DB::update('update proyecto set estado = ? where id = ?', [$request->estado, $request->id]);
    }

    public function registrarProyectoServicio(Request $request) {
        $request->validate([
            'estado' => ['required', 'max:11', 'min:1'],
            'nombre_responsable' => ['required', 'max:120', 'min:1'],
            'nombre_dependencia' => ['required', 'max:230', 'min:1'],
            'num_alumnos' => ['required', 'max:45', 'min:1'],
            'actividades' => ['required', 'max:250', 'min:1'],
            'horario' => ['required', 'max:100', 'min:1'],
            'requisitos' => ['required', 'max:250', 'min:1']
        ]);

        DB::transaction(function () use ($request) {
            $inscripcion = DB::table('inscripcion')->where('estado_inscripcion', "DEFAULT")->first();
            $responsable = DB::table('responsable')->where('nombre_responsable', $request->nombre_responsable)->first();
            $dependencia = DB::table('dependencia')->where('nombre_dependencia', $request->nombre_dependencia)->first();
            $proyecto_id = DB::table('proyecto')->insertGetId([
                'estado' => $request->estado,
                'inscripcion_id' => $inscripcion->id,
                'responsable_id' => $responsable->id,
                'dependencia_id' => $dependencia->id
            ]);
            DB::insert(
                'insert into proyecto_servicio (num_alumnos, actividades, horario, requisitos, proyecto_id) values (?, ?, ?, ?, ?)',
                [ $request->num_alumnos, $request->actividades, $request->horario, $request->requisitos, $proyecto_id ]
            );
        });
    }

    public function obtenerProyectosServicio(Request $request) {
        $query = Proyecto::all();
        $proyectos = array();
        foreach ($query as $proyecto) {
            $localArray = array(
                'id' => $proyecto->id,
                'estado' => $proyecto->estado,
                // 'inscripcion_id' => $proyecto->inscripcion_id,
                // 'responsable_id' => $proyecto->responsable_id,
                // 'dependencia_id' => $proyecto->dependencia_id,
                'id_proyecto_servicio' => $proyecto->proyectoServicio->id,
                'nombre_dependencia' => $proyecto->dependencia->nombre_dependencia,
                'num_alumnos' => $proyecto->proyectoServicio->num_alumnos,
                'actividades' => $proyecto->proyectoServicio->actividades,
                'direccion' => $proyecto->dependencia->direccion,
                'nombre_responsable' => $proyecto->responsable->nombre_responsable,
                'horario' => $proyecto->proyectoServicio->horario,
                'requisitos' => $proyecto->proyectoServicio->requisitos
            );
            array_push($proyectos, $localArray);
        }
        return response()->json($proyectos, 200);
    }

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

    public function activarDesactivarResponsable(Request $request) {
        DB::update('update responsable set estado = ? where id = ?', [$request->estado, $request->id]);
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

    public function obtenerResponsablesPorDependencia(Request $request) {
        $request->validate(['nombre_dependencia' => ['required', 'max:230', 'min:1']]);
        $dependencia = DB::table('dependencia')->where("nombre_dependencia", $request->nombre_dependencia)->first();
        $responsables = DB::table('responsable')->where('dependencia_id', $dependencia->id)->get();
        $nombres = array();
        foreach ($responsables as $responsable) {
            array_push($nombres, $responsable->nombre_responsable);
        }
        return response()->json($nombres, 200);
    }

    public function obtenerNombresDependencias(Request $request) {
        $resultado = DB::select('select nombre_dependencia from dependencia where estado = ?', ["ACTIVO"]);
        $nombres = array();
        foreach ($resultado as $dependencia) {
            array_push($nombres, $dependencia->nombre_dependencia);
        }
        return response()->json($nombres, 200);
    }
}
