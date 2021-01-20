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
        'inicio_inscripcion',
        'fin_inscripcion',
        'estado_inscripcion'
    ];

    public function proyecto()
    {
        return $this->hasOne(Proyecto::class, 'proyecto_id');
    }
}
