<?php

namespace App\Http\Controllers;

use App\Http\Controllers\dashboard\Analytics;

use Illuminate\Http\Request;
use App\Models\PasoCotizador;
use App\Models\OpcionCotizador;
use App\Models\ProductoCantidad;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class OpcionCotizadorController extends Controller
{
  /**
   * Crea una opción en blanco para el paso siguiente, generando los OPC_S según la lógica de store
   */
  public function crearOpcionBlanco(Request $request)
  {
    $request->validate([
      'selector' => 'required|integer', //id del selector actual
      'opcion_id' => 'required|integer', //id de la opcion seleccionada
      'paso_id' => 'required|integer', //id del paso siguiente
      'formula_tela' => 'nullable|string', //fórmula SQL para calcular cantidad de tela
    ]);
    //dd($request->all());
    // Recuperar avance de sesión
    $avance = Session::get('avance_temporal', '');
    $avance = json_decode($avance, true);

    $pasos = PasoCotizador::where('PAS_Activo', 1)
      ->where('PAS_Eliminado', 0)
      ->orderBy('PAS_Orden', 'asc')
      ->get();
    $pasoActual = $pasos->firstWhere('PAS_PasoId', $request->selector);
    $pasoSiguiente = $pasos->firstWhere('PAS_PasoId', $request->paso_id);
    if (!$pasoSiguiente) {
      return response()->json(['error' => 'Paso no encontrado'], 404);
    }

    // PRIMERO: Eliminar opciones siguientes con la misma ruta
    // Usar los OPC_S de la opción base, pero reemplazando el campo del paso actual con el ID de la opción
    $opcionBase = OpcionCotizador::where('OPC_OpcionId', $request->opcion_id)->first();
    
    if ($opcionBase) {
      $queryEliminar = OpcionCotizador::where('OPC_Eliminado', 0);
      
      // Agregar condiciones para todos los OPC_S ANTERIORES al paso actual (usar valores de la opción base)
      for ($i = 1; $i < $pasoActual->PAS_Orden; $i++) {
        $campoS = 'OPC_S' . (int) $i;
        $queryEliminar->where($campoS, $opcionBase->$campoS);
      }
      
      // Para el paso ACTUAL: usar el ID de la opción (NO el valor de $opcionBase que es 'T')
      // Esto es clave porque la opción 85 tiene OPC_S14='T', pero la opción 95 tiene OPC_S14='00085'
      $queryEliminar->where('OPC_S' . (int)$pasoActual->PAS_Orden, str_pad($request->opcion_id, 5, '0', STR_PAD_LEFT));
      
      // Filtrar opciones de pasos MAYORES al paso actual (eliminar todo lo que viene después)
      $queryEliminar->whereIn('OPC_PasoId', function($subquery) use ($pasoActual) {
        $subquery->select('PAS_PasoId')
                 ->from('RPT_PasosCotizador')
                 ->where('PAS_Orden', '>', (int) $pasoActual->PAS_Orden)
                 ->where('PAS_Eliminado', 0);
      });
      
      //ver consulta para debugging
      //dd($queryEliminar->toSql(), $queryEliminar->getBindings());

      // Eliminar las opciones siguientes
      $opcionesEliminadas = $queryEliminar->delete();
    }
    
    // SEGUNDO: Limpiar el avance de la sesión eliminando pasos posteriores al paso actual
    // Esto asegura que la sesión esté sincronizada con las opciones que acabamos de eliminar
    $avance = Analytics::limpiarAvancePosterior($avance, $pasoActual, $pasos);
    Session::put('avance_temporal', json_encode($avance));

    // Determinar el valor de OPC_Programacion según el tipo de paso
    $programacion = '';
    
    // Si es el paso de medidas (paso 6), usar las coordenadas del canvas
    if ($request->paso_id == 6) {
      $programacion = '{"inputAlto":{"x": 290,"y":155},"inputAncho":{"x":150,"y":20}}';
    }
    // Si es el paso de Resumen y se envió una fórmula de tela, guardarla
    elseif ($pasoSiguiente->PAS_Nombre === 'Resumen' && !empty($request->formula_tela)) {
      $programacion = $request->formula_tela;
    }

    $data = [
      'OPC_PasoId' => $request->paso_id,
      'OPC_ValorOpcion' => 'NUEVO',
      'OPC_Activo' => 1,
      'OPC_Eliminado' => 0,
      'OPC_Programacion' => $programacion,
    ];
    //dd($data);

    // Generar OPC_S para todos los pasos anteriores
    //dd($pasos);
    foreach ($pasos as $paso) {
      if ($paso->PAS_Orden <= $pasoSiguiente->PAS_Orden) {
        $orden = $paso->PAS_Orden;
        $htmlName = $paso->PAS_Html_name;
        if (isset($avance[$htmlName]) && is_numeric($avance[$htmlName]) && $avance[$htmlName] !== 'T') {
          $data['OPC_S' . (int)$orden] = str_pad($avance[$htmlName], 5, '0', STR_PAD_LEFT);
        }
      }
    }
    $data['OPC_S' . (int)$pasoActual->PAS_Orden] = str_pad($request->opcion_id, 5, '0', STR_PAD_LEFT);
   // dd((int)$pasoActual->PAS_Orden, $data);
    
    // ELIMINAR opción existente del paso siguiente con la misma combinación (antes de validar)
    // Esto permite cambiar libremente entre selectores (ej: de Resumen a otro y viceversa)
    $queryEliminarExistente = OpcionCotizador::where('OPC_Eliminado', 0);
    foreach ($data as $key => $value) {
      if (strpos($key, 'OPC_S') === 0 || in_array($key, ['OPC_ValorOpcion', 'OPC_PasoId'])) {
        $queryEliminarExistente->where($key, $value);
      }
    }
    $queryEliminarExistente->delete();
    
    // Crear la nueva opción (ya no es necesario validar duplicados porque los eliminamos arriba)
    $opcion = OpcionCotizador::create($data);
    //actualizar con el id de la opcion creada la misma opcion
    $data['OPC_S' . (int)$pasoSiguiente->PAS_Orden] = str_pad($opcion->OPC_OpcionId, 5, '0', STR_PAD_LEFT);
    $opcion->update($data);

    return response()->json(['success' => 'Opción en blanco creada correctamente.', 'opcion_id' => $opcion->OPC_OpcionId]);
  }

  /**
   * Actualiza la fórmula de cálculo de tela en una opción de Resumen
   */
  public function actualizarFormulaTela(Request $request)
  {
    $request->validate([
      'opcion_id' => 'required|integer',
      'formula_tela' => 'nullable|string',
    ]);

    $opcion = OpcionCotizador::where('OPC_OpcionId', $request->opcion_id)
      ->where('OPC_Eliminado', 0)
      ->first();

    if (!$opcion) {
      return response()->json(['error' => 'Opción no encontrada'], 404);
    }

    $opcion->OPC_Programacion = $request->formula_tela ?? '';
    $opcion->save();

    return response()->json([
      'success' => 'Fórmula actualizada correctamente.',
      'opcion_id' => $opcion->OPC_OpcionId
    ]);
  }

  /**
   * Actualiza la descripción personalizada de ruta e imagen en una opción de Resumen
   */
  public function actualizarDescripcionRuta(Request $request)
  {
    $request->validate([
      'opcion_id' => 'required|integer',
      'descripcion_ruta' => 'nullable|string',
      'imagen_resumen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $opcion = OpcionCotizador::where('OPC_OpcionId', $request->opcion_id)
      ->where('OPC_Eliminado', 0)
      ->first();

    if (!$opcion) {
      return response()->json(['error' => 'Opción no encontrada'], 404);
    }

    // Actualizar descripción
    $opcion->OPC_DescripcionRuta = $request->descripcion_ruta ?? '';
    
    // Manejar eliminación de imagen
    if ($request->has('eliminar_imagen') && $request->input('eliminar_imagen') == '1') {
      // Eliminar imagen anterior si existe
      if ($opcion->OPC_Imagen) {
        $oldImagePath = public_path('images/cotizador/') . $opcion->OPC_Imagen;
        if (File::exists($oldImagePath)) {
          File::delete($oldImagePath);
        }
        $opcion->OPC_Imagen = null;
      }
    }
    
    // Manejar nueva imagen
    if ($request->hasFile('imagen_resumen')) {
      // Eliminar imagen anterior si existe
      if ($opcion->OPC_Imagen) {
        $oldImagePath = public_path('images/cotizador/') . $opcion->OPC_Imagen;
        if (File::exists($oldImagePath)) {
          File::delete($oldImagePath);
        }
      }
      
      $image = $request->file('imagen_resumen');
      $filename = 'resumen_' . $opcion->OPC_OpcionId . '_' . time() . '.' . $image->getClientOriginalExtension();
      $image->move(public_path('images/cotizador'), $filename);
      $opcion->OPC_Imagen = $filename;
    }
    
    $opcion->save();

    return response()->json([
      'success' => 'Descripción e imagen actualizadas correctamente.',
      'opcion_id' => $opcion->OPC_OpcionId,
      'imagen' => $opcion->OPC_Imagen
    ]);
  }

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
    //dd($pasoActual);
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
      $campo = 'OPC_S' . (int)$j;
      if (isset($respondidos[$j])) {
        if (!is_numeric($respondidos[$j])) {
          $valor = 'T';
        } else {
          $valor = str_pad($respondidos[$j], 5, '0', STR_PAD_LEFT);
        }
        $query->where($campo, $valor);
      }
    }
    //dd($query->toSql(), $query->getBindings());
    $opcionesValidas = $query->orderBy('OPC_PasoId', 'asc')
      ->orderBy('OPC_OpcionId', 'asc')
      ->get();
      //dd($opcionesValidas,$pasoActual->PAS_Html_name);
    $tempOpcionId = $avance[$pasoActual->PAS_Html_name];
    $count = $opcionesValidas->count();
    $data = $opcionesValidas->map(function ($opcion) use ($pasoActual, $pasos, $avance, $count, $selector) {
      //se cambia el valor del avance para que sea el valor de la opcion y determinar el selector siguiente      
      $avance[$pasoActual->PAS_Html_name] = $opcion->OPC_OpcionId;
      // Determinar el selector siguiente usando Analytics, usando el id del selector actual como tope
      $selectorSiguienteArr = Analytics::getSelectorSiguienteConTope($avance, $selector);
      //dd($selectorSiguienteArr);
      $selectorSiguiente = '';

      $colocar_btnEliminar = true;
      $selectorSiguienteId = null;
      
      // Verificar si hay selector siguiente
      if (!isset($selectorSiguienteArr['mensaje']) && isset($selectorSiguienteArr['selector'])) {
        $selectorSiguienteTexto = $selectorSiguienteArr['selector'];
        
        if ($count == 1 && $selectorSiguienteTexto != 'Resumen') { 
          $colocar_btnEliminar = false;
        }
        
        // Obtener el ID del paso siguiente si no es "Resumen"
        if ($selectorSiguienteTexto != 'Resumen') {
          $pasoSiguiente = $pasos->firstWhere('PAS_Nombre', $selectorSiguienteTexto);
          if ($pasoSiguiente) {
            $selectorSiguienteId = $pasoSiguiente->PAS_PasoId;
          }
        } else {
          $selectorSiguienteId = $selectorSiguienteArr['selector_id'];
        }

      }
      //dd($selectorSiguienteId);
      // Siempre renderizar selectpicker con pasos mayores al actual
      $actualOrden = $pasoActual->PAS_Orden;
      $html = '<div class="d-flex align-items-center gap-2">';
      $html .= '<select class="form-control selectpicker selector-siguiente" data-id="' . $opcion->OPC_OpcionId . '" data-opcion-id="' . $opcion->OPC_OpcionId . '">';
      $html .= '<option value="">Elegir...</option>';
      
      $esResumen = false;
      $formulaTela = '';
      $descripcionRuta = '';
      $imagenResumen = '';
      $opcionResumenId = null;
      
      //dd($pasos->pluck('PAS_Orden', 'PAS_Nombre'), $actualOrden);
      foreach ($pasos as $paso) {
        if ($paso->PAS_Orden > $actualOrden) {
          $selected = ($paso->PAS_PasoId == $selectorSiguienteId) ? 'selected' : '';
          $html .= '<option value="' . $paso->PAS_PasoId . '" ' . $selected . '>' . $paso->PAS_Nombre . '</option>';
          
          // Verificar si el selector siguiente es Resumen
          if ($selected && $paso->PAS_Nombre === 'Resumen') {
            $esResumen = true;
            // Buscar la opción Resumen para obtener la fórmula actual y descripción de ruta
            $opcionResumen = OpcionCotizador::where('OPC_PasoId', $paso->PAS_PasoId)
              ->where('OPC_S' . (int)$pasoActual->PAS_Orden, str_pad($opcion->OPC_OpcionId, 5, '0', STR_PAD_LEFT))
              ->where('OPC_Eliminado', 0)
              ->first();
            if ($opcionResumen) {
              $formulaTela = $opcionResumen->OPC_Programacion ?? '';
              $descripcionRuta = $opcionResumen->OPC_DescripcionRuta ?? '';
              $imagenResumen = $opcionResumen->OPC_Imagen ?? '';
              $opcionResumenId = $opcionResumen->OPC_OpcionId;
            }
          }
        }
      }
      $html .= '</select>';
      
      // Agregar botones de edición si es Resumen
      if ($esResumen) {
        $formulaEscaped = htmlspecialchars($formulaTela, ENT_QUOTES, 'UTF-8');
        $descripcionEscaped = htmlspecialchars($descripcionRuta, ENT_QUOTES, 'UTF-8');
        
        // Botón para editar fórmula de tela
        $html .= '<button type="button" class="btn btn-sm btn-outline-info btn-editar-formula" 
          data-opcion-resumen-id="' . $opcionResumenId . '" 
          data-formula="' . $formulaEscaped . '" 
          title="Editar fórmula de tela">
          <i class="fa fa-calculator"></i>
        </button>';
        
        // Botón para editar descripción personalizada e imagen
        $html .= '<button type="button" class="btn btn-sm btn-outline-warning btn-editar-descripcion" 
          data-opcion-resumen-id="' . $opcionResumenId . '" 
          data-descripcion="' . $descripcionEscaped . '" 
          data-imagen="' . htmlspecialchars($imagenResumen, ENT_QUOTES, 'UTF-8') . '"
          title="Editar descripción e imagen">
          <i class="fa fa-file-alt"></i>
        </button>';
      }
      
      $html .= '</div>';
      $selectorSiguiente = $html;

      //dd($selectorSiguienteId, $selectorSiguiente, $pasoActual->PAS_PasoId);
      // Construir texto de activo con indicador de default (solo si está activa)
      $activoTexto = $opcion->OPC_Activo ? 'Sí' : 'No';
      if ($opcion->OPC_Activo && $opcion->OPC_EsDefault) {
        $activoTexto .= ' - <span class="badge bg-primary">Default</span>';
      }
      
      return [
        'selector_padre' =>  '',
        'valor_padre' => '',
        'selector' => $pasoActual->PAS_Nombre,
        'valor' => $opcion->OPC_OpcionId . ' - ' . $opcion->OPC_ValorOpcion,
        'activo' => $activoTexto,
        'imagen' => $opcion->OPC_Imagen,
        'acciones' => view('opciones.partials.acciones', compact('opcion', 'colocar_btnEliminar'))->render(),
        //renderizar el selector siguiente
        'selector_siguiente' => $selectorSiguiente,
        'id' => $opcion->OPC_OpcionId,
        'selector_id' => $pasoActual->PAS_PasoId,
      ];
    });

    $avance[$pasoActual->PAS_Html_name] = $tempOpcionId;
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

      // Construir texto de activo con indicador de default (solo si está activa)
      $activoTexto = $opcion->OPC_Activo ? 'Sí' : 'No';
      if ($opcion->OPC_Activo && $opcion->OPC_EsDefault) {
        $activoTexto .= ' - <span class="badge bg-primary">Default</span>';
      }
      
      return [
        'selector_padre' =>  $selectorPadre,
        'valor_padre' => $valorPadre,
        'selector' => $selector,
        'valor' => $opcion->OPC_ValorOpcion,
        'activo' => $activoTexto,
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

    // Si la opción es default, quitar el default de las demás opciones del mismo paso
    
    if ($data['OPC_EsDefault'] == 1) {
      // Quitar default de las demás opciones del mismo paso Y misma combinación de OPC_S
      $queryDefault = OpcionCotizador::where('OPC_PasoId', $data['OPC_PasoId'])
        ->where('OPC_OpcionId', '!=', $opcion->OPC_OpcionId)
        ->where('OPC_Eliminado', 0);
      
      // Agregar filtros por campos OPC_S para limitar a la misma combinación/ruta
      foreach ($opc_s_fields as $key => $value) {
        $queryDefault->where($key, $value);
      }
      
      $queryDefault->update(['OPC_EsDefault' => 0]);
    }

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
        $dataProducto = [
          'PCNT_OPC_OpcionId' => $opcionId,
          'PCNT_PROD_id' => $item['id'],
          'PCNT_PROD_nombre' => $item['name'],
          'PCNT_base_ancho' => 1,
          'PCNT_base_cantidad' => 1,
          'PCNT_precio_unitario' => isset($item['price']) ? $item['price'] : 0.0,
          'PCNT_atributos' => isset($item['attributes']) ? $item['attributes'] : null, // Guardar atributos del producto
          'PCNT_base_medida' => 'ANCHO',
        ];
        //si no existe el producto en la base de datos lo crea
        $producto = ProductoCantidad::where('PCNT_PROD_nombre', $item['name'])
          ->where('PCNT_OPC_OpcionId', $opcionId)->first();
        if (is_null($producto)) {
          $producto = ProductoCantidad::create($dataProducto);
        } else {
          $producto->update($dataProducto);
        }
      }
    }
    //200
    return response()->json(['success' => 'Opción creada correctamente.'], 200);
  }
  public function createProduct($nombreProducto, $opcionId)
  {
    $response = Http::get("http://127.0.0.1:3036/item/{$nombreProducto}");
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
        $dataProducto = [
          'PCNT_OPC_OpcionId' => $opcionId,
          'PCNT_PROD_id' => $item['id'],
          'PCNT_PROD_nombre' => $item['name'],
          'PCNT_base_ancho' => 1,
          'PCNT_base_cantidad' => 1,
          'PCNT_precio_unitario' => isset($item['price']) ? $item['price'] : 0.0,
          'PCNT_atributos' => isset($item['attributes']) ? $item['attributes'] : null, // Guardar atributos del producto
          'PCNT_base_medida' => 'ANCHO',
        ];
        //si no existe el producto en la base de datos lo crea
        $producto = ProductoCantidad::where('PCNT_PROD_nombre', $item['name'])
          ->where('PCNT_OPC_OpcionId', $opcionId)->first();
        if (is_null($producto)) {
          $producto = ProductoCantidad::create($dataProducto);
        } else {
          $producto->update($dataProducto);
        }
      }
    }
    
    // Si la opción es default, quitar el default de las demás opciones del mismo paso Y misma combinación de OPC_S
    if ($data['OPC_EsDefault'] == 1) {
      $queryDefault = OpcionCotizador::where('OPC_PasoId', $opcion->OPC_PasoId)
        ->where('OPC_OpcionId', '!=', $opcion->OPC_OpcionId)
        ->where('OPC_Eliminado', 0);
      
      // Agregar filtros por campos OPC_S de la opción actual para limitar a la misma combinación/ruta
      for ($i = 0; $i <= 21; $i++) {
        $field = 'OPC_S' . $i;
        if (!empty($opcion->$field) && $opcion->$field !== 'T') {
          $queryDefault->where($field, $opcion->$field);
        }
      }
      
      $queryDefault->update(['OPC_EsDefault' => 0]);
    }
    
    $opcion->update($data);
    return response()->json(['success' => 'Opción actualizada correctamente.'], 200);
  }

  /**
   * Obtiene el preview de las opciones y productos que se eliminarán al borrar una opción
   */
  public function previewEliminar($id)
  {
    $opcion = OpcionCotizador::where('OPC_OpcionId', $id)->first();
    
    if (!$opcion) {
      return response()->json(['error' => 'Opción no encontrada'], 404);
    }

    // Obtener el paso de la opción actual
    $pasoActual = PasoCotizador::where('PAS_PasoId', $opcion->OPC_PasoId)
      ->where('PAS_Eliminado', 0)
      ->first();

    if (!$pasoActual) {
      return response()->json(['error' => 'Paso no encontrado'], 404);
    }

    // Construir query para obtener opciones siguientes con la misma ruta
    $queryOpciones = OpcionCotizador::where('OPC_Eliminado', 0);
    
    // Agregar condiciones para todos los OPC_S hasta el paso actual
    for ($i = 1; $i <= $pasoActual->PAS_Orden; $i++) {
      $campoS = 'OPC_S' . $i;
      $queryOpciones->where($campoS, $opcion->$campoS);
    }
    
    // Filtrar opciones de pasos MAYORES al paso actual (opciones posteriores)
    $queryOpciones->whereIn('OPC_PasoId', function($subquery) use ($pasoActual) {
      $subquery->select('PAS_PasoId')
               ->from('RPT_PasosCotizador')
               ->where('PAS_Orden', '>', $pasoActual->PAS_Orden)
               ->where('PAS_Eliminado', 0);
    });

    // Obtener las opciones con sus relaciones
    $opcionesPosteriores = $queryOpciones->with(['paso', 'productos'])->get();

    // Preparar datos de opciones para la respuesta
    $opcionesData = $opcionesPosteriores->map(function($opc) {
      return [
        'id' => $opc->OPC_OpcionId,
        'paso' => $opc->paso ? $opc->paso->PAS_Nombre : 'Sin paso',
        'valor' => $opc->OPC_ValorOpcion,
        'activo' => $opc->OPC_Activo ? 'Sí' : 'No',
      ];
    });

    // Recopilar todos los productos de las opciones posteriores
    $productosData = [];
    foreach ($opcionesPosteriores as $opc) {
      foreach ($opc->productos as $producto) {
        $productosData[] = [
          'id' => $producto->PCNT_id,
          'nombre' => $producto->PCNT_PROD_nombre,
          'opcion_id' => $opc->OPC_OpcionId,
          'opcion_valor' => $opc->OPC_ValorOpcion,
          'paso' => $opc->paso ? $opc->paso->PAS_Nombre : 'Sin paso',
          'cantidad' => $producto->PCNT_base_cantidad,
          'precio' => $producto->PCNT_precio_unitario,
        ];
      }
    }

    // También obtener los productos de la opción actual
    $productosOpcionActual = [];
    foreach ($opcion->productos as $producto) {
      $productosOpcionActual[] = [
        'id' => $producto->PCNT_id,
        'nombre' => $producto->PCNT_PROD_nombre,
        'opcion_id' => $opcion->OPC_OpcionId,
        'opcion_valor' => $opcion->OPC_ValorOpcion,
        'paso' => $pasoActual->PAS_Nombre,
        'cantidad' => $producto->PCNT_base_cantidad,
        'precio' => $producto->PCNT_precio_unitario,
      ];
    }

    return response()->json([
      'opcion_actual' => [
        'id' => $opcion->OPC_OpcionId,
        'paso' => $pasoActual->PAS_Nombre,
        'valor' => $opcion->OPC_ValorOpcion,
      ],
      'opciones_posteriores' => $opcionesData,
      'productos_opcion_actual' => $productosOpcionActual,
      'productos_posteriores' => $productosData,
      'total_opciones' => count($opcionesData),
      'total_productos' => count($productosData) + count($productosOpcionActual),
    ]);
  }

  public function destroy($id)
  {
    //solo actualiza el campo OPC_Eliminado
    $opcion = OpcionCotizador::where('OPC_OpcionId', $id)->first();
    
    if (!$opcion) {
      return response()->json(['error' => 'Opción no encontrada'], 404);
    }

    // Obtener el paso de la opción actual
    $pasoActual = PasoCotizador::where('PAS_PasoId', $opcion->OPC_PasoId)
      ->where('PAS_Eliminado', 0)
      ->first();

    if ($pasoActual) {
      // Eliminar opciones posteriores con la misma ruta
      $queryEliminar = OpcionCotizador::where('OPC_Eliminado', 0);
      
      // Agregar condiciones para todos los OPC_S hasta el paso actual
      for ($i = 1; $i <= $pasoActual->PAS_Orden; $i++) {
        $campoS = 'OPC_S' . $i;
        $queryEliminar->where($campoS, $opcion->$campoS);
      }
      
      // Filtrar opciones de pasos MAYORES al paso actual
      $queryEliminar->whereIn('OPC_PasoId', function($subquery) use ($pasoActual) {
        $subquery->select('PAS_PasoId')
                 ->from('RPT_PasosCotizador')
                 ->where('PAS_Orden', '>', $pasoActual->PAS_Orden)
                 ->where('PAS_Eliminado', 0);
      });

      // Primero eliminar los productos asociados a las opciones posteriores
      $opcionesPosterioresIds = $queryEliminar->pluck('OPC_OpcionId')->toArray();
      if (!empty($opcionesPosterioresIds)) {
        ProductoCantidad::whereIn('PCNT_OPC_OpcionId', $opcionesPosterioresIds)->delete();
      }

      // Eliminar las opciones posteriores
      $queryEliminar->delete();
    }

    // Eliminar los productos de la opción actual
    ProductoCantidad::where('PCNT_OPC_OpcionId', $id)->delete();
    
    // Eliminar la opción actual
    $opcion->delete();
    
    return response()->json(['success' => 'Opción y sus dependencias eliminadas correctamente.']);
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

    // 2. Obtener el paso actual (el selector que se está visualizando)
    $pasoActual = \App\Models\PasoCotizador::where('PAS_PasoId', $id)
      ->where('PAS_Activo', 1)
      ->where('PAS_Eliminado', 0)
      ->first();

    if (!$pasoActual) {
      return 'RUTA SELECCIONADA > Selector no encontrado';
    }

    // 3. Obtener todos los pasos activos y ordenados HASTA el paso actual
    $pasos = \App\Models\PasoCotizador::where('PAS_Activo', 1)
      ->where('PAS_Eliminado', 0)
      ->where('PAS_Orden', '<=', $pasoActual->PAS_Orden) // Solo hasta el paso actual
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
          // Si es el paso actual, resaltarlo
          if ($paso->PAS_PasoId == $id) {
            $breadcrumbs[] = '【' . $opcion->OPC_ValorOpcion . '】';
          } else {
            $breadcrumbs[] = $opcion->OPC_ValorOpcion;
          }
        }
      }
    }

    // Puedes devolver un array o un string, aquí devuelvo string tipo "A > B > C"
    return implode(' > ', $breadcrumbs);
  }
}
