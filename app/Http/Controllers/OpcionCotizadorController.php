<?php

namespace App\Http\Controllers;

use App\Models\OpcionCotizador;
use App\Models\PasoCotizador;
use Illuminate\Http\Request;
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

            return [
                'selector_padre' => $selectorPadre,
                'valor_padre' => $valorPadre,
                'selector' => $opcion->paso->PAS_Nombre ?? '—',
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

        // Verifica si el valor de la opción ya existe
        $existeOpcion = OpcionCotizador::where('OPC_ValorOpcion', $request->OPC_ValorOpcion)
            ->where('OPC_PasoId', $request->OPC_PasoId)
            ->where('OPC_Eliminado', 0)
            ->exists();

        if ($existeOpcion) {
            return redirect()->back()->with('error', 'La opción ya existe para este paso.');
        }

        $data = $request->except('OPC_Imagen'); // Excluye la imagen del array

        if ($request->hasFile('OPC_Imagen')) {
            $image = $request->file('OPC_Imagen');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/cotizador'), $filename);  // Guarda la imagen en public/images/cotizador
            $data['OPC_Imagen'] = $filename;
        }

        $data['OPC_EsDefault'] = $request->has('OPC_EsDefault') ? 1 : 0;

        OpcionCotizador::create($data);

        return redirect()->route('opciones.index')->with('success', 'Opción creada correctamente.');
    }

    public function update(Request $request, $id)
    {
        $opcion = OpcionCotizador::findOrFail($id);

        $request->validate([
            'OPC_PasoId' => 'required|integer',
            'OPC_ValorOpcion' => 'required|string|max:100',
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
        // Manejo de la imagen
        if ($request->hasFile('OPC_Imagen')) {
            // Elimina la imagen anterior si existe
            if ($opcion->OPC_Imagen) {
                $oldImagePath = public_path('images/cotizador/') . $opcion->OPC_Imagen;
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
            }

            $image = $request->file('OPC_Imagen');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/cotizador'), $filename);
            $data['OPC_Imagen'] = $filename;
        }

        $data['OPC_EsDefault'] = $request->has('OPC_EsDefault') ? 1 : 0;

        $opcion->update($data);

        return redirect()->route('opciones.index')->with('success', 'Opción actualizada correctamente.');
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
