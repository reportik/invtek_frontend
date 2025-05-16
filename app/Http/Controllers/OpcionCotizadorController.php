<?php

namespace App\Http\Controllers;

use App\Models\OpcionCotizador;
use App\Models\PasoCotizador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class OpcionCotizadorController extends Controller
{
    public function index()
    {
        return view('opciones.index');
    }

    public function getOpcionesAjax(Request $request)
    {
        // solo las que no esten eliminadas
        $opciones = OpcionCotizador::with(['paso', 'padre'])
            ->where('OPC_Eliminado', 0)
            ->when($request->input('search.value'), function ($query) use ($request) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->where('OPC_ValorOpcion', 'like', "%$search%")
                        ->orWhereHas('paso', function ($q) use ($search) {
                            $q->where('PAS_Nombre', 'like', "%$search%");
                        });
                });
            })
            ->orderBy('OPC_PasoId', 'asc')
            ->orderBy('OPC_OpcionId', 'asc')
            ->get();

        $data = $opciones->map(function ($opcion) {
            return [
                'OPC_OpcionId' => $opcion->OPC_OpcionId,
                'OPC_ValorOpcion' => $opcion->OPC_ValorOpcion,
                'paso' => $opcion->paso->PAS_Orden . ' - ' . $opcion->paso->PAS_Nombre ?? '—',
                'padre' => $opcion->padre->OPC_ValorOpcion ?? '—',
                'OPC_Activo' => $opcion->OPC_Activo ? 'Sí' : 'No',
                'OPC_Imagen' => $opcion->OPC_Imagen,  // Añade esto
                'acciones' => view('opciones.partials.acciones', compact('opcion'))->render(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $pasos = PasoCotizador::where('PAS_Eliminado', 0)->pluck('PAS_Nombre', 'PAS_PasoId');
        $opcionesPadre = OpcionCotizador::whereNull('OPC_OpcionPadreId')->pluck('OPC_ValorOpcion', 'OPC_OpcionId');
        $opcion = new OpcionCotizador();
        $editMode = false;
        $action = route('opciones.store');

        return view('opciones.modals.form', compact('pasos', 'opcionesPadre', 'opcion', 'editMode', 'action'));
    }

    public function edit($id)
    {
        $opcion = OpcionCotizador::findOrFail($id);
        $pasos = PasoCotizador::where('PAS_Eliminado', 0)->pluck('PAS_Nombre', 'PAS_PasoId');
        $opcionesPadre = OpcionCotizador::whereNull('OPC_OpcionPadreId')->pluck('OPC_ValorOpcion', 'OPC_OpcionId');
        $editMode = true;
        $action = route('opciones.update', $opcion);

        return view('opciones.modals.form', compact('pasos', 'opcionesPadre', 'opcion', 'editMode', 'action'));
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
        $opcion = OpcionCotizador::findOrFail($id);
        $productos = $opcion->productos; //relación definida en el modelo OpcionCotizador
        return view('opciones.show', compact('opcion', 'productos'));
    }
}
