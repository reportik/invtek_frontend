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
use Illuminate\Support\Facades\Log;

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
        && $key !== 'inputLadoA'
        && $key !== 'inputLadoB'
        && $key !== 'inputAlto'
        && $key !== 'inputAncho'
        && $key !== 'inputRadio'
        && $key !== 'nombre_proyecto'
        && $key !== 'nombre_articulo'
        && $key !== 'siguiente-vista'
        && $key !== 'material_descripcion';
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
      //Log::info('getSelectorSiguiente(): selectorEditado: '.$selectorEditado);
      //dd($selectorEditado);
      $pasoEditado = $pasos->firstWhere('PAS_Html_name', $selectorEditado);
      //Log::info('getSelectorSiguiente(): pasoEditado: ', $pasoEditado);
      
      $avance = self::limpiarAvancePosterior($avance, $pasoEditado, $pasos);
      
      //verificar si el paso editado esta en el avance, si no, agregar el valor de la opcion editada
      if (!isset($avance[$pasoEditado->PAS_Html_name])) {
        $avance[$pasoEditado->PAS_Html_name] = $pasoEditado->PAS_OpcionId;
      }
      //Log::info('getSelectorSiguiente(): avance: ', $avance);
      // Actualizar la sesión
      //dd($avance);
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
      //where('OPC_PasoId', $paso->PAS_PasoId)
      $query = OpcionCotizador::where('OPC_PasoId', $paso->PAS_PasoId)->where('OPC_Activo', 1)
        ->where('OPC_Eliminado', 0);
      // Agregar TODAS las dependencias previas
      //dd($paso->PAS_Orden); //3
      //respondidos
      //dd($respondidos);
      for ($j = 1; $j < $paso->PAS_Orden; $j++) {
        $campo = 'OPC_S' . $j;
        if (isset($respondidos[$j])) {
          if (!is_numeric($respondidos[$j])) {
            $valor = 'T';
          } else {
            $valor = str_pad($respondidos[$j], 5, '0', STR_PAD_LEFT);
          }
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
      return ['mensaje' => 'BACKEND: No hay ningún selector siguiente' 
      , 'query' => $query->toSql(),
      'bindings' => $query->getBindings()];
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
      'query' => $query->toSql(),
      'bindings' => $query->getBindings(),
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
   * Devuelve el siguiente selector activo con las opciones válidas según las selecciones previas del usuario.
   * Esta versión NO limpia el avance de la sesión y usa un selector tope para limitar la búsqueda.
   * @param array|null $avance Opciones seleccionadas (si es null, toma de la sesión)
   * @param string|int|null $selectorTope ID (PAS_PasoId) o nombre (PAS_Html_name) del selector tope
   * @return array|null
   */
  public static function getSelectorSiguienteConTope($avance = null, $selectorTope = null)
  {
    // 1. Obtener avance actual
    if ($avance === null) {
      $avance = Session::get('avance_temporal', []);
      if (is_string($avance)) $avance = json_decode($avance, true);
    }

    // 2. Obtener todos los pasos activos y ordenados
    $pasos = PasoCotizador::where('PAS_Activo', 1)
      ->where('PAS_Eliminado', 0)
      ->orderBy('PAS_Orden', 'asc')
      ->get();

    // 3. Si se proporciona selector tope, encontrar su orden
    $ordenTope = null;
    $pasoTope = null;
    if (isset($selectorTope) && $selectorTope) {
      // Determinar si es un ID numérico o un nombre
      if (is_numeric($selectorTope)) {
        // Buscar por ID (PAS_PasoId)
        $pasoTope = $pasos->firstWhere('PAS_PasoId', $selectorTope);
      } else {
        // Buscar por nombre HTML (PAS_Html_name)
        $pasoTope = $pasos->firstWhere('PAS_Html_name', $selectorTope);
      }
      
      if ($pasoTope) {
        $ordenTope = $pasoTope->PAS_Orden;
      }
    }

    // 4. Filtrar solo campos relevantes hasta el selector tope
    $opciones = array_filter($avance, function ($key) {
      return !str_contains($key, 'sel_tela')
        && $key !== 'inputLadoA'
        && $key !== 'inputLadoB'
        && $key !== 'inputAlto'
        && $key !== 'inputAncho'
        && $key !== 'inputRadio'
        && $key !== 'nombre_proyecto'
        && $key !== 'nombre_articulo'
        && $key !== 'siguiente-vista'
        && $key !== 'material_descripcion';
    }, ARRAY_FILTER_USE_KEY);

    // 5. Filtrar opciones solo hasta el selector tope
    if ($ordenTope !== null) {
      $opcionesFiltradas = [];
      foreach ($opciones as $key => $value) {
        $pasoActual = $pasos->firstWhere('PAS_Html_name', $key);
        if ($pasoActual && $pasoActual->PAS_Orden <= $ordenTope) {
          $opcionesFiltradas[$key] = $value;
        }
      }
      $opciones = $opcionesFiltradas;
    }

    $ids = array_filter($opciones, function ($value) {
      return is_numeric($value);
    });
    $ids = array_values($ids);

    // 6. Identificar pasos respondidos hasta el selector tope
    $respondidos = [];
    foreach ($pasos as $paso) {
      // Si hay orden tope, solo considerar pasos hasta ese orden
      if ($ordenTope !== null && $paso->PAS_Orden > $ordenTope) {
        continue;
      }
      
      if (isset($avance[$paso->PAS_Html_name]) && is_numeric($avance[$paso->PAS_Html_name])) {
        $respondidos[(int)$paso->PAS_Orden] = $avance[$paso->PAS_Html_name];
      }
    }

    if (empty($respondidos)) {
      $ultimoOrden = 0;
    } else {
      $ultimoOrden = max(array_keys($respondidos));
    }

    // 7. Buscar el siguiente selector después del último orden (o del tope)
    $ordenInicio = $ordenTope !== null ? $ordenTope : $ultimoOrden;
    $encontrado = false;
    $siguienteSelector = null;
    $opcionesValidas = collect();
    $pantallaSiguiente = null;
    $query = null;

    foreach ($pasos as $paso) {
      if ($paso->PAS_Orden <= $ordenInicio) continue;

      $query = OpcionCotizador::where('OPC_PasoId', $paso->PAS_PasoId)
        ->where('OPC_Activo', 1)
        ->where('OPC_Eliminado', 0);

      // Agregar TODAS las dependencias previas hasta el selector tope (o todas si no hay tope)
      $ordenMaximo = $ordenTope !== null ? min($paso->PAS_Orden - 1, $ordenTope) : ($paso->PAS_Orden - 1);
      
      for ($j = 1; $j <= $ordenMaximo; $j++) {
        $campo = 'OPC_S' . $j;
        if (isset($respondidos[$j])) {
          if (!is_numeric($respondidos[$j])) {
            $valor = 'T';
          } else {
            $valor = str_pad($respondidos[$j], 5, '0', STR_PAD_LEFT);
          }
          $query->where($campo, $valor);
        }
      }

      $opciones = $query->get();

      if ($opciones->count() > 0) {
        $encontrado = true;
        $siguienteSelector = $paso;
        $opcionesValidas = $opciones;
        $pantallaSiguiente = $paso->PAS_Pantalla_Ubicacion;
        break;
      }
    }

    if (!$encontrado) {
      return [
        'mensaje' => 'BACKEND: No hay ningún selector siguiente',
        'query' => $query ? $query->toSql() : null,
        'bindings' => $query ? $query->getBindings() : null
      ];
    }

    // 8. Estructurar el resultado
    return [
      'query' => $query->toSql(),
      'bindings' => $query->getBindings(),
      'selector'    => $siguienteSelector->PAS_Nombre,
      'selector_nombre'    => $siguienteSelector->PAS_Html_name,
      'selector_container' => $siguienteSelector->PAS_Container,
      'selector_orden'  => $siguienteSelector->PAS_Orden,
      'selector_tipo'  => $siguienteSelector->PAS_Tipo_Selector,
      'selector_id'  => $siguienteSelector->PAS_PasoId,
      'pantalla_anterior' => null, // No aplica en esta versión sin edición
      'pantalla_siguiente' => self::getPantallaNombre($pantallaSiguiente),
      'pantalla_ubicacion' => $pantallaSiguiente,
      // Información del selector tope
      'selector_tope' => $selectorTope,
      'selector_tope_nombre' => $pasoTope ? $pasoTope->PAS_Html_name : null,
      'selector_tope_id' => $pasoTope ? $pasoTope->PAS_PasoId : null,
      'orden_tope' => $ordenTope,
      // Información de debugging
      'debug' => [
        'ultimo_orden' => $ultimoOrden,
        'orden_inicio' => $ordenInicio,
        'respondidos' => $respondidos,
        'avance_filtrado' => $opciones,
      ],
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
    $pantallaEditada = $pasoEditado->PAS_Pantalla_Ubicacion;
    //dd($pasoEditado, $pasos);
    foreach ($pasos as $paso) {
      if ($paso->PAS_Orden > $ordenEditado){ // && $paso->PAS_Pantalla_Ubicacion > $pantallaEditada) {

        unset($avance[$paso->PAS_Html_name]);
        if($paso->PAS_Html_name == 'canvas'){
          unset($avance['inputLadoA']);
          unset($avance['inputLadoB']);
          unset($avance['inputAlto']);
          unset($avance['inputAncho']);
          unset($avance['inputRadio']);
        }
        if($paso->PAS_Html_name == 'tipo_material'){
          unset($avance['material_descripcion']);
        }
      }
    }
    return $avance;
  }
  public static function getPantallaNombre($id)
  {
    if ($id == null) return null;
    //array con los nombres de las vistas
    $vistas = [
      1 => 'inicio',
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
  public static function getPantallaId($nombre)
  {
    if ($nombre == null) return null;
    //array con los nombres de las vistas
    $vistas = [
      'inicio' => 1,
      'tipo_producto' => 2,
      'tipo-confeccion' => 3,
      'tipo_confeccion' => 3,
      'medidas' => 4,
      'configuracion-medidas' => 4,
      'telas' => 5,
      'sistema_apertura' => 6,
      'sistema-apertura' => 6,
      'bastones' => 7,
      'resumen' => 8,
    ];
    return $vistas[$nombre];
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
    $valor = $request->input('valor');
    
    // if($selectorNombre != null){
    //   $avanceFusionado = array_merge($avance, [$selectorNombre => $valor]);
    //   Session::put('avance_temporal', json_encode($avanceFusionado));
    //   $avance = $avanceFusionado;
    // } else {      
    //   $avance = $avanceActual;
    // }
    // Obtener todos los pasos activos y ordenados
    $pasos = PasoCotizador::where('PAS_Activo', 1)
      ->where('PAS_Eliminado', 0)
      ->orderBy('PAS_Orden', 'asc')
      ->get();
    $pasoActual = $pasos->firstWhere('PAS_Html_name', $selectorNombre);
    if (!$pasoActual) {
      return response()->json(['mensaje' => 'Selector no encontrado'], 404);
    }
    //
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
        if (!is_numeric($respondidos[$j])) {
          $valor = 'T';
        } else {
          $valor = str_pad($respondidos[$j], 5, '0', STR_PAD_LEFT);
        }
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
    $result = self::getSelectorSiguiente($avance, 'tipo_riel');
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
    if($request->nombre_selector != null){
      $avanceFusionado = array_merge($avanceActual, [$request->nombre_selector => $request->valor]);
      Session::put('avance_temporal', json_encode($avanceFusionado));
      $avance = $avanceFusionado;
    } else {      
      $avance = $avanceActual;
    }
    
    //dd($avanceFusionado, $request->nombre_selector, $request->valor);
    //dd($avanceFusionado);
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
    //dd($productos);
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
    //Auth::check()
    // ? json_decode(Auth::user()->avance ?? [], true)
    // : 
    $avance = json_decode(Session::get('avance_temporal', []), true);
    // Si no hay avance, redirigir al inicio
    if (empty($avance)) {
      return redirect()->route('inicio');
    }
    // filtar las opciones que tengan valor de numero
    $opciones_numero = array_filter($avance, function ($value) {
      return is_numeric($value);
    });
    $opciones_numero = array_filter($opciones_numero, function ($key) {
      // 'inputAlto': 'inputAlto',
      // 'inputAncho': 'inputAncho',
      // 'inputLadoA': 'inputLadoA',
      // 'inputLadoB': 'inputLadoB',
      // 'inputRadio': 'inputRadio'
      return ($key !== 'inputLadoA') && ($key !== 'inputLadoB') && ($key !== 'inputAlto') && ($key !== 'inputAncho') && ($key !== 'inputRadio');
    }, ARRAY_FILTER_USE_KEY);

    $opciones = self::getOpcionesFromAvance($avance, $opciones_numero); // filtar las opciones que tengan valor de numero
    //dd($opciones);
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
    
    // Obtener ruta de pantallas visitadas (omitiendo la primera - "inicio")
    $ruta_pantallas = isset($avance['ruta_pantallas']) && is_array($avance['ruta_pantallas']) 
      ? array_slice($avance['ruta_pantallas'], 1) // Omitir la primera pantalla (inicio)
      : [];
    
    // Mapeo de nombres de vista a nombres de ruta Laravel
    // Esto es necesario porque en ruta_pantallas se guardan los nombres con guiones
    // pero las rutas Laravel tienen nombres diferentes
    $mapeo_rutas = [
      'tipo_producto' => 'tipo_producto',
      'tipo-producto' => 'tipo_producto',
      'tipo_confeccion' => 'tipo_confeccion',
      'tipo-confeccion' => 'tipo_confeccion',
      'configuracion-medidas' => 'medidas', // ← Aquí está el mapeo importante
      'configuracion_medidas' => 'medidas',
      'medidas' => 'medidas',
      'telas' => 'telas',
      'sistema_apertura' => 'sistema_apertura',
      'sistema-apertura' => 'sistema_apertura',
      'bastones' => 'bastones',
    ];
    
    // Nombres legibles para mostrar en el resumen
    $nombres_vistas = [
      'tipo_producto' => 'Tipo de Producto',
      'tipo-producto' => 'Tipo de Producto',
      'tipo_confeccion' => 'Tipo de Confección',
      'tipo-confeccion' => 'Tipo de Confección',
      'configuracion-medidas' => 'Configuración y Medidas',
      'configuracion_medidas' => 'Configuración y Medidas',
      'medidas' => 'Configuración y Medidas',
      'telas' => 'Tela',
      'sistema_apertura' => 'Sistema de Apertura',
      'sistema-apertura' => 'Sistema de Apertura',
      'bastones' => 'Accesorio de Apertura',
    ];
    
    // Crear array de vistas con nombres legibles para el resumen
    $vistas_resumen = [];
    foreach ($ruta_pantallas as $vista) {
      // Convertir el nombre de la vista al nombre de la ruta Laravel
      $ruta_laravel = isset($mapeo_rutas[$vista]) ? $mapeo_rutas[$vista] : $vista;
      
      if (isset($nombres_vistas[$vista])) {
        $vistas_resumen[] = [
          'ruta' => $ruta_laravel, // Usar el nombre de ruta Laravel correcto
          'nombre' => $nombres_vistas[$vista]
        ];
      }
    }
    
    //dd($links_opciones_resumen);
    // Devolver la vista con el avance
    return view('resumen', compact('odoo_cotizacion_numero', 'avance', 'subtotal', 'iva', 'total', 'opciones', 'descripcion_cortina', 'descripcion_cortinero', 'links_opciones_resumen', 'cotizacion_status', 'vistas_resumen'));
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
        'imagen' => $opcion ? $opcion->OPC_Imagen ?? 'default.png' : 'default.png'
      ];
    })->toArray();
    return $opciones;
  }
  /**
   * Calcula las cantidades de productos necesarios para la cotización basándose en las medidas capturadas
   * 
   * @param array $avance - Array con todos los valores seleccionados por el usuario durante el flujo
   * @param array $opciones_numero - Array filtrado con solo los IDs de opciones numéricas
   * @return array - Array asociativo [producto_id => ['precio_unitario' => X, 'cantidad' => Y]]
   */
  public function getProductos($avance, $opciones_numero)
  {
    // ==========================================
    // PASO 1: INICIALIZAR MEDIDAS
    // ==========================================
    $medida_ancho = 0;
    $medida_alto = 0;
    
    // ==========================================
    // PASO 2: VERIFICAR QUÉ MEDIDAS CAPTURÓ EL USUARIO
    // ==========================================
    // Dependiendo del tipo de riel seleccionado, el canvas muestra diferentes inputs
    $tieneAncho = !empty($avance['inputAncho']) && is_numeric($avance['inputAncho']);
    $tieneAlto = !empty($avance['inputAlto']) && is_numeric($avance['inputAlto']);
    $tieneLadoA = !empty($avance['inputLadoA']) && is_numeric($avance['inputLadoA']);
    $tieneLadoB = !empty($avance['inputLadoB']) && is_numeric($avance['inputLadoB']);
    $tieneRadio = !empty($avance['inputRadio']) && is_numeric($avance['inputRadio']);
    
    // ==========================================
    // PASO 3: CALCULAR MEDIDAS SEGÚN TIPO DE CONFIGURACIÓN
    // ==========================================
    
    // Caso 1: Riel curvo con lados A y B (Riel en escuadra)
    if ($tieneLadoA && $tieneLadoB && $tieneAlto && !$tieneAncho && !$tieneRadio) {
      $medida_ancho = $avance['inputLadoA'] + $avance['inputLadoB'];
      $medida_alto = $avance['inputAlto'];
    } 
    // Caso 2: Riel curvo con radio (Riel circular/semicircular)
    else if ($tieneAncho && $tieneAlto && $tieneRadio && !$tieneLadoA && !$tieneLadoB) {
      $medida_ancho = $avance['inputAncho'];
      $medida_alto = $avance['inputAlto'];
      $radio = $avance['inputRadio'];
    } 
    // Caso 3: Riel recto (configuración más común)
    else if ($tieneAncho && $tieneAlto && !$tieneLadoA && !$tieneLadoB && !$tieneRadio) {
      $medida_ancho = $avance['inputAncho'];
      $medida_alto = $avance['inputAlto'];
    } 
    // Caso 4: Configuración mixta o incompleta (fallback)
    else {
      $medida_ancho = $tieneAncho ? $avance['inputAncho'] : ($tieneLadoA && $tieneLadoB ? $avance['inputLadoA'] + $avance['inputLadoB'] : 0);
      $medida_alto = $tieneAlto ? $avance['inputAlto'] : 0;
    }

    // ==========================================
    // PASO 4: OBTENER PRODUCTOS ASOCIADOS A LAS OPCIONES SELECCIONADAS
    // ==========================================
    // Por defecto, usamos el ancho como medida base
    $medida = $medida_ancho;
    
    // Buscar todos los productos asociados a las opciones que el usuario seleccionó
    // (cada opción puede tener múltiples productos en la tabla RPT_ProductosCantidad)
    $productos = PCNT::whereIn('PCNT_OPC_OpcionId', array_values($opciones_numero))->get();

    // ==========================================
    // PASO 5: OBTENER PRECIOS DESDE ODOO
    // ==========================================
    // Consultar al API de Odoo los precios actualizados de todos los productos
    $precios = self::getOdooPrices($productos->pluck('PCNT_PROD_id')->toArray());
    
    $items = []; // Array final que contendrá todos los productos con sus cantidades
    
    // ==========================================
    // PASO 6: CALCULAR CANTIDAD DE CADA PRODUCTO
    // ==========================================
    $productos->each(function ($producto) use ($precios, $medida, $medida_alto, &$items) {
      // Solo procesar si existe el precio en Odoo
      if (isset($precios[$producto->PCNT_PROD_id])) {
        
        // Calcular cantidad según el campo PCNT_base_medida
        // Este campo indica si el producto se calcula por ANCHO, ALTO, HOJA o FORMULA
        switch ($producto->PCNT_base_medida) {
          case 'ANCHO':
            
            $cantidad = number_format($medida * $producto->PCNT_base_cantidad, $this->decimales, '.', '');
            break;
            
          case 'ALTO':
            // Fórmula: (alto_en_cm / base_cantidad)
            $cantidad = number_format($medida_alto * $producto->PCNT_base_cantidad, $this->decimales, '.', '');
            break;
          
          case 'HOJA':
            // en la session tenemos la variable numero_hojas, el valor es el ID de la opcion de la hoja
            $numero_hojas = Session::get('numero_hojas');
            // buscar la opcion de la hoja en la tabla de opciones
            $opcion_hoja = OpcionCotizador::where('OPC_OpcionId', $numero_hojas)->first();
            // obtener el valor de la opcion de la hoja
            $valor_hoja = $opcion_hoja->OPC_ValorOpcion;
            // multiplicar el valor de la opcion de la hoja por la base cantidad del producto
            $cantidad = intval($valor_hoja) * intval($producto->PCNT_base_cantidad);
          
            break;
          
          case 'FORMULA':
            // Fórmula: Ejecutar el query SQL guardado en PCNT_formula
            if (!empty($producto->PCNT_formula)) {
              try {
                // Obtener el número de hojas si existe
                $numero_hojas = 1;
                if (Session::has('numero_hojas')) {
                  $opcion_hoja = OpcionCotizador::where('OPC_OpcionId', Session::get('numero_hojas'))->first();
                  $numero_hojas = $opcion_hoja ? intval($opcion_hoja->OPC_ValorOpcion) : 1;
                }
                
                // Preparar los bindings con las medidas disponibles
                $bindings = [
                  'ancho' => $medida,
                  'alto' => $medida_alto,
                  'numeroHojas' => $numero_hojas,
                  'anchoTela' => 1, // Valor por defecto
                ];
                
                // Convertir el query de SQL Server (@variable) a Laravel (:variable)
                $sql_server_query = $producto->PCNT_formula;
                $laravel_sql_query = preg_replace('/@(\w+)/', ':$1', $sql_server_query);
                
                // Ejecutar el query
                $resultado = self::executeGenericSql($laravel_sql_query, $bindings);
                $cantidad = is_numeric($resultado) ? number_format($resultado, $this->decimales, '.', '') : 0;
              } catch (\Exception $e) {
                // En caso de error, usar 0 y registrar el error
                Log::error('Error ejecutando fórmula SQL: ' . $e->getMessage(), [
                  'formula' => $producto->PCNT_formula,
                  'producto_id' => $producto->PCNT_PROD_id
                ]);
                $cantidad = 0;
              }
            } else {
              $cantidad = 0;
            }
            break;
            
          default:
            // Por defecto, usar ancho (para HOJA o valores no especificados)
            $cantidad = number_format($medida * $producto->PCNT_base_cantidad, $this->decimales, '.', '');
            break;
        }
        
        // Agregar el producto al array de items
        $items[$producto->PCNT_PROD_id] = [
          'precio_unitario' => $precios[$producto->PCNT_PROD_id],
          'cantidad' => $cantidad,
        ];
      }
    });

    // ==========================================
    // PASO 7: CÁLCULO ESPECIAL PARA TELAS
    // ==========================================
    // Las telas tienen un cálculo diferente porque se venden por metros
    // y dependen del ancho de la tela (que viene en los atributos del producto)
    if (!isset($avance['producto_categoria']) || !isset($avance['tipo_material'])) {
      //return inicio 
      return redirect()->route('inicio');
    }
    $id_tela = $avance['producto_categoria']; // ID del producto de tela seleccionado
    $id_opcion_tela = $avance['tipo_material']; // ID de la opción "Tipo de Material"
    
    // Buscar la tela específica seleccionada por el usuario
    $tela = PCNT::where('PCNT_PROD_id', $id_tela)
      ->where('PCNT_OPC_OpcionId', $id_opcion_tela)
      ->first();
      
    if ($tela) {
      // A = Ancho de la cortina (en metros)
      // B = Alto de la cortina (en metros)
      // C = Ancho de la tela (obtenido de los atributos del producto en Odoo)
      $A = $medida;
      $B = $medida_alto;
      $C = ($tela->PCNT_atributos) ? self::getBaseCantidadTela($tela->PCNT_atributos, $medida) : 1;
      
      // Fórmula para calcular metros de tela necesarios:
      // 1. ceil(A * 2) = Ancho total necesario (x2 para pliegues tradicionales)
      // 2. / C = Dividir entre el ancho de la tela para saber cuántos lienzos se necesitan
      // 3. * (B + 0.45) = Multiplicar por el alto + 45cm para dobladillo y jareta
      // 4. ceil() = Redondear hacia arriba porque no se pueden comprar fracciones de metro
      $cantidad_tela = ceil((ceil(($A) * 2) / ($C)) * (($B) + 0.45));
      
      $items[$id_tela] = [
        'precio_unitario' => $precios[$id_tela],
        'cantidad' => $cantidad_tela,
      ];
    }

    return $items;
  }
  public function getBaseCantidadTela($atributos, $ancho)
  {
  //   [
  //     {
  //         "attribute_id": 22,
  //         "attribute_name": "ANCHO",
  //         "values": [
  //             {
  //                 "id": 110,
  //                 "name": "2.8 m"
  //             }
  //         ]
  //     }
  // ]
    $atributos = json_decode($atributos, true);
    //buscar el attribute_name es ANCHO, entonces return la tela más ancha
    foreach ($atributos as $atributo) {
      if ($atributo['attribute_name'] == 'ANCHO') {
        $ancho_maximo = 1; // Valor por defecto
        
        // Recorrer todos los valores y encontrar el ancho máximo
        foreach ($atributo['values'] as $valor) {
          $valor_ancho = floatval(str_replace(' m', '', $valor['name']));
          if ($valor_ancho > $ancho_maximo) {
            $ancho_maximo = $valor_ancho;
          }
        }
        
        return $ancho_maximo;
      }
    }
    return 1;
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
      // Validar que existan las claves necesarias
      if (!isset($producto['precio_unitario']) || !isset($producto['cantidad'])) {
        continue;
      }
      
      $precio_unitario = number_format($producto['precio_unitario'], $this->decimales, '.', '');
      $cantidad = number_format($producto['cantidad'], $this->decimales, '.', '');
      $subtotal += $precio_unitario * $cantidad;
    }
    return $subtotal;
  }
  public function bastones()
  {

    if (!array_key_exists('area_instalacion', Session::has('avance_temporal') ?   json_decode(Session::get('avance_temporal'), true) : [])) {
      return redirect()->route('inicio');
    }
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
    if (!array_key_exists('area_instalacion', Session::has('avance_temporal') ?   json_decode(Session::get('avance_temporal'), true) : [])) {
      return redirect()->route('inicio');
    }
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
    if (!array_key_exists('area_instalacion', Session::has('avance_temporal') ?   json_decode(Session::get('avance_temporal'), true) : [])) {
      return redirect()->route('inicio');
    }
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

    // Asegurarse de eliminar la versión con guión si existe
    if (isset($nuevoAvance['ruta-pantallas'])) {
      unset($nuevoAvance['ruta-pantallas']);
    }

    // Construir la pila a partir del avance previo
    if (is_string($avanceActual)) {
      $avanceActual = json_decode($avanceActual, true);
    }
    $stack = [];
    if (is_array($avanceActual)) {
      if (isset($avanceActual['ruta_pantallas']) && is_array($avanceActual['ruta_pantallas'])) {
        $stack = $avanceActual['ruta_pantallas'];
      } elseif (isset($avanceActual['ruta-pantallas']) && is_array($avanceActual['ruta-pantallas'])) {
        $stack = $avanceActual['ruta-pantallas'];
      }
    }

    $vistaActual = $request->input('actual-vista');

    // Si la vista actual ya está en la pila, significa que el usuario está retrocediendo
    if ($vistaActual !== null && ($key = array_search($vistaActual, $stack)) !== false) {
      // Eliminar todas las vistas posteriores a la actual
      $stack = array_slice($stack, 0, $key + 1);
    } else if ($vistaActual !== null) {
      // Agregar la vista actual a la pila si no es la misma que la última
      if (empty($stack) || end($stack) !== $vistaActual) {
        $stack[] = $vistaActual;
      }
    }

    // La vista anterior es el penúltimo elemento de la pila o 'inicio' si no hay
    $nuevoAvance['ruta_pantallas'] = $stack;
    $nuevoAvance['anterior-vista'] = count($stack) > 1 ?
      $stack[count($stack) - 2] :
      'inicio';

    //si es json convertir a array
    if (is_string($avanceActual)) {
      $avanceActual = json_decode($avanceActual, true);
    }
    if (is_string($nuevoAvance)) {
      $nuevoAvance = json_decode($nuevoAvance, true);
    }
    // Fusionar avance anterior con el nuevo
    $avanceFusionado = array_merge($avanceActual, $nuevoAvance);
    // Forzar que la pila final sea la calculada
    $avanceFusionado['ruta_pantallas'] = $stack;
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
    if (!array_key_exists('area_instalacion', Session::has('avance_temporal') ?   json_decode(Session::get('avance_temporal'), true) : [])) {
      return redirect()->route('inicio');
    }
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
    if (!array_key_exists('area_instalacion', Session::has('avance_temporal') ?   json_decode(Session::get('avance_temporal'), true) : [])) {
      return redirect()->route('inicio');
    }
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

  public static function getSelectoresPosterioresStatic($nombrePantalla)
  {
    $pantalla_id = self::getPantallaId($nombrePantalla);
    // Obtener todos los pasos activos y ordenados
    $pasos = PasoCotizador::where('PAS_Activo', 1)
      ->where('PAS_Eliminado', 0)
      ->where('PAS_Pantalla_Ubicacion', '>', $pantalla_id)
      ->orderBy('PAS_Orden', 'asc')
      ->get();
    // Filtrar los pasos que tienen pantalla ubicacion mayor al de la pantalla de destino
    $selectoresPosteriores = $pasos->filter(function ($paso) use ($pantalla_id) {
      return $paso->PAS_Pantalla_Ubicacion > $pantalla_id;
    });
    // Extraer solo los nombres HTML de los selectores
    $nombresSelectores = $selectoresPosteriores->pluck('PAS_Html_name');

    return $nombresSelectores;
  }

  public function getSelectoresPosteriores($nombrePantalla)
  {
    try {
      $selectores = self::getSelectoresPosterioresStatic($nombrePantalla);
      return response()->json([
        'success' => true,
        'selectores' => $selectores->toArray()
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al obtener selectores posteriores: ' . $e->getMessage()
      ], 500);
    }
  }
  public function tipo_confeccion()
  {
    if (!array_key_exists('area_instalacion', Session::has('avance_temporal') ?   json_decode(Session::get('avance_temporal'), true) : [])) {
      return redirect()->route('inicio');
    }
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
  /**
   * Genera descripciones de la cotización basándose en las opciones seleccionadas
   * Construye una descripción legible de la cortina y cortinero (si aplica)
   * incluyendo medidas y materiales seleccionados
   * 
   * @return array [descripcion_cortina, descripcion_cortinero, links_opciones_resumen]
   */
  public function getDescripcionOpciones()
  {
    // 1. Obtener avance de sesión
    $avance = Session::get('avance_temporal');
    $avance = json_decode($avance, true);
    
    // Si está vacío, retornar descripciones vacías
    if (empty($avance)) {
      return [
        'descripcion_cortina' => '',
        'descripcion_cortinero' => '',
        'links_opciones_resumen' => []
      ];
    }

    // 2. Obtener todos los pasos activos y ordenados
    $pasos = PasoCotizador::where('PAS_Activo', 1)
      ->where('PAS_Eliminado', 0)
      ->orderBy('PAS_Orden', 'asc')
      ->get();

    // 3. Construir mapa de opciones seleccionadas con sus valores legibles
    $opcionesSeleccionadas = [];
    $links_opciones = [];
    
    foreach ($pasos as $paso) {
      $htmlName = $paso->PAS_Html_name;
      $valorSeleccionado = isset($avance[$htmlName]) ? $avance[$htmlName] : null;
      
      if ($valorSeleccionado && is_numeric($valorSeleccionado)) {
        // Buscar la opción seleccionada
        $opcion = OpcionCotizador::where('OPC_OpcionId', $valorSeleccionado)
          ->where('OPC_Eliminado', 0)
          ->where('OPC_Activo', 1)
          ->first();
          
        if ($opcion) {
          // Guardar el valor legible usando el nombre del paso como clave
          $opcionesSeleccionadas[$paso->PAS_Nombre] = $opcion->OPC_ValorOpcion;
          
          // Guardar el link para el resumen (campo_sesion => ruta_vista)
          $rutaVista = self::getPantallaNombre($paso->PAS_Pantalla_Ubicacion);
          if ($rutaVista) {
            $links_opciones[] = [$htmlName, $rutaVista];
          }
        }
      }
    }

    // 4. Agregar información de medidas según el tipo de riel
    $medidas_texto = $this->construirTextoMedidas($avance);
    
    // 5. Agregar nombre de la tela si existe
    $nombre_tela = isset($avance['material_descripcion']) ? $avance['material_descripcion'] : null;

    // 6. Construir descripción de la cortina
    $descripcion_cortina = $this->construirDescripcionCortina($opcionesSeleccionadas, $medidas_texto, $nombre_tela);
    
    // 7. Construir descripción del cortinero (si aplica)
    $descripcion_cortinero = $this->construirDescripcionCortinero($opcionesSeleccionadas);

    return [
      'descripcion_cortina' => $descripcion_cortina,
      'descripcion_cortinero' => $descripcion_cortinero,
      'links_opciones_resumen' => $links_opciones
    ];
  }

  /**
   * Construye el texto de medidas según el tipo de riel seleccionado
   * 
   * @param array $avance
   * @return string
   */
  private function construirTextoMedidas($avance)
  {
    $medidas = [];
    
    // Verificar qué medidas están presentes
    $tieneAncho = !empty($avance['inputAncho']) && is_numeric($avance['inputAncho']);
    $tieneAlto = !empty($avance['inputAlto']) && is_numeric($avance['inputAlto']);
    $tieneLadoA = !empty($avance['inputLadoA']) && is_numeric($avance['inputLadoA']);
    $tieneLadoB = !empty($avance['inputLadoB']) && is_numeric($avance['inputLadoB']);
    $tieneRadio = !empty($avance['inputRadio']) && is_numeric($avance['inputRadio']);
    
    // Riel en escuadra (con lados A y B)
    if ($tieneLadoA && $tieneLadoB && $tieneAlto) {
      $medidas[] = "Lado A: {$avance['inputLadoA']}m";
      $medidas[] = "Lado B: {$avance['inputLadoB']}m";
      $medidas[] = "Alto: {$avance['inputAlto']}m";
    }
    // Riel curvo/circular (con radio)
    elseif ($tieneRadio && $tieneAncho && $tieneAlto) {
      $medidas[] = "Ancho: {$avance['inputAncho']}m";
      $medidas[] = "Alto: {$avance['inputAlto']}m";
      $medidas[] = "Radio: {$avance['inputRadio']}m";
    }
    // Riel recto (configuración estándar)
    elseif ($tieneAncho && $tieneAlto) {
      $medidas[] = "Ancho: {$avance['inputAncho']}m";
      $medidas[] = "Alto: {$avance['inputAlto']}m";
    }
    
    return !empty($medidas) ? implode(', ', $medidas) : '';
  }

  /**
   * Construye la descripción de la cortina
   * 
   * @param array $opciones - Opciones seleccionadas [nombre_paso => valor_opcion]
   * @param string $medidas - Texto con las medidas
   * @param string|null $nombre_tela - Nombre de la tela seleccionada
   * @return string
   */
  private function construirDescripcionCortina($opciones, $medidas, $nombre_tela)
  {
    // Verificar que tenemos los campos mínimos necesarios
    $campos_requeridos = ['Confección', 'Instalación Riel', 'Hojas'];
    foreach ($campos_requeridos as $campo) {
      if (!isset($opciones[$campo])) {
        return ''; // Si falta algún campo crítico, no generar descripción
      }
    }
    
    $partes = [];
    
    // Tipo de producto y confección
    if (isset($opciones['Tipo de producto'])) {
      $partes[] = $opciones['Tipo de producto'];
    }
    
    $partes[] = "con confección {$opciones['Confección']}";
    
    // Estilo de confección (si existe)
    if (isset($opciones['Estilo de confección'])) {
      $partes[] = "estilo {$opciones['Estilo de confección']}";
    }
    
    // Instalación del riel
    $partes[] = "{$opciones['Instalación Riel']}";
    
    // Dirección de apertura (si existe)
    if (isset($opciones['Dirección de apertura'])) {
      $partes[] = "apertura {$opciones['Dirección de apertura']}";
    }
    
    // Hojas
    $partes[] = "{$opciones['Hojas']}";
    
    // Medidas
    if ($medidas) {
      $partes[] = "medidas: {$medidas}";
    }
    
    // Material/Tela
    if ($nombre_tela) {
      $partes[] = "tela: {$nombre_tela}";
    } elseif (isset($opciones['Tipo de material'])) {
      $partes[] = "material: {$opciones['Tipo de material']}";
    }
    
    return ucfirst(implode(', ', $partes)) . '.';
  }

  /**
   * Construye la descripción del cortinero (si aplica)
   * 
   * @param array $opciones - Opciones seleccionadas [nombre_paso => valor_opcion]
   * @return string|null
   */
  private function construirDescripcionCortinero($opciones)
  {
    // Solo generar si el subproducto es "Cortina + Cortinero"
    if (!isset($opciones['Subproducto']) || $opciones['Subproducto'] !== 'Cortina + Cortinero') {
      return null;
    }
    
    // Verificar que tenemos los campos mínimos del cortinero
    $campos_requeridos = ['Sistema de apertura', 'Modelo del Riel', 'Material de riel'];
    foreach ($campos_requeridos as $campo) {
      if (!isset($opciones[$campo])) {
        return null;
      }
    }
    
    $partes = [];
    
    // Sistema de apertura
    $partes[] = "Sistema de apertura {$opciones['Sistema de apertura']}";
    
    // Superficie de instalación (si existe)
    if (isset($opciones['Superficie de instalación'])) {
      $partes[] = "{$opciones['Superficie de instalación']}";
    }
    
    // Modelo y material del riel
    $partes[] = "modelo {$opciones['Modelo del Riel']} de {$opciones['Material de riel']}";
    
    // Accesorio de apertura (si existe)
    if (isset($opciones['Accesorio de apertura'])) {
      $partes[] = "con {$opciones['Accesorio de apertura']}";
      
      // Material del accesorio (si existe)
      if (isset($opciones['Material accesorio'])) {
        $partes[] = "de {$opciones['Material accesorio']}";
      }
      
      // Modelo del accesorio (si existe)
      if (isset($opciones['Modelo accesorio'])) {
        $partes[] = "modelo {$opciones['Modelo accesorio']}";
      }
      
      // Largo del accesorio (si existe)
      if (isset($opciones['Largo accesorio'])) {
        $partes[] = "{$opciones['Largo accesorio']}";
      }
    }
    
    return 'Cortinero: ' . implode(', ', $partes) . '.';
  }

/**
 * Ejecuta una consulta SQL cruda con Named Bindings y devuelve el resultado.
 *
 * @param string $sql La consulta SQL con marcadores de posición con nombre (:variable).
 * @param array $bindings Array asociativo de variables ['variable_name' => valor, ...].
 * @return mixed El valor escalar (si es una columna/una fila) o un array de resultados.
 */
public function executeGenericSql(string $sql, array $bindings = []): mixed
{
    // 1. Ejecutar la consulta con los bindings.
    // DB::select usa los dos puntos (:) para buscar los nombres de las variables en $bindings.
    $results = DB::select($sql, $bindings);

    // Si no hay resultados, retorna null.
    if (empty($results)) {
        return null;
    }

    // 2. Manejar la columna de retorno (Para ser compatible con cualquier SELECT).
    
    $firstRow = $results[0];

    // Verificar si la consulta devolvió una sola fila y una sola columna (valor escalar).
    // Esto es común para funciones, IF/ELSE (como en tu ejemplo) o SELECT COUNT(*).
    if (is_object($firstRow) && count((array)$firstRow) === 1) {
        // Extraemos el valor de la única propiedad (columna), sin importar su nombre.
        $scalarValue = array_values((array)$firstRow);
        
        // Retorna el valor escalar.
        return $scalarValue[0];
    }
    
    // 3. Si es un SELECT con múltiples columnas o filas, retorna todos los objetos de resultado.
    return $results;
}

  /**
   * Devuelve el detalle de la cotización actual para mostrar en el modal
   * Incluye productos, cantidades, precios y totales
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function detalle_cotizacion()
  {
    try {
      // Obtener datos de sesión
      $avance = Session::get('avance_temporal') ?? [];
      $productos = Session::get('productos') ?? [];
      
      // Validar que existan datos
      if (empty($avance) || empty($productos)) {
        return response()->json([
          'success' => false, 
          'message' => 'No hay datos de cotización disponibles'
        ], 404);
      }
      
      // Decodificar avance
      $avance = is_string($avance) ? json_decode($avance, true) : $avance;
      
      // Obtener información general
      $nombre_proyecto = $avance['nombre_proyecto'] ?? 'Sin nombre';
      $nombre_articulo = $avance['nombre_articulo'] ?? 'Sin nombre';
      
      // Obtener IDs de productos
      $ids_productos = array_keys($productos);
      
      // Consultar nombres de productos desde la base de datos
      $productos_bd = PCNT::whereIn('PCNT_PROD_id', $ids_productos)
        ->select('PCNT_PROD_id', 'PCNT_PROD_nombre')
        ->get()
        ->keyBy('PCNT_PROD_id');
      
      // Construir array de productos con detalles
      $productos_detalle = [];
      foreach ($productos as $producto_id => $producto_data) {
        // Buscar el producto en la BD
        $producto_bd = $productos_bd->get($producto_id);
        $nombre_producto = $producto_bd ? $producto_bd->PCNT_PROD_nombre : 'Producto desconocido';
        
        // Obtener valores con valores por defecto
        $cantidad = isset($producto_data['cantidad']) ? floatval($producto_data['cantidad']) : 0;
        $precio_unitario = isset($producto_data['precio_unitario']) ? floatval($producto_data['precio_unitario']) : 0;
        
        // Calcular total: si existe precio_total lo usamos, sino lo calculamos
        $precio_total = isset($producto_data['precio_total']) 
          ? floatval($producto_data['precio_total']) 
          : ($cantidad * $precio_unitario);
        
        $productos_detalle[] = [
          'id' => $producto_id,
          'nombre' => $producto_id . ' - ' . $nombre_producto,
          'cantidad' => number_format($cantidad, 2, '.', ''),
          'precio_unitario' => number_format($precio_unitario, 2, '.', ''),
          'total' => number_format($precio_total, 2, '.', '')
        ];
      }
      
      // Calcular totales
      $subtotal = $this->getSubtotal($productos);
      $iva_porcentaje = 16; // IVA del 16%
      $iva = $subtotal * ($iva_porcentaje / 100);
      $total = $subtotal + $iva;
      
      // Obtener descripciones y opciones seleccionadas
      $descripciones = $this->getDescripcionOpciones();
      
      // Obtener todas las opciones seleccionadas de manera estructurada
      $opciones_seleccionadas = $this->obtenerOpcionesSeleccionadas($avance);
      
      // Obtener medidas
      $medidas = $this->obtenerMedidas($avance);
      
      return response()->json([
        'success' => true,
        'proyecto' => $nombre_proyecto,
        'articulo' => $nombre_articulo,
        'productos' => $productos_detalle,
        'subtotal' => number_format($subtotal, 2, '.', ''),
        'iva' => number_format($iva, 2, '.', ''),
        'iva_porcentaje' => $iva_porcentaje,
        'total' => number_format($total, 2, '.', ''),
        'descripcion_cortina' => $descripciones['descripcion_cortina'] ?? null,
        'descripcion_cortinero' => $descripciones['descripcion_cortinero'] ?? null,
        'opciones_seleccionadas' => $opciones_seleccionadas,
        'medidas' => $medidas,
        'nombre_tela' => $avance['material_descripcion'] ?? null
      ]);
    } catch (\Exception $e) {
      // Log del error para debugging
      Log::error('Error en detalle_cotizacion: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
        'line' => $e->getLine()
      ]);
      
      return response()->json([
        'success' => false,
        'message' => 'Error al cargar el detalle: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Obtiene las opciones seleccionadas en formato estructurado para el detalle
   * 
   * @param array $avance
   * @return array
   */
  private function obtenerOpcionesSeleccionadas($avance)
  {
    $pasos = PasoCotizador::where('PAS_Activo', 1)
      ->where('PAS_Eliminado', 0)
      ->orderBy('PAS_Orden', 'asc')
      ->get();

    $opciones = [];
    
    foreach ($pasos as $paso) {
      $htmlName = $paso->PAS_Html_name;
      $valorSeleccionado = isset($avance[$htmlName]) ? $avance[$htmlName] : null;
      
      if ($valorSeleccionado && is_numeric($valorSeleccionado)) {
        $opcion = OpcionCotizador::where('OPC_OpcionId', $valorSeleccionado)
          ->where('OPC_Eliminado', 0)
          ->where('OPC_Activo', 1)
          ->first();
          
        if ($opcion) {
          $opciones[] = [
            'categoria' => $paso->PAS_Nombre,
            'valor' => $opcion->OPC_ValorOpcion,
            'icono' => $this->obtenerIconoPorCategoria($paso->PAS_Nombre)
          ];
        }
      }
    }
    
    return $opciones;
  }

  /**
   * Obtiene las medidas en formato estructurado
   * 
   * @param array $avance
   * @return array
   */
  private function obtenerMedidas($avance)
  {
    $medidas = [];
    
    if (!empty($avance['inputAncho']) && is_numeric($avance['inputAncho'])) {
      $medidas[] = ['label' => 'Ancho', 'valor' => $avance['inputAncho'] . ' m'];
    }
    
    if (!empty($avance['inputAlto']) && is_numeric($avance['inputAlto'])) {
      $medidas[] = ['label' => 'Alto', 'valor' => $avance['inputAlto'] . ' m'];
    }
    
    if (!empty($avance['inputLadoA']) && is_numeric($avance['inputLadoA'])) {
      $medidas[] = ['label' => 'Lado A', 'valor' => $avance['inputLadoA'] . ' m'];
    }
    
    if (!empty($avance['inputLadoB']) && is_numeric($avance['inputLadoB'])) {
      $medidas[] = ['label' => 'Lado B', 'valor' => $avance['inputLadoB'] . ' m'];
    }
    
    if (!empty($avance['inputRadio']) && is_numeric($avance['inputRadio'])) {
      $medidas[] = ['label' => 'Radio', 'valor' => $avance['inputRadio'] . ' m'];
    }
    
    return $medidas;
  }

  /**
   * Retorna un icono apropiado según la categoría
   * 
   * @param string $categoria
   * @return string
   */
  private function obtenerIconoPorCategoria($categoria)
  {
    $iconos = [
      'Tipo de producto' => 'fa-tag',
      'Confección' => 'fa-cut',
      'Estilo de confección' => 'fa-palette',
      'Instalación Riel' => 'fa-grip-lines',
      'Tipo de riel' => 'fa-bars',
      'Color del riel' => 'fa-paint-brush',
      'Hojas' => 'fa-layer-group',
      'Dirección de apertura' => 'fa-arrows-alt-h',
      'Área de instalación' => 'fa-map-marker-alt',
      'Sistema de apertura' => 'fa-hand-pointer',
      'Accesorio de apertura' => 'fa-grip-vertical',
      'Material accesorio' => 'fa-cube',
      'Modelo accesorio' => 'fa-shapes',
      'Largo accesorio' => 'fa-ruler-horizontal'
    ];
    
    return $iconos[$categoria] ?? 'fa-check-circle';
  }
}
