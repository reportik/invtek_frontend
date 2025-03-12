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
use Illuminate\Support\Facades\Http;

class CotizacionController extends Controller
{
  public function store(Request $request)
  {

    // Validar los datos recibidos
    $validatedData = $request->validate([
      'cortina' => 'required|string', //espacio
      'sistema' => 'required|string',
      'tela' => 'required|string',
      'tela_id' => 'string',
      'tela_tipo' => 'string',
      'ancho' => 'required|numeric|min:0.1',
      'alto' => 'required|numeric|min:0.1',
      'hojas' => 'required|integer|min:1',
      'traslape' => 'required|numeric|min:0',
      'baston' => 'required|string',
      'mecanismo' => 'required|string',
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

        //$cotizacion->COCO_monto_total = $cotizacion->COCO_monto_total + ($validatedData['precio_unitario'] * $validatedData['cantidad']);

        //$cotizacion->save();

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
        //$cotizacion->COCO_monto_total = $validatedData['precio_unitario'] * $validatedData['cantidad'];
        $cotizacion->COCO_estatus = 'pendiente';
        $cotizacion->save();
      }



      // CREAR CORTINA COCOR RPT_CotizacionCortinas
      $cortina = new COCOR();
      $cortina->COCOR_COCO_id = $cotizacion->COCO_id;
      // **Crear la cortina en RPT_CotizacionCortinas**
      $cortina->COCOR_precio_unitario_productos = $validatedData['precio_unitario'];
      $cortina->COCOR_precio_total_productos = $validatedData['precio_unitario'] * $validatedData['cantidad'];
      $cortina->COCOR_cantidad = $validatedData['cantidad'];
      $cortina->COCOR_confeccion = $validatedData['sistema'];
      $cortina->COCOR_espacio = $validatedData['cortina'];
      $cortina->COCOR_tela_id = $validatedData['tela_id'];
      $cortina->COCOR_ancho = $validatedData['ancho'];
      $cortina->COCOR_alto = $validatedData['alto'];
      $cortina->COCOR_hojas = $validatedData['hojas'];
      $cortina->COCOR_traslape = $validatedData['traslape'];
      $cortina->COCOR_eliminado = 0; // Por defecto no eliminado
      $cortina->save();

      //CREAR DETALLE DE COTIZACIÓN COCORD RPT_CotizacionCortinaDetalleProductos
      //en la tabla de ProductosCantidad se agregaron los campos PCNT_base_ancho PCNT_base_cantidad ambos de tipo decimal
      //mi variable sistema que se comparará con la tabla de productos campo PROD_tipo que debe ser "sistema"
      // y revisara que el valor de sistema este en el campo PROD_PROM_id que es una cadena separada por comas "Tradicional"
      //para saber que productos lleva consultamos la tabla de productos (debe crearse una funcion que me devuelva el id de los productos donde segun el tipo)  y para saber la cantidad la tabla de ProductosCantidad cantidad = ((ancho*100)/PCNT_base_ancho) * PCNT_base_cantidad
      // Obtener los productos que se deben agregar según las especificaciones
      $especificaciones = [
        'sistema' => $validatedData['sistema'],
        //'espacio' => $validatedData['cortina'],
        'tela' => $validatedData['tela_tipo'],
      ];

      $productos = $this->obtenerProductosPorEspecificacion($especificaciones, $validatedData['ancho']);

      $precio_unitario_productos = 0;
      $precio_total_productos = 0;
      foreach ($productos as $producto) {
        // Insertar en RPT_CotizacionCortinaDetalleProductos (COCORD)
        $detalleProducto = new COCORD();
        $detalleProducto->COCORD_COCOR_id = $cortina->COCOR_id;
        $detalleProducto->COCORD_PROD_id = $producto['producto_id'];
        $detalleProducto->COCORD_cantidad = $producto['cantidad'];
        $detalleProducto->COCORD_precio_unitario = $producto['precio_unitario'];
        $detalleProducto->COCORD_total = $producto['precio_total'];
        $detalleProducto->save();

        $precio_unitario_productos += $producto['precio_unitario'];
        $precio_total_productos += $producto['precio_unitario'] * $producto['cantidad'] * $cortina->COCOR_cantidad;
      }

      $cortina->COCOR_precio_unitario_productos = $precio_unitario_productos;
      $cortina->COCOR_precio_total_productos = $precio_total_productos;
      $cortina->save();

      return response()->json([
        'success' => true,
        'message' => $cotizacion_id ? 'Cotización actualizada con éxito' : 'Cotización guardada con éxito',
        'cotizacion' => $cotizacion->COCO_id
      ], 200);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Error al procesar la cotización', 'error' => $e->getMessage()], 500);
    }
  }

