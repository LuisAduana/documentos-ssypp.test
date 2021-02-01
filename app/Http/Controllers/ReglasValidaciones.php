<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReglasValidaciones {

    public static function getValidacionesInscripcion() {
        return [
            'inscripcion_inicio' => ['required', 'date_format:Y-m-d H:i:s'],
            'fin_inscripcion' => ['required', 'date_format:Y-m-d H:i:s'],
            'estado_inscripcion' => ['required', 'max:10', 'min:1']
        ];
    }

    public static function getValidacionesProyectoPractica(Request $request, bool $tipo) {
        $validaciones = self::tipoValidacion($request, $tipo, "proyecto_practica");
        return [
            'ip' => ['integer'],
            'estado' => ['required', 'max:11', 'min:1'],
            'nombre_responsable' => ['required', 'max:120', 'min:1'],
            'nombre_dependencia' => ['required', 'max:230', 'min:1'],
            'nombre_proyecto' => ['required', 'max:250', 'min:1', $validaciones],
            'descripcion_general' => ['required', 'max:250', 'min:1'],
            'objetivo_general' => ['required', 'max:250', 'min:1'],
            'objetivos_inmediatos' => ['required', 'max:250', 'min:1'],
            'objetivos_mediatos' => ['required', 'max:250', 'min:1'],
            'metodologia' => ['max:50'],
            'recursos' => ['max:250'],
            'actividades_funcionales' => ['max:250'],
            'responsabilidades' => ['max:200'],
            'duracion' => ['max:200'],
            'horario' => ['max:200']
        ];
    }

    public static function getValidacionesProyectoServicio() {
        return [
            'estado' => ['required', 'max:11', 'min:1'],
            'nombre_responsable' => ['required', 'max:120', 'min:1'],
            'nombre_dependencia' => ['required', 'max:230', 'min:1'],
            'num_alumnos' => ['required', 'max:45', 'min:1'],
            'actividades' => ['required', 'max:250', 'min:1'],
            'horario' => ['required', 'max:100', 'min:1'],
            'requisitos' => ['required', 'max:250', 'min:1']
        ];
    }

    public static function getValidacionesResponsable(Request $request, bool $tipo) {
        $validaciones = self::tipoValidacion($request, $tipo, "responsable");
        return [
            'nombre_responsable' => ['required', 'max:120', 'min:1', $validaciones],
            'cargo' => ['required', 'max:100', 'min:1'],
            'correo' => ['required', 'email', 'max:130', 'min:1'],
            'num_contacto' => ['required', 'max:20', 'min:10'],
            'estado' => ['required', 'max:15', 'min:1'],
            'nombre_dependencia' => ['required', 'max:230', 'min:1']
        ];
    }

    public static function getValidacionesDependencia(Request $request, bool $tipo) {
        $validaciones = self::tipoValidacion($request, $tipo, "dependencia");
        return [
            'nombre_dependencia' => ['required', 'max:230', 'min:1', $validaciones],
            'nombre_contacto' => ['required', 'max:200', 'min:1'],
            'direccion' => ['required', 'max:250', 'min:1'],
            'ciudad' => ['required', 'max:120', 'min:1'],
            'correo' => ['required', 'email', 'max:130', 'min:1'],
            'num_contacto' => ['required', 'max:20', 'min:10'],
            'sector' => ['required', 'max:50', 'min:1'],
            'num_us_directos' => ['required', 'max:30', 'min:1'],
            'num_us_indirectos' => ['required', 'max:30', 'min:1'],
            'estado' => ['required', 'max:15', 'min:1']
        ];
    }

    public static function getValidacionesCambioEstado() {
        return [
            'id' => ['required'],
            'estado' => ['required', 'max:11', 'min:1']
        ];
    }

    public static function getMensajesPersonalizados() {
        return [
            'nombre_dependencia.unique' => 'El nombre de la dependencia ya ha sido registrado.',
            'nombre_responsable.unique' => 'El nombre del responsable ya ha sido registrado.'
        ];
    }

     static function tipoValidacion(Request $request, bool $tipo, string $tabla) {
        $regla = "unique:".$tabla;
        if ($tipo == true) {
            return $regla;
        } else {
            return Rule::unique($tabla)->ignore($request->id);
        }
    }
}