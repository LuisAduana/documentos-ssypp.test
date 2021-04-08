<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    public $timestamps = false;
    public $table = "alumno";
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'matricula',
        'bloque',
        'seccion',
        'proyectos',
        'users_id',
        'profesor_id'
    ];

    public function documento() {
      return $this->hasOne(Documento::class, "alumno_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class, "users_id");
    }

    public function profesor() 
    {
        return $this->belongsTo(Profesor::class);
    }

    public function proyectos()
    {
        return $this->morphToMany(Proyecto::class, 'taggable');
    }
}
