<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
  public $timestamps = false;
  public $table = "documento";
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'id',
    'nombre',
    'tipo',
    'ruta',
    'estado',
    'alumno_id',
    'proyecto_id'
  ];

  public function alumno()
    {
        return $this->belongsTo(Alumno::class, "alumno_id");
    }

  public function proyecto() 
  {
      return $this->belongsTo(Profesor::class, "proyecto_id");
  }
}
