<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpcionCotizador extends Model
{
    protected $table = 'RPT_OpcionesCotizador';
    protected $primaryKey = 'OPC_OpcionId';
    public $timestamps = false;

    protected $fillable = [
        'OPC_PasoId',
        'OPC_ValorOpcion',
        'OPC_OpcionPadreId',
        'OPC_EsMultiSeleccion',
        'OPC_Imagen',
        'OPC_EsDefault',
        'OPC_Activo',
        'OPC_Eliminado',
        'OPC_CMM_Variante',
        'OPC_Descripcion',
        'OPC_EsProducto',
        'OPC_S1',
        'OPC_S2',
        'OPC_S3',
        'OPC_S4',
        'OPC_S5',
        'OPC_S6',
        'OPC_S7',
        'OPC_S8',
        'OPC_S9',
        'OPC_S10',
        'OPC_S11',
        'OPC_S12',
        'OPC_S13',
        'OPC_S14',
        'OPC_S15',
        'OPC_S16',
        'OPC_S17',
        'OPC_S18',
        'OPC_S19',
        'OPC_S20'
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
