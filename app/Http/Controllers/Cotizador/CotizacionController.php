<?php

namespace App\Http\Controllers\Cotizador;

use Carbon\Carbon;
use App\Models\COCO;
use App\Models\COCOR;
use App\Models\COCORD;
use App\Models\COCOD;
use App\Models\PCNT;
use App\Models\PROD;
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
    $cotizaciones = \DB::select("exec GetCotizacionDetalle ?", [$request->input('id')]);

    return response()->json([
      "draw" => intval($request->input('draw')), // Necesario para DataTables
      "recordsTotal" => count($cotizaciones),
      "recordsFiltered" => count($cotizaciones),
      "data" => $cotizaciones
    ]);

    //return response()->json(['success' => true, 'cotizaciones' => $cotizaciones], 200);
  }

  public function actualizaCantidadesCotizacion(Request $request)
  {
    //try {
    // Obtener datos del request
    $id = $request->input('id');
    $nueva_cantidad = $request->input('cantidad');

    if (!$id || !$nueva_cantidad || $nueva_cantidad <= 0) {
      return response()->json(['success' => false, 'message' => 'Datos inválidos'], 400);
    }

    // Buscar la cotización
    $cotizacion = COCOR::find($id);

    if (!$cotizacion) {
      return response()->json(['success' => false, 'message' => 'Cotización no encontrada'], 404);
    }

    // Actualizar cantidad
    $cotizacion->COCOR_cantidad = $nueva_cantidad;

    // Buscar los detalle de la cotización

    $cotizacion_detalles = COCORD::where('COCORD_COCOR_id', $id)->get();
    $precio_unitario_productos = 0;
    $precio_total_productos = 0;
    //dd($cotizacion_detalles);
    //recorrer los detalles de la cotización y actualizar la cantidad, precio y total
    foreach ($cotizacion_detalles as $cotizacion_detalle) {
      // Obtener el producto
      $producto = PROD::find($cotizacion_detalle->COCORD_PROD_id);
      // Obtener el porcentaje de la tabla de productos cantidad
      $pcnt = PCNT::where('PCNT_PROD_id', $cotizacion_detalle->COCORD_PROD_id)
        ->where('PCNT_ancho_min', '<=', $cotizacion->COCOR_ancho)
        ->where('PCNT_ancho_max', '>=', $cotizacion->COCOR_ancho)
        ->first();
      //Si no se encuentra el porcentaje, se mantiene el precio unitario del producto
      if (!$pcnt) {
        $cnt = 1;
      } else {
        $cnt = $pcnt->PCNT_cantidad;
      }

      // Actualizar la cantidad en el detalle de la cotización
      $cotizacion_detalle->COCORD_cantidad = $cnt * $nueva_cantidad;
      //Actualizar el precio
      $cotizacion_detalle->COCORD_precio_unitario = $producto->PROD_precio_unitario;
      $cotizacion_detalle->COCORD_total = $producto->PROD_precio_unitario * $cnt * $nueva_cantidad;
      $cotizacion_detalle->save();

      $precio_unitario_productos += $producto->PROD_precio_unitario;
      $precio_total_productos += $producto->PROD_precio_unitario * $cnt * $nueva_cantidad;
    }

    //Actualizar el precio total de la cotización
    $cotizacion->COCOR_precio_unitario_productos = $precio_unitario_productos;
    $cotizacion->COCOR_precio_total_productos = $precio_total_productos;
    $cotizacion->save();

    return response()->json(['success' => true, 'message' => 'Cantidad actualizada con éxito'], 200);
    //} catch (\Exception $e) {

    return response()->json(['success' => false, 'message' => 'Ocurrió un error al actualizar la cantidad'], 500);
    //}
  }

  public function delete(Request $request)
  {
    //obtener datos del request
    $id = $request->input('id');

    //delete COCOR
    COCOR::where('COCOR_id', $id)->delete();
    COCORD::where('COCORD_COCOR_id', $id)->delete();

    return response()->json(['success' => true, 'message' => 'partida eliminada con éxito'], 200);
  }
}
