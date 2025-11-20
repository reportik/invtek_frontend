<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoCantidad extends Model
{
    protected $table = 'RPT_ProductosCantidad';
    protected $primaryKey = 'PCNT_id';
    public $timestamps = false;

    protected $fillable = [
        'PCNT_OPC_OpcionId',
        'PCNT_PROD_id',
        'PCNT_PROD_nombre',
        'PCNT_base_ancho',
        'PCNT_base_cantidad',
        'PCNT_precio_unitario',
        'PCNT_atributos',
        'PCNT_base_medida',
        'PCNT_formula'
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'PCNT_atributos' => 'array', // Automáticamente serializa/deserializa JSON
    ];

    // Relaciones opcionales
    public function opcion()
    {
        return $this->belongsTo(OpcionCotizador::class, 'PCNT_OPC_OpcionId', 'OPC_OpcionId');
    }
}
