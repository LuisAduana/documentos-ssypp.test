<?php

namespace App\Http\Controllers;

use App\Models\AlumnoProyecto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{

    /**
     * Crea una nueva autenticación del SPA.
     */
    public function login(Request $request)
    {
        $request->validate([
            'correo' => ['required'],
            'password' => ['required']
        ]);

        if (Auth::attempt($request->only('correo', 'password'))) {
            return response()->json(Auth::user(), 200);
        }

        throw ValidationException::withMessages([
            'correo' => ['Las credenciales son inválidas']
        ]);
    }

    public function obtenerInformacionAlumno(Request $request) {
        $usuario = User::where("id", $request->id)->first();
        $alumno_proyecto = AlumnoProyecto::where("alumno_id", $usuario->alumno->id)->first();
        $coordinador = User::where("rol_usuario", "COORDINADOR")->first();
        $informacion = array(
            "nombres_coordinador" => $coordinador->nombres,
            "apellido_paterno_coordinador" => $coordinador->apellido_paterno,
            "apellido_materno_coordinador" => $coordinador->apellido_materno,
            "correo_coordinador" => $coordinador->correo,
            "num_coordinador" => $coordinador->num_contacto,
            "nombre_responsable" => $alumno_proyecto->proyecto->responsable->nombre_responsable,
            "correo_responsable" => $alumno_proyecto->proyecto->responsable->correo,
            "num_responsable" => $alumno_proyecto->proyecto->responsable->num_contacto
        );
        return response()->json($informacion, 200);
    }

    /**
     * Cierra la autenticación SPA en el servidor.
     */
    public function logout() 
    {
        Auth::logout();
    }
}
