@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Material')

@section('content')
<style>
    .responsive-logo {
        height: 100px;
        margin-bottom: -100px;
    }

    .card-img-top {
        object-fit: cover;
        height: 180px;
    }
</style>

<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">
<div class="container text-center" style="max-width: 900px;">
    <div class="d-flex align-items-center justify-content-center my-4">
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #59981A;">
        <h2 class="titulo">Material</h2>
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #59981A;">
    </div>

    <form id="form_material" action="{{ route('guardarAvance') }}" method="POST">
        @csrf
        <div id="div_categorias">

            @if (Auth::check() && Auth::user()->role_id == 1)
            <span class="subtitulo fw-bold d-block mb-3" style="display: block; text-align:left">ELIGE EL <a
                    href="{{ route('opciones.show', 7) }}" target="_blank">TIPO DE MATERIAL</a> EN
                QUE DESEAS CONFECCIONAR TU CORTINA</span>
            @else
            <span class="subtitulo fw-bold d-block mb-3" style="display: block; text-align:left">ELIGE EL TIPO DE
                MATERIAL EN QUE DESEAS CONFECCIONAR TU CORTINA</span>
            @endif
            <div name="card_tipo_material" class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                {{-- @foreach ($tipo_tela as $tela)
                <div class="col">
                    <div class="card h-100">
                        <img class="card-img-top" src="{{ asset('images/cotizador/' . $tela['imagen']) }}"
                            alt="Card image" style="cursor: pointer; object-fit: contain;">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_tela"
                                    id="radio3_{{ $loop->index }}" value="{{ $tela['id'] }}" onclick="toggleSelect_3()"
                                    {{ $tela['a_selected']=='true' ? 'checked' : '' }}>
                                <label class="form-check-label subtitulo" for="radio3_{{ $loop->index }}">
                                    {{ $tela['valor'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach --}}
            </div>
        </div>

        <div class="row" id="div_materiales" style="display: none;">
            <div class="col-md-6 text-start">
                <label class="form-label fw-bold subtitulo text-uppercase">Selecciona Material:</label>

                {{-- @php
                $telasPorPadre = collect($telas)->groupBy('id_padre');
                @endphp

                @foreach ($telasPorPadre as $idPadre => $grupoTelas)
                <div id="div_tela_{{ $idPadre }}" class="div_tela" style="display: none;">
                    <select id="sel_tela_{{ $idPadre }}" name="sel_tela_{{ $idPadre }}"
                        class="selectpicker sel_tipo_tela form-control border-success mb-3" data-live-search="true"
                        data-size="5" onchange="selectEligeTela(event)">
                        @foreach ($grupoTelas as $tela)
                        <option data-imagen="{{ $tela['imagen'] }}" data-descripcion="{{ $tela['descripcion'] }}"
                            value="{{ $tela['id'] }}">
                            {{ $tela['valor'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endforeach --}}
                <div id="div_sel_material" class="mb-3">
                </div>
                <label class="form-label fw-bold subtitulo text-uppercase">ó selecciona del cátalogo:</label>
                <button type="button" class="btn btn-primary form-control mt-2" data-bs-toggle="modal"
                    data-bs-target="#catalogoModal">
                    Ver Catálogo
                </button>
            </div>
            <input type="text" id="material" name="material" value="" hidden>

            {{-- Tarjeta de vista previa --}}
            <div class="col-md-6">
                <div class="card">
                    <img id="tarjeta_imagen" src="" class="card-img-top" alt="Material seleccionado">
                    <div class="card-body">
                        <h6 id="tarjeta_titulo" class="card-title"></h6>
                        <p id="tarjeta_descripcion" class="card-text"></p>
                        <p class="card-text">*Vista previa del material seleccionado</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botones de navegación --}}
        <div class="text-end mt-4">
            <input type="text" name="siguiente-vista" value="sistema_apertura" hidden>
            <input type="text" name="actual-vista" value="telas" hidden>
            <a href="#" name="anterior-vista" class="btn btn-outline-success fw-bold me-2">
                <i class="fas fa-arrow-left me-2"></i>Regresar
            </a>
            <button id="btnSiguiente" type="submit" class="btn btn-success fw-bold">Siguiente</button>
        </div>
    </form>
    <input type="text" name="pantalla_ubicacion" value="5" hidden>
</div>

@include('modals.catalogoModal')
@endsection

@section('page-script')
<script>
    let cargandoSelectores = true; // Variable global para controlar si se están cargando selectores
    
    /* // Muestra el select y carga catálogo (Comentado por ahora por cambios de diseño)
    function toggleSelect_3() {
        //hide all divs
        $('.div_tela').hide();
        const selected = document.querySelector('input[name="tipo_material"]:checked')?.value;
        console.log(selected);
        $('#div_tela_'+selected).show();
        cargarCatalogo(selected);
        updateCardMaterial();
    }
     */

    function getVisibleSelectpicker() {
        const select = document.getElementById('producto_categoria_selector');
        if (select) {
            return select;
        }
       return null;
    }

  /*   function selectEligeMaterial() {
        updateCardMaterial();
    } */

    async function updateCardMaterial() {
        const select = getVisibleSelectpicker();
        if (!select) return;

        const selectedValue = $(select).selectpicker('val');
        const selectedText = $(select).find('option:selected').text();

        document.getElementById('tarjeta_titulo').innerText = selectedText;
        document.getElementById('material').value = selectedText;
        
        const selectedOption = $(select).find('option:selected');
        const imagen = selectedOption.data('imagen');
        const descripcion = selectedOption.data('descripcion');
        document.getElementById('tarjeta_descripcion').innerText = descripcion;
        document.getElementById('tarjeta_material').src = `{{ asset('images/categories') }}/${imagen}`;
    }

    // Form validation
    document.getElementById('form_material').addEventListener('submit', function(e) {
        const tipo = document.querySelector('input[name="tipo_material"]:checked');
        const material = getVisibleSelectpicker();

        if (!tipo) {
            e.preventDefault();
            alert('Selecciona el tipo de material.');
            return;
        }

        if (!$(material).selectpicker('val')) {
            e.preventDefault();
            alert('Selecciona un material específico.');
            return;
        }
    });

    /* window.onload = () => { //esto queda comentado por ahora por cambios de diseño
        toggleSelect_3();
    } */

  /*   function cargarCatalogo(tipo) {
        const container = document.getElementById('material-container');
        container.innerHTML = '';
        
        const materialSeleccionada = document.querySelector('input[name="tipo_material"]:checked');
        // Filtrar las material según el tipo seleccionado
        const materialFiltradas = material.filter(material => material.id_padre === materialSeleccionada.value);
        materialFiltradas.forEach(material => {
            const card = document.createElement('div');
            card.className = 'col-md';
            card.innerHTML = `
                <div class="card mb-4" data-id="${material.id}" style="cursor: pointer;">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img class="card-img lazyload" data-src="{{ asset('images/categories') }}/${material.imagen}" alt="${material.valor}" />
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h6 class="card-title">${material.valor}</h6>
                                <div class="text-end">
                                    <button class="btn btn-sm btn-primary" data-bs-dismiss="modal" onclick="selectMaterial(event)">Seleccionar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;    
            container.appendChild(card);
        });

        // Lazyload con IntersectionObserver
        const lazyImages = document.querySelectorAll('.lazyload');
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.src = entry.target.dataset.src;
                    entry.target.classList.remove('lazyload');
                    obs.unobserve(entry.target);
                }
            });
        });
        lazyImages.forEach(img => observer.observe(img));
    } */

    function selectMaterial(event) {
        const card = event.target.closest('.card');
        const materialId = card.dataset.id;
        const select = getVisibleSelectpicker();
        if (select) {
            $(select).selectpicker('val', materialId).selectpicker('refresh');
            updateCardImage();
        }
    }

    /* function showModal(src) {
        const modal = document.getElementById('imageModal');
        const img = document.getElementById('modalImage');
        img.src = src;
        modal.style.display = 'flex';
    } */

    async function updateCardImage() {
        const select = getVisibleSelectpicker();
        if (!select) return;
        
        const selectedValue = $(select).selectpicker('val');
        const selectedText = $(select).find('option:selected').text();
        
        document.getElementById('tarjeta_titulo').innerText = selectedText;
        document.getElementById('material').value = selectedText;
        
        //const selectedOption = $(select).find('option:selected');
        const imagen = selectedValue + '.png';
        //const descripcion = selectedOption.data('descripcion');
        //document.getElementById('tarjeta_descripcion').innerText = descripcion;
        
        /* try {
        const res = await fetch(`http://itekniaapp.serveftp.com:3036/get-image/${selectedValue}`);
        const data = await res.json();
        document.getElementById('tarjeta_imagen').src = `data:image/png;base64,${data.image}`;
        } catch (err) {
        document.getElementById('tarjeta_imagen').src = '';
        console.error('Error al cargar imagen:', err);
        } */
        document.getElementById('tarjeta_imagen').src = `{{ asset('images/categories') }}/${imagen}`;
    }

    $(document).ready(function () {
        //1.- obtener valores de sesión
        const gvaloresSesion = @json(session()->all());
        let valoresSesion = gvaloresSesion['avance_temporal'] || {};
        //hide select
        $('.selectpicker').hide();

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
        2.- validar si hay valores en la sesión para habilitar o deshabilitar el botón siguiente
        bloque
        */
        if (Object.keys(valoresSesion).length === 0 || valoresSesion === null) {
        $(`#btnSiguiente`).attr('disabled', true);
        }else{
        $(`#btnSiguiente`).attr('disabled', false);
        }
        
        //3.- obtener selector siguiente para mostrar el primer selector
        getSelectorSiguiente(null, null);
        
        //4.- obtener selectores a cargar y llenarlos con los valores de la sesión

        selectoresACargar = selectores.filter(selector => selector.PAS_Pantalla_Ubicacion == $('input[name="pantalla_ubicacion"]').val());
        console.log('BLOQUE selectores a cargar: ', selectoresACargar);
        
        // Función para cargar selectores de forma secuencial
        function cargarSelectores() {
            let indice = 0;
            
            function cargarSiguiente() {
                if (indice >= selectoresACargar.length) {
                    // Marcar que terminó la carga de selectores
                    cargandoSelectores = false;
                    console.log('BLOQUE: Carga de selectores completada');
                    return;
                }
                
                const selector = selectoresACargar[indice];
                
                if (selector.PAS_Pantalla_Ubicacion == $('input[name="pantalla_ubicacion"]').val() &&
                    valoresSesion[selector.PAS_Html_name]) {

                    if (indice === selectoresACargar.length - 1) {
                        getSelectorAndFill(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], selector.PAS_Pantalla_Ubicacion);
                        console.log('BLOQUE último selector: ', selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name]);
                        getSelectorSiguiente(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name]);
                        // Esperar un poco antes de marcar como completado
                        setTimeout(() => {
                            cargandoSelectores = false;
                            console.log('BLOQUE: Carga de selectores completada');
                        }, 500);
                    } else {
                        console.log('BLOQUE llenando selector: ', selector.PAS_Html_name);
                        getSelectorAndFill(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], selector.PAS_Pantalla_Ubicacion);
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

        
        $('div[name="card_tipo_material"]').on('change', function () {
            // Verificar si se están cargando selectores
            if (cargandoSelectores) {
                console.log('BLOQUE: Ignorando evento change de card durante carga de selectores');
                return;
            }
            
            const materialSeleccionado = $('input[name="tipo_material"]:checked').val();
            
            //const data = imagenes_medidas_array.find(i => i.id_riel == rielSeleccionado);
            console.log("materialSeleccionado: ", materialSeleccionado);
            
            //obtener data-programacion del input seleccionado
            //const dataProgramacion = $('input[name="tipo_material"]:checked').data('programacion');
            // Contenedor donde irá el selectpicker
            const selectContainer = document.getElementById('div_sel_material'); // O el div adecuado
            // Contenedor del modal
            const modalContainer = document.getElementById('telas-container');
            fetchAndFillProductosByCategory(materialSeleccionado, selectContainer, modalContainer);
            
            getSelectorSiguiente('tipo_material', materialSeleccionado);
        });

        // Evento delegado para el selectpicker de material que se crea dinámicamente
        $(document).on('change', '#producto_categoria_selector', function () {
            // Verificar si se están cargando selectores o asignando valores programáticamente
            if (cargandoSelectores || window.asignandoValoresProgramaticamente) {
                console.log('BLOQUE: Ignorando evento change de select durante carga/asignación programática');
                return;
            }
            
            const materialSeleccionado = $(this).val();
            console.log("Material específico seleccionado: ", materialSeleccionado);
            
            // Actualizar la tarjeta de vista previa
            updateCardImage();
            
            // Llamar al siguiente selector si es necesario
            getSelectorSiguiente('producto_categoria', materialSeleccionado);
        });
        


    });
</script>
@endsection