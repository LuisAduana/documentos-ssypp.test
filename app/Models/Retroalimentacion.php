<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retroalimentacion extends Model
{
  public $timestamps = false;
  public $table = "retroalimentacion";
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
      'id',
      'mensaje',
      'fecha_envio',
      'documento_id'
  ];

  public function documento()
  {
      return $this->belongsTo(Documento::class);
  }

}
