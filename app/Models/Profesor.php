<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    public $timestamps = false;
    public $table = "profesor";
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'num_personal'.
        'users_id'
    ];

    public function alumno()
    {
        return $this->hasOne(Alumno::class, 'profesor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
