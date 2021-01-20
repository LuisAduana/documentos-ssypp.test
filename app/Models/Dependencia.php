<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dependencia extends Model
{
    public $timestamps = false;
    public $table = "dependencia";
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'nombre_dependencia',
        'nombre_contacto',
        'direccion',
        'ciudad',
        'correo',
        'num_contacto',
        'sector',
        'num_us_directos',
        'num_us_indirectos',
        'estado'
    ];

    public function responsable()
    {
        return $this->hasOne(Responsable::class, 'dependencia_id');
    }

    public function Proyecto()
    {
        return $this->hasOne(Proyecto::class, 'dependencia_id');
    }
}
