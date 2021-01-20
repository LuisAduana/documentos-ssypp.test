<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    public $timestamps = false;
    public $table = "proyecto";
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'estado',
        'inscripcion_id',
        'responsable_id',
        'dependencia_id'
    ];

    public function proyectoServicio()
    {
        return $this->hasOne(ProyectoServicio::class, 'proyecto_id');
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class);
    }

    public function responsable()
    {
        return $this->belongsTo(Responsable::class);
    }
}
