<form id="form-producto" method="POST"
    action="{{ $editMode ? route('productos.update', $producto->PCNT_id) : route('productos.store') }}">
    @csrf
    @if($editMode) @method('PUT') @endif
    <input type="hidden" name="PCNT_OPC_OpcionId" value="{{ $producto->PCNT_OPC_OpcionId }}">

    <div class="mb-2">
        <label>Producto</label>
        <select name="PCNT_PROD_id" id="PCNT_PROD_id" class="form-control selectpicker" data-live-search="true"
            required>
            @foreach($productosDisponibles as $id => $productoItem)
            <option value="{{ $id }}" data-nombre="{{ $productoItem['name'] }}" data-price="{{ $productoItem['price'] }}" 
            data-atributos="{{ $productoItem['attributes'] }}"  
            {{ old('PCNT_PROD_id', $producto->PCNT_PROD_id ?? '') == $id ? 'selected' : '' }}>
                {{ $productoItem['name'] }}
            </option>
            @endforeach
        </select>

    </div>
    <div class="mb-2" style="display: none;">
        <input type="text" id="PCNT_PROD_nombre" name="PCNT_PROD_nombre">
    </div>
    <div class="mb-4 mt-4 d-flex align-items-center flex-row gap-2" style="flex-wrap: wrap;">
        <span>Por cada</span>
        <select name="PCNT_base_medida" id="PCNT_base_medida" class="form-control selectpicker" 
            style="width: 120px; display: inline-block;" required>
            <option value="ANCHO" {{ old('PCNT_base_medida', $producto->PCNT_base_medida ?? '') == 'ANCHO' ? 'selected' : '' }}>ANCHO</option>
            <option value="ALTO" {{ old('PCNT_base_medida', $producto->PCNT_base_medida ?? '') == 'ALTO' ? 'selected' : '' }}>ALTO</option>
            <option value="HOJA" {{ old('PCNT_base_medida', $producto->PCNT_base_medida ?? '') == 'HOJA' ? 'selected' : '' }}>HOJA</option>
            <option value="LIENZO" {{ old('PCNT_base_medida', $producto->PCNT_base_medida ?? '') == 'LIENZO' ? 'selected' : '' }}>LIENZO</option>
        </select>
        <span>se usará la cantidad de</span>
        <input type="number" step="1" min="1" name="PCNT_base_cantidad" class="form-control form-control-sm"
            style="width: 70px; display: inline-block;"
            value="{{ old('PCNT_base_cantidad', $producto->PCNT_base_cantidad ?? '1') }}" required>
        <span>pieza(s).</span>
    </div>

    <div class="mb-2">
        <label>Precio Unitario</label>
        <input type="number" step="0.01" id="PCNT_precio_unitario" name="PCNT_precio_unitario" class="form-control"
            @readonly(true) value="{{ old('PCNT_precio_unitario', $producto->PCNT_precio_unitario ?? '') }}" required>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-primary">Guardar</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        // Inicializar todos los selectpickers
        $('.selectpicker').selectpicker('refresh');
        
        // Si estamos en modo edición, cargar los valores iniciales
        @if($editMode)
            const selectedOption = $('select[name="PCNT_PROD_id"]').find(':selected');
            const nombre = selectedOption.data('nombre');
            const price = selectedOption.data('price');
            
            $('#PCNT_PROD_nombre').val(nombre);
            $('#PCNT_precio_unitario').val(price);
            
            // Refrescar el selectpicker del producto para asegurar que se vea la selección
            $('#PCNT_PROD_id').selectpicker('val', '{{ $producto->PCNT_PROD_id }}');
            
            // Refrescar el selectpicker de base_ancho para asegurar que se vea la selección
            $('#PCNT_base_ancho').selectpicker('val', '{{ $producto->PCNT_base_ancho ?? "ANCHO" }}');
            
            // Refrescar todos los selectpickers
            $('.selectpicker').selectpicker('refresh');
        @else
            // En modo creación, establecer valor por defecto
            $('#PCNT_base_ancho').selectpicker('val', 'ANCHO');
            $('.selectpicker').selectpicker('refresh');
        @endif
        
        // Evento cuando cambia la selección del producto
        $('select[name="PCNT_PROD_id"]').on('changed.bs.select', function () {
            selectedOption = $(this).find(':selected');
            nombre = selectedOption.data('nombre');
            price = selectedOption.data('price');
            
            $('#PCNT_PROD_nombre').val(nombre);
            $('#PCNT_precio_unitario').val(price);
        });
    });
</script>