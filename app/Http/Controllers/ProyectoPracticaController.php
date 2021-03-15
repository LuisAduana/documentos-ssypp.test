<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use App\Models\Inscripcion;
use App\Models\Proyecto;
use App\Models\ProyectoPractica;
use App\Models\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProyectoPracticaController extends Controller
{

    public function registrarProyectoPractica(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesProyectoPractica($request, true));

        DB::transaction(function () use ($request) {
            $inscripcion = Inscripcion::where("estado", "DEFAULT")->first();
            $dependencia = Dependencia::where("nombre_dependencia", $request->nombre_dependencia)->first();
            $responsable = Responsable::where("nombre_responsable", $request->nombre_responsable)->first();
            $proyecto_id = DB::table("proyecto")->insertGetId([
                "estado" => $request->estado,
                "inscripcion_id" => $inscripcion->id,
                "dependencia_id" => $dependencia->id,
                "responsable_id" => $responsable->id
            ]);
            ProyectoPractica::create([
                "nombre_proyecto" => $request->nombre_proyecto,
                "descripcion_general" => $request->descripcion_general,
                "objetivo_general" => $request->objetivo_general,
                "objetivos_inmediatos" => $request->objetivos_inmediatos,
                "objetivos_mediatos" => $request->objetivos_mediatos,
                "metodologia" => $request->metodologia,
                "recursos" => $request->recursos,
                "actividades_funcionales" => $request->actividades_funcionales,
                "responsabilidades" => $request->responsabilidades,
                "duracion" => $request->duracion,
                "horario" => $request->horario,
                "proyecto_id"  => $proyecto_id
            ]);
        });
    }

    public function modificarProyectoPractica(Request $request) {
        $request->validate(ReglasValidaciones::getValidacionesProyectoPractica($request, false));

        DB::transaction(function () use ($request) {
            $responsable = Responsable::where("nombre_responsable", $request->nombre_responsable)->first();
            $dependencia = Dependencia::where("nombre_dependencia", $request->nombre_dependencia)->first();
            Proyecto::where("id", $request->proyecto_id)->update([
                "dependencia_id" => $dependencia->id,
                "responsable_id" => $responsable->id
            ]);
            ProyectoPractica::where("id", $request->id)->update([
                "nombre_proyecto" => $request->nombre_proyecto,
                "descripcion_general" => $request->descripcion_general,
                "objetivo_general" => $request->objetivo_general,
                "objetivos_inmediatos" => $request->objetivos_inmediatos,
                "objetivos_mediatos" => $request->objetivos_mediatos,
                "metodologia" => $request->metodologia,
                "recursos" => $request->recursos,
                "actividades_funcionales" => $request->actividades_funcionales,
                "responsabilidades" => $request->responsabilidades,
                "duracion" => $request->duracion,
                "horario" => $request->horario,
            ]);
        });
    }

    public function obtenerProyectosPractica(Request $request) {
        $query = ProyectoPractica::get();

        $proyectos = array();
        foreach ($query as $proyecto) {
            $localArray = array(
                "id" => $proyecto->id,
                "nombre_proyecto" => $proyecto->nombre_proyecto,
                "descripcion_general" => $proyecto->descripcion_general,
                "objetivo_general" => $proyecto->objetivo_general,
                "objetivos_inmediatos" => $proyecto->objetivos_inmediatos,
                "objetivos_mediatos" => $proyecto->objetivos_mediatos,
                "metodologia" => $proyecto->metodologia,
                "recursos" => $proyecto->recursos,
                "actividades_funcionales" => $proyecto->actividades_funcionales,
                "responsabilidades" => $proyecto->responsabilidades,
                "duracion" => $proyecto->duracion,
                "horario" => $proyecto->horario,
                "proyecto_id" => $proyecto->proyecto_id,
                "estado" => $proyecto->proyecto->estado,
                "inscripcion_id" => $proyecto->proyecto->inscripcion_id,
                "responsable_id" => $proyecto->proyecto->responsable_id,
                "dependencia_id" => $proyecto->proyecto->dependencia_id,
                "nombre_dependencia" => $proyecto->proyecto->dependencia->nombre_dependencia,
                "nombre_responsable" => $proyecto->proyecto->responsable->nombre_responsable
            );
            array_push($proyectos, $localArray);
        }
        if ($request->tipo_consulta == "ACTIVO") {
            $proyectosActivos = array();
            foreach ($proyectos as $proyecto) {
                if ($proyecto["estado"] == "ACTIVO") {
                    array_push($proyectosActivos, $proyecto);
                }
            }
            return response()->json($proyectosActivos, 200);
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
                Proyecto::where("id", $request->proyecto_id)->update(["estado" => $request->estado]);
            }
        });
    }
}
