<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Responsable extends Model
{
    public $timestamps = false;
    public $table = "responsable";
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'nombre_responsable',
        'cargo',
        'correo',
        'num_contacto',
        'estado',
        'dependencia_id'
    ];

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class);
    }
}
