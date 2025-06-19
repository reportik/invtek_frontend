<form id="form-opcion" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if($editMode) @method('PUT') @endif
    @if($editMode) <h4>Editar Opción</h4> @else <h4>Nueva Opción</h4> @endif
    
    <div class="row">
        <!-- Selector Padre: Paso -->
        <div class="col-md-6 mb-2">
            <label>Selector Padre (Si depende de otra opción)</label>
            <select id="selector_padre_paso" class="form-control selectpicker" data-live-search="true">
                <option value="">— Selecciona un paso —</option>
                @foreach($pasos as $id => $nombre)
                <option value="{{ $id }}">
                    {{ $nombre }}
                </option>
                @endforeach
            </select>
        </div>
        <!-- Valor Padre: Opción asociada al paso -->
        <div class="col-md-6 mb-2">
            <label>Valor Padre</label>
            <select id="selector_valor_padre" name="OPC_OpcionPadreId" class="form-control selectpicker" data-live-search="true">
                <option value="">— Selecciona un valor —</option>
                {{-- Opciones se llenan dinámicamente vía JS --}}
            </select>
        </div>
    </div>
    
    {{-- selector selectpicker --}}
    <div class="mb-2">
        <label>Selector Opción</label>
        <select id="selector" name="OPC_PasoId" class="form-control selectpicker" data-live-search="true" required>
            @foreach($pasos as $id => $nombre)
            <option value="{{ $id }}" {{ old('OPC_PasoId', $opcion->OPC_PasoId ?? '') == $id ? 'selected' : '' }}>
                {{ $nombre }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="mb-2">
        <label>Valor Opción</label>
        <input type="text" name="OPC_ValorOpcion" class="form-control"
            value="{{ old('OPC_ValorOpcion', $opcion->OPC_ValorOpcion ?? '') }}" required>
    </div>

    <div class="mb-2">
        <label>Descripción Opción</label>
        <textarea name="OPC_Descripcion" class="form-control" rows="2"
            placeholder="Descripción de la opción">{{ old('OPC_Descripcion', $opcion->OPC_Descripcion ?? '') }}</textarea>
    </div>

    

    <div class="mb-2">
        <label>Imagen</label>
        <input type="file" name="OPC_Imagen" class="form-control @error('OPC_Imagen') is-invalid @enderror">
        @if(isset($opcion->OPC_Imagen))
            @if($opcion->OPC_PasoId == 22)
            <img src="{{ asset('images/telas/' . $opcion->OPC_Imagen) }}" alt="Imagen actual"
                style="max-width: 100px; max-height: 100px;">
            @else
            <img src="{{ asset('images/cotizador/' . $opcion->OPC_Imagen) }}" alt="Imagen actual"
                style="max-width: 100px; max-height: 100px;">
            @endif
            <div class="form-check mb-2 ml-4 mt-4">
                <input type="checkbox" name="eliminar_imagen" class="form-check-input" id="eliminar_imagen" value="1">
                <label class="form-check-label" for="eliminar_imagen">Eliminar Imagen</label>
            </div>
        @endif
        @error('OPC_Imagen')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-check mb-2 ml-4 mt-4">
        <input type="checkbox" name="OPC_EsDefault" class="form-check-input" value="1" {{ old('OPC_EsDefault',
            $opcion->OPC_EsDefault ?? false) ? 'checked' : '' }}>
        <label class="form-check-label">¿Es el valor Default?</label>
    </div>

    <div class="form-check mb-2 ml-4">
        <input type="checkbox" name="OPC_EsProducto" class="form-check-input" value="1" {{ old('OPC_EsProducto',
            $opcion->OPC_EsProducto ?? false) ? 'checked' : '' }}>
        <label class="form-check-label">¿Es Producto?</label>
    </div>
    <div class="form-check mb-2 ml-4">
        <input type="checkbox" name="OPC_Activo" class="form-check-input" value="1" {{ old('OPC_Activo',
            $opcion->OPC_Activo ?? false) ? 'checked' : '' }}>
        <label class="form-check-label">¿Activo?</label>
    </div>

    <div class="text-end">
        @if($editMode)
            <button type="button" id="btn-duplicar" class="btn btn-warning mb-3 me-2">Duplicar</button>
        @endif
        <button type="button" id="btn-cancelar" class="btn btn-secondary mb-3 me-2" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary mb-3">Guardar</button>
    </div>
</form>

<script>
    $('.selectpicker').selectpicker('refresh');
    var opcionesPorPaso = @json($opcionesPorPaso);
    // Evento para cargar las opciones del valor padre según el paso padre seleccionado
    $('#selector_padre_paso').on('changed.bs.select', function() {
        var pasoId = $(this).val();
        var $valorPadre = $('#selector_valor_padre');
        $valorPadre.empty().append('<option value="">— Selecciona un valor —</option>');
        if (opcionesPorPaso[pasoId]) {
            opcionesPorPaso[pasoId].forEach(function(opcion) {
                $valorPadre.append('<option value="' + opcion.id + '">' + opcion.valor + '</option>');
            });
        }
        $valorPadre.selectpicker('refresh');
    });
</script>
@if ($editMode && $id_padre_paso !== '' && $id_padre_valor !== '' && $id_padre_paso !== null && $id_padre_valor !== null)
    <script>
        // Setear el selector padre y valor padre
        $('#selector_padre_paso').val({{ $id_padre_paso }}).selectpicker('refresh');
        //trigger changed para cargar las opciones del valor padre
        $('#selector_padre_paso').trigger('changed.bs.select');
        // Setear el selector valor padre
        $('#selector_valor_padre').val({{ $id_padre_valor }}).selectpicker('refresh');
    </script>
@endif

@if ($editMode)
    <script>
    $('#btn-duplicar').on('click', function() {
        
        // Cambia el título
        $('h4:contains("Editar Opción")').text('Duplicar Opción');
        // Cambia el modo: elimina el input _method PUT
        $('#form-opcion').find('input[name="_method"]').remove();
        // Cambia la acción del formulario al de crear
        $('#form-opcion').attr('action', '{{ route('opciones.store') }}');
        // Cambia el botón submit si tienes texto "Actualizar" por "Crear"
        $('#form-opcion button[type="submit"]').text('Crear');
        // Quita el botón duplicar para evitar doble click
        $(this).remove();
        //limpia Imagen
        $('#form-opcion input[name="OPC_Imagen"]').val('');
        //limpia eliminar_imagen
        $('#form-opcion input[name="eliminar_imagen"]').prop('checked', false);
        //img tag
        $('#form-opcion img').remove();
    });
    </script>
@endif
