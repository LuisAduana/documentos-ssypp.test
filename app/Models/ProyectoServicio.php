<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProyectoServicio extends Model
{
    public $timestamps = false;
    public $table = "proyecto_servicio";
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'num_alumnos',
        'actividades',
        'horario',
        'requisitos',
        'proyecto_id'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }
}
