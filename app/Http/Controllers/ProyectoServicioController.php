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
      $this->validate(
        $request,
        ReglasValidaciones::getValidacionesProyectoServicio($request, true),
        ReglasValidaciones::getMensajesPersonalizados()
      );

        DB::transaction(function () use ($request) {
            $inscripcion = Inscripcion::where("estado", "DEFAULT")->first();
            $responsable = Responsable::where("nombre_responsable", $request->nombre_responsable)->first();
            $dependencia = Dependencia::where("nombre_dependencia", $request->nombre_dependencia)->first();
            $proyecto_id = DB::table('proyecto')->insertGetId([
                "estado" => $request->estado,
                "inscripcion_id" => $inscripcion->id,
                "responsable_id" => $responsable->id,
                "dependencia_id" => $dependencia->id
            ]);
            ProyectoServicio::create([
                "nombre_proyecto" => $request->nombre_proyecto,
                "num_alumnos" => $request->num_alumnos,
                "actividades" => $request->actividades,
                "horario" => $request->horario,
                "requisitos" => $request->requisitos,
                "proyecto_id" => $proyecto_id
            ]);
        });
    }

    public function modificarProyectoServicio(Request $request) {
      $this->validate(
        $request,
        ReglasValidaciones::getValidacionesProyectoServicio($request, false),
        ReglasValidaciones::getMensajesPersonalizados()
      );

        DB::transaction(function () use ($request) {
            $responsable = Responsable::where("nombre_responsable", $request->nombre_responsable)->first();
            $dependencia = Dependencia::where("nombre_dependencia", $request->nombre_dependencia)->first();
            
            Proyecto::where("id", $request->proyecto_id)->update([
                "estado" => $request->estado,
                "responsable_id" => $request->responsable_id,
                "dependencia_id" => $request->dependencia_id
            ]);
            ProyectoServicio::where("id", $request->id)->update([
                "nombre_proyecto" => $request->nombre_proyecto,
                "num_alumnos" => $request->num_alumnos,
                "actividades" => $request->actividades,
                "horario" => $request->horario,
                "requisitos" => $request->requisitos
            ]);
        });
    }

    public function obtenerProyectosServicio(Request $request) {
        $query = ProyectoServicio::get();

        $proyectos = array();
        foreach ($query as $proyecto) {
            $localArray = array(
                "id" => $proyecto->id,
                "nombre_proyecto" => $proyecto->nombre_proyecto,
                "num_alumnos" => $proyecto->num_alumnos,
                "actividades" => $proyecto->actividades,
                "horario" => $proyecto->horario,
                "requisitos" => $proyecto->requisitos,
                "proyecto_id" => $proyecto->proyecto->id,
                "estado" => $proyecto->proyecto->estado,
                "inscripcion_id" => $proyecto->proyecto->inscripcion_id,
                "responsable_id" => $proyecto->proyecto->responsable_id,
                "dependencia_id"=> $proyecto->proyecto->dependencia_id,
                "nombre_dependencia" => $proyecto->proyecto->dependencia->nombre_dependencia,
                "direccion" => $proyecto->proyecto->dependencia->direccion,
                "nombre_responsable" => $proyecto->proyecto->responsable->nombre_responsable,
            );
            array_push($proyectos, $localArray);
        }

        if ($request->tipo_consulta == "ACTIVO") {
            $proyectosNoAsignados = array();
            foreach ($proyectos as $proyecto) {
                if ($proyecto["estado"] == "ACTIVO") {
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
