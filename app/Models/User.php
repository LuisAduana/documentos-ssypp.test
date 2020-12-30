<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'correo',
        'password',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'estado',
        'num_contacto',
        'rol_usuario'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function profesor()
    {
        return $this->hasOne(Profesor::class, 'users_id');
    }
}
