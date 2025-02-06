<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class COCO extends Model
{
  use HasFactory;

  protected $table = 'RPT_CotizacionesCortinas';
  protected $primaryKey = 'COCO_id';
  public $timestamps = false;

  protected $fillable = [
    'COCO_fecha',
    'COCO_usuario',
    'COCO_monto_total',
    'COCO_estatus'
  ];
}
