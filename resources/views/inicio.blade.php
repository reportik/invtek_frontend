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
        <input type="text" name="actual-vista" value="inicio" hidden>
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
        //1.- obtener valores de sesión
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

        /*
        bloque
        */
        //2.- validar si hay valores en la sesión para habilitar o deshabilitar el botón siguiente
        if (Object.keys(valoresSesion).length === 0 || valoresSesion === null) {
        $(`#btnSiguiente`).attr('disabled', true);
        }else{
        $(`#btnSiguiente`).attr('disabled', false);
        }
        

        //3.- obtener selector siguiente para mostrar el primer selector
        getSelectorSiguiente(null, null);

        
        // selectores.forEach(selector => {
        //             if (!valoresSesion[selector.PAS_Html_name]) {
        //                 console.log('ocultando selector: ', selector.PAS_Html_name);
        //                 $(`#${selector.PAS_Container}`).hide();
        //             } else {
        //                 console.log('mostrando selector: ', selector.PAS_Html_name);
        //                 $(`#${selector.PAS_Container}`).show();
        //             }
        //         });
        //4.- obtener selectores a cargar y llenarlos con los valores de la sesión
        const selectoresACargar = selectores.filter(selector => selector.PAS_Pantalla_Ubicacion == $('input[name="pantalla_ubicacion"]').val());
         //pila de selectores a cargar
         let pilaSelectores = selectoresACargar;
        console.log('BLOQUE selectores: ', selectoresACargar);
        
        // Función para cargar selectores de forma secuencial
        function cargarSelectores() {
            // Bloquear pantalla una sola vez al inicio de la carga
            $.blockUI({
                css: {
                    border: 'none',
                    padding: '15px',
                    backgroundColor: '#000',
                    '-webkit-border-radius': '10px',
                    '-moz-border-radius': '10px',
                    opacity: 0.5,
                    color: '#fff'
                }
            });
            
            let indice = 0;
            
            function cargarSiguiente() {
                if (indice >= selectoresACargar.length) {
                    // Marcar que terminó la carga de selectores
                    cargandoSelectores = false;
                    console.log('BLOQUE: Carga de selectores completada');
                    // Desbloquear pantalla al final de toda la carga
                    $.unblockUI();
                    return;
                }
                
                const selector = selectoresACargar[indice];
                
                if (selector.PAS_Pantalla_Ubicacion == $('input[name="pantalla_ubicacion"]').val() &&
                    valoresSesion[selector.PAS_Html_name]) {

                    if (indice === selectoresACargar.length - 1) {
                        getSelectorAndFill(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], selector.PAS_Pantalla_Ubicacion, false);
                        console.log('BLOQUE último selector: ', selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name]);
                        // Esperar un poco antes de marcar como completado
                        setTimeout(() => {
                            cargandoSelectores = false;
                            getSelectorSiguiente(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], false);
                            //asignarSelectores(pilaSelectores, valoresSesion, selectoresACargar); 
                            console.log('BLOQUE: Carga de selectores completada');
                            // Desbloquear pantalla al final de toda la carga
                            $.unblockUI();
                        }, 1000);
                    } else {
                        console.log('BLOQUE llenando selector: ', selector.PAS_Html_name);
                        getSelectorAndFill(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], selector.PAS_Pantalla_Ubicacion, false);
                        // Esperar un poco antes de cargar el siguiente
                        setTimeout(() => {
                            indice++;
                            cargarSiguiente();
                        }, 500);
                    }
                } else {
                    indice++;
                    cargarSiguiente();
                }
            }
            
            cargarSiguiente();
        }
        
        // Iniciar carga de selectores
        cargarSelectores();


       

        
        
        function asignarSelectores(pilaSelectores, valoresSesion, selectoresACargar) {
            selectoresACargar.forEach(selector => {
            //si el selecotr ya tiene ese valor, no asignar nuevamente
            //obtener el valor del selector tipo_confeccion
            console.log('ASIGNANDO VALOR A *** selector: ', selector.PAS_Html_name);
            const valor = obtenerValorSelectorPorTipo(selector.PAS_Html_name, selector.PAS_Tipo_Selector);
            if (valor != valoresSesion[selector.PAS_Html_name]) {
                setTimeout(() => {
                asignarValor(selector.PAS_Html_name, selector.PAS_Tipo_Selector, valor);
                }, 1000);
            }else{
                //sacar el selector de la pila
                pilaSelectores.shift();
            }
            
        });
        }
        
        /*
        /bloque
        */
        //$('.selectpicker').selectpicker();
        
        //asignarValoresDesdeSesion(valoresSesion);

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