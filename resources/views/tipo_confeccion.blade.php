@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Tipo de Confección')

@section('content')

<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">

<div class="container text-center" style="max-width: 700px;">
    <div style="display: flex; align-items: center; justify-content: center; margin: 20px 0;">
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
        <h2 class="titulo">
            Tipo de confección
        </h2>
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
    </div>
    <form id="form_confeccion" action="{{ route('guardarAvance') }}" method="POST">
        @csrf
        <div class="row" id="">
            <div id="div_confeccion" class="mb-4 col-md-6 text-start">
                @if(Auth::check() && Auth::user()->role_id == 1)
                <label for="tipo_confeccion" class="form-label fw-bold text-uppercase">
                    <a href="{{ route('opciones.show', 4) }}" target="_blank">TIPO DE CONFECCIÓN:</a>
                </label>
                @else
                <label for="tipo_confeccion" class="form-label fw-bold text-uppercase">TIPO DE CONFECCIÓN:</label>
                @endif
                <select id="tipo_confeccion" name="tipo_confeccion" class="selectpicker form-control border-success"
                    data-live-search="true" required>
                    {{-- <option value="">-- Selecciona una opción --</option>
                    @foreach ($tiposConfeccion as $item )

                    <option value="{{ $item['id'] }}" data-descripcion="{{ $item['descripcion'] }}"
                        data-img="{{ $item['imagen'] }}">{{ $item['valor'] }}</option>
                    @endforeach --}}

                </select>
                <div class="descripcionSeleccion" id="descripcionTipoConfeccion">
                    {{ $descripcion_tipo_confeccion[old('tipo_confeccion',
                    array_key_first($descripcion_tipo_confeccion))] ??
                    '' }}
                </div>
            </div>
            <div class="col-md-6 text-start">
                {{-- Tarjeta estilo personalizada --}}
                <div id="confeccion_info_card" class="card d-none" style="position: absolute width: 100%;">
                    <img id="confeccion_img" class="card-img-top" src="" alt="Sistema"
                        style="cursor:pointer; width: 100%; height: 180px; object-fit: contain;" onclick="">
                    <div class="card-body">
                        <div class="text-start">
                            <h5 id="confeccion_nombre" class="titulo"></h5>
                            <p id="confeccion_descripcion" class="mb-0 text-muted "></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="row mt-4 text-start">
                <div id="contenedor_tarjetas_confeccion" class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                    <div class="col-md-12 text-start">
                        <label for="radio_step_2" class="form-label fw-bold text-uppercase">
                            @if(Auth::check() && Auth::user()->role_id == 1)
                            <a href="{{ route('opciones.show', 5) }}" target="_blank">
                                Estilo de confección / Fullness:</a>
                            @else
                            Estilo de confección / Fullness:
                            @endif
                        </label>
                    </div>
                    <div id="card_radio_step_2" name="card_radio_step_2" class="row col-md-12 g-4 mb-4">

                    </div>
                </div>
            </div>
            <div class="col text-end mt-4">
                {{-- Botón de cancelar --}}
                {{-- Botón de regresar route('tipo_producto') --}}
                <a href="#" name="anterior-vista" class="btn btn-outline-success fw-bold me-2">
                    <i class="fas fa-arrow-left me-2"></i>Regresar
                </a>
                {{-- Botón de siguiente --}}
                <button id="btnSiguiente" type="submit" class="btn btn-success fw-bold">Siguiente</button>
            </div>
        </div>
        <input type="text" name="siguiente-vista" value="medidas" hidden>
        <input type="text" name="actual-vista" value="tipo-confeccion" hidden>
    </form>
    <input type="text" id="pantalla_ubicacion" name="pantalla_ubicacion" value="3" hidden>
</div>

@endsection

