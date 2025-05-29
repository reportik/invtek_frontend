<?php

namespace App\Http\Controllers\dashboard;

use Illuminate\Http\Request;
use App\Models\OpcionCotizador;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class Analytics extends Controller
{
  public function getOpcionesArrayPadres($values)
  {
    // Buscar los nodos padres por los ids Padres,  proporcionados
    $filtro = array_keys($values);

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
    $aperturas = self::getOpcionesPorValor('Sistema de apertura');
    $apertura_ids = $aperturas->pluck('OPC_OpcionId')->toArray();

    // 2. Traer todos los hijos de esas aperturas
    $hijos = self::getOpcionesArrayPadres(array_flip($apertura_ids));

    // 3. Traer hijos de los rieles (para colores)
    $rieles = $hijos->where('OPC_PasoId', 'riel');
    $rieles_ids = $rieles->pluck('OPC_OpcionId')->toArray();
    $coloresBD = self::getOpcionesArrayPadres(array_flip($rieles_ids));

    // 4. Mapeo
    $sistemas_apertura = $aperturas->map(function ($op) {
      return [
        'id' => $op->OPC_OpcionId,
        'valor' => $op->OPC_ValorOpcion,
      ];
    })->values();

    $sistemas = $hijos->where('OPC_PasoId', 'sistema')->groupBy('OPC_OpcionPadreId')->map(function ($items) {
      return $items->map(function ($op) {
        return [
          'id' => $op->OPC_OpcionId,
          'nombre' => $op->OPC_ValorOpcion,
          'descripcion' => $op->OPC_Descripcion,
          'image' => $op->OPC_Imagen,
        ];
      })->values();
    });

    $rieles = $rieles->groupBy('OPC_OpcionPadreId')->map(function ($items) {
      return $items->map(function ($op) {
        return [
          'id' => $op->OPC_OpcionId,
          'nombre' => $op->OPC_ValorOpcion,
        ];
      })->values();
    });

    $colores = $coloresBD->where('OPC_PasoId', 'color_riel')->groupBy('OPC_OpcionPadreId')->map(function ($items) {
      return $items->map(function ($op) {
        return [
          'nombre' => $op->OPC_ValorOpcion,
          'hex' => $op->OPC_HexColor ?? '#ccc',
        ];
      })->values();
    });
    //dd($sistemas_apertura);
    return view('sistema_apertura', [
      'sistemas_apertura' => $sistemas_apertura,
      'sistemas' => [],
      'rieles' => [],
      'colores' => [],
    ]);
  }
  public function telas()
  {
    $cards_3 = [
      ["opcion_radio" => "Blackout", "image" => "img9.PNG", "a_selected" => "true"],
      ["opcion_radio" => "Sheer", "image" => "img10.PNG", "a_selected" => ""]
      // ["opcion_radio" => "Decorativa", "image" => "img11.PNG"]
    ];

    // Consulta todos los datos de la tabla
    $telas = \DB::table('RPT_ODOO_CORTINAS')->select('id', 'name', 'Tipo')->get();

    // Separar las telas en dos arrays según el tipo
    $telas_blackout = $telas->where('Tipo', 'blackout')->values();
    $telas_sheer = $telas->where('Tipo', 'sheer')->values();
    $version = random_int(1, 10000);
    return view('catalogo_telas', compact('telas_blackout', 'telas_sheer', 'cards_3', 'version'));
  }


  public function inicio()
  {
    OpcionCotizador::where('OPC_Eliminado', 0)->get();
    //reemplazar $opcionesCalidad con el array de opciones de la base de datos
    $opciones = self::getOpcionesPorValor('Calidad');
    //obtener el valor de la columna OPC_ValorOpcion y como llave el valor de la columna OPC_OpcionId del array $opciones
    $opcionesCalidad = \Arr::pluck($opciones, 'OPC_ValorOpcion', 'OPC_OpcionId');
    //
    $opcionesCalidadDescripcion = \Arr::pluck($opciones, 'OPC_Descripcion', 'OPC_OpcionId');

    return view('inicio', compact('opcionesCalidad', 'opcionesCalidadDescripcion'));
  }
  public function guardarAvance(Request $request)
  {
    // Obtener avance actual desde sesión (si no logueado) o base de datos (si logueado)
    $avanceActual = auth()->check()
      ? json_decode(auth()->user()->avance ?? '[]', true)
      : json_decode(session('avance_temporal', '[]'), true);

    // Datos nuevos desde el request
    $nuevoAvance = $request->except('_token'); // Excluye campos no necesarios

    // Fusionar avance anterior con el nuevo
    $avanceFusionado = array_merge($avanceActual, $nuevoAvance);

    if (auth()->check()) {
      // Guardar en base de datos
      auth()->user()->update(['avance' => json_encode($avanceFusionado)]);
      session()->forget('avance_temporal');
    } else {
      // Guardar en sesión
      session(['avance_temporal' => json_encode($avanceFusionado)]);
    }

    // Si contiene 'resumen' en el nuevo avance → redirige a resumen
    if (isset($avanceFusionado['resumen'])) {
      return redirect()->route('resumen');
    }

    // Si se indicó una siguiente vista
    return $request->filled('siguiente-vista')
      ? redirect()->route($request->input('siguiente-vista'))
      : redirect()->route('inicio');
  }

  public function medidas()
  {
    $rieles = self::getOpcionesPorValor('Instalación Riel');
    dd($rieles);
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
    $imagenes = self::getOpcionesArrayPadres($rieles);
    $imagenes_medidas = $imagenes->map(function ($opcion) {
      return [
        'id_imagen' => $opcion->OPC_OpcionId,
        'id_riel' => $opcion->OPC_OpcionPadreId,
        'image' => $opcion->OPC_Imagen,
        'coordenadas' => $opcion->OPC_Descripcion
      ];
    })->toArray();
    //dd($tiposRiel, $imagenes_medidas);
    return view('configuracion_medidas', compact('tiposRiel', 'imagenes_medidas'));
  }
  public function tipo_producto()
  {
    return view('tipo_producto');
  }
  public function tipo_confeccion()
  {
    $tiposConfeccion = self::getOpcionesPorValor('Confeccion');
    $tiposConfeccion = \Arr::pluck($tiposConfeccion, 'OPC_ValorOpcion', 'OPC_OpcionId');
    $cards = self::getOpcionesArrayPadres($tiposConfeccion);

    $cards_confeccion = $cards->map(function ($opcion) {
      return [
        'tipo' => $opcion->OPC_OpcionPadreId,
        'image' => $opcion->OPC_Imagen,
        'opcion_radio' => $opcion->OPC_ValorOpcion,
        'a_selected' => "false",
      ];
    })->toArray();
    //dd($tiposConfeccion, $cards_confeccion);
    return view('tipo_confeccion', compact('tiposConfeccion', 'cards_confeccion'));
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
      $response = Http::get('http://localhost:8036/items');
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
}
