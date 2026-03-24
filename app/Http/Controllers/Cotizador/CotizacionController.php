<?php

namespace App\Http\Controllers\Cotizador;

use Carbon\Carbon;
use App\Models\COCO;
use App\Models\PCNT;
use App\Models\PROD;
use App\Models\COCOD;
use App\Models\COCOR;
use App\Models\COCORD;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class CotizacionController extends Controller
{
  public function nuevaCotizacion()
  {
    $id_cotizacion = Session::get('cotizacion_id');
    $cotizacion = COCO::where('COCO_id', $id_cotizacion)->first();
    if ($cotizacion) {
      // Si la cotización ya tiene un número de Odoo, marcarla como "pendiente-pago"
      // De lo contrario, marcarla como "guardada"
      if (!empty($cotizacion->COCO_odoo_cotizacion)) {
        $cotizacion->COCO_estatus = 'en_revision';
      } else {
        $cotizacion->COCO_estatus = 'borrador';
      }
      $cotizacion->save();
    }
    Session::forget('cotizacion_id');
    Session::forget('productos');
    Session::forget('avance_temporal');
    
    return response()->json([
      'success' => true,
      'message' => 'Cotización archivada con éxito',
    ], 200);
  }
  public function store(Request $request)
  {

    // Validar los datos recibidos
    $validatedData = $request->validate([
      'cortina' => 'required|string', //espacio
      'sistema' => 'required|string',
      'tela' => 'required|string',
      'tela_id' => 'string',
      'tela_tipo' => 'string',
      //'inputAncho' => 'required|numeric|min:0.1',
      //'inputAlto' => 'required|numeric|min:0.1',
      'hojas' => 'required|integer|min:1',
      'traslape' => 'required|numeric|min:0',
      'baston' => 'required|string',
      'mecanismo' => 'required|string',
      'cantidad' => 'required|integer|min:1',
    ]);

    $cotizacion_id = $request->input('cotizacion_id');



    //try {
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
      $cotizacion->COCO_estatus = 'borrador';
      $cotizacion->save();
    }



    // CREAR CORTINA COCOR RPT_CotizacionCortinas
    $cortina = new COCOR();
    $cortina->COCOR_COCO_id = $cotizacion->COCO_id;
    // **Crear la cortina en RPT_CotizacionCortinas**

    $cortina->COCOR_cantidad = $validatedData['cantidad'];
    $cortina->COCOR_confeccion = $validatedData['sistema'];
    $cortina->COCOR_espacio = $validatedData['cortina'];
    $cortina->COCOR_tela_id = $validatedData['tela_id'];
    $cortina->COCOR_ancho = $validatedData['inputAncho'];
    $cortina->COCOR_alto = $validatedData['inputAlto'];
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
    //dd($productos);
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
    // } catch (\Exception $e) {
    //   return response()->json(['success' => false, 'message' => 'Error al procesar la cotización', 'error' => $e->getMessage()], 500);
    // }
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

      $response = Http::get("http://127.0.0.1:3036/product/{$producto->PROD_id}/price/{$price_list_id}");

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

      $response = Http::get("http://127.0.0.1:3036/product/{$cotizacion_detalle->COCORD_PROD_id}/price/{$price_list_id}");

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
    $cotizacionId = $request->input('cotizacion_id');
    $partidaId = $request->input('id');

    if ($cotizacionId !== null && $cotizacionId !== '') {
      return $this->eliminarCotizacionCompleta((int) $cotizacionId);
    }

    if ($partidaId === null || $partidaId === '') {
      return response()->json(['success' => false, 'message' => 'Identificador inválido'], 422);
    }

    try {
      if ($this->tablaExiste('RPT_CotizacionCortinaDetalleProductos')) {
        COCORD::where('COCORD_COCOR_id', $partidaId)->delete();
      }
      if ($this->tablaExiste('RPT_CotizacionCortinas')) {
        COCOR::where('COCOR_id', $partidaId)->delete();
      }
    } catch (\Throwable $e) {
      Log::error('Error al eliminar partida de cotización', ['partida_id' => $partidaId, 'error' => $e->getMessage()]);
      return response()->json(['success' => false, 'message' => 'No se pudo eliminar la partida'], 500);
    }

    return response()->json(['success' => true, 'message' => 'partida eliminada con éxito'], 200);
  }

  /**
   * Elimina cabecera COCO y filas relacionadas (COCOR, COCORD, COCOD).
   */
  private function eliminarCotizacionCompleta(int $cocoId)
  {
    $coco = COCO::where('COCO_id', $cocoId)->first();
    if (!$coco) {
      return response()->json(['success' => false, 'message' => 'Cotización no encontrada'], 404);
    }

    if (Auth::check() && (int) $coco->COCO_usuario !== (int) Auth::id()) {
      return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
    }

    try {
      DB::connection()->transaction(function () use ($cocoId) {
        if ($this->tablaExiste('RPT_CotizacionCortinas')) {
          $cocorIds = COCOR::where('COCOR_COCO_id', $cocoId)->pluck('COCOR_id');
          if ($cocorIds->isNotEmpty() && $this->tablaExiste('RPT_CotizacionCortinaDetalleProductos')) {
            COCORD::whereIn('COCORD_COCOR_id', $cocorIds)->delete();
          }
          COCOR::where('COCOR_COCO_id', $cocoId)->delete();
        }

        if ($this->tablaExiste('RPT_CotizacionesCortinasDetalle')) {
          COCOD::where('COCOD_COCO_id', $cocoId)->delete();
        }

        COCO::where('COCO_id', $cocoId)->delete();
      });
    } catch (\Throwable $e) {
      Log::error('Error al eliminar cotización', ['coco_id' => $cocoId, 'error' => $e->getMessage()]);

      return response()->json(['success' => false, 'message' => 'No se pudo eliminar la cotización'], 500);
    }

    return response()->json(['success' => true, 'message' => 'Cotización eliminada con éxito'], 200);
  }

  private function tablaExiste(string $table): bool
  {
    $connection = (new COCO())->getConnectionName() ?? config('database.default');
    return Schema::connection($connection)->hasTable($table);
  }

  public function createOdooCotizacion($id, $pricelist_id, $order_lines)
  {

    $response = Http::post('http://127.0.0.1:3036/create-quotation/', [
      'partner_id' => 1, // ID del cliente en Odoo
      'pricelist_id' => $pricelist_id, // ID de la lista de precios
      'order_lines' => $order_lines,
    ]);

    return  $response->json();
  }
  public function createOdooCotizacion2()
  {
    // {
    //   "partner_id": 123,
    //   "pricelist_id": 1,
    //   "order_lines": [
    //     {
    //       "description": "Cortina blackout 2x2m instalación incluida",
    //       "quantity": 1,
    //       "price_unit": 1500.00
    //     }
    //   ]
    // }

    $order_lines = [
      [
        'product_id' => 7058,
        'description' => 'Cortina blackout 2x2m instalación incluida',
        'quantity' => 1,
        'price_unit' => 2505.60,
      ],
      [
        "type" => "note",
        "description" => "Esta es una observación adicional del cliente"
      ],
    ];
    $response = Http::post('http://localhost:3036/create-quotation2/', [
      'partner_id' => 1, // ID del cliente en Odoo
      'pricelist_id' => 1, // ID de la lista de precios
      'order_lines' => $order_lines, // Detalles de los productos
    ]);

    return  $response->json();
  }

  public function createOdooContact()
  {

    $response = Http::post('localhost:3036/create-contact/', [
      'email' => 'prueba1@gmail.com', //
      'name' => 'Python prueba', //

    ]);

    return  $response->json();
  }


  public function createQuotation(Request $request)
  {
    $cotizacion_id = $request->input('id');
    $price_list_id = (Auth::check()) ? Auth::user()->price_list_id : 1; 
    $cotizaciones = \DB::select("exec GetCotizacionDetalleProductos ?", [$cotizacion_id]);

    //crear la cotización en Odoo
    $order_lines = collect($cotizaciones)->map(function ($cotizacion) {
      return [
        'product_id' => $cotizacion->ProductoId,
        'quantity' => $cotizacion->Cantidad,
        'price_unit' => $cotizacion->PrecioUnitario,
      ];
    });
    //dd($order_lines);
    $data = self::createOdooCotizacion($cotizacion_id, $price_list_id, $order_lines);
    //dd($data);
    // Verificar la respuesta
    if ($data['status'] === 'success') {
      return response()->json(['order_id' => $data['order_id']]); // Devolver JSON
    } else {
      return response()->json(['error' => 'Error al crear la cotización.'], 500);
    }
  }

  /**
   * Restaura cotización en sesión (avance y productos) y redirige al cotizador.
   * Misma lógica que LoginRequest al reanudar borrador.
   */
  public function cargarCotizacion(int|string $id): RedirectResponse
  {
    $cocoId = (int) $id;

    $coco = COCO::where('COCO_id', $cocoId)
      ->where('COCO_usuario', Auth::id())
      ->whereNotIn('COCO_estatus', ['orden_venta', 'cancelada', 'sale', 'cancel', 'cancelled'])
      ->first();

    if (!$coco) {
      abort(404);
    }

    if (!$this->tablaExiste('RPT_CotizacionesCortinasDetalle')) {
      Session::put('cotizacion_id', $cocoId);
      Session::put('avance_temporal', json_encode(['siguiente-vista' => 'inicio']));
      Session::put('productos', json_encode([]));

      return redirect()->route('inicio')->with('warning', 'No hay detalle guardado; inicia de nuevo el cotizador.');
    }

    $detalle = COCOD::where('COCOD_COCO_id', $cocoId)->first();

    Session::put('cotizacion_id', $cocoId);

    if ($detalle && $detalle->COCOD_opciones !== null && $detalle->COCOD_opciones !== '') {
      Session::put('avance_temporal', $detalle->COCOD_opciones);
    } else {
      Session::put('avance_temporal', json_encode(['siguiente-vista' => 'inicio']));
    }

    $productosJson = ($detalle && $detalle->COCOD_productos !== null && $detalle->COCOD_productos !== '')
      ? $detalle->COCOD_productos
      : json_encode([]);
    Session::put('productos', $productosJson);

    if (Auth::check()) {
      $user = Auth::user();
      $user->avance = Session::get('avance_temporal');
      $user->save();
    }

    $raw = Session::get('avance_temporal');
    $avanceArr = is_string($raw) ? json_decode($raw, true) : $raw;
    if (is_array($avanceArr) && ($avanceArr['siguiente-vista'] ?? '') === 'resumen') {
      return redirect()->route('resumen')->with('success', 'Cotización cargada.');
    }

    return redirect()->route('inicio')->with('success', 'Cotización cargada. Puedes continuar editando.');
  }

  /**
   * Muestra las cotizaciones del usuario autenticado
   * Sincroniza con Odoo y oculta orden de venta/canceladas.
   */
  public function cotizacionesGuardadas()
  {
    $usuario_id = Auth::id();

    $cotizaciones = COCO::where('COCO_usuario', $usuario_id)
      ->whereNotIn('COCO_estatus', ['orden_venta', 'cancelada', 'sale', 'cancel', 'cancelled'])
      ->orderBy('COCO_fecha', 'desc')
      ->get();

    $this->sincronizarEstatusConOdoo($cotizaciones);

    // Re-consultar para respetar cambios de estatus (ocultar orden_venta/cancelada)
    $cotizaciones = COCO::where('COCO_usuario', $usuario_id)
      ->whereNotIn('COCO_estatus', ['orden_venta', 'cancelada', 'sale', 'cancel', 'cancelled'])
      ->orderBy('COCO_fecha', 'desc')
      ->get();

    $cotizaciones = $cotizaciones
      ->filter(fn ($c) => $c->debeMostrarseEnMisCotizaciones())
      ->values();

    $cotizaciones_con_detalle = $cotizaciones->map(function ($cotizacion) {
      $detalle = COCOD::where('COCOD_COCO_id', $cotizacion->COCO_id)->first();
      $opciones = $detalle ? json_decode($detalle->COCOD_opciones, true) : [];
      $estatusClave = $this->normalizarEstatusLocal($cotizacion->COCO_estatus);

      return [
        'id' => $cotizacion->COCO_id,
        'fecha' => $cotizacion->COCO_fecha,
        'estatus' => $this->etiquetaEstatus($estatusClave),
        'estatus_clave' => $estatusClave,
        'nombre_proyecto' => $opciones['nombre_proyecto'] ?? 'Sin nombre',
        'nombre_articulo' => $opciones['nombre_articulo'] ?? 'Sin descripción',
        'odoo_cotizacion' => $cotizacion->COCO_odoo_cotizacion,
      ];
    });
    
    return view('cotizacion.guardadas', [
      'cotizaciones' => $cotizaciones_con_detalle
    ]);
  }

  private function sincronizarEstatusConOdoo($cotizaciones): void
  {
    foreach ($cotizaciones as $cotizacion) {
      $estatusActual = $this->normalizarEstatusLocal($cotizacion->COCO_estatus);

      // Borrador: no requiere verificación con Odoo.
      if ($estatusActual === 'borrador') {
        continue;
      }

      $orderId = $cotizacion->COCO_odoo_cotizacion;
      if (empty($orderId)) {
        continue;
      }

      try {
        $response = Http::timeout(10)->get("http://127.0.0.1:3036/quotation-status/{$orderId}");
        if (!$response->successful()) {
          // Fallback para APIs que esperan body con order_id.
          $response = Http::timeout(10)->post('http://127.0.0.1:3036/quotation-status', [
            'order_id' => (int) $orderId,
          ]);
        }

        if (!$response->successful()) {
          continue;
        }

        $payload = $response->json();
        $odooState = strtolower(
          (string) ($payload['state'] ?? $payload['status'] ?? $payload['odoo_state'] ?? '')
        );
        if ($odooState === '') {
          continue;
        }

        $nuevoEstatus = $this->mapearEstatusDesdeOdoo($odooState);
        if ($nuevoEstatus !== $estatusActual) {
          $cotizacion->COCO_estatus = $nuevoEstatus;
          $cotizacion->save();
        }
      } catch (\Throwable $e) {
        Log::warning('No se pudo sincronizar estatus de cotizacion con Odoo', [
          'cotizacion_id' => $cotizacion->COCO_id,
          'odoo_order_id' => $orderId,
          'error' => $e->getMessage(),
        ]);
      }
    }
  }

  private function mapearEstatusDesdeOdoo(string $odooState): string
  {
    return match ($odooState) {
      'draft' => 'en_revision',
      'sent' => 'enviada',
      'sale' => 'orden_venta',
      'cancel', 'cancelled' => 'cancelada',
      default => 'en_revision',
    };
  }

  private function normalizarEstatusLocal(?string $estatus): string
  {
    $key = strtolower((string) $estatus);

    return match ($key) {
      'guardada', 'creacion', 'pendiente', 'borrador' => 'borrador',
      'cotizada', 'pendiente-pago', 'en_revision' => 'en_revision',
      'enviada', 'sent' => 'enviada',
      'orden_venta', 'sale' => 'orden_venta',
      'cancelada', 'cancel', 'cancelled' => 'cancelada',
      default => 'en_revision',
    };
  }

  private function etiquetaEstatus(string $estatus): string
  {
    return match ($estatus) {
      'borrador' => 'Borrador',
      'en_revision' => 'En revisión',
      'enviada' => 'Enviada',
      'orden_venta' => 'Orden de venta',
      'cancelada' => 'Cancelada',
      default => 'En revisión',
    };
  }
}
