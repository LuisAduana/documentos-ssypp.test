<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\ProyectoPractica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProyectoPracticaController extends Controller
{

    public function registrarProyectoPractica(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesProyectoPractica($request, true));

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
                'insert into proyecto_practica (nombre_proyecto, descripcion_general, objetivo_general, 
                                                objetivos_inmediatos, objetivos_mediatos, metodologia,
                                                recursos, actividades_funcionales, responsabilidades,
                                                duracion, horario, proyecto_id) 
                                        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [ $request->nombre_proyecto, $request->descripcion_general, $request->objetivo_general,
                  $request->objetivos_inmediatos, $request->objetivos_mediatos, $request->metodologia,
                  $request->recursos, $request->actividades_funcionales, $request->responsabilidades,
                  $request->duracion, $request->horario, $proyecto_id ]
            );
        });
    }

    public function modificarProyectoPractica(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesProyectoPractica($request, false));

        DB::transaction(function () use ($request) {
            $responsable = DB::table('responsable')->where('nombre_responsable', $request->nombre_responsable)->first();
            $dependencia = DB::table('dependencia')->where('nombre_dependencia', $request->nombre_dependencia)->first();
            DB::update('update proyecto set responsable_id = ?, dependencia_id = ? where id = ?', [
                $responsable->id, $dependencia->id, $request->id_proyecto
            ]);
            DB::update('update proyecto_practica set nombre_proyecto = ?, descripcion_general = ?, objetivo_general = ?, 
                        objetivos_inmediatos = ?, objetivos_mediatos = ?, metodologia = ?, recursos = ?,
                        actividades_funcionales = ?, responsabilidades = ?, duracion = ?, horario = ?, proyecto_id = ?
                        where id = ?', 
                [
                    $request->nombre_proyecto, $request->descripcion_general, $request->objetivo_general,
                    $request->objetivos_inmediatos, $request->objetivos_mediatos, $request->metodologia,
                    $request->recursos, $request->actividades_funcionales, $request->responsabilidades,
                    $request->duracion, $request->horario, $request->id_proyecto, $request->id
                ]
            );
        });
    }

    public function obtenerProyectosPractica(Request $request) {
        $query = ProyectoPractica::all();

        $proyectos = array();
        foreach ($query as $proyecto) {
            $localArray = array(
                'id' => $proyecto->id,
                'nombre_proyecto' => $proyecto->nombre_proyecto,
                'descripcion_general' => $proyecto->descripcion_general,
                'objetivo_general' => $proyecto->objetivo_general,
                'objetivos_inmediatos' => $proyecto->objetivos_inmediatos,
                'objetivos_mediatos' => $proyecto->objetivos_mediatos,
                'metodologia' => $proyecto->metodologia,
                'recursos' => $proyecto->recursos,
                'actividades_funcionales' => $proyecto->actividades_funcionales,
                'responsabilidades' => $proyecto->responsabilidades,
                'duracion' => $proyecto->duracion,
                'horario' => $proyecto->horario,
                'proyecto_id' => $proyecto->proyecto_id,
                'id_proyecto' => $proyecto->proyecto->id,
                'estado' => $proyecto->proyecto->estado,
                'inscripcion_id' => $proyecto->proyecto->inscripcion_id,
                'responsable_id' => $proyecto->proyecto->responsable_id,
                'dependencia_id' => $proyecto->proyecto->dependencia_id,
                'nombre_dependencia' => $proyecto->proyecto->dependencia->nombre_dependencia,
                'nombre_responsable' => $proyecto->proyecto->responsable->nombre_responsable
            );
            array_push($proyectos, $localArray);
        }

        if ($request->tipo_consulta == "NO ASIGNADO") {
            $proyectosNoAsignados = array();
            foreach ($proyectos as $proyecto) {
                if ($proyecto["estado"] == "NO ASIGNADO") {
                    array_push($proyectosNoAsignados, $proyecto);
                }
            }
            return response()->json($proyectosNoAsignados, 200);
        }

        return response()->json($proyectos, 200);
    }

    public function cambiarEstadoProyecto(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesCambioEstado());

        DB::update('update proyecto set estado = ? where id = ?', [$request->estado, $request->id]);
    }
}
