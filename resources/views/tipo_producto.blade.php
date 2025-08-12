@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Tipo de Producto')

@section('content')

<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">
{{-- @php
$avance = Session::get('avance_temporal', []);
$avance = json_decode($avance, true);
dd($avance);
@endphp --}}
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
                <a href="{{ route('opciones.show', 11) }}" target="_blank">TIPO DE PRODUCTO:</a>
            </label>
            @else
            <label for="tipo" class="form-label fw-bold text-uppercase">TIPO DE PRODUCTO:</label>
            @endif
            <select name="tipo" id="tipo" class="selectpicker form-control border-success" data-live-search="true">
                {{-- @foreach($tipo_producto as $tp)
                @if($loop->first)
                <option value="{{ $tp['id'] }}" selected>{{ $tp['valor'] }}</option>
                @else
                <option value="{{ $tp['id'] }}">{{ $tp['valor'] }}</option>
                @endif
                @endforeach --}}
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
            <div class="ml-3" name="radio_subproducto">
                {{-- @foreach ($subproducto as $sp)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="subproducto" value="{{ $sp['id'] }}"
                        id="radio{{ $sp['id'] }}" {{ $sp['a_selected']=='true' ? 'checked' : '' }}>
                    <label class="form-check-label titulo" for="radio{{ $sp['id'] }}">
                        {{ $sp['valor'] }}
                    </label>
                </div>
                @endforeach --}}
            </div>

        </div>
        <input type="text" name="siguiente-vista" value="tipo_confeccion" hidden>
        <input type="text" name="actual-vista" value="tipo-producto" hidden>

        {{-- Botón Siguiente --}}
        <div class="text-end">
            <a href="#" name="anterior-vista" class="btn btn-outline-success fw-bold me-2">
                <i class="fas fa-arrow-left me-2"></i>Regresar
            </a>
            <button id="btnSiguiente" type="submit" class="btn btn-outline-success fw-bold">Siguiente</button>
        </div>
    </form>
    <input type="text" id="pantalla_ubicacion" name="pantalla_ubicacion" value="2" hidden>


</div>

@endsection

@section('page-script')
<script>
    const descripcionesTipoProducto = @json($descripcion_tipo_producto);
    //console.log(descripcionesTipoProducto);
    
    //console.log(selectores);
    
    $('#tipo').on('changed.bs.select', function () {
        const seleccion = $(this).val();
        $('#descripcionTipoProducto').text(descripcionesTipoProducto[seleccion] ?? '');        
        console.log('...................SELECCIONADO TIPO CON VALOR: ', seleccion);
        getSelectorSiguiente('tipo', seleccion);
       
    });

    //evento de radio subproducto
    $('div[name="radio_subproducto"]').on('change', function () {
        const seleccion = $('input[name="subproducto"]:checked').val();
        console.log('...................SELECCIONADO SUB PRODUCTO CON VALOR: ', seleccion);
        getSelectorSiguiente('subproducto', seleccion);
    });
    
    //trigger subproducto al terminar de cargar la pagina
    
    

    $(document).ready(function () {
        //ocultar selectores
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

        /*
        bloque
        */
        if (Object.keys(valoresSesion).length === 0 || valoresSesion === null) {
        $(`#btnSiguiente`).attr('disabled', true);
        }else{
        $(`#btnSiguiente`).attr('disabled', false);
        }

        getSelectorSiguiente(null, null);
        //console.log(valoresSesion['tipo']);
        selectores.forEach(selector => {
            //ocultar selectores si no estan en el avance_temporal
            if (!valoresSesion[selector.PAS_Html_name]) {
                console.log('ocultando selector: ', selector.PAS_Container);
                $(`#${selector.PAS_Container}`).hide();
            } else {
                //llenar selector
                console.log('llenando selector: ', selector.PAS_Html_name);
                getSelectorAndFill(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], selector.PAS_Pantalla_Ubicacion);
            }
        });
        /*
        /bloque
        */
        $('.selectpicker').selectpicker();
        
        asignarValoresDesdeSesion(valoresSesion);
        //trigger subproducto
        

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