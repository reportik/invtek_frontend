<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class COCOR extends Model
{
    use HasFactory;

    protected $table = 'RPT_CotizacionCortinas';
    protected $primaryKey = 'COCOR_id';
    public $timestamps = false; // Si no usas `created_at` y `updated_at`

    protected $fillable = [
        'COCOR_COCO_id',
        'COCOR_precio_unitario_productos',
        'COCOR_precio_total_productos',
        'COCOR_cantidad',
        'COCOR_confeccion',
        'COCOR_espacio',
        'COCOR_tela_id',
        'COCOR_ancho',
        'COCOR_alto',
        'COCOR_hojas',
        'COCOR_traslape',
        'COCOR_eliminado'
    ];

    // Relación con Cotización
    public function cotizacion()
    {
        return $this->belongsTo(RPTCotizacion::class, 'COCOR_COCO_id', 'COCO_id');
    }

    // Relación con Tela (si hay una tabla de telas)
    public function tela()
    {
        return $this->belongsTo(RPTTela::class, 'COCOR_tela_id', 'id');
    }

    // Relación con los productos detallados
    public function productosDetalle()
    {
        return $this->hasMany(RPTCotizacionCortinaDetalleProductos::class, 'COCORD_COCOR_id', 'COCOR_id');
    }
}
