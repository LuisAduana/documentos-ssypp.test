<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProyectoPractica extends Model
{
    public $timestamps = false;
    public $table = "proyecto_practica";
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'nombre_proyecto',
        'descripcion_general',
        'objetivo_general',
        'objetivos_inmediatos',
        'objetivos_mediatos',
        'metodologia',
        'recursos',
        'actividades_funcionales',
        'responsabilidades',
        'duracion',
        'horario',
        'estado_proyecto',
        'proyecto_id'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }
}
