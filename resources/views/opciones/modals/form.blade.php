<form id="form-opcion" method="POST" action="{{ $action }}">
    @csrf
    @if($editMode) @method('PUT') @endif

    <div class="mb-2">
        <label>Paso</label>
        <select name="OPC_PasoId" class="form-control" required>
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
        <label>Opción Padre</label>
        <select name="OPC_OpcionPadreId" class="form-control">
            <option value="">— Ninguno —</option>
            @foreach($opcionesPadre as $id => $val)
            <option value="{{ $id }}" {{ old('OPC_OpcionPadreId', $opcion->OPC_OpcionPadreId ?? '') == $id ? 'selected'
                : '' }}>
                {{ $val }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="mb-2">
        <label>Imagen</label>
        <input type="file" name="OPC_Imagen" class="form-control @error('OPC_Imagen') is-invalid @enderror">
        @if(isset($opcion->OPC_Imagen))
        <img src="{{ asset('images/cotizador/' . $opcion->OPC_Imagen) }}" alt="Imagen actual"
            style="max-width: 100px; max-height: 100px;">
        <div class="form-check">
            <input type="checkbox" name="eliminar_imagen" class="form-check-input" id="eliminar_imagen" value="1">
            <label class="form-check-label" for="eliminar_imagen">Eliminar Imagen</label>
        </div>
        @endif
        @error('OPC_Imagen')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-check mb-2">
        <input type="checkbox" name="OPC_EsDefault" class="form-check-input"
            value="{{ old($opcion->OPC_EsDefault !== false) ? 1 : 0 }}" {{ old('OPC_EsDefault', $opcion->OPC_EsDefault
        ?? false) ?
        'checked' : '' }}>
        <label class="form-check-label">¿Es el valor Default?</label>
    </div>

    <div class="form-check mb-2">
        <input type="checkbox" name="OPC_Activo" class="form-check-input"
            value="{{ ($opcion->OPC_Activo !== false) ? 1 : 0 }}" {{ old('OPC_Activo', $opcion->OPC_Activo ?? false) ?
        'checked' :
        '' }}>
        <label class="form-check-label">¿Activo?</label>
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
</form>