@section('page-script')
<script>
    const tarjetasConfeccion = @json($cards_confeccion);
    const descripcionesTipoConfeccion = @json($descripcion_tipo_confeccion);
    let cargandoSelectores = true; // Variable global para controlar si se están cargando selectores
    
    // Definir eventos después de que se cargue el DOM
    $(document).ready(function() {
        $('#tipo_confeccion').on('changed.bs.select', function () {
        // Verificar si se están cargando selectores o asignando valores programáticamente
        if (cargandoSelectores || window.asignandoValoresProgramaticamente) {
            console.log('BLOQUE: Ignorando evento changed.bs.select durante carga/asignación programática');
            return;
        }
        
        console.log('Tipo de confección seleccionado:', $(this).val());
        const tipoSeleccionado = $(this).val();
        // texto de la opción seleccionada
        const textoSeleccionado = $(this).find('option:selected').text();
        //const contenedor = $('#contenedor_tarjetas_confeccion');
        // contenedor.empty(); // Limpiar tarjetas anteriores
        const option = $(this).find('option:selected');
        const img = option.data('img');
        const valor = option.text();
        const descripcion = option.data('descripcion');
        console.log(img);
        
        if (img) {
        $('#confeccion_info_card').removeClass('d-none');
        $('#confeccion_nombre').text(valor);
        $('#confeccion_descripcion').text(descripcion || '');
        $('#confeccion_img')
        .attr('src', `${assetapp}/images/cotizador/${img}`)
        .attr('onclick', `showModal('${assetapp}/images/cotizador/${img}')`);
        
        
        }else{
        $('#confeccion_info_card').addClass('d-none');
        }

        $('#descripcionTipoConfeccion').text(descripcionesTipoConfeccion[textoSeleccionado] ?? '');
        console.log('Selector despues de seleccionar TIPO de confección con valor: ', textoSeleccionado + ' ' + tipoSeleccionado);
        getSelectorSiguiente('tipo_confeccion', tipoSeleccionado);

        //const filtradas = tarjetasConfeccion.filter(t => t.tipo === tipoSeleccionado);// Filtrar tarjetas por tipo de confección

       /*  if (filtradas.length === 0) {
            contenedor.append(`<div class="col">
                <div class="alert alert-warning">No hay tarjetas disponibles para esta confección.</div>
            </div>`);
            return;
        } else {
            contenedor.append(`<div class="col-md-12 text-start">
                <label for="tipo_confeccion" class="form-label fw-bold text-uppercase">
                    @if(Auth::check() && Auth::user()->role_id == 1)
                    <a href="{{ route('opciones.show', 5) }}" target="_blank">
                    Estilo de confección / Fullness:</a>
                    @else
                    Estilo de confección / Fullness:
                    @endif 
                </label>
            </div>`);
        } */

      /*   filtradas.forEach((item, index) => {
            const checked = item.a_selected === 'true' ? 'checked' : '';
            const tarjeta = `
                <div class="">
                    <div class="card h-100">
                        <img class="card-img-top" src="${assetapp}/images/cotizador/${item.image}" style="cursor:pointer; width: 100%; height: 180px; object-fit: cover;"
                            onclick="showModal('${assetapp}/images/cotizador/${item.image}')">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="radio_step_2"
                                    id="radio2_${index}" value="${item.opcion_radio}" ${checked}
                                    >
                                <label class="subtitulo" for="radio2_${index}">
                                    ${item.opcion_radio}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            contenedor.append(tarjeta);
        }); */
       
        });

        ////evento de radio_card confeccion
        $('div[name="card_radio_step_2"]').on('change', function () {
            // Verificar si se están cargando selectores
            if (cargandoSelectores) {
                console.log('BLOQUE: Ignorando evento change de radio durante carga de selectores');
                return;
            }
            
            let seleccion = $('input[name="radio_step_2"]:checked').val();
            console.log('...................SELECCIONADO CONFECCION CON VALOR: ', seleccion);
            getSelectorSiguiente('radio_step_2', seleccion);
        });

        //// Validar que se haya seleccionado una opción de confección
        $('#form_confeccion').on('submit', function (e) {
        let tipoSeleccionado = $('#tipo_confeccion').val();
        let opcionSeleccionada = $('input[name="radio_step_2"]:checked').val();
        //si tipoSeleccionado y opcionSeleccionada estan dentro de un div visible
        let divTipoConfeccion = $('#div_confeccion');
        let divRadioStep2 = $('div[name="card_radio_step_2"]');
        let valid = true;
        if (divTipoConfeccion.is(':visible') && !tipoSeleccionado) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: '¡Atención!',
                text: 'Por favor, selecciona un Tipo de confección.',
                confirmButtonText: 'Aceptar'
            });
            valid = false;
        }
        if (divRadioStep2.is(':visible') && !opcionSeleccionada) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: '¡Atención!',
                text: 'Por favor, selecciona un Estilo de confección o Fullness.',
                confirmButtonText: 'Aceptar'
            });
            valid = false;
        }
        });
        
        $('.selectpicker').selectpicker();
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
        //4.- obtener selectores a cargar y llenarlos con los valores de la sesión
        selectoresACargar = selectores.filter(selector => selector.PAS_Pantalla_Ubicacion == $('input[name="pantalla_ubicacion"]').val());
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
                        getSelectorSiguiente(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], false);
                      
                        // Esperar un poco antes de marcar como completado
                        setTimeout(() => {
                            cargandoSelectores = false;
                            console.log('BLOQUE: Carga de selectores completada');
                            // Desbloquear pantalla al final de toda la carga
                            $.unblockUI();
                        }, 500);
                    } else {
                        console.log('BLOQUE llenando selector: ', selector.PAS_Html_name);
                        getSelectorAndFill(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], selector.PAS_Pantalla_Ubicacion, false);
                        // Esperar un poco antes de cargar el siguiente
                        setTimeout(() => {
                            indice++;
                            cargarSiguiente();
                        }, 300);
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
        
        //5.- definir el valor de siguiente-vista
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