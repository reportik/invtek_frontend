<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PasoCotizador;
use App\Models\OpcionCotizador;
use App\Models\ProductoCantidad;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class OpcionCotizadorController extends Controller
{
  public function index($id = null)
  {
    $pasos = PasoCotizador::where('PAS_Eliminado', 0)->pluck('PAS_Nombre', 'PAS_PasoId');
    //$opcionesPadre = OpcionCotizador::pluck('OPC_ValorOpcion', 'OPC_OpcionId');
    //dd($pasos);

    return view('opciones.index', compact('pasos', 'id'));
  }
  public function getOpcionesRutaAjax(Request $request)
  {
    $selector = $request->input('selector'); // selector es el paso actual
    $avance = Session::get('avance_temporal', '');
    $avance = json_decode($avance, true);

    // Obtener todos los pasos activos y ordenados
    $pasos = PasoCotizador::where('PAS_Activo', 1)
      ->where('PAS_Eliminado', 0)
      ->orderBy('PAS_Orden', 'asc')
      ->get();

    $pasoActual = $pasos->firstWhere('PAS_PasoId', $selector);
    // Solo dependencias hasta el paso actual
    $respondidos = [];
    foreach ($pasos as $paso) { //
      if (
        isset($avance[$paso->PAS_Html_name]) &&
        is_numeric($avance[$paso->PAS_Html_name]) &&
        $paso->PAS_Orden < $pasoActual->PAS_Orden
      ) {
        $respondidos[(int)$paso->PAS_Orden] = $avance[$paso->PAS_Html_name]; //guarda el orden y el valor
      } else {
        $respondidos[(int)$paso->PAS_Orden] = 'T';
      }
    }
    //dd($respondidos);
    $query = OpcionCotizador::where('OPC_Eliminado', 0)
      ->where('OPC_PasoId', $selector);
    for ($j = 1; $j < $pasoActual->PAS_Orden; $j++) {
      //if ($pasoActual->PAS_Orden <= $ultimoOrden) continue; //solo los posteriores
      $campo = 'OPC_S' . $j;
      if (isset($respondidos[$j])) {
        $valor = str_pad($respondidos[$j], 5, '0', STR_PAD_LEFT);
        $query->where($campo, $valor);
      }
    }
    //dd($query->toSql(), $query->getBindings());
    $opcionesValidas = $query->orderBy('OPC_PasoId', 'asc')
      ->orderBy('OPC_OpcionId', 'asc')
      ->get();
    $data = $opcionesValidas->map(function ($opcion) use ($pasoActual) {
      return [
        'selector_padre' =>  '',
        'valor_padre' => '',
        'selector' => $pasoActual->PAS_Nombre,
        'valor' => $opcion->OPC_ValorOpcion,
        'activo' => $opcion->OPC_Activo ? 'Sí' : 'No',
        'imagen' => $opcion->OPC_Imagen,
        'acciones' => view('opciones.partials.acciones', compact('opcion'))->render(),
      ];
    });

    return response()->json(['data' => $data]);
  }
  public function getOpcionesAjax(Request $request)
  {
    $selector = $request->input('selector'); // selector es el paso actual
    if ($selector == -1) {
      $opciones = OpcionCotizador::with(['paso', 'padre.paso'])
        ->where('OPC_Eliminado', 0)
        ->orderBy('OPC_PasoId', 'asc')
        ->orderBy('OPC_OpcionId', 'asc')
        ->get();
    } else {
      $opciones = OpcionCotizador::with(['paso', 'padre.paso'])
        ->where('OPC_Eliminado', 0)
        ->where('OPC_PasoId', $selector)
        ->orderBy('OPC_PasoId', 'asc')
        ->orderBy('OPC_OpcionId', 'asc')
        ->get();
    }

    $data = $opciones->map(function ($opcion) {
      // Obtener el nombre del paso del padre si existe
      $selectorPadre = '—';
      if ($opcion->padre && $opcion->padre->paso) {
        $selectorPadre = $opcion->padre->paso->PAS_Nombre;
      }
      // Obtener el valor de la opción padre si existe
      $valorPadre = $opcion->padre->OPC_ValorOpcion ?? '—';
      $id_padre = $opcion->padre->OPC_OpcionId ?? '—';
      $selector = $opcion->paso->PAS_Nombre;
      if (\Auth::check() && \Auth::user()->id == 2) {
        $selectorPadre = $id_padre . ' ' . $selectorPadre;
        $selector = $opcion->OPC_OpcionId . ' ' . $opcion->paso->PAS_Nombre;
      }

      return [
        'selector_padre' =>  $selectorPadre,
        'valor_padre' => $valorPadre,
        'selector' => $selector,
        'valor' => $opcion->OPC_ValorOpcion,
        'activo' => $opcion->OPC_Activo ? 'Sí' : 'No',
        'imagen' => $opcion->OPC_Imagen,
        'acciones' => view('opciones.partials.acciones', compact('opcion'))->render(),
      ];
    });

    return response()->json(['data' => $data]);
  }


  public function create()
  {
    $pasos = PasoCotizador::where('PAS_Eliminado', 0)->pluck('PAS_Nombre', 'PAS_PasoId');
    $opcion = new OpcionCotizador();
    $editMode = false;
    $action = route('opciones.store');
    $opcionesPorPaso = OpcionCotizador::where('OPC_Eliminado', 0)
      ->get()
      ->groupBy('OPC_PasoId')
      ->map(function ($grupo) {
        return $grupo->map(function ($opcion) {
          return [
            'id' => $opcion->OPC_OpcionId,
            'valor' => $opcion->OPC_ValorOpcion,
          ];
        });
      });
    return view('opciones.modals.form', compact('pasos', 'opcionesPorPaso', 'opcion', 'editMode', 'action'));
  }

  public function edit($id)
  {
    $opcion = OpcionCotizador::findOrFail($id);
    //Attempt to read property "OPC_PasoId" on null
    if ($opcion->padre) {
      $id_padre_paso = $opcion->padre->OPC_PasoId ? $opcion->padre->OPC_PasoId : ''; // para setear el selector padre
      $id_padre_valor = $opcion->padre->OPC_OpcionId ? $opcion->padre->OPC_OpcionId : ''; // para setear el selector valor padre
    } else {
      $id_padre_paso = '';
      $id_padre_valor = '';
    }

    $pasos = PasoCotizador::where('PAS_Eliminado', 0)->pluck('PAS_Nombre', 'PAS_PasoId');
    $editMode = true;
    $action = route('opciones.update', $opcion);
    $opcionesPorPaso = OpcionCotizador::where('OPC_Eliminado', 0)
      ->get()
      ->groupBy('OPC_PasoId')
      ->map(function ($grupo) {
        return $grupo->map(function ($opcion) {
          return [
            'id' => $opcion->OPC_OpcionId,
            'valor' => $opcion->OPC_ValorOpcion,
          ];
        });
      });
    return view('opciones.modals.form', compact('pasos', 'id_padre_paso', 'id_padre_valor', 'opcionesPorPaso', 'opcion', 'editMode', 'action'));
  }
  public function store(Request $request)
  {
    $request->validate([
      'OPC_PasoId' => 'required|integer',
      'OPC_ValorOpcion' => 'required|string|max:100',
      'OPC_Imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Valida la imagen
    ]);

    //dd($request->all());
    // Verifica si el valor de la opción ya existe
    /*
     $existeOpcion = OpcionCotizador::where('OPC_ValorOpcion', $request->OPC_ValorOpcion)
      ->where('OPC_PasoId', $request->OPC_PasoId)
      ->where('OPC_OpcionPadreId', $request->OPC_OpcionPadreId)
      ->where('OPC_Eliminado', 0)
      ->exists();

    if ($existeOpcion) {
      //400
      return response()->json(['error' => 'La opción ya existe para este paso.'], 400);
    } */

    $data = $request->except('OPC_Imagen'); // Excluye la imagen del array
    $path = public_path('images/cotizador');
    if ($request->OPC_PasoId == 22) {
      $path = public_path('images/telas');
    }
    if ($request->hasFile('OPC_Imagen')) {
      $image = $request->file('OPC_Imagen');
      $filename = time() . '.' . $image->getClientOriginalExtension();
      $image->move($path, $filename);  // Guarda la imagen en public/images/cotizador
      $data['OPC_Imagen'] = $filename;
    }

    $data['OPC_EsDefault'] = $request->has('OPC_EsDefault') ? 1 : 0;
    $data['OPC_EsProducto'] = $request->has('OPC_EsProducto') ? 1 : 0;
    $data['OPC_Activo'] = $request->has('OPC_Activo') ? 1 : 0;

    $idSelector = $request->input('id');
    // Llenar S1, S2, S3... solo hasta el paso anterior al paso actual
    $avance = Session::get('avance_temporal', '');
    $avance = json_decode($avance, true);
    $pasos = \App\Models\PasoCotizador::where('PAS_Activo', 1)
      ->where('PAS_Eliminado', 0)
      ->orderBy('PAS_Orden', 'asc')
      ->get();
    $pasoActual = $pasos->firstWhere('PAS_PasoId', $idSelector);
    //dd($idSelector);
    foreach ($pasos as $paso) {
      if ($paso->PAS_Orden < $pasoActual->PAS_Orden) {
        $orden = $paso->PAS_Orden;
        $htmlName = $paso->PAS_Html_name;
        if (isset($avance[$htmlName]) && is_numeric($avance[$htmlName]) && $avance[$htmlName] !== 'T') {
          $data['OPC_S' . (int)$orden] = str_pad($avance[$htmlName], 5, '0', STR_PAD_LEFT);
        }
      }
      // Si no hay selección, no se asigna nada, se mantiene el valor por default ('T')
    }
    //dd("data", $data);
    $path = public_path('images/cotizador');
    if ($request->OPC_PasoId == 22) {
      $path = public_path('images/telas');
    }
    if ($request->hasFile('OPC_Imagen')) {
      $image = $request->file('OPC_Imagen');
      $filename = time() . '.' . $image->getClientOriginalExtension();
      $image->move($path, $filename);  // Guarda la imagen en public/images/cotizador
      $data['OPC_Imagen'] = $filename;
    }

    // Verificar existencia usando todos los campos OPC_S y los campos clave
    $campos_base = [
      'OPC_ValorOpcion'   => $data['OPC_ValorOpcion'] ?? null,
      'OPC_PasoId'        => $data['OPC_PasoId'] ?? null,
      'OPC_OpcionPadreId' => $data['OPC_OpcionPadreId'] ?? null,
    ];
    $opc_s_fields = [];
    foreach ($data as $key => $value) {
      if (strpos($key, 'OPC_S') === 0) {
        $opc_s_fields[$key] = $value;
      }
    }
    $query = OpcionCotizador::query();
    foreach (array_merge($campos_base, $opc_s_fields) as $key => $value) {
      $query->where($key, $value);
    }
    $query->where('OPC_Eliminado', 0);
    //dd($query->toSql(), $query->getBindings());
    $opcion = $query->first();
    if ($opcion) {
      return response()->json(['error' => 'Ya existe una opción con los mismos valores.'], 422);
    }
    // No existe, crear
    $opcion = OpcionCotizador::create($data);

    $producto = null;
    if ($data['OPC_EsProducto'] == 1) {
      $producto = self::createProduct($data['OPC_ValorOpcion'], $opcion->OPC_OpcionId);
      if (is_null($producto)) {
        $opcion->OPC_EsProducto = 0;
        $opcion->save();
        return response()->json(['success' => 'Opción creada y producto no encontrado en Odoo (verifique el nombre del producto).'], 200);
      }
    }
    //verificar si existe key path_filter en OPC_Programacion: true or false
    $jsonString = $data['OPC_Programacion'];
    $programacion_array = json_decode($jsonString, true); // Decodificar el JSON a un array
    if ($data['OPC_Programacion'] != '' && array_key_exists('path_filter', $programacion_array)) {
      $opcionId = $opcion->OPC_OpcionId;


      $response = Http::timeout(300)->post("http://localhost:3036/products/by-category", $programacion_array);
      $json = $response->json();
      // Validar estructura de la respuesta
      //dd($json);
      //borrar todos los productos de la opcion
      ProductoCantidad::where('PCNT_OPC_OpcionId', $opcionId)->delete();

      foreach ($json as $item) {
        $data = [
          'PCNT_OPC_OpcionId' => $opcionId,
          'PCNT_PROD_id' => $item['id'],
          'PCNT_PROD_nombre' => $item['name'],
          'PCNT_base_ancho' => 0,
          'PCNT_base_cantidad' => 0,
          'PCNT_precio_unitario' => isset($item['price']) ? $item['price'] : 0.0
        ];
        //si no existe el producto en la base de datos lo crea
        $producto = ProductoCantidad::where('PCNT_PROD_nombre', $item['name'])
          ->where('PCNT_OPC_OpcionId', $opcionId)->first();
        if (is_null($producto)) {
          $producto = ProductoCantidad::create($data);
        } else {
          $producto->update($data);
        }
      }
    }
    //200
    return response()->json(['success' => 'Opción creada correctamente.'], 200);
  }
  public function createProduct($nombreProducto, $opcionId)
  {
    $response = Http::get("http://itekniaapp.serveftp.com:3036/item/{$nombreProducto}");
    $json = $response->json();
    // Validar estructura de la respuesta
    if (!isset($json['product'])) {
      return null;
    }

    $product = $json['product'];
    $precio = isset($json['price_list_1']) ? $json['price_list_1'] : 0.0;
    $data = [
      'PCNT_OPC_OpcionId' => $opcionId,
      'PCNT_PROD_id' => $product['id'],
      'PCNT_PROD_nombre' => $product['name'],
      'PCNT_base_ancho' => 100,
      'PCNT_base_cantidad' => 1,
      'PCNT_precio_unitario' => $precio
    ];
    //si no existe el producto en la base de datos lo crea
    $producto = ProductoCantidad::where('PCNT_PROD_nombre', $product['name'])
      ->where('PCNT_OPC_OpcionId', $opcionId)->first();
    if (is_null($producto)) {
      $producto = ProductoCantidad::create($data);
    } else {
      $producto->update($data);
    }
    return $producto;
  }
  public function update(Request $request, $id)
  {
    set_time_limit(-1);

    $opcion = OpcionCotizador::findOrFail($id);

    $request->validate([
      'OPC_PasoId' => 'required|integer',
      'OPC_ValorOpcion' => 'required|string|max:100',
      //Imagen puede no venir
      'OPC_Imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Valida la imagen
    ]);

    $data = $request->except('OPC_Imagen');
    // Manejo de la eliminación de la imagen
    if ($request->has('eliminar_imagen') && $request->input('eliminar_imagen') == 1) {
      if ($opcion->OPC_Imagen) {
        // $oldImagePath = public_path('images/cotizador/') . $opcion->OPC_Imagen;
        // if (File::exists($oldImagePath)) {
        //     File::delete($oldImagePath);
        // }
        $data['OPC_Imagen'] = null;  // Establece el nombre de la imagen en null en la base de datos
      }
    }
    $path = public_path('images/cotizador');
    if ($request->OPC_PasoId == 22) {
      $path = public_path('images/telas');
    }
    // Manejo de la imagen
    if ($request->hasFile('OPC_Imagen')) {
      // Elimina la imagen anterior si existe
      if ($opcion->OPC_Imagen) {
        $oldImagePath = $path . $opcion->OPC_Imagen;
        if (File::exists($oldImagePath)) {
          File::delete($oldImagePath);
        }
      }

      $image = $request->file('OPC_Imagen');
      $filename = time() . '.' . $image->getClientOriginalExtension();
      $image->move($path, $filename);
      $data['OPC_Imagen'] = $filename;
    }

    $data['OPC_EsDefault'] = $request->has('OPC_EsDefault') ? 1 : 0;
    $data['OPC_EsProducto'] = $request->has('OPC_EsProducto') ? 1 : 0;
    $data['OPC_Activo'] = $request->has('OPC_Activo') ? 1 : 0;

    //dd($data);

    if ($data['OPC_EsProducto'] == 1) {
      /*  $programacion = $data['OPC_Programacion'];
            $opcionId = $opcion->OPC_OpcionId;
            $response = Http::get("http://localhost:3036/products/by-category", [
                'path_filter' => $programacion
            ]);
            //dd($response);
            $json = $response->json();
            // Validar estructura de la respuesta
            $data = [
                'PCNT_OPC_OpcionId' => $opcionId,
                'PCNT_PROD_id' => $json['id'],
                'PCNT_PROD_nombre' => $json['name'],
                'PCNT_base_ancho' => 0,
                'PCNT_base_cantidad' => 0,
                'PCNT_precio_unitario' => isset($json['list_price']) ? $json['list_price'] : 0.0
            ];
            //si no existe el producto en la base de datos lo crea
            $producto = ProductoCantidad::where('PCNT_PROD_nombre', $json['name'])
                ->where('PCNT_OPC_OpcionId', $opcionId)->first();
            if (is_null($producto)) {
                $producto = ProductoCantidad::create($data);
            } else {
                $producto->update($data);
            } */
      $producto = self::createProduct($data['OPC_ValorOpcion'], $opcion->OPC_OpcionId);
      //dd($data['OPC_ValorOpcion'], $opcion->OPC_OpcionId, $producto);
      if (is_null($producto)) {
        return response()->json(['error' => 'Producto no encontrado en Odoo. Verifique el nombre del producto o desmarque la opción "Es Producto"'], 500);
      }
    }
    //verificar si existe key path_filter en OPC_Programacion: true or false
    $jsonString = $data['OPC_Programacion'];
    $programacion_array = json_decode($jsonString, true); // Decodificar el JSON a un array
    if (
      $data['OPC_Programacion'] !== '' &&
      is_array($programacion_array) &&
      array_key_exists('path_filter', $programacion_array)
    ) {
      $opcionId = $opcion->OPC_OpcionId;


      $response = Http::timeout(300)->post("http://localhost:3036/products/by-category", $programacion_array);
      $json = $response->json();
      // Validar estructura de la respuesta
      //dd($json);
      //borrar todos los productos de la opcion
      ProductoCantidad::where('PCNT_OPC_OpcionId', $opcionId)->delete();

      foreach ($json as $item) {
        $data = [
          'PCNT_OPC_OpcionId' => $opcionId,
          'PCNT_PROD_id' => $item['id'],
          'PCNT_PROD_nombre' => $item['name'],
          'PCNT_base_ancho' => 0,
          'PCNT_base_cantidad' => 0,
          'PCNT_precio_unitario' => isset($item['price']) ? $item['price'] : 0.0
        ];
        //si no existe el producto en la base de datos lo crea
        $producto = ProductoCantidad::where('PCNT_PROD_nombre', $item['name'])
          ->where('PCNT_OPC_OpcionId', $opcionId)->first();
        if (is_null($producto)) {
          $producto = ProductoCantidad::create($data);
        } else {
          $producto->update($data);
        }
      }
    }
    $opcion->update($data);
    return response()->json(['success' => 'Opción actualizada correctamente.'], 200);
  }

  public function destroy($id)
  {
    //solo actualiza el campo OPC_Eliminado
    $opcion = OpcionCotizador::findOrFail($id);
    $opcion->OPC_Eliminado = 1;
    $opcion->save();
    //OpcionCotizador::destroy($id);
    return redirect()->route('opciones.index', $opcion->OPC_PasoId)->with('success', 'Opción eliminada.');
  }

  // show
  public function show($id)
  {
    $rutaSelectores = self::getDescripcionRutaBreadcrumbs($id);
    //$pasos = PasoCotizador::where('PAS_Eliminado', 0)->pluck('PAS_Nombre', 'PAS_PasoId');
    return view('opciones.index', compact('rutaSelectores', 'id'));
  }

  public static function getDescripcionRutaBreadcrumbs($id)
  {
    // 1. Obtener avance de sesión
    $avance = session()->get('avance_temporal', '');
    $avance = json_decode($avance, true);

    // 2. Obtener todos los pasos activos y ordenados
    $pasos = \App\Models\PasoCotizador::where('PAS_Activo', 1)
      ->where('PAS_Eliminado', 0)
      ->orderBy('PAS_Orden', 'asc')
      ->get();

    $breadcrumbs = [];
    $breadcrumbs[] = 'RUTA SELECCIONADA';

    foreach ($pasos as $paso) {
      $htmlName = $paso->PAS_Html_name;
      $valorSeleccionado = isset($avance[$htmlName]) ? $avance[$htmlName] : null;
      if ($valorSeleccionado && is_numeric($valorSeleccionado)) {
        $opcion = \App\Models\OpcionCotizador::where('OPC_OpcionId', str_pad($valorSeleccionado, 5, '0', STR_PAD_LEFT))
          ->where('OPC_PasoId', $paso->PAS_PasoId)
          ->where('OPC_Eliminado', 0)
          ->where('OPC_Activo', 1)
          ->first();
        if ($opcion) {
          $breadcrumbs[] = $opcion->OPC_ValorOpcion;
        } else {
          //$breadcrumbs[] = 'No seleccionado';
        }
      } else {
        //$breadcrumbs[] = 'No seleccionado';
      }
    }
    //elimina el ultimo elemento si es "No seleccionado"
    //if (end($breadcrumbs) == 'No seleccionado') {
    //array_pop($breadcrumbs);
    //}
    // Puedes devolver un array o un string, aquí devuelvo string tipo "A > B > C"
    return implode(' > ', $breadcrumbs);
  }
}
