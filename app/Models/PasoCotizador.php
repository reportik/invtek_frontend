<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasoCotizador extends Model
{
    protected $table = 'RPT_PasosCotizador';
    protected $primaryKey = 'PAS_PasoId';
    public $timestamps = false;

    protected $fillable = [
        'PAS_Nombre',
        'PAS_Orden',
        'PAS_Activo',
        'PAS_Eliminado',
        'PAS_Html_Id'
    ];
}
