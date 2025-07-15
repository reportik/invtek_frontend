@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Tipo de Producto')

@section('content')

<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">

<div class="container text-center" style="max-width: 700px;">
    <div style="display: flex; align-items: center; justify-content: center; margin: 20px 0;">
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
        <h2 class="titulo">
            Tipo de Producto
        </h2>
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
    </div>
    <form action="{{ route('guardarAvance') }}" method="POST">
        {{-- Tipo de Producto --}}
        <div class="mb-3 text-start col-md-8 col-sm-12" id="div_tipo_producto">
            @csrf
            @if(Auth::check() && Auth::user()->role_id == 1)
            <label for="tipo" class="form-label fw-bold text-uppercase">
                <a href="{{ route('opciones.show', 1) }}" target="_blank">TIPO DE PRODUCTO:</a>
            </label>
            @else
            <label for="tipo" class="form-label fw-bold text-uppercase">TIPO DE PRODUCTO:</label>
            @endif
            <select name="tipo" id="tipo" class="selectpicker form-control border-success" data-live-search="true"
                required>
                @foreach($tipo_producto as $tp)
                @if($loop->first)
                <option value="{{ $tp['id'] }}" selected>{{ $tp['valor'] }}</option>
                @else
                <option value="{{ $tp['id'] }}">{{ $tp['valor'] }}</option>
                @endif
                @endforeach
            </select>
            <div class="descripcionSeleccion" id="descripcionTipoProducto">
                {{ $descripcion_tipo_producto[old('tipo',
                array_key_first($descripcion_tipo_producto))] ??
                '' }}
            </div>
        </div>
        {{-- Subproducto --}}
        <div class="mb-3 text-start col-md-6 col-sm-12" id="div_subproducto">
            @csrf
            @if(Auth::check() && Auth::user()->role_id == 1)
            <label for="subproducto" class="form-label fw-bold text-uppercase">
                <a href="{{ route('opciones.show', 1) }}" target="_blank">SUB PRODUCTO:</a>
            </label>
            @else
            <label for="subproducto" class="form-label fw-bold text-uppercase">SUB PRODUCTO:</label>
            @endif
            <div class="ml-3">
                @foreach ($subproducto as $sp)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="subproducto" value="{{ $sp['id'] }}"
                        id="radio{{ $sp['id'] }}" {{ $sp['a_selected']=='true' ? 'checked' : '' }}>
                    <label class="form-check-label titulo" for="radio{{ $sp['id'] }}">
                        {{ $sp['valor'] }}
                    </label>
                </div>
                @endforeach
            </div>

        </div>
        <input type="text" name="siguiente-vista" value="tipo_confeccion" hidden>

        {{-- Botón Siguiente --}}
        <div class="text-end">
            <a href="{{ route('inicio') }}" class="btn btn-outline-success fw-bold me-2">Regresar</a>
            <button type="submit" class="btn btn-outline-success fw-bold">Siguiente</button>
        </div>
    </form>


</div>

@endsection

@section('page-script')
<script>
    const descripcionesTipoProducto = @json($descripcion_tipo_producto);
    //console.log(descripcionesTipoProducto);
    
    $('#tipo').on('changed.bs.select', function () {
        const seleccion = $(this).val();
        $('#descripcionTipoProducto').text(descripcionesTipoProducto[seleccion] ?? '');
    });
    $(document).ready(function () {
        
        
        $('.selectpicker').selectpicker();
        const gvaloresSesion = @json(session()->all());
        let valoresSesion = gvaloresSesion['avance_temporal'] || {};

        // Solución: si es string, parsear
        if (typeof valoresSesion === 'string') {
            try {
                valoresSesion = JSON.parse(valoresSesion);
            } catch (e) {
                console.error('Error al parsear valoresSesion:', e);
                valoresSesion = {};
            }
        }
        asignarValoresDesdeSesion(valoresSesion);

        //definir el valor de siguiente-vista
        const siguienteVista = valoresSesion['siguiente-vista'] || '';
        if (siguienteVista === 'resumen') {
            $('input[name="siguiente-vista"]').val('resumen');
            $('.btn-success').text('Resumen');
        } else {
            
            $('.btn-success').text('Siguiente');
        }
    });
</script>
@endsection