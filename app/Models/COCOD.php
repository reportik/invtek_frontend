<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class COCOD extends Model
{
  use HasFactory;

  protected $table = 'RPT_CotizacionesCortinasDetalle';
  protected $primaryKey = 'COCOD_id';
  public $timestamps = false;

  protected $fillable = [
    'COCOD_COCO_id',
    'COCOD_precio',
    'COCOD_cantidad',
    'COCOD_espacio',
    'COCOD_confeccion',
    'COCOD_tela',
    'COCOD_ancho',
    'COCOD_alto',
    'COCOD_hojas',
    'COCOD_traslape',
    'COCOD_baston',
    'COCOD_mecanismo',
    'COCOD_eliminado'
  ];
}
