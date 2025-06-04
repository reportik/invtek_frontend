@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Tela')

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
        <h2 class="titulo">Tela</h2>
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #59981A;">
    </div>

    <form id="form_tela" action="{{ route('guardarAvance') }}" method="POST">
        @csrf


        <span class="subtitulo fw-bold d-block mb-3" style="display: block; text-align:left">ELIGE EL TIPO DE TELA EN
            QUE DESEAS CONFECCIONAR TU CORTINA</span>

        <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
            @foreach ($cards_3 as $item)
            <div class="col">
                <div class="card h-100">
                    <img class="card-img-top" src="{{ asset('images/' . $item['image']) }}" alt="Card image"
                        onclick="showModal('{{ asset('images/' . $item['image']) }}')"
                        style="cursor: pointer; object-fit: contain;">
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_tela" id="radio3_{{ $loop->index }}"
                                value="{{ $item['opcion_radio'] }}" onclick="toggleSelect_3()" {{
                                $item['a_selected']=='true' ? 'checked' : '' }}>
                            <label class="form-check-label subtitulo" for="radio3_{{ $loop->index }}">
                                {{ $item['opcion_radio'] }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-md-6 text-start">
                <label class="form-label fw-bold subtitulo text-uppercase">Selecciona tu Tela:</label>

                <select id="sel_tela_bo" name="sel_tela_bo"
                    class="selectpicker sel_tipo_tela form-control border-success mb-3" data-live-search="true"
                    data-size="5" onchange="selectEligeTela(event)">
                    @foreach ($telas_blackout as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>

                <select id="sel_tela_sheer" name="sel_tela_sheer"
                    class="selectpicker sel_tipo_tela form-control border-success mb-3" data-live-search="true"
                    data-size="5" style="display: none;" onchange="selectEligeTela(event)">
                    @foreach ($telas_sheer as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
                <label class="form-label fw-bold subtitulo text-uppercase">ó selecciona del cátalogo:</label>
                <button type="button" class="btn btn-primary form-control mt-2" data-bs-toggle="modal"
                    data-bs-target="#catalogoModal">
                    Ver Catálogo
                </button>
            </div>
            <input type="text" id="tela" name="tela" value="" hidden>

            {{-- Tarjeta de vista previa --}}
            <div class="col-md-6">
                <div class="card">
                    <img id="tarjeta_imagen" src="" class="card-img-top mt-3" alt="Tela seleccionada"
                        style="border-radius: 8px 8px 0 0;">
                    <div class="card-body">
                        <h6 id="tarjeta_titulo" class="card-title"></h6>
                        <p class="card-text">*Vista previa de la tela seleccionada</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botones de navegación --}}
        <div class="text-end mt-4">
            <input type="text" name="siguiente-vista" value="sistema_apertura" hidden>
            <a href="{{ route('medidas') }}" class="btn btn-outline-success fw-bold me-2">Regresar</a>

            <button type="submit" class="btn btn-success fw-bold">Siguiente</button>
        </div>
    </form>
</div>

@include('modals.catalogoModal')
@endsection

@section('page-script')
<script>
    const telasBlackout = @json($telas_blackout);
    const telasSheer = @json($telas_sheer);

    // Muestra el select y carga catálogo
    function toggleSelect_3() {
        $('#sel_tela_bo').selectpicker('hide');
        $('#sel_tela_sheer').selectpicker('hide');

        const selected = document.querySelector('input[name="tipo_tela"]:checked')?.value;
        if (selected === 'Blackout') {
            $('#sel_tela_bo').selectpicker('show');
            cargarCatalogo('blackout');
        } else if (selected === 'Sheer') {
            $('#sel_tela_sheer').selectpicker('show');
            cargarCatalogo('sheer');
        }

        updateCardImage();
    }

    function getVisibleSelectpicker() {
        return [...document.querySelectorAll('select.sel_tipo_tela')].find(el => $(el).is(':visible')) || null;
    }

    function selectEligeTela() {
        updateCardImage();
    }

    async function updateCardImage() {
        const select = getVisibleSelectpicker();
        if (!select) return;

        const selectedValue = $(select).selectpicker('val');
        const selectedText = $(select).find('option:selected').text();

        document.getElementById('tarjeta_titulo').innerText = selectedText;
        document.getElementById('tela').value = selectedText;

        try {
            const res = await fetch(`http://itekniaapp.serveftp.com:3036/get-image/${selectedValue}`);
            const data = await res.json();
            document.getElementById('tarjeta_imagen').src = `data:image/png;base64,${data.image}`;
        } catch (err) {
            document.getElementById('tarjeta_imagen').src = '';
            console.error('Error al cargar imagen:', err);
        }
    }

    // Form validation
    document.getElementById('form_tela').addEventListener('submit', function(e) {
        const tipo = document.querySelector('input[name="tipo_tela"]:checked');
        const tela = getVisibleSelectpicker();

        if (!tipo) {
            e.preventDefault();
            alert('Selecciona el tipo de tela.');
            return;
        }

        if (!$(tela).selectpicker('val')) {
            e.preventDefault();
            alert('Selecciona una tela específica.');
            return;
        }
    });

    window.onload = () => {
        toggleSelect_3();
    }

    function cargarCatalogo(tipo) {
        const container = document.getElementById('telas-container');
        container.innerHTML = '';
        const telas = tipo === 'blackout' ? telasBlackout : telasSheer;

        telas.forEach(tela => {
            const card = document.createElement('div');
            card.className = 'col-md';
            card.innerHTML = `
                <div class="card mb-4" data-id="${tela.id}" style="cursor: pointer;">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img class="card-img lazyload" data-src="{{ asset('images/telas_resized') }}/img_${tela.id}_${tela.Tipo}.png" alt="${tela.name}" />
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h6 class="card-title">${tela.name}</h6>
                                <div class="text-end">
                                    <button class="btn btn-sm btn-primary" data-bs-dismiss="modal" onclick="selectTela(event)">Seleccionar</button>
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
    }

    function selectTela(event) {
        const card = event.target.closest('.card');
        const telaId = card.dataset.id;
        const select = getVisibleSelectpicker();
        if (select) {
            $(select).selectpicker('val', telaId).selectpicker('refresh');
            updateCardImage();
        }
    }

    function showModal(src) {
        const modal = document.getElementById('imageModal');
        const img = document.getElementById('modalImage');
        img.src = src;
        modal.style.display = 'flex';
    }

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
        //trigger 
        updateCardImage();

        //console.log('Valores de sesión:', valoresSesion);
        //si siguiente-vista en valoresSesion es resuemen, entonces input type hidden tendra valor de resumen y boton siguiente texto de resumen
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