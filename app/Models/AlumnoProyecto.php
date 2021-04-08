<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumnoProyecto extends Model
{
    public $timestamps = true;
    public $table = "alumno_proyecto";
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'tipo_proyecto',
        'alumno_id',
        'proyecto_id'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function alumno() 
    {
        return $this->belongsTo(Alumno::class);
    }
}
