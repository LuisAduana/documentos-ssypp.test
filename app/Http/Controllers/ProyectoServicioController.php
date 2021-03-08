<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use App\Models\Inscripcion;
use App\Models\Proyecto;
use App\Models\ProyectoServicio;
use App\Models\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProyectoServicioController extends Controller
{

    public function registrarProyectoServicio(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesProyectoServicio());

        DB::transaction(function () use ($request) {
            $inscripcion = Inscripcion::where("estado_inscripcion", "DEFAULT")->first();
            $responsable = Responsable::where("nombre_responsable", $request->nombre_responsable)->first();
            $dependencia = Dependencia::where("nombre_dependencia", $request->nombre_dependencia)->first();
            $proyecto_id = DB::table('proyecto')->insertGetId([
                'estado' => $request->estado,
                'inscripcion_id' => $inscripcion->id,
                'responsable_id' => $responsable->id,
                'dependencia_id' => $dependencia->id
            ]);
            ProyectoServicio::create([
                "num_alumnos" => $request->num_alumnos,
                "actividades" => $request->actividades,
                "horario" => $request->horario,
                "requisitos" => $request->requisitos,
                "proyecto_id" => $proyecto_id
            ]);
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

        DB::transaction(function () use ($request) {
            $proyecto = Proyecto::where("id", $request->id)->first();
            $dependencia = Dependencia::where("id", $proyecto->dependencia_id)->first();
            $responsable = Responsable::where("id", $proyecto->responsable_id)->first();
            if ($dependencia->estado == "INACTIVO" || $responsable->estado == "INACTIVO") {
                throw ValidationException::withMessages([
                    "estado" => ["La dependencia o responsable del proyecto están inactivos. Por favor actívelos."]
                ]);
            } else {
                Proyecto::where("id", $request->id)->update(["estado" => $request->estado]);
            }
        });
    }
}
