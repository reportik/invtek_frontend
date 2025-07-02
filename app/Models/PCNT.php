<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PCNT extends Model
{
    use HasFactory;

    protected $table = 'RPT_ProductosCantidad';
    protected $primaryKey = 'PCNT_id';
    public $timestamps = false;

    protected $fillable = [
        'PCNT_modelo',
        'PCNT_ancho_min',
        'PCNT_ancho_max',
        'PCNT_PROD_id',
        'PCNT_PROD_nombre',
        'PCNT_base_ancho',
        'PCNT_base_cantidad',
        'PCNT_OPC_OpcionId',
    ];

    // Relación con el producto
    public function producto()
    {
        return $this->belongsTo(RPTProductos::class, 'PCNT_PROD_id', 'PROD_id');
    }
}
