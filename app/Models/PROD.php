<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROD extends Model
{
    use HasFactory;

    protected $table = 'RPT_Productos';
    protected $primaryKey = 'PROD_id';
    public $timestamps = false;

    protected $fillable = [
        'PROD_nombre',
        'PROD_tipo',
        'PROD_precio_unitario'
    ];

    // Relación con detalles de cortinas
    public function detallesCortinas()
    {
        return $this->hasMany(RPTCotizacionCortinaDetalleProductos::class, 'COCORD_PROD_id', 'PROD_id');
    }

    // Relación con la tabla de cantidades por tamaño
    public function productosCantidad()
    {
        return $this->hasMany(RPTProductosCantidad::class, 'PCNT_PROD_id', 'PROD_id');
    }
}
