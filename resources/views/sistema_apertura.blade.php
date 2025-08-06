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
        <div class="row">
            <div class="col-md-6">

                {{-- Sistema de apertura --}}
                <div id="div_sistema_apertura" name="div_sistema_apertura" class="mb-4 text-start">
                    @if(Auth::check() && Auth::user()->role_id == 1)
                    <label class="form-label fw-bold text-uppercase"><a href="{{ route('opciones.show', 8) }}"
                            target="_blank">Sistema de apertura:</a></label>
                    @else
                    <label class="form-label fw-bold text-uppercase">Sistema de apertura:</label>
                    @endif

                    <select id="sistema_apertura" name="sistema_apertura"
                        class="selectpicker form-control border-success" data-live-search="true" required>
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
                <div id="div_superficie_instalacion" name="div_superficie_instalacion" class="mb-4 text-start">
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
                <div id="div_sistema_riel" name="div_sistema_riel" class="mb-4 text-start">
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
                        class="selectpicker form-control border-success" data-live-search="true" required></select>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-md-6">
                {{-- Riel --}}
                <div id="div_material_riel" name="div_material_riel" class="mb-4 text-start">
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
                        class="selectpicker form-control border-success" data-live-search="true" required></select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                {{-- Colores --}}
                <div id="div_color_riel" name="div_color_riel" class="mb-4 text-start">
                    @if(Auth::check() && Auth::user()->role_id == 1)
                    <label class="form-label fw-bold text-uppercase"><a href="{{ route('opciones.show', 13) }}"
                            target="_blank">Color riel:</a></label>
                    @else
                    <label class="form-label fw-bold text-uppercase">Color riel:</label>
                    @endif
                    <div id="info_color_riel" name="div_color_riel" class="form-text text-muted mt-1"><i
                            class="fa fa-info-circle"></i>
                        Selecciona primero un material de
                        riel.</div>
                    </label>
                    <div id="div_color_selector" name="div_color_selector" class="d-flex flex-wrap"></div>
                    <input type="text" id="color" name="color_selector" hidden>
                </div>
            </div>

        </div>
        {{-- Botones --}}
        <div class="text-end mt-4">
            <a href="{{ route('telas') }}" class="btn btn-outline-success fw-bold me-2">Regresar</a>
            <button type="submit" id="btnSiguiente" class="btn btn-success fw-bold">Siguiente</button>
        </div>
    </form>
    <input type="text" id="pantalla_ubicacion" name="pantalla_ubicacion" value="6" hidden>
</div>
@endsection


