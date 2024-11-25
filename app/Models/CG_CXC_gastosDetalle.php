<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CG_CXC_gastosDetalle extends Model
{

  protected $table = 'CG_gastos_detalle';

  protected $primaryKey = 'GAD_id';

  public $timestamps = false;

  public $incrementing = false;
}
