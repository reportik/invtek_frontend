<?php

namespace App\Http\Controllers\Cotizador;

use Carbon\Carbon;
use App\Models\COCO;
use App\Models\COCOD;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CotizacionController extends Controller
{
  public function store(Request $request)
  {

    // Validar los datos recibidos
    $validatedData = $request->validate([
      'cortina' => 'required|string',
      'sistema' => 'required|string',
      'tela' => 'required|string',
      'tela_id' => 'string',
      'ancho' => 'required|numeric|min:0.1',
      'alto' => 'required|numeric|min:0.1',
      'hojas' => 'required|integer|min:1',
      'traslape' => 'required|numeric|min:0',
      'baston' => 'required|string',
      'mecanismo' => 'required|string',
      'precio_unitario' => 'required|numeric|min:1',
      'cantidad' => 'required|integer|min:1',
    ]);

    $cotizacion_id = $request->input('cotizacion_id');

    try {
      if (!empty($cotizacion_id)) {
        // **Actualizar cotización existente**
        $cotizacion = COCO::find($cotizacion_id);
        if (!$cotizacion) {
          return response()->json(['success' => false, 'message' => 'Cotización no encontrada'], 404);
        }

        $cotizacion->COCO_monto_total = $cotizacion->COCO_monto_total + ($validatedData['precio_unitario'] * $validatedData['cantidad']);
        $cotizacion->save();

        // **Actualizar detalles de cotización**
        /*  $detalle = COCOD::where('COCOD_COCO_id', $cotizacion->COCO_id)->first();
        if (!$detalle) {
          return response()->json(['success' => false, 'message' => 'Detalles de cotización no encontrados'], 404);
        } */
      } else {
        // **Crear nueva cotización**
        $cotizacion = new COCO();
        $cotizacion->COCO_fecha = Carbon::now();
        $cotizacion->COCO_usuario = Auth::check() ? Auth::user()->id : 'invitado';
        $cotizacion->COCO_monto_total = $validatedData['precio_unitario'] * $validatedData['cantidad'];
        $cotizacion->COCO_estatus = 'pendiente';
        $cotizacion->save();
      }

      $detalle = new COCOD();
      $detalle->COCOD_COCO_id = $cotizacion->COCO_id;

      // **Actualizar/Crear detalles de la cotización**
      $detalle->COCOD_precio = $validatedData['precio_unitario'];
      $detalle->COCOD_cantidad = $validatedData['cantidad'];
      $detalle->COCOD_espacio = $validatedData['cortina'];
      $detalle->COCOD_confeccion = $validatedData['sistema'];
      $detalle->COCOD_tela = $validatedData['tela'];
      $detalle->COCOD_tela_id = $validatedData['tela_id'];
      $detalle->COCOD_ancho = $validatedData['ancho'];
      $detalle->COCOD_alto = $validatedData['alto'];
      $detalle->COCOD_hojas = $validatedData['hojas'];
      $detalle->COCOD_traslape = $validatedData['traslape'];
      $detalle->COCOD_baston = $validatedData['baston'];
      $detalle->COCOD_mecanismo = $validatedData['mecanismo'];
      $detalle->COCOD_eliminado = 0;
      $detalle->save();

      return response()->json([
        'success' => true,
        'message' => $cotizacion_id ? 'Cotización actualizada con éxito' : 'Cotización guardada con éxito',
        'cotizacion' => $cotizacion->COCO_id
      ], 200);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Error al procesar la cotización', 'error' => $e->getMessage()], 500);
    }
  }

  public function getCotizaciones(Request $request)
  {
    $cotizaciones = \DB::select("
        SELECT
            COCOD_id AS eliminar,
            COCOD_tela_id AS producto,

            COCOD_precio AS precio_unitario,
            COCOD_cantidad AS cantidad,
            COCOD_precio * COCOD_cantidad AS total,

            CONCAT(
                'Cortina ', COCOD_confeccion, ' para ', COCOD_espacio,
                ' con tela ', COCOD_tela,
                '. Medidas: ', COCOD_ancho, 'm x ', COCOD_alto, 'm, ',
                'con ', COCOD_hojas, ' hoja(s), traslape de ', COCOD_traslape, 'cm, ',
                'mecanismo ', COCOD_mecanismo,
                ' y bastón de ', COCOD_baston, '.'
            ) AS descripcion
        FROM RPT_CotizacionesCortinasDetalle
        WHERE COCOD_COCO_id = ?
    ", [$request->input('id')]);

    return response()->json([
      "draw" => intval($request->input('draw')), // Necesario para DataTables
      "recordsTotal" => count($cotizaciones),
      "recordsFiltered" => count($cotizaciones),
      "data" => $cotizaciones
    ]);

    //return response()->json(['success' => true, 'cotizaciones' => $cotizaciones], 200);
  }
}
