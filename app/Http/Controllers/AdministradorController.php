<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdministradorController extends Controller
{
    public function registro(Request $request) 
    {
        $request->validate([
            'correo' => ['required', 'max:150', 'email', 'unique:users'],
            'password' => ['required', 'max:120', 'min:7'],
            'nombres' => ['required', 'max:90', 'min:1'],
            'apellido_paterno' => ['required', 'max:45', 'min:1'],
            'apellido_materno' => ['required', 'max:45', 'min:1'],
            'estado' => ['required', 'max:10', 'min:1'],
            'num_contacto' => ['required', 'max:20', 'min:1'],
            'rol_usuario' => ['required', 'max:13', 'min:1']
        ]);

        User::create([
            'correo' => $request->correo,
            'password' => Hash::make($request->password),
            'nombres' => $request->nombres,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'estado' => $request->estado,
            'num_contacto' => $request->num_contacto,
            'rol_usuario' => $request->rol_usuario
        ]);
    }
}
