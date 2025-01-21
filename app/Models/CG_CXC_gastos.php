<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CG_CXC_gastos extends Model
{

  protected $table = 'CG_gastos';

  protected $primaryKey = 'GAS_id';

  public $timestamps = false;

  public $incrementing = false;
}
