<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class COCORD extends Model
{
    use HasFactory;

    protected $table = 'RPT_CotizacionCortinaDetalleProductos';
    protected $primaryKey = 'COCORD_id';
    public $timestamps = false;

    protected $fillable = [
        'COCORD_COCOR_id',
        'COCORD_PROD_id',
        'COCORD_cantidad',
        'COCORD_precio_unitario',
        'COCORD_total'
    ];

    // Relación con la cortina
    public function cortina()
    {
        return $this->belongsTo(RPTCotizacionCortinas::class, 'COCORD_COCOR_id', 'COCOR_id');
    }

    // Relación con el producto
    public function producto()
    {
        return $this->belongsTo(RPTProductos::class, 'COCORD_PROD_id', 'PROD_id');
    }
}
