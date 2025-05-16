<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpcionCotizador extends Model
{
    protected $table = 'RPT_OpcionesCotizador';
    protected $primaryKey = 'OPC_OpcionId';
    public $timestamps = false;

    protected $fillable = [
        'OPC_PasoId', 'OPC_ValorOpcion', 'OPC_OpcionPadreId', 'OPC_EsMultiSeleccion',
        'OPC_Imagen', 'OPC_EsDefault', 'OPC_Activo', 'OPC_Eliminado', 'OPC_CMM_Variante', 'OPC_Descripcion'
    ];

    public function paso()
    {
        return $this->belongsTo(PasoCotizador::class, 'OPC_PasoId', 'PAS_PasoId');
    }

    public function padre()
    {
        return $this->belongsTo(self::class, 'OPC_OpcionPadreId');
    }
    // relacion a tabla RPT_Productos
    public function productos()
    {
        return $this->hasMany(ProductoCantidad::class, 'PCNT_OPC_OpcionId', 'OPC_OpcionId');
    }
}
