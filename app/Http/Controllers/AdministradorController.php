<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdministradorController extends Controller
{
    public function registrarCoordinador(Request $request)
    {
        $rules = [
            'correo' => 'required|max:150|email|unique:users',
            'password' => 'required|min:7|max:120',
            'nombres' => 'required|min:1|max:90',
            'apellido_paterno' => 'required|min:1|max:45',
            'apellido_materno' => 'required|min:1|max:45',
            'estado' => 'required|min:1|max:10',
            'num_contacto' => 'required|min:1|max:20',
            'rol_usuario' => 'required|min:1|max:13',
            'num_personal' => 'required|min:10|max:10|unique:profesor'
        ];

        $customMessages = [
            'correo.unique' => 'El correo electrónico :attribute ya ha sido registrado.',
            'num_personal.unique' => 'El número de personal :attribute ya ha sido registrado.',
        ];

        $this->validate($request, $rules, $customMessages);

        DB::transaction(function () use ($request) {
            DB::insert(
                'insert into users (correo, password, nombres, 
                apellido_paterno, apellido_materno, estado, num_contacto, 
                rol_usuario) values (?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->correo, Hash::make($request->password), $request->nombres,
                $request->apellido_paterno, $request->apellido_materno,
                $request->estado, $request->num_contacto, $request->rol_usuario
                ]
            );

            $id = DB::table('users')->where('correo', $request->correo)->value('id');

            DB::insert(
                'insert into profesor (num_personal, users_id) values (?, ?)',
                [$request->num_personal, $id]
            );
        });
    }

    public function desactivarCoordinador(Request $request) {
        User::update('update users set estado = ? where correo = ?', ["DESACTIVADO", $request->correo]);
    }

    public function obtenerCoordinadores(Request $request)
    {
        $query = User::where('rol_usuario', 'COORDINADOR')->get();
        $coordinadores = array();
        foreach ($query as $coordinador) {
            $localArray = array(
                'correo' => $coordinador->correo,
                'nombres' => $coordinador->nombres,
                'apellido_paterno' => $coordinador->apellido_paterno,
                'apellido_materno' => $coordinador->apellido_materno,
                'estado' => $coordinador->estado,
                'num_contacto' => $coordinador->num_contacto,
                'rol_usuario' => $coordinador->rol_usuario,
                'num_personal' => $coordinador->profesor->num_personal
            );
            array_push($coordinadores, $localArray);
        }
        return response()->json($coordinadores, 200);
    }

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
