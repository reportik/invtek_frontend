<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PasoCotizador;
use App\Models\OpcionCotizador;
use App\Models\ProductoCantidad;
use Illuminate\Support\Facades\File;

class OpcionCotizadorController extends Controller
{
    public function index($id = null)
    {
        $pasos = PasoCotizador::where('PAS_Eliminado', 0)->pluck('PAS_Nombre', 'PAS_PasoId');
        //$opcionesPadre = OpcionCotizador::pluck('OPC_ValorOpcion', 'OPC_OpcionId');
        //dd($pasos);

        return view('opciones.index', compact('pasos', 'id'));
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
        $existeOpcion = OpcionCotizador::where('OPC_ValorOpcion', $request->OPC_ValorOpcion)
            ->where('OPC_PasoId', $request->OPC_PasoId)
            ->where('OPC_OpcionPadreId', $request->OPC_OpcionPadreId)
            ->where('OPC_Eliminado', 0)
            ->exists();

        if ($existeOpcion) {
            //400
            return response()->json(['error' => 'La opción ya existe para este paso.'], 400);
        }
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
        if ($data['OPC_EsProducto'] == 1) {
            $producto = self::createProduct($data['OPC_ValorOpcion'], $data['OPC_OpcionId']);
            if (is_null($producto)) {
                return response()->json(['error' => 'Producto no encontrado en Odoo. Verifique el nombre del producto o desmarque la opción "Es Producto"'], 500);
            }
        }
        OpcionCotizador::create($data);
        //200
        return response()->json(['success' => 'Opción creada correctamente.'], 200);
    }
    public function createProduct($nombreProducto, $opcionId)
    {
        $response = Http::get("http://itekniaapp.serveftp.com:3036/item/{$nombreProducto}");
        $json = $response->json();

        // Validar estructura de la respuesta
        if (!isset($json['product']) || !isset($json['template'])) {
            return null;
        }

        $product = $json['product'];

        $data = [
            'PCNT_OPC_OpcionId' => $opcionId,
            'PCNT_PROD_id' => $product['id'],
            'PCNT_PROD_nombre' => $product['name'],
            'PCNT_base_ancho' => 100,
            'PCNT_base_cantidad' => 1,
            'PCNT_precio_unitario' => isset($product['list_price']) ? $product['list_price'] : 0.0
        ];

        ProductoCantidad::create($data);
        return $product;
    }
    public function update(Request $request, $id)
    {
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
        if ($data['OPC_EsProducto'] == 1) {
            $producto = self::createProduct($data['OPC_ValorOpcion'], $data['OPC_OpcionId']);
            if (is_null($producto)) {
                return response()->json(['error' => 'Producto no encontrado en Odoo. Verifique el nombre del producto o desmarque la opción "Es Producto"'], 500);
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
        return redirect()->route('opciones.index')->with('success', 'Opción eliminada.');
    }

    // show
    public function show($id)
    {
        $pasos = PasoCotizador::where('PAS_Eliminado', 0)->pluck('PAS_Nombre', 'PAS_PasoId');
        return view('opciones.index', compact('pasos', 'id'));
    }
}
