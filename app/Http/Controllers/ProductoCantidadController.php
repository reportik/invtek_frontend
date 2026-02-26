<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OpcionCotizador;
use App\Models\ProductoCantidad;
use Illuminate\Support\Facades\Http;

class ProductoCantidadController extends Controller
{
    public function getProductosAjax(Request $request, $opcionId)
    {
        $productos = ProductoCantidad::where('PCNT_OPC_OpcionId', $opcionId)
            ->when($request->input('search.value'), function ($query) use ($request) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->where('PCNT_PROD_nombre', 'like', "%$search%")
                        ->orWhere('PCNT_id', 'like', "%$search%");
                });
            })
            ->orderBy('PCNT_id', 'asc')
            ->get();

        $data = $productos->map(function ($producto) {
            return [
                'PCNT_id' => $producto->PCNT_id,
                'PCNT_PROD_nombre' => $producto->PCNT_PROD_id . ' - ' . $producto->PCNT_PROD_nombre,
                'PCNT_base_ancho' => $producto->PCNT_base_ancho,
                'PCNT_base_cantidad' => $producto->PCNT_base_cantidad,
                'PCNT_precio_unitario' => $producto->PCNT_precio_unitario ?? '—',
                'acciones' => view('productos.partials.acciones', compact('producto'))->render(),
            ];
        });

        return response()->json(['data' => $data]);
    }
    public function getProductosByMaterial($materialId)
    {
        $programacion_array = [
            'path_filter' => 'TELAS/CORTINAS/PLEGABLES/DECORATIVAS'
        ];
        $response = Http::post("http://localhost:3036/products/by-category", $programacion_array);
        $json = $response->json();
        dd($json);
    }

    /**
     * Sincroniza productos desde el endpoint externo (FastAPI) incluyendo sus atributos
     * Ejemplo de respuesta del endpoint:
     * [
     *   {
     *     "id": 6423,
     *     "name": "BRUNO COEL BLACKOUT BBAR COLOR WHITE",
     *     "price": 200.0,
     *     "attributes": [
     *       {
     *         "attribute_id": 22,
     *         "attribute_name": "ANCHO",
     *         "values": [{"id": 110, "name": "2.8 m"}]
     *       }
     *     ]
     *   }
     * ]
     */
    public function syncProductosFromExternalAPI($opcionId, $pathFilter)
    {
        try {
            // Llamar al endpoint externo
            $response = Http::post("http://localhost:3036/products/by-category", [
                'path_filter' => $pathFilter
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al obtener productos del servicio externo'
                ], 500);
            }

            $productosExternos = $response->json();
            $productosSincronizados = [];

            foreach ($productosExternos as $prodExterno) {
                // Buscar o crear el producto en la base de datos local
                $producto = ProductoCantidad::updateOrCreate(
                    [
                        'PCNT_OPC_OpcionId' => $opcionId,
                        'PCNT_PROD_id' => $prodExterno['id']
                    ],
                    [
                        'PCNT_PROD_nombre' => $prodExterno['name'] ?? '',
                        'PCNT_precio_unitario' => $prodExterno['price'] ?? 0,
                        'PCNT_atributos' => $prodExterno['attributes'] ?? [], // El cast automáticamente convierte a JSON
                        'PCNT_base_ancho' => 100, // Valor por defecto, ajustar según sea necesario
                        'PCNT_base_cantidad' => 1, // Valor por defecto, ajustar según sea necesario
                    ]
                );

                $productosSincronizados[] = $producto;
            }

            return response()->json([
                'success' => true,
                'message' => 'Productos sincronizados correctamente',
                'productos_sincronizados' => count($productosSincronizados),
                'data' => $productosSincronizados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al sincronizar productos: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getProductosByCategory($materialId)
    {
        $productos = ProductoCantidad::where('PCNT_OPC_OpcionId', $materialId)->get();
        
        // Los atributos ya se deserializan automáticamente gracias al cast en el modelo
        // No es necesario hacer json_decode manualmente
        
        return response()->json(['data' => $productos]);
    }

    public function show($id)
    {
        $opcion = OpcionCotizador::findOrFail($id);
        return view('productos.show', compact('opcion'));
    }

    public function getByOpcion($opcionId)
    {
        $productos = ProductoCantidad::where('PCNT_OPC_OpcionId', $opcionId)->get();
        return response()->json(['data' => $productos]);
    }
    public function getOdooProductos()
    {
        $productos = Http::get("http://127.0.0.1:3036/products/active/sellable");
        $productos = collect($productos->json())->toArray();
        $formateado = [];
        foreach ($productos as $producto) {
            $formateado[$producto['id']] = [
                'name' => $producto['name'],
                'price' => round($producto['list_price'], 2), // redondea a 2 decimales
                //'attributes' => $producto['attributes'],
            ];
        }
        return $formateado;
    }
    public function create(Request $request)
    {
        $producto = new ProductoCantidad();
        $producto->PCNT_OPC_OpcionId = $request->get('opcion_id');
        $productosDisponibles = self::getOdooProductos();
        //dd($productosDisponibles);

        return view('productos.modals.form', compact('producto', 'productosDisponibles'))->with('editMode', false);
    }

    public function edit($id)
    {
        $producto = ProductoCantidad::findOrFail($id);

        $productosDisponibles = self::getOdooProductos();

        return view('productos.modals.form', compact('producto', 'productosDisponibles'))->with('editMode', true);
    }

    public function store(Request $request)
    {
        //$response = Http::get("http://127.0.0.1:3036/product/{$producto->PROD_id}/price/{$price_list_id}");

        //$precio = $response->successful() ? $response->json()['pricelist_price'] : 0;

        // $productosConCantidadYPrecio[] = [
        //     'producto_id' => $producto->PROD_id,
        //     'nombre' => $producto->PROD_nombre,
        //     'cantidad' => $cantidad,
        //     'precio_unitario' => $precio,
        //     'precio_total' => $precio * $cantidad,
        // ];

        $data = $request->validate([
            'PCNT_OPC_OpcionId' => 'required|integer',
            'PCNT_PROD_id' => 'required|integer',
            'PCNT_PROD_nombre' => 'string|max:255',
            'PCNT_base_ancho' => 'required|numeric',
            'PCNT_base_medida' => 'required|string|in:ANCHO,ALTO,HOJA,FORMULA',
            'PCNT_base_cantidad' => 'nullable|numeric',
            'PCNT_precio_unitario' => 'required|numeric',
            'PCNT_formula' => 'nullable|string',
            //'PCNT_atributos' => 'nullable|array' // Permitir atributos como array
        ]);

        // Si PCNT_atributos viene como string JSON, no hacer nada (el cast del modelo lo maneja)
        // Si viene como array, el cast del modelo lo convertirá automáticamente a JSON

        ProductoCantidad::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $producto = ProductoCantidad::findOrFail($id);

        $data = $request->validate([
            'PCNT_PROD_id' => 'required|integer',
            'PCNT_PROD_nombre' => 'string|max:255',
            'PCNT_base_ancho' => 'required|numeric',
            'PCNT_base_medida' => 'required|string|in:ANCHO,ALTO,HOJA,FORMULA',
            'PCNT_base_cantidad' => 'nullable|numeric',
            'PCNT_precio_unitario' => 'required|numeric',
            'PCNT_formula' => 'nullable|string',
            //'PCNT_atributos' => 'nullable|array' // Permitir atributos como array
        ]);

        // El cast del modelo convierte automáticamente el array a JSON
        $producto->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        ProductoCantidad::destroy($id);
        return response()->json(['success' => true]);
    }
}
