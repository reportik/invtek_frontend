@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Sistema de Apertura')

@section('content')

<style>
    .color-option {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid #000;
        display: inline-block;
        cursor: pointer;
        margin-right: 5px;
        position: relative;
    }

    .color-option.selected {
        outline: 2px solid limegreen;
    }

    .color-option:hover::after {
        content: attr(data-color);
        position: absolute;
        bottom: -20px;
        left: 0;
        font-size: 12px;
        background-color: white;
        padding: 2px 6px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .card-system {
        border: 1px solid #ccc;
        padding: 1rem;
        border-radius: 10px;
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .card-system img {
        width: 180px;
        height: auto;
        border-radius: 8px;
    }
</style>
<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">
<div class="container text-center" style="max-width: 900px;">

    <div class="d-flex align-items-center justify-content-center my-4">
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #59981A;">
        <h2 class="titulo">Sistema de Apertura</h2>
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #59981A;">
    </div>

    <form id="form_apertura" action="{{ route('guardarAvance') }}" method="POST">
        @csrf
        <input type="hidden" name="siguiente-vista" value="bastones">
        <input type="hidden" name="actual-vista" value="sistema_apertura">
        <div class="row">
            <div class="col-md-6">

                {{-- Sistema de apertura --}}
                <div id="div_sistema_apertura" name="div_sistema_apertura" class="mb-4 text-start" style="display: none;">
                    @if(Auth::check() && Auth::user()->role_id == 1)
                    <label class="form-label fw-bold text-uppercase"><a href="{{ route('opciones.show', 8) }}"
                            target="_blank">Sistema de apertura:</a></label>
                    @else
                    <label class="form-label fw-bold text-uppercase">Sistema de apertura:</label>
                    @endif

                    <select id="sistema_apertura" name="sistema_apertura"
                        class="selectpicker form-control border-success" data-live-search="true">
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                {{-- Tarjeta estilo personalizada --}}
                <div id="sistema_info_card" class="card d-none" style="position: absolute; display: none">
                    <img id="sistema_img" class="card-img-top" src="" alt="Sistema"
                        style="cursor:pointer; width: 100%; height: 180px; object-fit: cover;" onclick="">
                    <div class="card-body">
                        <div class="text-start">
                            <h5 id="sistema_nombre" class="titulo"></h5>
                            <p id="sistema_descripcion" class="mb-0 text-muted "></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-md-6">
                {{-- Tipo de instalación --}}
                <div id="div_superficie_instalacion" name="div_superficie_instalacion" class="mb-4 text-start" style="display: none;">
                    @if(Auth::check() && Auth::user()->role_id == 1)
                    <label class="form-label fw-bold text-uppercase"><a href="{{ route('opciones.show', 9) }}"
                            target="_blank">Superficie de Instalación:</a></label>
                    @else
                    <label class="form-label fw-bold text-uppercase">Superficie de Instalación:</label>
                    @endif
                    <div id="radio_superficie_instalacion_riel" name="radio_superficie_instalacion_riel">

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                {{-- Sistema de riel--}}
                <div id="div_sistema_riel" name="div_sistema_riel" class="mb-4 text-start" style="display: none;">
                    @if(Auth::check() && Auth::user()->role_id == 1)
                    <label class="form-label fw-bold text-uppercase"><a href="{{ route('opciones.show', 10) }}"
                            target="_blank">Sistema de riel:</a></label>
                    @else
                    <label class="form-label fw-bold text-uppercase">Sistema de riel:</label>
                    @endif

                    <div id="info_sistema_riel" class="form-text text-muted mt-1 d-none"><i
                            class="fa fa-info-circle"></i> Selecciona primero la superficie de instalación.</div>
                    </label>
                    <select id="sistema_riel_selector" name="sistema_riel_selector"
                        class="selectpicker form-control border-success" data-live-search="true"></select>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-md-6">
                {{-- Riel --}}
                <div id="div_material_riel" name="div_material_riel" class="mb-4 text-start" style="display: none;">
                    @if(Auth::check() && Auth::user()->role_id == 1)
                    <label class="form-label fw-bold text-uppercase"><a href="{{ route('opciones.show', 12) }}"
                            target="_blank">Material riel:</a></label>
                    @else
                    <label class="form-label fw-bold text-uppercase">Material riel:</label>
                    @endif
                    <div id="info_material_riel" class="form-text text-muted mt-1"><i class="fa fa-info-circle"></i>
                        Selecciona primero un sistema de riel.</div>
                    </label>
                    <select id="material_riel_selector" name="material_riel_selector"
                        class="selectpicker form-control border-success" data-live-search="true"></select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                {{-- Colores --}}
                <div id="div_color_riel" name="div_color_riel" class="mb-4 text-start" style="display: none;">
                    @if(Auth::check() && Auth::user()->role_id == 1)
                    <label class="form-label fw-bold text-uppercase"><a href="{{ route('opciones.show', 13) }}"
                            target="_blank">Color riel:</a></label>
                    @else
                    <label class="form-label fw-bold text-uppercase">Color riel:</label>
                    @endif
                    <!-- <div id="info_color_riel" name="div_color_riel" class="form-text text-muted mt-1"><i
                            class="fa fa-info-circle"></i>
                        Selecciona primero un material de
                        riel.</div> -->
                    </label>
                    <div id="div_color_selector" name="div_color_selector" class="d-flex flex-wrap"></div>
                    <input type="text" id="color" name="color_selector" hidden>
                </div>
            </div>

        </div>
        {{-- Botones --}}
        <div class="text-end mt-4">
            <a href="#" name="anterior-vista" class="btn btn-outline-success fw-bold me-2">
                <i class="fas fa-arrow-left me-2"></i>Regresar
            </a>
            {{-- Botón para regresar al resumen (comentado por ahora)
            <button type="button" id="btnResumen" class="btn btn-outline-success fw-bold me-2" onclick="window.location.href='{{ route('resumen') }}'">
                <i class="fas fa-file-alt me-2"></i>Ir al Resumen
            </button>
            --}}
            <button type="submit" id="btnSiguiente" class="btn btn-success fw-bold">Siguiente</button>
        </div>
    </form>
    <input type="text" id="pantalla_ubicacion" name="pantalla_ubicacion" value="6" hidden>
</div>
@endsection


@section('page-script')
<script>
    let cargandoSelectores = true; // Variable global para controlar si se están cargando selectores
    let window_load = false;

    // Función para actualizar la sesión avance_temporal
    // Envía solo la clave-valor individual, el servidor hace el merge
    async function actualizarSesionAvanceTemporal(clave, valor) {
        try {
            // Crear un objeto simple con solo el campo a actualizar
            const campoActualizar = {};
            campoActualizar[clave] = valor;
            
            // Guardar en la sesión (el servidor hará el merge)
            const response = await fetch(`${routeapp}/actualizar-sesion`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    clave: 'avance_temporal',
                    valor: JSON.stringify(campoActualizar)
                })
            });

            if (!response.ok) {
                throw new Error('Error al actualizar la sesión');
            }

            const data = await response.json();
            console.log('✅ Sesión actualizada:', clave, '=', valor);
            if (data.avance_fusionado) {
                console.log('📦 Avance completo:', data.avance_fusionado);
            }
            return data;
        } catch (error) {
            console.error('❌ Error en actualizarSesionAvanceTemporal:', error);
            throw error;
        }
    }

    $(document).ready(function () {
        // Verificar y guardar valores en sesión para cada link de opción
        let valoresSesion = @json(session()->get('avance_temporal'));
        
        // Link: Sistema de apertura (id: 8)
        $('a[href="{{ route('opciones.show', 8) }}"]').on('click', function (e) {
            if (!valoresSesion['sistema_apertura']) {
                e.preventDefault();
                const valorActual = $('#sistema_apertura').val();
                if (valorActual) {
                    actualizarSesionAvanceTemporal('sistema_apertura', valorActual);
                }
                setTimeout(() => {
                    window.open($(this).attr('href'), '_blank');
                }, 100);
            }
        });

        // Link: Superficie de Instalación (id: 9)
        $('a[href="{{ route('opciones.show', 9) }}"]').on('click', function (e) {
            if (!valoresSesion['superficie_instalacion_riel']) {
                e.preventDefault();
                const valorActual = $('input[name="superficie_instalacion_riel"]:checked').val();
                if (valorActual) {
                    actualizarSesionAvanceTemporal('superficie_instalacion_riel', valorActual);
                }
                setTimeout(() => {
                    window.open($(this).attr('href'), '_blank');
                }, 100);
            }
        });

        // Link: Sistema de riel (id: 10)
        $('a[href="{{ route('opciones.show', 10) }}"]').on('click', function (e) {
            if (!valoresSesion['sistema_riel_selector']) {
                e.preventDefault();
                const valorActual = $('#sistema_riel_selector').val();
                if (valorActual) {
                    actualizarSesionAvanceTemporal('sistema_riel_selector', valorActual);
                }
                setTimeout(() => {
                    window.open($(this).attr('href'), '_blank');
                }, 100);
            }
        });

        // Link: Material riel (id: 12)
        $('a[href="{{ route('opciones.show', 12) }}"]').on('click', function (e) {
            if (!valoresSesion['material_riel_selector']) {
                e.preventDefault();
                const valorActual = $('#material_riel_selector').val();
                if (valorActual) {
                    actualizarSesionAvanceTemporal('material_riel_selector', valorActual);
                }
                setTimeout(() => {
                    window.open($(this).attr('href'), '_blank');
                }, 100);
            }
        });

        // Link: Color riel (id: 13)
        $('a[href="{{ route('opciones.show', 13) }}"]').on('click', function (e) {
            if (!valoresSesion['color_selector']) {
                e.preventDefault();
                const valorActual = $('input[name="color_selector"]').val();
                if (valorActual) {
                    actualizarSesionAvanceTemporal('color_selector', valorActual);
                }
                setTimeout(() => {
                    window.open($(this).attr('href'), '_blank');
                }, 100);
            }
        });

        $('#info_material_riel').toggleClass('d-none');
        //1.- obtener valores de sesión (ya declarado arriba)
        console.log('BLOQUE valoresSesion: ', valoresSesion);
        //convertir valoresSesion a json
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
        selectoresACargar = selectores.filter(selector => selector.PAS_Pantalla_Ubicacion == $('input[name="pantalla_ubicacion"]').val() && valoresSesion[selector.PAS_Html_name]);
        console.log('BLOQUE selectores a cargar: ', selectoresACargar);
        
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
                            console.log('BLOQUE: Carga de selectores completada');
                            // Desbloquear pantalla al final de toda la carga
                            $.unblockUI();
                        }, 900);
                    } else {
                        console.log('BLOQUE llenando selector: ', selector.PAS_Html_name);
                        getSelectorAndFill(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], selector.PAS_Pantalla_Ubicacion, false);
                        // Esperar un poco antes de cargar el siguiente
                        setTimeout(() => {
                            indice++;
                            cargarSiguiente();
                        }, 600);
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

        //buscar en los elementos con clase color-option y asignar clase selected
        $('.color-option').each(function () {
            const color = $(this).data('color');
            if (valoresSesion['div_color_selector'] === color) {
                $(this).addClass('selected');
                //$('#color_riel_selector').val(color);
            }
        });


        //5.- definir el valor de siguiente-vista 
        const siguienteVista = valoresSesion['siguiente-vista'] || '';
        /* Comentado: Lógica para mostrar botón de resumen cuando se edita desde el resumen
        if (siguienteVista === 'resumen') {
            $('input[name="siguiente-vista"]').val('resumen');
            $('.btn-success').text('Resumen');
            // Descomentar la siguiente línea si se desea mostrar el botón "Ir al Resumen"
            // $('#btnResumen').show();
        } else {
            $('.btn-success').text('Siguiente');
        }
        */

        // Eventos protegidos durante la carga
        $('#sistema_apertura').on('changed.bs.select', function () {
            // Verificar si se están cargando selectores o asignando valores programáticamente
            if (cargandoSelectores ) {
                console.log('BLOQUE: Ignorando evento changed.bs.select durante carga/asignación programática');
                return;
            }
            
           
                const aperturaId = $(this).val();
                console.log('...................SELECCIONADO TIPO CON VALOR: ', aperturaId);
                getSelectorSiguiente('sistema_apertura', aperturaId);
                $('#info_material_riel, #info_color_riel').addClass('d-none');
            
        });

        $('div[name="radio_superficie_instalacion_riel"]').on('change', function () {
            // Verificar si se están cargando selectores
            if (cargandoSelectores) {
                console.log('BLOQUE: Ignorando evento change de radio durante carga de selectores');
                return;
            }
            
          
                const seleccion = $('input[name="superficie_instalacion_riel"]:checked').val();
                console.log('...................SELECCIONADO SUPERFICIE DE INSTALACION CON VALOR: ', seleccion);
                getSelectorSiguiente('superficie_instalacion_riel', seleccion);
                $('#div_color_selector').empty();
                $('#info_material_riel, #info_color_riel').addClass('d-none');
            
        });

        $('#sistema_riel_selector').on('changed.bs.select', function () {
            // Verificar si se están cargando selectores o asignando valores programáticamente
            if (cargandoSelectores ) {
                console.log('BLOQUE: Ignorando evento changed.bs.select durante carga/asignación programática');
                return;
            }
            
          
                const option = $(this).find('option:selected');
                const nombre = option.data('nombre');
                const descripcion = option.data('descripcion');
                const imagen = option.data('imagen');
                console.log('nombre: ', nombre);
                console.log('descripcion: ', descripcion);
                console.log('imagen: ', imagen);
                console.log('...................SELECCIONADO SISTEMA DE RIEL CON VALOR: ', option.val());
                getSelectorSiguiente('sistema_riel_selector', option.val());

                $('#sistema_nombre').text(nombre);
                $('#sistema_descripcion').text(descripcion || '');
                $('#sistema_img')
                    .attr('src', `${assetapp}/images/cotizador/${imagen}`)
                    .attr('onclick', `showModal('${assetapp}/images/cotizador/${imagen}')`);

                $('#sistema_info_card').removeClass('d-none');
                $('#div_color_selector').empty(); 
                $('#info_color_riel').addClass('d-none');
            
        });

        $('#material_riel_selector').on('changed.bs.select', function () {
            // Verificar si se están cargando selectores o asignando valores programáticamente
            if (cargandoSelectores ) {
                console.log('BLOQUE: Ignorando evento changed.bs.select durante carga/asignación programática');
                return;
            }
            
            const materialId = $(this).val();
            console.log('...................SELECCIONADO MATERIAL DE RIEL CON VALOR: ', materialId);
            
                getSelectorSiguiente('material_riel_selector', materialId);
            
        });

        // Función para validar si un elemento está visible y tiene valor
        function validarCampoVisible(selector, mensajeError) {
            const elemento = $(selector);
            const divContenedor = elemento.closest('div[id^="div_"]');
            
            // Si el div contenedor está visible, validar que el campo tenga valor
            if (divContenedor.is(':visible')) {
                if (!elemento.val() && !elemento.is(':checked')) {
                    return mensajeError;
                }
            }
            return null; // No hay error
        }

        // Función para validar radio buttons visibles
        function validarRadioVisible(nombre, mensajeError) {
            const divContenedor = $(`input[name="${nombre}"]`).closest('div[id^="div_"]');
            
            // Si el div contenedor está visible, validar que algún radio esté seleccionado
            if (divContenedor.is(':visible')) {
                if (!$(`input[name="${nombre}"]:checked`).val()) {
                    return mensajeError;
                }
            }
            return null; // No hay error
        }

        $('#form_apertura').on('submit', function (e) {
            e.preventDefault();

            // Remover atributo 'required' de campos ocultos para evitar errores del navegador
            $('div[id^="div_"]:not(:visible)').find('input, select, textarea').each(function() {
                $(this).removeAttr('required');
            });

            // Validar sistema de apertura
            let error = validarCampoVisible('#sistema_apertura', 'Por favor selecciona un sistema de apertura.');
            if (error) return mostrarError(error);

            // Validar superficie de instalación
            error = validarRadioVisible('superficie_instalacion_riel', 'Por favor selecciona una superficie de instalación.');
            if (error) return mostrarError(error);

            // Validar sistema de riel
            error = validarCampoVisible('#sistema_riel_selector', 'Por favor selecciona una opción de sistema de riel.');
            if (error) return mostrarError(error);

            // Validar material de riel
            error = validarCampoVisible('#material_riel_selector', 'Por favor selecciona un material de riel.');
            if (error) return mostrarError(error);

            // Validar color - es un input hidden, no un radio
            const divColorContenedor = $('#div_color_riel');
            if (divColorContenedor.is(':visible')) {
                if (!$('input[name="color_selector"]').val()) {
                    return mostrarError('Por favor selecciona un color.');
                }
            }

            this.submit(); // solo si todo está bien
        });

        // Función para restaurar atributos 'required' en campos visibles
        function restaurarRequired() {
            $('div[id^="div_"]:visible').find('input, select, textarea').each(function() {
                // Solo restaurar si el campo originalmente tenía 'required' en el HTML
                if ($(this).data('original-required') !== false) {
                    $(this).attr('required', 'required');
                }
            });
        }

        // Al cargar la página, marcar qué campos originalmente tenían 'required'
        $(document).ready(function() {
            $('input, select, textarea').each(function() {
                if ($(this).attr('required')) {
                    $(this).data('original-required', true);
                } else {
                    $(this).data('original-required', false);
                }
            });
        });

        function mostrarError(mensaje) {
            // Restaurar atributos 'required' antes de mostrar el error
            //restaurarRequired();
            
            Swal.fire({
                icon: 'warning',
                title: '¡Atención!',
                text: mensaje,
                confirmButtonText: 'Aceptar'
            });
        }
            
        });
</script>
@endsection