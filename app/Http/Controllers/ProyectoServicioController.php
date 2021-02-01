<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\ProyectoServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProyectoServicioController extends Controller
{

    public function registrarProyectoServicio(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesProyectoServicio());

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

    public function modificarProyectoServicio(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesProyectoServicio());

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

    public function obtenerProyectosServicio(Request $request) {
        $query = ProyectoServicio::all();

        $proyectos = array();
        foreach ($query as $proyecto) {
            $localArray = array(
                'id' => $proyecto->id,
                'num_alumnos' => $proyecto->num_alumnos,
                'actividades' => $proyecto->actividades,
                'horario' => $proyecto->horario,
                'requisitos' => $proyecto->requisitos,
                'proyecto_id' => $proyecto->proyecto_id,
                'estado' => $proyecto->estado,
                'id_proyecto' => $proyecto->proyecto->id,
                'estado' => $proyecto->proyecto->estado,
                'inscripcion_id' => $proyecto->proyecto->inscripcion_id,
                'responsable_id' => $proyecto->proyecto->responsable_id,
                'dependencia_id' => $proyecto->proyecto->dependencia_id,
                'nombre_dependencia' => $proyecto->proyecto->dependencia->nombre_dependencia,
                'direccion' => $proyecto->proyecto->dependencia->direccion,
                'nombre_responsable' => $proyecto->proyecto->responsable->nombre_responsable,
            );
            array_push($proyectos, $localArray);
        }
        
        return response()->json($proyectos, 200);
    }

    public function cambiarEstadoProyecto(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesCambioEstado());

        DB::update('update proyecto set estado = ? where id = ?', [$request->estado, $request->id]);
    }
}
