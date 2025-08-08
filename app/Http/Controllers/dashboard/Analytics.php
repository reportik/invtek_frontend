<?php

namespace App\Http\Controllers\dashboard;

use Carbon\Carbon;
use App\Models\COCO;
use App\Models\PCNT;
use App\Models\COCOD;
use App\Models\COCOR;
use Illuminate\Http\Request;
use App\Models\PasoCotizador;
use App\Models\OpcionCotizador;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class Analytics extends Controller
{
  public $decimales = 2;

  /**
   * Devuelve el siguiente selector activo con las opciones válidas según las selecciones previas del usuario.
   * Usa los modelos PasoCotizador y OpcionCotizador.
   * @param array|null $avance Opciones seleccionadas (si es null, toma de la sesión)
   * @return array|null
   */
  /**
   * Devuelve el siguiente selector activo con las opciones válidas según las selecciones previas del usuario.
   * Si se pasa el nombre de un selector editado, limpia el avance de los pasos posteriores.
   * @param array|null $avance Opciones seleccionadas (si es null, toma de la sesión)
   * @param string|null $selectorEditado Nombre del selector que se está editando
   * @return array|null
   */
  public static function getSelectorSiguiente($avance = null, $selectorEditado = null)
  {
    // 1. Obtener avance actual
    if ($avance === null) {
      $avance = Session::get('avance_temporal', []);
      if (is_string($avance)) $avance = json_decode($avance, true);
    }

    // 2. Filtrar solo campos relevantes (IDs numéricos válidos en la tabla de opciones)
    $opciones = array_filter($avance, function ($key) {
      return !str_contains($key, 'sel_tela')
        && $key !== 'lado_a'
        && $key !== 'lado_b'
        && $key !== 'alto'
        && $key !== 'ancho'
        && $key !== 'radio'
        && $key !== 'nombre_proyecto'
        && $key !== 'nombre_articulo'
        && $key !== 'siguiente-vista';
    }, ARRAY_FILTER_USE_KEY);

    $ids = array_filter($opciones, function ($value) {
      return is_numeric($value);
    });
    $ids = array_values($ids);
    //dd($ids);
    // 3. Obtener todos los pasos activos y ordenados
    $pasos = PasoCotizador::where('PAS_Activo', 1)
      ->where('PAS_Eliminado', 0)
      ->orderBy('PAS_Orden', 'asc')
      ->get();

    // Si se pasa el nombre del selector editado, limpiar avance
    if (isset($selectorEditado) && $selectorEditado) {
      $pasoEditado = $pasos->firstWhere('PAS_Html_name', $selectorEditado);
      $avance = self::limpiarAvancePosterior($avance, $pasoEditado, $pasos);
      // Actualizar la sesión
      Session::put('avance_temporal', json_encode($avance));
    }

    // 4. Identificar pasos respondidos
    $respondidos = [];
    foreach ($pasos as $paso) {
      if (isset($avance[$paso->PAS_Html_name]) && is_numeric($avance[$paso->PAS_Html_name])) {
        $respondidos[(int)$paso->PAS_Orden] = $avance[$paso->PAS_Html_name];
      }
    }
    if (empty($respondidos)) { //si no hay respondidos, el ultimo orden es 0
      $ultimoOrden = 0;
    } else {
      $ultimoOrden = max(array_keys($respondidos)); //el ultimo orden es el maximo de los respondidos
    }
    $encontrado = false;
    $siguienteSelector = null;
    $opcionesValidas = collect();
    $pantallaSiguiente = null;
    // Buscar desde el siguiente paso al último
    foreach ($pasos as $paso) {
      if ($paso->PAS_Orden <= $ultimoOrden) continue;
      $query = OpcionCotizador::where('OPC_PasoId', $paso->PAS_PasoId)
        ->where('OPC_Activo', 1)
        ->where('OPC_Eliminado', 0);
      // Agregar TODAS las dependencias previas
      //dd($paso->PAS_Orden); //3
      //respondidos
      //dd($respondidos);
      for ($j = 1; $j < $paso->PAS_Orden; $j++) {
        $campo = 'OPC_S' . $j;
        if (isset($respondidos[$j])) {
          $valor = str_pad($respondidos[$j], 5, '0', STR_PAD_LEFT);
          $query->where($campo, $valor);
        }
      }
      //ver consulta final con antes de ejecutarla
      //dd($query->toSql(), $query->getBindings());
      //order by 
      //$query->orderBy('OPC_ValorOpcion', 'desc');
      $opciones = $query->get();
      //dd($opciones);
      if ($opciones->count() > 0) {
        $encontrado = true;
        $siguienteSelector = $paso;
        $opcionesValidas = $opciones;
        $pantallaSiguiente = $paso->PAS_Pantalla_Ubicacion;
        break;
      }
    }
    if (!$encontrado) {
      return ['mensaje' => 'BACKEND: No hay ningún selector siguiente'];
    }
    $pantallaAnterior = null;
    if (isset($selectorEditado) && $selectorEditado) {
      $pantallaEditada = $pasoEditado->PAS_Pantalla_Ubicacion;
      // Buscar el paso anterior con una pantalla menor
      $pantallaAnteriorId = null;
      foreach ($pasos->sortByDesc('PAS_Orden') as $pasoAnt) {
        if (
          $pasoAnt->PAS_Orden < $pasoEditado->PAS_Orden &&
          $pasoAnt->PAS_Pantalla_Ubicacion < $pantallaEditada
        ) {
          $pantallaAnteriorId = $pasoAnt->PAS_Pantalla_Ubicacion;
          //$pantallaSiguiente = $pasoAnt->PAS_Pantalla_Ubicacion + 1;
          break;
        }
      }
      $pantallaAnterior = $pantallaAnteriorId;
    }
    // 5. Estructurar el resultado
    return [
      'selector'    => $siguienteSelector->PAS_Nombre,
      'selector_nombre'    => $siguienteSelector->PAS_Html_name,
      'selector_container' => $siguienteSelector->PAS_Container,
      'selector_orden'  => $siguienteSelector->PAS_Orden,
      'selector_tipo'  => $siguienteSelector->PAS_Tipo_Selector,
      'selector_id'  => $siguienteSelector->PAS_PasoId,
      'pantalla_anterior' => self::getPantallaNombre($pantallaAnterior),
      'pantalla_siguiente' => self::getPantallaNombre($pantallaSiguiente),
      'pantalla_ubicacion' => $pantallaSiguiente,
      'data'      => $opcionesValidas->map(function ($op) {
        return [
          'id_opcion' => $op->OPC_OpcionId,
          'id_selector' => $op->OPC_PasoId,
          'id_padre' => $op->OPC_OpcionPadreId,
          'valor' => $op->OPC_ValorOpcion,
          'imagen' => $op->OPC_Imagen,
          'descripcion' => $op->OPC_Descripcion,
          'programacion' => $op->OPC_Programacion
        ];
      })->values()->toArray(),
    ];
  }
  /**
   * Elimina del avance todos los selectores de mayor orden al editado
   * @param array $avance
   * @param string $selectorEditado
   * @param \Illuminate\Support\Collection $pasos
   * @return array
   */
  public static function limpiarAvancePosterior($avance, $pasoEditado, $pasos)
  {
    if (!$pasoEditado) return $avance;
    $ordenEditado = $pasoEditado->PAS_Orden;
    foreach ($pasos as $paso) {
      if ($paso->PAS_Orden > $ordenEditado) {
        unset($avance[$paso->PAS_Html_name]);
      }
    }
    return $avance;
  }
  public static function getPantallaNombre($id)
  {
    if ($id == null) return null;
    //array con los nombres de las vistas
    $vistas = [
      1 => 'inicio', //ver los return views
      2 => 'tipo_producto',
      3 => 'tipo_confeccion',
      4 => 'medidas',
      5 => 'telas',
      6 => 'sistema_apertura',
      7 => 'bastones',
      8 => 'resumen',
    ];
    return $vistas[$id];
  }
  /**
   * Devuelve la data del selector actual, llenando sus opciones según las dependencias respondidas hasta ese paso
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public static function SelectorActual(Request $request)
  {
    $avance = Session::get('avance_temporal', '');
    $avance = json_decode($avance, true);
    $selectorNombre = $request->input('nombre_selector'); // nombre del selector actual

    // Obtener todos los pasos activos y ordenados
    $pasos = PasoCotizador::where('PAS_Activo', 1)
      ->where('PAS_Eliminado', 0)
      ->orderBy('PAS_Orden', 'asc')
      ->get();
    $pasoActual = $pasos->firstWhere('PAS_Html_name', $selectorNombre);
    if (!$pasoActual) {
      return response()->json(['mensaje' => 'Selector no encontrado'], 404);
    }
    // Solo dependencias hasta el paso actual
    $respondidos = [];
    foreach ($pasos as $paso) { //
      if (
        isset($avance[$paso->PAS_Html_name]) &&
        is_numeric($avance[$paso->PAS_Html_name]) &&
        $paso->PAS_Orden <= $pasoActual->PAS_Orden
      ) {
        $respondidos[(int)$paso->PAS_Orden] = $avance[$paso->PAS_Html_name]; //guarda el orden y el valor
      } else {
        $respondidos[(int)$paso->PAS_Orden] = 'T';
      }
    }

    $query = OpcionCotizador::where('OPC_PasoId', $pasoActual->PAS_PasoId)
      ->where('OPC_Activo', 1)
      ->where('OPC_Eliminado', 0);
    //foreach ($pasos as $paso) { //

    // Agregar TODAS las dependencias previas
    //dd($paso->PAS_Orden); //3
    //respondidos
    //dd($respondidos);
    for ($j = 1; $j < $pasoActual->PAS_Orden; $j++) {
      //if ($pasoActual->PAS_Orden <= $ultimoOrden) continue; //solo los posteriores
      $campo = 'OPC_S' . $j;
      if (isset($respondidos[$j])) {
        $valor = str_pad($respondidos[$j], 5, '0', STR_PAD_LEFT);
        $query->where($campo, $valor);
      }
    }
    //}
    //dd($query->toSql(), $query->getBindings());
    $opcionesValidas = $query->get();
    $result = [
      'selector_nombre'    => $pasoActual->PAS_Html_name,
      'selector_container' => $pasoActual->PAS_Container,
      'selector_orden'  => $pasoActual->PAS_Orden,
      'selector_tipo'  => $pasoActual->PAS_Tipo_Selector,
      'selector_id'  => $pasoActual->PAS_PasoId,
      //'pantalla'  => self::getPantallaNombre($pasoActual->PAS_Pantalla_Ubicacion),
      'data'      => $opcionesValidas->map(function ($op) {
        return [
          'id_opcion' => $op->OPC_OpcionId,
          'id_selector' => $op->OPC_PasoId,
          'id_padre' => $op->OPC_OpcionPadreId,
          'valor' => $op->OPC_ValorOpcion,
          'imagen' => $op->OPC_Imagen,
          'descripcion' => $op->OPC_Descripcion,
          'programacion' => $op->OPC_Programacion
        ];
      })->values()->toArray(),
    ];
    return response()->json($result);
  }


  public function testGetSelectorSiguiente()
  {
    // $avance = [
    //   'area_instalacion' => 7,
    //   'tipo' => 247,
    // ];
    $avance = Session::get('avance_temporal', '');
    $avance = json_decode($avance, true);
    $result = self::getSelectorSiguiente($avance, 'area_instalacion');
    dd($result);
  }
  public function SelectorSiguiente(Request $request)
  {
    //GUARDAR EL VALOR DEL SELECTOR EN LA SESION
    // Fusionar avance anterior con el nuevo
    $avanceActual = Session::get('avance_temporal', '');
    $avanceActual = json_decode($avanceActual, true);
    if ($avanceActual === null) {
      $avanceActual = [];
    }
    $avanceFusionado = array_merge($avanceActual, [$request->nombre_selector => $request->valor]);
    //dd($avanceFusionado);

    Session::put('avance_temporal', json_encode($avanceFusionado));

    $avance = $avanceFusionado;
    $result = self::getSelectorSiguiente($avance, $request->nombre_selector);
    return response()->json($result);
  }

  public function updateQuotation()
  {
    // $order_lines = [
    //   [
    //     "type" => "product",
    //     'product_id' => 7058, //Invtek cortina en Odoo
    //     'description' => 'Invtek cortina',
    //     'quantity' => 1,
    //     'price_unit' => 200,
    //   ],
    //   [
    //     "type" => "note",
    //     "description" => 'Invtek cortina editada'
    //   ]
    // ];
    // dd($order_lines);
    // // Actualizar cotización de productos
    // $response_2 = Http::post('http://localhost:3036/update-quotation-main', [
    //   'partner_id' => 1,
    //   'pricelist_id' => 1,
    //   'order_lines' => $order_lines,
    //   'order_id' => 101
    // ]);

    $avance = Session::get('avance_temporal') ?? [];
    if (empty($avance)) {
      return response()->json(['success' => false, 'message' => 'No hay datos para cotizar'], 404);
    }
    $productos = Session::get('productos') ?? [];
    dd($productos);
    if (empty($productos)) {
      return response()->json(['success' => false, 'message' => 'No hay productos para cotizar'], 404);
    }

    $avance = json_decode($avance, true);
    $nombre_articulo = $avance['nombre_articulo'];
    $nombre_proyecto = $avance['nombre_proyecto'];
    $price_list = 1;
    $partner_id = 15;
    if (Auth::check()) {
      $partner_id = Auth::user()->odoo_partner_id;
      $price_list = Auth::user()->price_list_id;
    }
    $descripciones = self::getDescripcionOpciones();
    $cortinero = null;
    if ($descripciones['descripcion_cortinero'] !== '') {
      $cortinero = [
        "type" => "note",
        "description" => $descripciones['descripcion_cortinero']
      ];
    }
    $precio_unitario = self::getSubtotal($productos);

    $order_lines = [
      [
        "type" => "product",
        'product_id' => 7058, //Invtek cortina en Odoo
        'description' => $nombre_articulo . ' (' . $nombre_proyecto . ')',
        'quantity' => 1,
        'price_unit' => number_format($precio_unitario, $this->decimales, '.', ''), //precio unitario de la cotizacion a dos decimales
      ],
      [
        "type" => "note",
        "description" => $descripciones['descripcion_cortina']
      ]
    ];
    if ($cortinero) {
      $order_lines[] = $cortinero; //agregamos la nota de cortinero
    }

    // productos
    // "productos": {
    //     "7061": {
    //         "precio_unitario": 2,
    //         "cantidad": 1,
    //         "precio_total": 2
    //     },
    //     "3555": {
    //         "precio_unitario": 369.41,
    //         "cantidad": 0.3333333333333333,
    //         "precio_total": 123.13666666666667
    //     }
    // }
    $order_lines_productos = [];
    collect($productos)->each(function ($value, $key) use (&$order_lines_productos) {
      $order_lines_productos[] = [
        'product_id' => $key,
        'quantity' => $value['cantidad'],
        'price_unit' => $value['precio_unitario']
      ];
    });
    $cotizacion_odoo = COCO::where('COCO_id', Session::get('cotizacion_id'))->first();
    $id_cotizacion_2 = null;


    // 2. Cotización principal (cotizacion-1)
    $nota_ref_cot2 = [
      "type" => "note",
      "description" => "REF COT. " . $id_cotizacion_2
    ];
    $order_lines_con_ref = $order_lines;
    $order_lines_con_ref[] = $nota_ref_cot2;

    $id_cotizacion_1 = null;

    //dd($order_lines_con_ref, ($cotizacion_odoo && !empty($cotizacion_odoo->COCO_odoo_cotizacion)));
    if ($cotizacion_odoo && !empty($cotizacion_odoo->COCO_odoo_cotizacion)) {
      // Actualizar cotización principal
      $response = Http::post('http://localhost:3036/update-quotation-main', [
        'partner_id' => $partner_id,
        'pricelist_id' => $price_list,
        'order_lines' => $order_lines_con_ref,
        'order_id' => (int) $cotizacion_odoo->COCO_odoo_cotizacion
      ]);

      $id_cotizacion_1 = $cotizacion_odoo->COCO_odoo_cotizacion;
    }

    return response()->json([
      'success' => true,
      'response' => $response->json()
    ]);
  }
  public function resumen()
  {
    // Obtener el avance del usuario logueado o de la sesión temporal
    if (empty(Session::get('avance_temporal', []))) {
      return redirect()->route('inicio');
    }
    $avance = Auth::check()
      ? json_decode(Auth::user()->avance ?? [], true)
      : json_decode(Session::get('avance_temporal', []), true);
    // Si no hay avance, redirigir al inicio
    if (empty($avance)) {
      return redirect()->route('inicio');
    }
    // filtar las opciones que tengan valor de numero
    $opciones_numero = array_filter($avance, function ($value) {
      return is_numeric($value);
    });
    $opciones_numero = array_filter($opciones_numero, function ($key) {
      return ($key !== 'lado_a') && ($key !== 'lado_b') && ($key !== 'alto') && ($key !== 'ancho') && ($key !== 'radio');
    }, ARRAY_FILTER_USE_KEY);

    $opciones = self::getOpcionesFromAvance($avance, $opciones_numero); // filtar las opciones que tengan valor de numero
    //Con las opciones calculamos los productos
    //dd('avance', $avance);
    $productos = self::getProductos($avance, $opciones_numero);
    //dd($productos);
    //guardar en la session los productos
    Session::put('productos', $productos);
    $cotizacion_id = Session::has('cotizacion_id') ? Session::get('cotizacion_id') : null;
    //dd($cotizacion_id);
    $odoo_cotizacion_numero = '';
    if (!is_null($cotizacion_id)) {

      // **Actualizar cotización existente**
      $cotizacion = COCO::find($cotizacion_id);
      $cotizacion->COCO_fecha = Carbon::now();
      $cotizacion->COCO_usuario = Auth::check() ? Auth::user()->id : '0'; //si no hay usuario logueado, se guarda como invitado
      //$cotizacion->COCO_monto_total = $validatedData['precio_unitario'] * $validatedData['cantidad'];
      $cotizacion->save();

      //obtener los datos de la cotizacion
      if ($cotizacion->COCO_odoo_cotizacion != null && $cotizacion->COCO_odoo_cotizacion != '') {
        $odoo_cotizacion_numero = $cotizacion->COCO_odoo_cotizacion;
      }
    } else {
      //guardar en la base de datos la cotizacion
      $cotizacion = new COCO();
      $cotizacion->COCO_fecha = Carbon::now();
      $cotizacion->COCO_usuario = Auth::check() ? Auth::user()->id : '0'; //si no hay usuario logueado, se guarda como invitado
      //$cotizacion->COCO_monto_total = $validatedData['precio_unitario'] * $validatedData['cantidad'];
      $cotizacion->COCO_estatus = 'creacion';
      $cotizacion->save();
      Session::put('cotizacion_id', $cotizacion->COCO_id);
    }

    //borrar los detalles de la cotizacion
    COCOD::where('COCOD_COCO_id', $cotizacion->COCO_id)->delete();
    //guardar los detalles del proyecto
    $cortina = new COCOD();
    $cortina->COCOD_COCO_id = $cotizacion->COCO_id;
    // **Crear la cortina en RPT_CotizacionCortinas**
    $cortina->COCOD_cantidad = 1;
    $cortina->COCOD_opciones = json_encode($avance);
    $cortina->COCOD_productos = json_encode($productos);
    $cortina->COCOD_eliminado = 0; // Por defecto no eliminado
    $cortina->save();
    //dd($cortina);
    //con los productos calculamos el subtotal
    $subtotal = self::getSubtotal($productos);

    $iva = $subtotal * 0.16;
    $total = $subtotal + $iva;

    $descripciones = $this->getDescripcionOpciones();
    //dd($descripciones);
    $descripcion_cortina = $descripciones['descripcion_cortina'];
    $descripcion_cortinero = $descripciones['descripcion_cortinero'];
    $links_opciones_resumen = $descripciones['links_opciones_resumen'];
    $cotizacion_status = strtoupper($cotizacion->COCO_estatus);
    // Devolver la vista con el avance
    return view('resumen', compact('odoo_cotizacion_numero', 'avance', 'subtotal', 'iva', 'total', 'opciones', 'descripcion_cortina', 'descripcion_cortinero', 'links_opciones_resumen', 'cotizacion_status'));
  }
  public function getOpcionesFromAvance($avance, $opciones_numero)
  {
    // obtener el valor de las opciones de la base de datos por id
    $opciones = OpcionCotizador::whereIn('OPC_OpcionId', array_values($opciones_numero))->get();
    // Mapear las opciones para obtener el valor y la descripción
    $opciones = collect($avance)->map(function ($value, $key) use ($opciones) {
      $opcion = $opciones->firstWhere('OPC_OpcionId', $value);
      return [
        'id' => $value,
        'valor' => $opcion ? $opcion->OPC_ValorOpcion : $value,
        'descripcion' => $opcion ? $opcion->OPC_Descripcion : '',
        'imagen' => $opcion ? $opcion->OPC_Imagen : ''
      ];
    })->toArray();
    return $opciones;
  }
  public function getProductos($avance, $opciones_numero)
  {
    if ($avance['tipo_riel'] == 12) { //riel recto
      $medida_ancho = $avance['ancho'];
      $medida_alto = $avance['alto'];
      $area = $medida_ancho * $medida_alto;
    } else if ($avance['tipo_riel'] == 13) { //riel curvo
      $medida_ancho = $avance['lado_a'] + $avance['lado_b'];
      $medida_alto = $avance['alto'];
      $area = $medida_ancho * $medida_alto;
    } else if ($avance['tipo_riel'] == 183) { //riel curvo
      $medida_ancho = $avance['ancho'];
      $medida_alto = $avance['alto'];
      $radio = $avance['radio'];

      $area = $radio * $radio * pi();
    }
    //dd(array_values($opciones_numero));
    $medida = $medida_ancho;
    $productos = PCNT::whereIn('PCNT_OPC_OpcionId', array_values($opciones_numero))->get();
    $precios = self::getOdooPrices($productos->pluck('PCNT_PROD_id')->toArray());
    $items = [];
    //dd($productos->pluck('PCNT_PROD_id')->toArray());
    $productos->each(function ($producto) use ($precios, $medida, &$items) {
      //dd($precios[$producto->PCNT_PROD_id], $producto->PCNT_PROD_id);
      //si existe el precio
      if (isset($precios[$producto->PCNT_PROD_id])) {
        $items[$producto->PCNT_PROD_id] = [
          'precio_unitario' => $precios[$producto->PCNT_PROD_id],
          'cantidad' => number_format(($medida * 100) / $producto->PCNT_base_ancho * $producto->PCNT_base_cantidad, $this->decimales, '.', ''),
          //'precio_total' => $precios[$producto->PCNT_PROD_id] * ($medida * 100) / $producto->PCNT_base_ancho * $producto->PCNT_base_cantidad
        ];
      }
    });
    return $items;
  }
  public function getOdooPrices($ids)
  {
    //obtener precios de odoo
    $response = Http::post('http://localhost:3036/getOdooPrices/', [
      'ids' => $ids, // ID del cliente en Odoo
    ]);
    //dd($ids, $response->json());
    $precios = $response->json();
    return $precios;
  }
  public function getSubtotal($productos)
  {
    /* array:2 [▼ // app\Http\Controllers\dashboard\Analytics.php:160
      7061 => array:3 [▼
        "precio_unitario" => 2.0
        "cantidad" => 1.0
        "precio_total" => 2.0
      ]
      3555 => array:3 [▶]
    ] */
    $subtotal = 0;
    foreach ($productos as $producto) {
      $precio_unitario = number_format($producto['precio_unitario'], $this->decimales, '.', '');
      $subtotal += $precio_unitario * number_format($producto['cantidad'], $this->decimales, '.', '');
    }
    return $subtotal;
  }
  public function bastones()
  {
    // Esta función obtiene las opciones siguientes y las devuelve en un formato adecuado para la vista. EJEMPLO:
    /* [
      'accesorios' => [
        ['id' => 1, 'valor' => 'Bastón']
      ],
      'materiales' => [
        ['id' => 10, 'valor' => 'Acrílico', 'id_padre' => 1]
      ],
      'modelos' => [
        ['id' => 100, 'valor' => 'BASTÓN CON GANCHOS', 'imagen' => 'modelo1.png', 'precio' => 191.43, 'id_padre' => 10]
      ],
      'largos' => [
        ['id' => 200, 'valor' => '60" (1.52 m)', 'id_padre' => 100]
      ]
    ] */
    // Obtener las opciones de accesorios
    $accesorios = self::getOpcionesPorValorElementoHTML('Accesorio de apertura');
    $result = [];
    $result['accesorios'] = $accesorios->map(function ($opcion) {
      return [
        'id' => $opcion->OPC_OpcionId,
        'valor' => $opcion->OPC_ValorOpcion,
        'id_padre' => $opcion->OPC_OpcionPadreId
      ];
    })->values()->toArray();
    // Obtener las opciones de materiales
    $materiales = self::getOpcionesPorValorElementoHTML('Material accesorio');
    $result['materiales'] = $materiales->map(function ($opcion) {
      return [
        'id' => $opcion->OPC_OpcionId,
        'valor' => $opcion->OPC_ValorOpcion,
        'id_padre' => $opcion->OPC_OpcionPadreId
      ];
    })->values()->toArray();
    // Obtener las opciones de modelos
    $modelos = self::getOpcionesPorValorElementoHTML('Modelo accesorio');
    $result['modelos'] = $modelos->map(function ($opcion) {
      return [
        'id' => $opcion->OPC_OpcionId,
        'valor' => $opcion->OPC_ValorOpcion,
        'imagen' => $opcion->OPC_Imagen ?? '',
        'id_padre' => $opcion->OPC_OpcionPadreId
      ];
    })->values()->toArray();
    // Obtener las opciones de largos
    $largos = self::getOpcionesPorValorElementoHTML('Largo accesorio');
    $result['largos'] = $largos->map(function ($opcion) {
      return [
        'id' => $opcion->OPC_OpcionId,
        'valor' => $opcion->OPC_ValorOpcion,
        'id_padre' => $opcion->OPC_OpcionPadreId
      ];
    })->values()->toArray();
    $selectores = self::getSelectoresPorPantalla(7);
    //dd($result); // Para depurar y ver el resultado antes de continuar
    // Devolver el resultado
    return view('bastones', ['result' => $result, 'selectores' => $selectores]);
  }
  public function getOpcionesPorValorElementoHTML($valor)
  {
    // Buscar el nodo padre por valor
    $opcionPadre = PasoCotizador::where('PAS_Eliminado', 0)
      ->where('PAS_Nombre', $valor)
      ->first();
    //dd($valor);
    // Verificar si se encontró
    if (!$opcionPadre) {
      return []; // lanzar una excepción
    }

    // Buscar hijos activos del nodo padre
    return OpcionCotizador::where('OPC_Eliminado', 0)
      ->where('OPC_PasoId', $opcionPadre->PAS_PasoId)
      ->where('OPC_Activo', 1)
      ->orderBy('OPC_ValorOpcion', 'asc')
      ->get();
  }
  public function getOpcionesArrayPadres($values)
  {
    // Buscar los nodos hijos por los ids Padres proporcionados
    $filtro = array_keys($values);
    // Devolver los hijos activos de los nodos padres
    return OpcionCotizador::where('OPC_Eliminado', 0)
      ->wherein('OPC_OpcionPadreId', $filtro)
      ->where('OPC_Activo', 1)
      ->get();
  }
  public function getOpcionesPorValor($valor)
  {
    // Buscar el nodo padre por valor
    $opcionPadre = OpcionCotizador::where('OPC_Eliminado', 0)
      ->where('OPC_ValorOpcion', $valor)
      ->first();

    // Verificar si se encontró
    if (!$opcionPadre) {
      return []; // O podrías lanzar una excepción
    }

    // Buscar hijos activos del nodo padre
    return OpcionCotizador::where('OPC_Eliminado', 0)
      ->where('OPC_OpcionPadreId', $opcionPadre->OPC_OpcionId)
      ->where('OPC_Activo', 1)
      ->get();
  }
  public function sistema_apertura()
  {
    // 1. Traer "Sistema de apertura" (Manual, Motorizado)
    $aperturas = self::getOpcionesPorValorElementoHTML('Sistema de apertura');
    $apertura_ids = $aperturas->pluck('OPC_OpcionId')->toArray();
    // 2. Traer todos los hijos de esas aperturas
    $hijos_aperturas = self::getOpcionesArrayPadres(array_flip($apertura_ids));
    $sistemas_apertura = $aperturas->map(function ($op) {
      return [
        'id' => $op->OPC_OpcionId,
        'valor' => $op->OPC_ValorOpcion,
      ];
    })->values();
    //dd($sistemas_apertura);

    $superficie_instalacion = $hijos_aperturas->map(function ($op) {
      return [
        'id' => $op->OPC_OpcionId,
        'valor' => $op->OPC_ValorOpcion,
        'id_padre' => $op->OPC_OpcionPadreId
      ];
    })->values();
    $rieles = self::getOpcionesPorValorElementoHTML('Modelo del Riel');
    $rieles = $rieles->map(function ($op) {
      return [
        'id' => $op->OPC_OpcionId,
        'valor' => $op->OPC_ValorOpcion,
        'descripcion' => $op->OPC_Descripcion ?? '',
        'imagen' => $op->OPC_Imagen ?? '',
        'id_padre' => $op->OPC_OpcionPadreId
      ];
    })->values();

    $materiales = self::getOpcionesPorValorElementoHTML('Material de riel');
    $materiales = $materiales->map(function ($op) {
      return [
        'id' => $op->OPC_OpcionId,
        'valor' => $op->OPC_ValorOpcion,
        'id_padre' => $op->OPC_OpcionPadreId
      ];
    })->values();

    $colores = self::getOpcionesPorValorElementoHTML('Color de riel');
    $colores = $colores->map(function ($op) {
      return [
        'nombre' => $op->OPC_ValorOpcion,
        'descripcion' => $op->OPC_Descripcion ?? '',
        'hex' => $op->OPC_Programacion ?? '#ccc',
        'id_padre' => $op->OPC_OpcionPadreId
      ];
    })->values();
    $selectores = self::getSelectoresPorPantalla(6);

    return view('sistema_apertura', [
      'sistemas_apertura' => $sistemas_apertura,
      'superficie_instalacion' => $superficie_instalacion,
      'sistemas_rieles' => $rieles,
      'materiales_rieles' => $materiales,
      'colores_rieles' => $colores,
      'selectores' => $selectores
    ]);
  }
  public function telas()
  {
    $tipo_material = self::getOpcionesPorValorElementoHTML('Tipo de material');
    $tipo_material = $tipo_material->map(function ($op) {
      return [
        'id' => $op->OPC_OpcionId,
        'valor' => $op->OPC_ValorOpcion,
        'id_padre' => $op->OPC_OpcionPadreId,
        'imagen' => $op->OPC_Imagen ?? '',
        'descripcion' => $op->OPC_Descripcion ?? '',
        'a_selected' => $op->OPC_EsDefault ? 'true' : 'false',
      ];
    })->values();

    // Consulta todos los datos de la tabla
    //$telas = \DB::table('RPT_ODOO_CORTINAS')->select('id', 'name', 'Tipo')->get();
    $version = random_int(1, 10000);
    /*  $material = self::getOpcionesPorValorElementoHTML('Material');
    $material = $material->map(function ($op) {
      return [
        'id' => $op->OPC_OpcionId,
        'valor' => $op->OPC_ValorOpcion,
        'id_padre' => $op->OPC_OpcionPadreId,
        'imagen' => $op->OPC_Imagen ?? '',
        'descripcion' => $op->OPC_Descripcion ?? '',
        'a_selected' => $op->OPC_EsDefault ? 'true' : 'false',
      ];
    })->values(); */
    $selectores = self::getSelectoresPorPantalla(5);
    //dd(compact('telas', 'tipo_tela', 'version'));
    return view('catalogo_telas', compact('tipo_material', 'version', 'selectores'));
  }

  public function inicio()
  {
    //Session::forget('avance_temporal');
    //dd(Auth::check());
    $avance = Session::get('avance_temporal', []);
    //dd($avance);
    if (Auth::check() && !empty($avance)) {
      Auth::user()->avance = $avance;
      Auth::user()->save();
    } else {
      $avance = [
        'siguiente-vista' => 'inicio',
      ];
    }
    //dd(Auth::user());

    // OpcionCotizador::where('OPC_Eliminado', 0)->get();
    //reemplazar $opcionesCalidad con el array de opciones de la base de datos
    $opciones = self::getOpcionesPorValorElementoHTML('Calidad');
    //obtener el valor de la columna OPC_ValorOpcion y como llave el valor de la columna OPC_OpcionId del array $opciones
    $opcionesCalidad = \Arr::pluck($opciones, 'OPC_ValorOpcion', 'OPC_OpcionId');
    //obtener la descripción de la columna OPC_Descripcion y como llave el valor de la columna OPC_OpcionId del array $opciones
    $opcionesCalidadDescripcion = \Arr::pluck($opciones, 'OPC_Descripcion', 'OPC_OpcionId');

    $area_instalacion_opciones = self::getOpcionesPorValorElementoHTML('Área de instalación');
    $area_instalacion = \Arr::pluck($area_instalacion_opciones, 'OPC_ValorOpcion', 'OPC_OpcionId');
    //ordenar alfabeticamente por OPC_ValorOpcion sin importar mayusculas o minusculas ni acentos
    //sort($area_instalacion, SORT_LOCALE_STRING);
    //$area_instalacion = array_values($area_instalacion);
    //dd($area_instalacion);
    $descripcion_area_instalacion = \Arr::pluck($area_instalacion_opciones, 'OPC_Descripcion', 'OPC_OpcionId');
    //dd($descripcion_area_instalacion);
    // Si se indicó una siguiente vista
    if (is_string($avance)) {
      $avance = json_decode($avance, true);
    }
    /* if ($avance['siguiente-vista'] != 'resumen') {
      $avance['siguiente-vista'] = 'inicio';
    } */
    $selectores = self::getSelectoresPorPantalla(1);
    if (!isset($avance['siguiente-vista'])) {
      $avance['siguiente-vista'] = 'inicio';
    }
    //dd($avance);
    if (empty($avance) || $avance['siguiente-vista'] != 'resumen') {
      return view('inicio', compact('opcionesCalidad', 'opcionesCalidadDescripcion', 'area_instalacion', 'descripcion_area_instalacion', 'selectores'));
    }
    return redirect()->route($avance['siguiente-vista']);
  }
  public function guardarAvance(Request $request)
  {
    //dd($request->input('siguiente-vista')); // Para depurar y ver el resultado antes de continuar
    // Obtener avance actual desde sesión (si no logueado) o base de datos (si logueado)
    $avanceActual = Session::get('avance_temporal', []);
    if (empty($avanceActual)) {
      $avanceActual = [];
    }
    //dd($avanceActual);
    // Datos nuevos desde el request
    $nuevoAvance = $request->except('_token', 'actual-vista'); // Excluye campos no necesarios
    $nuevoAvance['anterior-vista'] = $request->input('actual-vista');
    //si es json convertir a array
    if (is_string($avanceActual)) {
      $avanceActual = json_decode($avanceActual, true);
    }
    if (is_string($nuevoAvance)) {
      $nuevoAvance = json_decode($nuevoAvance, true);
    }
    // Fusionar avance anterior con el nuevo
    $avanceFusionado = array_merge($avanceActual, $nuevoAvance);
    //dd($avanceFusionado);

    Session::put('avance_temporal', json_encode($avanceFusionado));
    if (Auth::check()) {
      //guardar avance en la base de datos
      Auth::user()->avance = json_encode($avanceFusionado);
      Auth::user()->save();
    }
    // Si contiene 'resumen' en el nuevo avance → redirige a resumen
    if (isset($avanceFusionado['resumen'])) {
      return redirect()->route('resumen');
    }

    // Obtener las opciones de calidad
    /*  $opciones = self::getOpcionesPorValorElementoHTML('Calidad');
    $opcionesCalidad = \Arr::pluck($opciones, 'OPC_ValorOpcion', 'OPC_OpcionId');
    $opcio nesCalidadDescripcion = \Arr::pluck($opciones, 'OPC_Descripcion', 'OPC_OpcionId');
    */
    $opcionesCalidad = [];
    $opcionesCalidadDescripcion = [];
    $area_instalacion = [];
    $descripcion_area_instalacion = [];
    if ($avanceFusionado['siguiente-vista'] == 'inicio') {
      $area_instalacion_opciones = self::getOpcionesPorValorElementoHTML('Área de instalación');
      $area_instalacion = \Arr::pluck($area_instalacion_opciones, 'OPC_ValorOpcion', 'OPC_OpcionId');
      $descripcion_area_instalacion = \Arr::pluck($area_instalacion_opciones, 'OPC_Descripcion', 'OPC_OpcionId');
    }

    //dd($avanceFusionado);
    //dd(Session::get('avance_temporal'));
    // Si se indicó una siguiente vista
    return $request->filled('siguiente-vista')
      ? redirect()->route($request->input('siguiente-vista'))
      : view('inicio', compact('opcionesCalidad', 'opcionesCalidadDescripcion', 'area_instalacion', 'descripcion_area_instalacion'));
  }

  public function medidas()
  {
    $hojas = self::getOpcionesPorValorElementoHTML('Hojas');
    $hojas = \Arr::pluck($hojas, 'OPC_ValorOpcion', 'OPC_OpcionId');
    //dd($hojas);
    $rieles = self::getOpcionesPorValorElementoHTML('Instalación Riel');
    //dd($rieles);
    $tiposRiel = $rieles->map(function ($opcion) {
      return [
        'id_riel' => $opcion->OPC_OpcionId,
        'tipo' => $opcion->OPC_OpcionPadreId,
        'image' => $opcion->OPC_Imagen,
        'opcion_radio' => $opcion->OPC_ValorOpcion,
        'a_selected' => "false",
      ];
    })->toArray();
    $rieles = \Arr::pluck($rieles, 'OPC_ValorOpcion', 'OPC_OpcionId');
    $hijos = self::getOpcionesArrayPadres($rieles);

    //solo regresar los no nulos
    $hijos_imagenes_medidas = $hijos->filter(function ($opcion) {
      return $opcion->OPC_PasoId == 6;
    });
    $imagenes_medidas = $hijos_imagenes_medidas->map(function ($opcion) {
      return [
        'id_paso' => $opcion->OPC_PasoId,
        'id_opcion' => $opcion->OPC_OpcionId,
        'id_riel' => $opcion->OPC_OpcionPadreId,
        'image' => $opcion->OPC_Imagen,
        'descripcion' => $opcion->OPC_Descripcion,
        'coordenadas' => $opcion->OPC_Programacion
      ];
    })->toArray();

    $hijos_imagenes_hojas = $hijos->filter(function ($opcion) {
      return $opcion->OPC_PasoId == 21;
    });
    $hijos_imagenes_hojas = $hijos_imagenes_hojas->map(function ($opcion) {
      return [
        'id' => $opcion->OPC_OpcionId,
        'id_paso' => $opcion->OPC_PasoId,
        'id_imagen' => $opcion->OPC_OpcionId,
        'id_riel' => $opcion->OPC_OpcionPadreId,
        'valor' => $opcion->OPC_ValorOpcion,
        'image' => $opcion->OPC_Imagen,
        'descripcion' => $opcion->OPC_Descripcion,
        'coordenadas' => $opcion->OPC_Programacion
      ];
    })->toArray();
    $direccion_apertura = self::getOpcionesPorValorElementoHTML('Dirección de apertura');
    $direccion_apertura = $direccion_apertura->map(function ($opcion) {
      return [
        'id' => $opcion->OPC_OpcionId,
        'descripcion' => $opcion->OPC_Descripcion,
        'programacion' => $opcion->OPC_Programacion,
        'imagen' => $opcion->OPC_Imagen,
        'id_padre' => $opcion->OPC_OpcionPadreId,
        'opcion_radio' => $opcion->OPC_ValorOpcion,
        'a_selected' => $opcion->OPC_EsDefault ? 'true' : 'false',
      ];
    })->toArray();
    $selectores = self::getSelectoresPorPantalla(4);
    //dd($imagenes_medidas, $hojas);
    return view('configuracion_medidas', compact('tiposRiel', 'imagenes_medidas', 'hojas', 'hijos_imagenes_hojas', 'direccion_apertura', 'selectores'));
  }
  public function tipo_producto()
  {
    $opciones_tipo_producto = self::getOpcionesPorValorElementoHTML('Tipo de producto');
    //dd($tipo_producto);
    $tipo_producto = $opciones_tipo_producto->map(function ($op) {
      return [
        'id' => $op->OPC_OpcionId,
        'valor' => $op->OPC_ValorOpcion,
        'id_padre' => $op->OPC_OpcionPadreId,
        'imagen' => $op->OPC_Imagen ?? '',
        'descripcion' => $op->OPC_Descripcion ?? '',
        'a_selected' => $op->OPC_EsDefault ? 'true' : 'false',
      ];
    })->values();

    $descripcion_tipo_producto = \Arr::pluck($opciones_tipo_producto, 'OPC_Descripcion', 'OPC_OpcionId');

    $subproducto = self::getOpcionesPorValorElementoHTML('Subproducto');
    $subproducto = $subproducto->map(function ($op) {
      return [
        'id' => $op->OPC_OpcionId,
        'valor' => $op->OPC_ValorOpcion,
        'id_padre' => $op->OPC_OpcionPadreId,
        'imagen' => $op->OPC_Imagen ?? '',
        'descripcion' => $op->OPC_Descripcion ?? '',
        'a_selected' => $op->OPC_EsDefault ? 'true' : 'false',
      ];
    })->values();
    $selectores = self::getSelectoresPorPantalla(2);
    //$area_instalacion = self::getOpcionesPorValorElementoHTML('Área de instalación');
    //$area_instalacion = \Arr::pluck($area_instalacion, 'OPC_ValorOpcion', 'OPC_OpcionId');

    return view('tipo_producto', compact('tipo_producto', 'subproducto', 'descripcion_tipo_producto', 'selectores'));
  }
  public static function getSelectoresPorPantalla($pantalla_id)
  {
    //$selectores = PasoCotizador::where('PAS_Pantalla_Ubicacion', $pantalla_id)->get();
    $selectores = PasoCotizador::where('PAS_Eliminado', 0)->where('PAS_Activo', 1)
      ->where('PAS_Pantalla_Ubicacion', '>=', $pantalla_id)
      ->orderBy('PAS_Orden', 'asc')->get();
    return $selectores;
  }
  public function tipo_confeccion()
  {
    $tiposConfecciondb = self::getOpcionesPorValorElementoHTML('Confección');
    $tiposConfeccion_ids = \Arr::pluck($tiposConfecciondb, 'OPC_ValorOpcion', 'OPC_OpcionId');
    $tiposConfeccion = $tiposConfecciondb->map(function ($opcion) {
      return [
        'id' => $opcion->OPC_OpcionId,
        'valor' => $opcion->OPC_ValorOpcion,
        'descripcion' => $opcion->OPC_Descripcion,
        'imagen' => $opcion->OPC_Imagen,
        'id_padre' => $opcion->OPC_OpcionPadreId
      ];
    })->toArray();
    //$tiposConfeccion = [];
    $descripcion_tipo_confeccion = \Arr::pluck($tiposConfecciondb, 'OPC_Descripcion', 'OPC_OpcionId');

    $cards = self::getOpcionesArrayPadres($tiposConfeccion_ids);

    $cards_confeccion = $cards->map(function ($opcion) {
      return [
        'tipo' => $opcion->OPC_OpcionPadreId,
        'image' => $opcion->OPC_Imagen,
        'opcion_radio' => $opcion->OPC_ValorOpcion,
        'a_selected' => "false",
      ];
    })->toArray();
    $selectores = self::getSelectoresPorPantalla(3);
    //dd($tiposConfeccion, $cards_confeccion);
    return view('tipo_confeccion', compact('cards_confeccion', 'descripcion_tipo_confeccion', 'selectores'));
  }
  public function guardarArticulo(Request $request)
  {
    //dd($request->all());
    $articulo = new OpcionCotizador();
    $articulo->OPC_ValorOpcion = $request->input('valor_opcion');
    $articulo->OPC_OpcionPadreId = $request->input('opcion_padre_id');
    $articulo->OPC_Descripcion = $request->input('descripcion');
    $articulo->OPC_Activo = 1;
    $articulo->OPC_Eliminado = 0;
    $articulo->save();

    return redirect()->back()->with('success', 'Artículo guardado correctamente.');
  }

  public function index($id = null)
  {
    ini_set('memory_limit', '256M');
    /* $data = [];
    try { 
      $data = $response->json();
    } catch (\Throwable $th) {
      //throw $th;
    } */
    $cards_1 = [
      ["opcion_radio" => "Muro Interior", "image" => "im1.png", "a_selected" => "true"],
      ["opcion_radio" => "Muro Exterior", "image" => "im2.png", "a_selected" => ""],
      ["opcion_radio" => "Techo Interior", "image" => "im3.png", "a_selected" => ""],
      ["opcion_radio" => "Techo Exterior", "image" => "im4.png", "a_selected" => ""],
      ["opcion_radio" => "Escuadra", "image" => "im5.png", "a_selected" => ""]
    ];

    $cards_2 = [
      ["opcion_radio" => "Tradicional", "image" => "IMG6.jpg", "a_selected" => "true"],
      ["opcion_radio" => "Ripplefold", "image" => "IMG7.jpg", "a_selected" => ""] //,
      //["opcion_radio" => "Ojillos", "image" => "IMG8.jpg", "a_selected" => ""]
    ];
    $color_palette = [
      "cafe" => "#9b7c5f",
      "naranja" => "#e58552",
      "blanco" => "#ffffff",
      "champagne" => "#887D79",
      "ivory" => "#f5f4db",
      "oxford" => "#5c5757",
      "plata" => "#B2B2B2",
      "dark grey" => "#393B3C",
      "chocolate" => "#323032",
      "gris" => "#a3a3a3",
      "negro" => "#000000",
      "transparente" => "#e8f8f7",
      "natural" => "#949395",
      "olivo" => "#6B6158",
      "roble oscuro" => "#4C302A",
      "maple" => "#B48F72",
      "mate" => "#A4A59E",
      "aluminio" => "#B7B8C0"
    ];
    $cards_rieles_tradicional = [
      ["opcion_radio" => "Sistema RM y RT", "image" => "riel_RMyRT.png", "a_selected" => "true", "colors" => ["aluminio", "gris", "negro", "cafe"]], // Blanco, Gris, Negro, Café],
      ["opcion_radio" => "Sistema RHD", "image" => "riel_RHD.png", "a_selected" => "", "colors" => ["aluminio", "gris", "negro"]],
      ["opcion_radio" => "Sistema Murotrack", "image" => "riel_MT.png", "a_selected" => "", "colors" => ["blanco", "gris"]],
      ["opcion_radio" => "Sistema RC", "image" => "riel_RC.png", "a_selected" => "", "colors" => ["blanco"]],
      ["opcion_radio" => "Sistema RMC", "image" => "riel_RMC.png", "a_selected" => "", "colors" => ["blanco"]],
      ["opcion_radio" => "Sistema Flostrack", "image" => "riel_FT.png", "a_selected" => "", "colors" => ["chocolate", "gris", "olivo", "roble oscuro", "maple", "champagne", "negro", "aluminio"]],
      ["opcion_radio" => "Sistema Europeo", "image" => "riel_EUROPEO.png", "a_selected" => "", "colors" => ["blanco"]]
    ];
    $cards_rieles_ripplefold = [
      ["opcion_radio" => "Sistema RM y RT", "image" => "riel_RMyRT.png", "a_selected" => "true", "colors" => ["aluminio", "gris", "negro", "cafe"]], // Blanco, Gris, Negro, Café],
      ["opcion_radio" => "Sistema RHD", "image" => "riel_RHD.png", "a_selected" => "", "colors" => ["aluminio", "gris", "negro"]],
      ["opcion_radio" => "Sistema Murotrack", "image" => "riel_MT.png", "a_selected" => "", "colors" => ["blanco", "gris"]],
      ["opcion_radio" => "Sistema RC", "image" => "riel_RC.png", "a_selected" => "", "colors" => ["blanco"]],

      ["opcion_radio" => "Sistema Flostrack", "image" => "riel_FT.png", "a_selected" => "", "colors" => ["chocolate", "gris", "olivo", "roble oscuro", "maple", "champagne", "negro", "aluminio"]],
      ["opcion_radio" => "Sistema Europeo", "image" => "riel_EUROPEO.png", "a_selected" => "", "colors" => ["blanco"]]
    ];

    $cards_3 = [
      ["opcion_radio" => "Blackout", "image" => "img9.PNG", "a_selected" => "true"],
      ["opcion_radio" => "Sheer", "image" => "img10.PNG", "a_selected" => ""]
      // ["opcion_radio" => "Decorativa", "image" => "img11.PNG"]
    ];

    $steps = [
      ["a_selected" => "true", "title" => "ESPACIO O UBICACIÓN", "number" => "1"],
      ["a_selected" => "false", "title" => "SISTEMA DE CONFECCIÓN", "number" => "2"],
      ["a_selected" => "false", "title" => "TIPO DE TELA", "number" => "3"],
      ["a_selected" => "false", "title" => "MEDIDAS Y HOJAS", "number" => "4"],
      ["a_selected" => "false", "title" => "ACCESORIOS", "number" => "5"]
    ];

    // Consulta todos los datos de la tabla
    $telas = \DB::table('RPT_ODOO_CORTINAS')->select('id', 'name', 'Tipo')->get();

    // Separar las telas en dos arrays según el tipo
    $telas_blackout = $telas->where('Tipo', 'blackout')->values();
    $telas_sheer = $telas->where('Tipo', 'sheer')->values();

    /*try {
      // Ruta al archivo JSON en la carpeta public
      $path_blackout = public_path('BLACKOUT.json');
      $path_sheer = public_path('SHEER.json');

      // Lee el contenido del archivo
      $json_blackout = File::get($path_blackout);
      $json_sheer = File::get($path_sheer);

      // Decodifica el JSON a un array asociativo
      $data_blackout = json_decode($json_blackout, true);
      $data_sheer = json_decode($json_sheer, true);

      // Asigna el contenido a una variable
      $telas_blackout = $data_blackout;
      $telas_sheer = $data_sheer;
    } catch (\Throwable $th) {
      //throw $th;
    }*/
    $version = random_int(1, 10000);

    return view('main', compact('color_palette', 'cards_rieles_ripplefold', 'cards_rieles_tradicional', 'cards_1', 'cards_2', 'cards_3', 'steps', 'telas_blackout', 'telas_sheer', 'version', 'id'));

    //return view('welcome');
    // $var = new ComprobacionGastosController();
    // return $var->index(); //para devolver de momento la vista de comprobacion de gastos
    // return view('content.dashboard.dashboards-analytics');
  }
  public function set_password()
  {
    return view('content.authentications.auth-update-password');
  }
  public function cotizar()
  {
    $avance = Session::get('avance_temporal') ?? [];
    if (empty($avance)) {
      return response()->json(['success' => false, 'message' => 'No hay datos para cotizar'], 404);
    }
    $productos = Session::get('productos') ?? [];
    if (empty($productos)) {
      return response()->json(['success' => false, 'message' => 'No hay productos para cotizar'], 404);
    }

    $avance = json_decode($avance, true);
    $nombre_articulo = $avance['nombre_articulo'];
    $nombre_proyecto = $avance['nombre_proyecto'];
    $price_list = 1;
    $partner_id = 15;
    if (Auth::check()) {
      $partner_id = Auth::user()->odoo_partner_id;
      $price_list = Auth::user()->price_list_id;
    }
    $descripciones = self::getDescripcionOpciones();
    $cortinero = null;
    if ($descripciones['descripcion_cortinero'] !== '') {
      $cortinero = [
        "type" => "note",
        "description" => $descripciones['descripcion_cortinero']
      ];
    }
    $precio_unitario = self::getSubtotal($productos);

    $order_lines = [
      [
        "type" => "product",
        'product_id' => 7058, //Invtek cortina en Odoo
        'description' => $nombre_articulo . ' (' . $nombre_proyecto . ')',
        'quantity' => 1,
        'price_unit' => number_format($precio_unitario, $this->decimales, '.', ''),
      ],
      [
        "type" => "note",
        "description" => $descripciones['descripcion_cortina']
      ]
    ];
    if ($cortinero) {
      $order_lines[] = $cortinero; //agregamos la nota de cortinero
    }

    // productos
    // "productos": {
    //     "7061": {
    //         "precio_unitario": 2,
    //         "cantidad": 1,
    //         "precio_total": 2
    //     },
    //     "3555": {
    //         "precio_unitario": 369.41,
    //         "cantidad": 0.3333333333333333,
    //         "precio_total": 123.13666666666667
    //     }
    // }
    $order_lines_productos = [];
    collect($productos)->each(function ($value, $key) use (&$order_lines_productos) {
      $order_lines_productos[] = [
        'product_id' => $key,
        'quantity' => number_format($value['cantidad'], $this->decimales, '.', ''),
        'price_unit' => number_format($value['precio_unitario'], $this->decimales, '.', '')
      ];
    });
    $cotizacion_odoo = COCO::where('COCO_id', Session::get('cotizacion_id'))->first();
    $id_cotizacion_2 = null;
    //dd($cotizacion_odoo->COCO_odoo_cotizacion_productos, $cotizacion_odoo->COCO_odoo_cotizacion);
    // 1. Cotización de productos (cotizacion-2)
    if ($cotizacion_odoo && !empty($cotizacion_odoo->COCO_odoo_cotizacion_productos)) {
      // Actualizar cotización de productos
      $response_2 = Http::post('http://localhost:3036/update-quotation-products', [
        'partner_id' => $partner_id,
        'pricelist_id' => $price_list,
        'order_lines' => $order_lines_productos,
        'order_id' => (int) $cotizacion_odoo->COCO_odoo_cotizacion_productos
      ]);
      $id_cotizacion_2 = $cotizacion_odoo->COCO_odoo_cotizacion_productos;
    } else {
      // Crear cotización de productos
      $response_2 = Http::post('http://localhost:3036/create-quotation-products', [
        'partner_id' => $partner_id,
        'pricelist_id' => $price_list,
        'order_lines' => $order_lines_productos,
      ]);
      $id_cotizacion_2 = $response_2->json()['order_id'] ?? null;
      if ($cotizacion_odoo && $id_cotizacion_2) {
        $cotizacion_odoo->COCO_odoo_cotizacion_productos = $id_cotizacion_2;
        $cotizacion_odoo->save();
      }
    }

    // 2. Cotización principal (cotizacion-1)
    $nota_ref_cot2 = [
      "type" => "note",
      "description" => "REF COT. " . $id_cotizacion_2
    ];
    $order_lines_con_ref = $order_lines;
    $order_lines_con_ref[] = $nota_ref_cot2;

    $id_cotizacion_1 = null;
    if ($cotizacion_odoo && !empty($cotizacion_odoo->COCO_odoo_cotizacion)) {
      // Actualizar cotización principal
      $response = Http::post('http://localhost:3036/update-quotation-main', [
        'partner_id' => $partner_id,
        'pricelist_id' => $price_list,
        'order_lines' => $order_lines_con_ref,
        'order_id' => (int) $cotizacion_odoo->COCO_odoo_cotizacion
      ]);
      $id_cotizacion_1 = $cotizacion_odoo->COCO_odoo_cotizacion;
    } else {
      // Crear cotización principal
      $response = Http::post('http://localhost:3036/create-quotation-main', [
        'partner_id' => $partner_id,
        'pricelist_id' => $price_list,
        'order_lines' => $order_lines_con_ref,
      ]);
      $id_cotizacion_1 = $response->json()['order_id'] ?? null;
      if ($cotizacion_odoo && $id_cotizacion_1) {
        $cotizacion_odoo->COCO_odoo_cotizacion = $id_cotizacion_1;
        $cotizacion_odoo->save();
      }
    }

    if (is_numeric($id_cotizacion_1) && is_numeric($id_cotizacion_2)) {
      //actualizar el estatus de la cotizacion
      $cotizacion_odoo->COCO_estatus = 'cotizada';
      $cotizacion_odoo->save();
    }
    return response()->json([
      'success' => true,
      'cotizacion_1' => $id_cotizacion_1,
      'cotizacion_2' => $id_cotizacion_2,
      'response_1' => $response->json(),
      'response_2' => $response_2->json(),
      'cotizacion_status' => $cotizacion_odoo->COCO_estatus
    ]);
  }
  public function getDescripcionOpciones()
  {
    $opciones = Session::get('avance_temporal');
    $opciones = json_decode($opciones, true);
    //dd($opciones);
    //quitar cuando el key contenga sel_tela
    //si esta vacio $opciones
    if (empty($opciones)) {
      return response()->json([
        'descripcion_cortina' => '',
        'descripcion_cortinero' => ''
      ]);
    }
    //quitar cuando el key contenga sel_tela
    $opciones = array_filter($opciones, function ($key) {
      return !str_contains($key, 'sel_tela');
    }, ARRAY_FILTER_USE_KEY);
    //quitar las siguientes opciones
    /* "lado_a" => null
    "lado_b" => null
    "alto" => "3"
    "ancho" => "1"
    "radio" => null */
    $opciones = array_filter($opciones, function ($key) {
      return ($key !== 'lado_a') && ($key !== 'lado_b') && ($key !== 'alto') && ($key !== 'ancho') && ($key !== 'radio');
    }, ARRAY_FILTER_USE_KEY);

    //obtener los ids de las opciones solo si son numeros
    $ids = array_filter($opciones, function ($value) {
      return is_numeric($value);
    });
    $ids = array_values($ids);
    //dd($ids);
    //dd($opciones);
    $tipo_material = $opciones['tipo_material'];
    $estilo_confeccion = $opciones['radio_step_2'];

    //dd($tipo_material, $estilo_confeccion);
    //Ejecutar un query para obtener la descripcion de menos de 250 caracteres DB::select
    $query = "SELECT
        p.PAS_Nombre AS SELECTOR,
        o.OPC_ValorOpcion AS OPCION_SELECCIONADA
      FROM
        RPT_OpcionesCotizador o
        INNER JOIN
        RPT_PasosCotizador p ON o.OPC_PasoId = p.PAS_PasoId
        
      WHERE 
          o.OPC_OpcionId IN (" . implode(',', $ids) . ")

      UNION ALL

      SELECT
        'TIPO DE MATERIAL' AS SELECTOR,
        '$tipo_material' AS OPCION_SELECCIONADA
     
      ";
    //dd($query);
    $descripcion_db = DB::select($query);
    //dd($descripcion_db);
    //convertir $descripcion a array Instalación $descripcion['Instalación Riel']
    $descripcion = [];
    foreach ($descripcion_db as $key => $value) {
      $descripcion[$value->SELECTOR] = $value->OPCION_SELECCIONADA;
    }
    //dd($descripcion);
    //convertir $descripcion a string

    //Query result ejemplo:
    /*
    //datos para Cortinas
    Tipo de producto: Cortina + Cortinero
    Confección: Tradicional
    Estilo de confeccion: Plitz Francés
      Instalación Riel: Riel recto
      Dirección de apertura: Izquierda
      Hojas: 1 Hoja //estas son la cantidad de hojas de tela de la cortina
      Tipo de tela: Blackout
      TELA: OCEAN BLUE //nombre de la tela seleccionada
      
      //datos para cortineros
      Sistema de apertura: Manual
      Superficie de instalación: Instalación a muro
      Sistema de riel: RM
      Material de riel: Aluminio
      Accesorio de apertura: Bastón (RM)
      Material accesorio: Acrilico
      Modelo accesorio: Calibre 1/2" (12.7mm) capacidades diferentes
      Largo accesorio: 48"  (1.22 m)

      //no incluir en descripcion:
      Calidad: Residencial
      Área de instalación: Interior 
      */

    // Llaves requeridas para cada descripción
    $requeridas_cortina = [
      'Instalación Riel',
      'Dirección de apertura',
      'Hojas',
      'Tipo de material',
      //'TELA'
    ];
    $requeridas_cortinero = [
      'Sistema de apertura',
      'Superficie de instalación',
      'Modelo del Riel',
      'Material de riel',
      'Accesorio de apertura',
      'Material accesorio',
      'Modelo accesorio',
      'Largo accesorio'
    ];
    //dd($descripcion);
    //links cortina
    $links_opciones_cortina = [
      ['tipo', 'tipo_producto'],
      ['tipo_confeccion', 'tipo_confeccion'],
      ['radio_step_2', 'tipo_confeccion'],
      ['tipo_riel', 'medidas'],
      ['numero_hojas', 'medidas'],
      ['tipo_material', 'telas'],
      //['tela', 'telas']
    ];
    //links cortinero
    $links_opciones_cortinero = [
      ['sistema_apertura', 'sistema_apertura'],
      ['superficie_instalacion_riel', 'sistema_apertura'],
      ['sistema_riel_selector', 'sistema_apertura'],
      ['material_riel_selector', 'sistema_apertura']
    ];
    $links_opciones_resumen = [];
    // Verifica existencia de llaves para cortina
    $descripcion_cortina = '';
    foreach ($requeridas_cortina as $key) {
      //dd($descripcion[$key]);
      if (!isset($descripcion[$key])) {

        $descripcion_cortina = null;
        break;
      }
    }
    //dd($descripcion, $descripcion_cortina);
    if ($descripcion_cortina === null) {
      // Si faltó alguna llave, ya está vacía
    } else {
      $links_opciones_resumen = $links_opciones_cortina;
      $descripcion_cortina = "Cortina con confeccion "
        . $descripcion['Confección'] . " (" . $estilo_confeccion . "), con " . $descripcion['Instalación Riel'] . ", direccion de apertura " .
        $descripcion['Dirección de apertura'] . ", con " . $descripcion['Hojas'] . ", y material " . $descripcion['Tipo de material'];
      //" (" . $descripcion['Tipo de tela'] . ").";
    }
    //dd($descripcion_cortina);
    // Verifica existencia de llaves para cortinero solo si es Cortina + Cortinero
    $descripcion_cortinero = '';
    //dd($descripcion);
    if ($descripcion['Subproducto'] == 'Cortina + Cortinero') {
      foreach ($requeridas_cortinero as $key) {
        if (!isset($descripcion[$key])) {
          dd($descripcion[$key]);
          $descripcion_cortinero = null;
          break;
        }
      }
      if ($descripcion_cortinero === null) {
        // Si faltó alguna llave, ya está vacía
      } else {
        $links_opciones_resumen = array_merge($links_opciones_resumen, $links_opciones_cortinero);
        $descripcion_cortinero = "El cortinero tendra sistema de apertura " . $descripcion['Sistema de apertura'] .
          ", " . $descripcion['Superficie de instalación'] . ", modelo de riel " . $descripcion['Modelo del Riel'] . " de material " . $descripcion['Material de riel'] .
          ", ademas de " . $descripcion['Accesorio de apertura'] . " de material " . $descripcion['Material accesorio'] . " (" . $descripcion['Modelo accesorio'] . " " . $descripcion['Largo accesorio'] . ")";
      }
    }
    //dd($descripcion_cortinero);
    return array(
      'descripcion_cortina' => ($descripcion_cortina),
      'descripcion_cortinero' => ($descripcion_cortinero),
      'links_opciones_resumen' => ($links_opciones_resumen)
    );
  }
}
