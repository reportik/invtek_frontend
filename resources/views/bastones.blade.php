@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Accesorios de Apertura')

@section('content')
<style>
    .d-none {
        display: none;
    }
</style>
<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">
<div class="container text-center" style="max-width: 900px;">
    <div class="d-flex align-items-center justify-content-center my-4">
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #59981A;">
        <h2 class="titulo">Accesorio de Apertura</h2>
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #59981A;">
    </div>

    <form id="form_accesorios" action="{{ route('guardarAvance') }}" method="POST">
        @csrf
        <input type="hidden" name="siguiente-vista" value="resumen">
        <input type="hidden" name="actual-vista" value="bastones">

        <div class="row mb-4">
            <div id="div_accesorio" class="col-md-6 text-start" style="display: none;">
                @if(Auth::check() && Auth::user()->role_id == 1)
                <label class="form-label fw-bold"><a href="{{ route('opciones.show', 14) }}" target="_blank">Accesorio
                        de apertura:</a></label>
                @else
                <label class="form-label fw-bold subtitulo text-uppercase">Accesorio de apertura:</label>
                @endif
                <label class="form-label fw-bold">

                    <i id="info_accesorio" class="fa fa-info-circle text-muted ms-1 d-none"
                        title="Selecciona un tipo de accesorio."></i>
                </label>
                <select id="accesorio_selector" name="accesorio" class="selectpicker form-control border-success"
                    data-live-search="true">
                    <option value="">-- Selecciona --</option>
                    {{-- Opciones cargadas con JS --}}
                </select>
            </div>

            <div id="div_material" class="col-md-6 text-start" style="display: none;">
                @if(Auth::check() && Auth::user()->role_id == 1)
                <label class="form-label fw-bold"><a href="{{ route('opciones.show', 15) }}"
                        target="_blank">Material:</a></label>
                @else
                <label class="form-label fw-bold subtitulo text-uppercase">Material:</label>
                @endif
                <label class="form-label fw-bold">

                    <i id="info_material" class="fa fa-info-circle text-muted ms-1 d-none"
                        title="Selecciona el material del accesorio."></i>
                </label>
                <select id="material_selector" name="material" class="selectpicker form-control border-success"
                    data-live-search="true">
                    <option value="">-- Selecciona --</option>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div id="div_modelo" class="col-md-12 text-start" style="display: none;">
                @if(Auth::check() && Auth::user()->role_id == 1)
                <label class="form-label fw-bold"><a href="{{ route('opciones.show', 16) }}"
                        target="_blank">Modelo:</a></label>
                @else
                <label class="form-label fw-bold subtitulo text-uppercase">Modelo:</label>
                @endif
                <label class="form-label fw-bold">

                    <i id="info_modelo" class="fa fa-info-circle text-muted ms-1 d-none"
                        title="Selecciona el modelo disponible."></i>
                </label>
                <select id="modelo_selector" name="modelo" class="selectpicker form-control border-success"
                    data-live-search="true">
                    <option value="">-- Selecciona --</option>
                </select>
            </div>
        </div>

        <div id="div_largo" class="card d-none mb-4 text-start p-3 d-none" style="display: none;">
            <div class="row">
                <div class="col-md-4">
                    <img id="modelo_img" src="" class="img-fluid rounded" alt="Imagen del modelo">
                </div>
                <div class="col-md-8">
                    <h5 id="modelo_nombre" class="text-success fw-bold"></h5>
                    <p class="h6 fw-semibold text-dark mb-2" style="display: none;">
                        $<span id="modelo_precio">0.00</span>
                        <small class="text-muted">IVA incluido</small>
                    </p>
                    <div id="" class="mt-3">
                        @if(Auth::check() && Auth::user()->role_id == 1)
                        <label class="form-label fw-bold"><a href="{{ route('opciones.show', 17) }}"
                                target="_blank">Largo:</a></label>
                        @else
                        <label class="form-label fw-bold subtitulo text-uppercase">Largo:</label>
                        @endif
                        <select id="largo_selector" name="largo" class="selectpicker form-control border-success"
                            data-live-search="true">
                            <option value="">-- Selecciona --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botones --}}
        <div class="text-end">
            <a href="#" name="anterior-vista" class="btn btn-outline-success fw-bold me-2">
                <i class="fas fa-arrow-left me-2"></i>Regresar
            </a>
            <button type="submit" id="btnSiguiente" class="btn btn-success fw-bold">Siguiente</button>
        </div>
    </form>
    <input type="text" name="pantalla_ubicacion" value="7" hidden>