  public function obtenerProductosPorEspecificacion($especificaciones, $ancho)
  {
    // Obtener la lista de precios del usuario (price_list_id) si el usuario no está autenticado, se asigna 1
    $price_list_id = 1;
    if (Auth::check()) {
      $usuario = Auth::user();
      $price_list_id = $usuario->price_list_id;
    }

    if (!$price_list_id) {
      return response()->json(['error' => 'No se encontró lista de precios para el usuario'], 400);
    }

    $productos = PROD::where(function ($query) use ($especificaciones) {
      foreach ($especificaciones as $campo => $valor) {
        $query->orWhere(function ($q) use ($campo, $valor) {
          $q->where('PROD_tipo', $campo)
            ->where('PROD_PROM_id', 'LIKE', "%{$valor}%"); // Buscar como cadena en SQL Server
        });
      }
    })->get();

    $productosConCantidadYPrecio = [];

    foreach ($productos as $producto) {
      // Obtener la cantidad desde ProductosCantidad
      $productoCantidad = PCNT::where('PCNT_PROD_id', $producto->PROD_id)->first();
      $cantidad = ($ancho * 100) / $productoCantidad->PCNT_base_ancho * $productoCantidad->PCNT_base_cantidad;

      // Obtener precio desde FastAPI

      $response = Http::get("http://itekniaapp.serveftp.com:3036/product/{$producto->PROD_id}/price/{$price_list_id}");

      $precio = $response->successful() ? $response->json()['pricelist_price'] : 0;

      $productosConCantidadYPrecio[] = [
        'producto_id' => $producto->PROD_id,
        'nombre' => $producto->PROD_nombre,
        'cantidad' => $cantidad,
        'precio_unitario' => $precio,
        'precio_total' => $precio * $cantidad,
      ];
    }

    return $productosConCantidadYPrecio;
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

    $ancho = $cotizacion->COCOR_ancho;
    // Buscar los detalle de la cotización

    $cotizacion_detalles = COCORD::where('COCORD_COCOR_id', $id)->get();
    $precio_unitario_productos = 0;
    $precio_total_productos = 0;
    //dd($cotizacion_detalles);
    //recorrer los detalles de la cotización y actualizar la cantidad, precio y total
    foreach ($cotizacion_detalles as $cotizacion_detalle) {
      // Obtener el producto
      //$producto = PROD::find($cotizacion_detalle->COCORD_PROD_id);
      // Obtener el porcentaje de la tabla de productos cantidad
      /* $pcnt = PCNT::where('PCNT_PROD_id', $cotizacion_detalle->COCORD_PROD_id)
        ->where('PCNT_ancho_min', '<=', $cotizacion->COCOR_ancho)
        ->where('PCNT_ancho_max', '>=', $cotizacion->COCOR_ancho)
        ->first(); */
      $productoCantidad = PCNT::where('PCNT_PROD_id', $cotizacion_detalle->COCORD_PROD_id)->first();
      $cantidad = ($ancho * 100) / $productoCantidad->PCNT_base_ancho * $productoCantidad->PCNT_base_cantidad;
      //ceil($numero): Esta función toma un valor de punto flotante y lo redondea al entero más alto (hacia arriba). 

      //Si no se encuentra el porcentaje, se mantiene el precio unitario del producto
      if (!$cantidad) {
        $cnt = 1;
      } else {
        $cnt = $cantidad;
      }
      $price_list_id = 1;
      if (Auth::check()) {
        $usuario = Auth::user();
        $price_list_id = $usuario->price_list_id;
      }

      $response = Http::get("http://itekniaapp.serveftp.com:3036/product/{$cotizacion_detalle->COCORD_PROD_id}/price/{$price_list_id}");

      $precio = $response->successful() ? $response->json()['pricelist_price'] : 0;

      // Actualizar la cantidad en el detalle de la cotización
      $cotizacion_detalle->COCORD_cantidad = $cnt * $nueva_cantidad;
      //Actualizar el precio
      $cotizacion_detalle->COCORD_precio_unitario = $precio;
      $cotizacion_detalle->COCORD_total = $precio * $cnt * $nueva_cantidad;
      $cotizacion_detalle->save();

      $precio_unitario_productos += $precio;
      $precio_total_productos += $precio * $cnt * $nueva_cantidad;
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