@section('page-script')
<script>
    const sistemas = @json($sistemas_apertura);
        const instalaciones = @json($superficie_instalacion);
        const sistemasRieles = @json($sistemas_rieles);
        const materiales = @json($materiales_rieles);
        const colores = @json($colores_rieles);
        
        // AUTOSELECCIONAR primer sistema de apertura
        $(document).ready(function () {
           // console.log('Sistemas de apertura:', sistemas);
            if (sistemas.length > 0) {
                //cargarSelect('#sistema_apertura', sistemas , 'valor');
                //$('#sistema_apertura').val(sistemas[0].id).selectpicker('refresh').trigger('changed.bs.select');
                // $('input[name="superficie_instalacion_riel"]').first().prop('checked', true).trigger('change');
                // $('#info_material_riel').toggleClass('d-none');
                // $('#info_color_riel').toggleClass('d-none');
            }
            $('#info_material_riel').toggleClass('d-none');
            $('#info_material_riel').toggleClass('d-none');

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
            //console.log(selectores);
            //console.log(valoresSesion['tipo']);
            selectores.forEach(selector => {
            //ocultar selectores si no estan en el avance_temporal
            if (!valoresSesion[selector.PAS_Html_name]) {
                console.log('ocultando selector: ', selector.PAS_Html_name);
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

            asignarValoresDesdeSesion(valoresSesion);
           /*  //trigger sistema_riel_selector
            $('#sistema_riel_selector').trigger('changed.bs.select');
            asignarValoresDesdeSesion(valoresSesion);
            // trigger material_riel_selector
            $('#material_riel_selector').trigger('changed.bs.select');
            asignarValoresDesdeSesion(valoresSesion); */

            //buscar en los elementos con clase color-option y asignar clase selected
            $('.color-option').each(function () {
                const color = $(this).data('color');
                if (valoresSesion['div_color_selector'] === color) {
                    $(this).addClass('selected');
                    //$('#color_riel_selector').val(color);
                }
            });


            //definir el valor de siguiente-vista
            const siguienteVista = valoresSesion['siguiente-vista'] || '';
            if (siguienteVista === 'resumen') {
                $('input[name="siguiente-vista"]').val('resumen');
                $('.btn-success').text('Resumen');
            } else {
            
                $('.btn-success').text('Siguiente');
            }
        });

        $('#sistema_apertura').on('changed.bs.select', function () {
            const aperturaId = $(this).val();
            console.log('...................SELECCIONADO TIPO CON VALOR: ', aperturaId);
            getSelectorSiguiente('sistema_apertura', aperturaId);
            //const instalacionesFiltradas = instalaciones.filter(item => item.id_padre == aperturaId);
            //cargar_radio_buttons('#radio_buttons_horizontal_list_group', instalacionesFiltradas, 'valor', 'id');
           

            // Mostrar ayuda si no hay instalaciones
            // $('#info_sistema_riel').toggleClass('d-none', instalacionesFiltradas.length > 0);
            // $('#sistema_riel_selector').empty().selectpicker('refresh');
            // $('#material_riel_selector').empty().selectpicker('refresh');
            // $('#div_color_selector').empty();
            $('#info_material_riel, #info_color_riel').addClass('d-none');
        });

      /*   function cargar_radio_buttons(selector, data, labelField, idField) {
            const container = $(selector);
            container.empty();

            data.forEach(item => {
                const id = `radio_btn_${item[idField]}`;
                const radio = `
                    <div class="form-check form-check-inline me-4">
                        <input class="form-check-input" type="radio" name="superficie_instalacion_riel" id="${id}" value="${item[idField]}" required>
                        <label class="form-check-label titulo" for="${id}">${item[labelField]}</label>
                    </div>`;
                container.append(radio);
            });

            
        } */
       $('div[name="radio_superficie_instalacion_riel"]').on('change', function () {
            const seleccion = $('input[name="superficie_instalacion_riel"]:checked').val();
            console.log('...................SELECCIONADO SUPERFICIE DE INSTALACION CON VALOR: ', seleccion);
            getSelectorSiguiente('superficie_instalacion_riel', seleccion);
        $('#div_color_selector').empty();
        $('#info_material_riel, #info_color_riel').addClass('d-none');
        });

        $('#sistema_riel_selector').on('changed.bs.select', function () {
            const option = $(this).find('option:selected');

            const nombre = option.data('nombre');
            const descripcion = option.data('descripcion');
            const imagen = option.data('imagen');
            console.log('nombre: ', nombre);
            console.log('descripcion: ', descripcion);
            console.log('imagen: ', imagen);
            //$('#sistema_info_card').addClass('d-none');
            // if (!nombre || !imagen) {
            //     $('#sistema_info_card').addClass('d-none');
            //     return;
            // }
            console.log('...................SELECCIONADO SISTEMA DE RIEL CON VALOR: ', option.val());
            getSelectorSiguiente('sistema_riel_selector', option.val());

            $('#sistema_nombre').text(nombre);
            $('#sistema_descripcion').text(descripcion || '');
            $('#sistema_img')
                .attr('src', `${assetapp}/images/cotizador/${imagen}`)
                .attr('onclick', `showModal('${assetapp}/images/cotizador/${imagen}')`);

            $('#sistema_info_card').removeClass('d-none');

            // Cargar materiales como antes
           /*  const rielId = $(this).val();
            const materialesFiltrados = materiales.filter(m => m.id_padre == rielId);
            cargarSelect('#material_riel_selector', materialesFiltrados, 'valor');
            $('#info_material_riel').toggleClass('d-none', materialesFiltrados.length > 0);
           */ 
          $('#div_color_selector').empty(); 
          $('#info_color_riel').addClass('d-none');
        });

        $('#material_riel_selector').on('changed.bs.select', function () {
            const materialId = $(this).val();
            const coloresFiltrados = colores.filter(c => c.id_padre == materialId);
            const contenedor = $('#div_color_selector');
            contenedor.empty();

            // $('#info_color_riel').toggleClass('d-none', coloresFiltrados.length > 0);

            // coloresFiltrados.forEach(color => {
            //     const div = $(`<div class="color-option" style="background-color: ${color.hex}" data-color="${color.nombre}"></div>`);
            //     div.on('click', function () {

            //         $('.color-option').removeClass('selected');
            //         $(this).addClass('selected');
            //         $('#color_riel_selector').val(color.nombre);
            //     });
            //     contenedor.append(div);
            // });
            console.log('...................SELECCIONADO MATERIAL DE RIEL CON VALOR: ', materialId);
            getSelectorSiguiente('material_riel_selector', materialId);
        });

        $('#form_apertura').on('submit', function (e) {
            e.preventDefault();

            if (!$('#sistema_apertura').val()) {
                return mostrarError('Por favor selecciona un sistema de apertura.');
            }

            if (!$('input[name="superficie_instalacion_riel"]:checked').val()) {
                return mostrarError('Por favor selecciona una superficie de instalación.');
            }

            if (!$('#sistema_riel_selector').val()) {
                return mostrarError('Por favor selecciona una opción de sistema de riel.');
            }

            if (!$('#material_riel_selector').val()) {
                return mostrarError('Por favor selecciona un material de riel.');
            }

            if (!$('input[name="color_selector"]').val()) {
                return mostrarError('Por favor selecciona un color.');
            }

            this.submit(); // solo si todo está bien
        });

        /* function cargarSelect(selector, data, labelField) {
            const select = $(selector);
            select.empty().append('<option value="">-- Selecciona --</option>');
            (data || []).forEach(item => {
                let option = `<option value="${item.id}"`;

                // Solo agregar atributos extra si es sistema de riel
                if (selector === '#sistema_riel_selector') {
                    option += ` data-nombre="${item.valor}" data-descripcion="${item.descripcion}" data-imagen="${item.imagen}"`;
                }

                option += `>${item[labelField]}</option>`;
                select.append(option);
            });
            select.selectpicker('refresh');
        } */

        function mostrarError(mensaje) {
            Swal.fire({
                icon: 'warning',
                title: '¡Atención!',
                text: mensaje,
                confirmButtonText: 'Aceptar'
            });
        }
</script>
@endsection