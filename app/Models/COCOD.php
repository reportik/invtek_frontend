<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class COCOD extends Model
{
  //ESTE MODELO FUE REEMPLAZADO POR COCOR Y NO SE DEBERIA USAR EN NINGUNA PARTE DEL SISTEMA
  use HasFactory;

  protected $table = 'RPT_CotizacionesCortinasDetalle';
  protected $primaryKey = 'COCOD_id';
  public $timestamps = false;

  protected $fillable = [
    'COCOD_COCO_id',
    'COCOD_cantidad',
    'COCOD_opciones',
    'COCOD_productos',
    'COCOD_eliminado'
  ];
}