</div>
@endsection

@section('page-script')
<script>
    let cargandoSelectores = true; // Variable global para controlar si se están cargando selectores
    
    //obtener de result array
    const accesorios = @json($result['accesorios']);     // id, valor, id_padre
    const materiales = @json($result['materiales']);     // id, valor, id_padre
    const modelos = @json($result['modelos']);           // id, valor, imagen, precio, id_padre
    const largos = @json($result['largos']);             // id, valor, id_padre

    $(document).ready(() => {

        //$('#modelo_card').addClass('d-none');
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
                        }, 500);
                    } else {
                        console.log('BLOQUE llenando selector: ', selector.PAS_Html_name);
                        getSelectorAndFill(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], selector.PAS_Pantalla_Ubicacion, false);
                        // Esperar un poco antes de cargar el siguiente
                        setTimeout(() => {
                            indice++;
                            cargarSiguiente();
                        }, 1000);
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

        // Eventos protegidos durante la carga
        $('#accesorio_selector').on('changed.bs.select', function () {
            // Verificar si se están cargando selectores o asignando valores programáticamente
            if (cargandoSelectores) {
                console.log('BLOQUE: Ignorando evento changed.bs.select durante carga/asignación programática');
                return;
            }
            
            const id = $(this).val();
            console.log('...................SELECCIONADO ACCESORIO CON VALOR: ', id);
            getSelectorSiguiente('accesorio', id);
        });

        $('#material_selector').on('changed.bs.select', function () {
            // Verificar si se están cargando selectores o asignando valores programáticamente
            if (cargandoSelectores ) {
                console.log('BLOQUE: Ignorando evento changed.bs.select durante carga/asignación programática');
                return;
            }
            
            const id = $(this).val();
            console.log('...................SELECCIONADO MATERIAL CON VALOR: ', id);
            getSelectorSiguiente('material', id);
        });

        $('#modelo_selector').on('changed.bs.select', function () {
            // Verificar si se están cargando selectores o asignando valores programáticamente
            if (cargandoSelectores ) {
                console.log('BLOQUE: Ignorando evento changed.bs.select durante carga/asignación programática');
                return;
            }
            
            const id = $(this).val();
            const modelo = modelos.find(m => m.id == id);
            if (!modelo) return;

            $('#modelo_nombre').text(modelo.valor);
            $('#modelo_img').attr('src', `${assetapp}/images/cotizador/${modelo.imagen}`);
            $('#div_largo').removeClass('d-none');

            console.log('...................SELECCIONADO MODELO CON VALOR: ', id);
            getSelectorSiguiente('modelo', id);
        });

        $('#largo_selector').on('changed.bs.select', function () {
            // Verificar si se están cargando selectores o asignando valores programáticamente
            if (cargandoSelectores ) {
                console.log('BLOQUE: Ignorando evento changed.bs.select durante carga/asignación programática');
                return;
            }
            
            const id = $(this).val();
            console.log('...................SELECCIONADO LARGO CON VALOR: ', id);
            getSelectorSiguiente('largo', id);
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

        $('#form_accesorios').on('submit', function (e) {
            e.preventDefault();

            // Validar accesorio
            let error = validarCampoVisible('#accesorio_selector', 'Selecciona un accesorio.');
            if (error) return mostrarError(error);

            // Validar material
            error = validarCampoVisible('#material_selector', 'Selecciona un material.');
            if (error) return mostrarError(error);

            // Validar modelo
            error = validarCampoVisible('#modelo_selector', 'Selecciona un modelo.');
            if (error) return mostrarError(error);

            // Validar largo
            error = validarCampoVisible('#largo_selector', 'Selecciona el largo.');
            if (error) return mostrarError(error);

            this.submit();
        });

        function mostrarError(msg) {
            Swal.fire({
                icon: 'warning',
                title: '¡Atención!',
                text: msg,
                confirmButtonText: 'Aceptar'
            });
        }
        
    });
</script>
@endsection