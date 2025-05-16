<form id="form-producto" method="POST"
    action="{{ $editMode ? route('productos.update', $producto->PCNT_id) : route('productos.store') }}">
    @csrf
    @if($editMode) @method('PUT') @endif
    <input type="hidden" name="PCNT_OPC_OpcionId" value="{{ $producto->PCNT_OPC_OpcionId }}">

    <div class="mb-2">
        <label>Producto</label>
        <select name="PCNT_PROD_id" id="PCNT_PROD_id" class="form-control selectpicker" data-live-search="true"
            required>
            @foreach($productosDisponibles as $id => $producto)
            <option value="{{ $id }}" data-nombre="{{ $producto['name'] }}" data-price="{{ $producto['price'] }}" {{
                old('PCNT_PROD_id', $productoSeleccionado->PCNT_PROD_id ?? '') == $id ? 'selected' : '' }}>
                {{ $producto['name'] }}
            </option>
            @endforeach
        </select>

    </div>
    <div class="mb-2" style="display: none;">
        <input type="text" id="PCNT_PROD_nombre" name="PCNT_PROD_nombre">
    </div>
    <div class="mb-2">
        <label>Ancho Base (en cm para calculo)</label>
        <input type="number" step="1" min="1" name="PCNT_base_ancho" class="form-control"
            value="{{ old('PCNT_base_ancho', $producto->PCNT_base_ancho ?? '1') }}" required>
    </div>

    <div class="mb-2">
        <label>Cantidad por Ancho Base</label>
        <input type="number" step="1" min="1" name="PCNT_base_cantidad" class="form-control"
            value="{{ old('PCNT_base_cantidad', $producto->PCNT_base_cantidad ?? '1') }}" required>
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
    $('.selectpicker').selectpicker('refresh');
    const selectedOption = $('select[name="PCNT_PROD_id"]').find(':selected');
    const nombre = selectedOption.data('nombre');
    const price = selectedOption.data('price');
    
    $('#PCNT_precio_unitario').val(price);
    $('#PCNT_PROD_nombre').val(nombre);

    $('select[name="PCNT_PROD_id"]').on('changed.bs.select', function () {
        const selectedOption = $(this).find(':selected');
        const nombre = selectedOption.data('nombre');
        const price = selectedOption.data('price');
        
        $('#PCNT_PROD_nombre').val(nombre);
        $('#PCNT_precio_unitario').val(price);
    });
</script>