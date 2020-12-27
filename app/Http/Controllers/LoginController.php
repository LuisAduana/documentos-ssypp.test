<?php

namespace App\Http\Controllers;

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

    /**
     * Cierra la autenticación SPA en el servidor.
     */
    public function logout() 
    {
        Auth::logout();
    }
}
