@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Inicio Cotizador')

@section('content')
<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">

<div class="container text-center" style="max-width: 600px;">
    <div style="text-align: center;">
        <img src="{{ asset('images/img_cotizador.png') }}" alt="Logo"
            style="height: 200px; display: block; margin: 0 auto;" class="">
    </div>

    <form id="formAvance" action="{{ route('guardarAvance') }}" method="POST" style="margin-top: -20px;">
        @csrf
        {{-- Texto de bienvenida a usuario si esta logueado --}}
        @if(Auth::check())
        <p class="text-center titulo mb-2" style="font-size: 1.2rem;">Bienvenido {{
            Auth::user()->name }}
            <br>
            Para
            comenzar
            con tu cotización favor de
            llenar los
            siguientes campos:
        </p>
        @else
        <p class="text-center titulo mb-2 " style="font-size: 1.2rem;">Hola, estas a punto de
            realizar una
            cotización como
            invitado.</p>
        @endif

        {{-- Nombre del proyecto --}}
        <div class="mb-3 text-start">
            <label for="nombre_proyecto" class="form-label fw-bold text-uppercase">
                NOMBRE DEL PROYECTO:
                {{-- <i class="fa fa-info-circle" title="Introduce un nombre para identificar el proyecto."></i> --}}
            </label>
            <input type="text" name="nombre_proyecto" id="nombre_proyecto" class="form-control border-success"
                value="{{ old('nombre_proyecto') }}" required>
            <div class="descripcionSeleccion" id="descripcionNombreProyecto">Nombre para identificar tu proyecto.</div>
        </div>
        {{-- Nombre del artículo --}}
        <div class="mb-3 text-start">
            <label for="nombre_articulo" class="form-label fw-bold text-uppercase">
                NOMBRE DEL ARTÍCULO:
                {{-- <i class="fa fa-info-circle" title="Introduce un nombre para identificar el artículo."></i> --}}
            </label>
            <input type="text" name="nombre_articulo" id="nombre_articulo" class="form-control border-success" required>
            <div class="descripcionSeleccion" id="descripcionNombreArticulo">Nombre para identificar el artículo.</div>
        </div>
        {{-- Área de instalación (Selectpicker) --}}
        <div class="mb-4 text-start" id="div_area_instalacion">
            @if(Auth::check() && Auth::user()->role_id == 1)
            <label for="area_instalacion" class="form-label fw-bold text-uppercase">
                <a href="{{ route('opciones.show', 3) }}" target="_blank">ÁREA DE INSTALACIÓN:</a>
            </label>
            @else
            <label for="area_instalacion" class="form-label fw-bold text-uppercase">
                NOMBRE DEL ÁREA DE INSTALACIÓN:
            </label>
            @endif
            <select name="area_instalacion" id="area_instalacion" class="selectpicker form-control border-success"
                data-live-search="true" required>
                {{-- select first key --}}
                @foreach($area_instalacion as $key => $value )
                @if($loop->first)

                <option value="{{ $key }}" selected>{{ $value }}</option>
                @else
                <option value="{{ $key }}">{{ $value }}</option>
                @endif
                @endforeach
            </select>
            <div class="descripcionSeleccion" id="descripcionAreaInstalacion">
                {{ $descripcion_area_instalacion[old('area_instalacion',
                array_key_first($descripcion_area_instalacion))] ??
                '' }}
            </div>
        </div>
        {{-- <div class="mb-3 text-start">
            @if(Auth::check() && Auth::user()->role_id == 1)
            <label for="calidad" class="form-label fw-bold text-uppercase"><a href="{{ route('opciones.show', 18) }}"
                    target="_blank">CALIDAD:</a></label>
            @else
            <label for="calidad" class="form-label fw-bold text-uppercase">CALIDAD:</label>
            @endif

            <select id="calidad" name="calidad" class="selectpicker form-control border-success" data-live-search="true"
                required>
                @foreach($opcionesCalidad as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            <small id="descripcionCalidad" class="form-text text-muted d-block mt-4" style="font-size: 1.1rem;">
                {{ $opcionesCalidadDescripcion[old('calidad', array_key_first($opcionesCalidadDescripcion))] ??
                'Seleccione una calidad para ver su descripción.' }}
            </small>
        </div> --}}
        <input type="text" name="siguiente-vista" value="tipo_producto" hidden>
        <div class="text-end">
            <button id="btnSiguiente" type="submit" class="btn btn-success fw-bold btn-full-width">Siguiente</button>
        </div>

    </form>
    <input type="text" id="pantalla_ubicacion" name="pantalla_ubicacion" value="1" hidden>
</div>
@endsection



@section('page-script')
<script>
    const descripciones = @json($opcionesCalidadDescripcion);

  $('#calidad').on('changed.bs.select', function () {
    const seleccion = $(this).val();
    $('#descripcionCalidad').text(descripciones[seleccion] ?? 'Seleccione una calidad para ver su descripción.');
  });

  const descripcionesAreaInstalacion = @json($descripcion_area_instalacion);
  $('#area_instalacion').on('changed.bs.select', function () {
    const seleccion = $(this).val();
    $('#descripcionAreaInstalacion').text(descripcionesAreaInstalacion[seleccion] ?? '');
    //obtener selector siguiente, usando la funcion getSelectorSiguiente
    console.log('obtener selector siguiente');
    console.log('area_instalacion', seleccion);
    getSelectorSiguiente('area_instalacion', seleccion);
    
    
  });
    
    $(document).ready(function () {
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
            $('#btnSiguiente').text('Resumen');
        } else {
            
            $('#btnSiguiente').text('Siguiente');
        }
        });
</script>
@endsection