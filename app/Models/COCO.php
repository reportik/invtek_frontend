<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class COCO extends Model
{
  use HasFactory;

  protected $table = 'RPT_CotizacionesCortinas';
  protected $primaryKey = 'COCO_id';
  public $timestamps = false;

  protected $fillable = [
    'COCO_fecha',
    'COCO_usuario',
    'COCO_monto_total',
    'COCO_estatus',
    'COCO_odoo_cotizacion',
    'COCO_odoo_cotizacion_productos'
  ];

  /**
   * Estatus almacenados como “borrador” en el listado (alineado con CotizacionController::normalizarEstatusLocal).
   */
  public static function estatusEsBorradorListado(?string $estatus): bool
  {
    $key = strtolower((string) $estatus);

    return in_array($key, ['guardada', 'creacion', 'pendiente', 'borrador'], true);
  }

  /**
   * Borradores siempre visibles; el resto solo si ya tienen folio en Odoo.
   */
  public function debeMostrarseEnMisCotizaciones(): bool
  {
    if (self::estatusEsBorradorListado($this->COCO_estatus)) {
      return true;
    }

    return trim((string) ($this->COCO_odoo_cotizacion ?? '')) !== '';
  }
}
