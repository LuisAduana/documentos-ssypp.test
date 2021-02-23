<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use App\Models\Inscripcion;
use App\Models\Responsable;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UtilidadesController extends Controller
{
    public function obtenerProyectosSeleccionadosAlumno(Request $request) {

        $inscripcion = DB::table("inscripcion")->orderByRaw("id DESC")->limit(1)->first();
        
        $proyectos_seleccionados = array();
        for($i = 0; $i < count($request->proyectos); $i++) {
            $query = Proyecto::where("id", $request->proyectos[$i])->first();
            array_push($proyectos_seleccionados, $query);
        }
        
        $proyectos= array();
        if ($inscripcion->tipo_inscripcion == "servicio") {
            foreach($proyectos_seleccionados as $proyecto) {
                $localArray = array(
                    "id" => $proyecto->id,
                    "estado" => $proyecto->estado,
                    "nombre_dependencia" => $proyecto->dependencia->nombre_dependencia,
                    "nombre_responsable" => $proyecto->responsable->nombre_responsable,
                    "num_alumnos" => $proyecto->proyectoServicio->num_alumnos,
                    "actividades" => $proyecto->proyectoServicio->actividades,
                    "horario" => $proyecto->proyectoServicio->horario,
                    "requisitos" => $proyecto->proyectoServicio->requisitos
                );
                array_push($proyectos, $localArray);
            }
        } else {
            foreach($proyectos_seleccionados as $proyecto) {
                $localArray = array(
                    "id" => $proyecto->id,
                    "estado" => $proyecto->estado,
                    "nombre_dependencia" => $proyecto->dependencia->nombre_dependencia,
                    "nombre_responsable" => $proyecto->responsable->nombre_responsable,
                    "nombre_proyecto" => $proyecto->proyectoPractica->nombre_proyecto,
                    "descripcion_general" => $proyecto->proyectoPractica->descripcion_general,
                    "objetivo_general" => $proyecto->proyectoPractica->objetivo_general,
                    "objetivos_inmediatos" => $proyecto->proyectoPractica->objetivos_inmediatos,
                    "objetivos_mediatos" => $proyecto->proyectoPractica->objetivos_mediatos,
                    "metodologia" => $proyecto->proyectoPractica->metodologia,
                    "recursos" => $proyecto->proyectoPractica->recursos,
                    "actividades_funcionales" => $proyecto->proyectoPractica->actividades_funcionales,
                    "responsabilidades" => $proyecto->proyectoPractica->responsabilidades,
                    "duracion" => $proyecto->proyectoPractica->duracion,
                    "horario" => $proyecto->proyectoPractica->horario
                );
                array_push($proyectos, $localArray);
            }
        }

        $respuesta = array(
            "proyectos" => $proyectos,
            "tipo_inscripcion" => $inscripcion->tipo_inscripcion
        );

        return response()->json($respuesta, 200);
    }

    public function obtenerProyectosInscripcion(Request $request) {
    
        $proyectos = array();
        $tipo_inscripcion = "";

        DB::transaction(function () use (&$proyectos, &$tipo_inscripcion) {
            $cuenta = DB::table('inscripcion')->where('estado_inscripcion', 'ACTIVO')->count();
            if ($cuenta === 0) {
                throw ValidationException::withMessages(['inscripcion' => ['No existe una inscripción activa.']]);
            }
            $inscripcion = DB::table('inscripcion')->where('estado_inscripcion', 'ACTIVO')->first();

            $query = Proyecto::where('estado', 'INSCRIPCION')->get();
            $tipo_inscripcion = $inscripcion->tipo_inscripcion;

            if ($inscripcion->tipo_inscripcion == "servicio") {
                foreach ($query as $proyecto) {
                    $localArray = array(
                        'id' => $proyecto->id,
                        'estado' => $proyecto->estado,
                        'nombre_dependencia' => $proyecto->dependencia->nombre_dependencia,
                        'direccion' => $proyecto->dependencia->direccion,
                        'nombre_responsable' => $proyecto->responsable->nombre_responsable,
                        'actividades' => $proyecto->proyectoServicio->actividades,
                        'horario' => $proyecto->proyectoServicio->horario,
                        'requisitos' => $proyecto->proyectoServicio->requisitos,
                        'num_alumnos' => $proyecto->proyectoServicio->num_alumnos
                    );
                    array_push($proyectos, $localArray);
                }
            } else {
                foreach ($query as $proyecto) {
                    $localArray = array(
                        'id' => $proyecto->id,
                        'estado' => $proyecto->estado,
                        'nombre_dependencia' => $proyecto->dependencia->nombre_dependencia,
                        'direccion' => $proyecto->dependencia->direccion,
                        'nombre_responsable' => $proyecto->responsable->nombre_responsable,
                        'nombre_proyecto' => $proyecto->proyectoPractica->nombre_proyecto,
                        'descripcion_general' => $proyecto->proyectoPractica->descripcion_general,
                        'objetivo_general' => $proyecto->proyectoPractica->objetivo_general,
                        'objetivos_inmediatos' => $proyecto->proyectoPractica->objetivos_inmediatos,
                        'objetivos_mediatos' => $proyecto->proyectoPractica->objetivos_mediatos,
                        'metodologia' => $proyecto->proyectoPractica->metodologia,
                        'recursos' => $proyecto->proyectoPractica->recursos,
                        'actividades_funcionales' => $proyecto->proyectoPractica->actividades_funcionales,
                        'responsabilidades' => $proyecto->proyectoPractica->responsabilidades,
                        'duracion' => $proyecto->proyectoPractica->duracion,
                        'horario' => $proyecto->proyectoPractica->horario
                    );
                    array_push($proyectos, $localArray);
                }
            }
        });
        $respuesta = array(
            'tipo_inscripcion' => $tipo_inscripcion,
            'proyectos' => $proyectos
        );
        return response()->json($respuesta, 200);
    }

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
