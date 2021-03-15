<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    public $timestamps = false;
    public $table = "inscripcion";
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'token_inscripcion',
        'inscripcion_inicio',
        'fin_inscripcion',
        'tipo_inscripcion',
        'estado'
    ];

    public function proyecto()
    {
        return $this->hasOne(Proyecto::class, 'proyecto_id');
    }
}
