@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Medidas')

@section('content')

<style>
    .responsive-logo {
        height: 100px;
        margin-bottom: -100px;
    }

    .medida-input {
        position: absolute;
        border: 2px solid red;
        padding: 4px;
        width: 80px;
        font-size: 14px;
        background-color: white;
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.3);
        display: none;
    }

    #mensajeSeleccion {
        color: #888;
        font-style: italic;
        margin-top: 10px;
    }
</style>
<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">
<div class="container text-center" style="max-width: 900px;">
    <div style="display: flex; align-items: center; justify-content: center; margin: 20px 0;">
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
        <h2 class="titulo">
            Configuración y medidas
        </h2>
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
    </div>

    <form id="form_medidas" action="{{ route('guardarAvance') }}" method="POST">
        @csrf


        {{-- Selección tipo de riel --}}
        <div class="mb-4">
            <label class="form-label fw-bold subtitulo text-uppercase"
                style="display: block; text-align:left">Instalación
                del riel:
            </label>
            <div class="row row-cols-1 row-cols-md-3 g-4 mb-4" id="contenedor_tarjetas_riel">
                @foreach ($tiposRiel as $index => $item)
                <div class="col">
                    <div class="card">
                        <img class="card-img-top" src="{{ asset('images/cotizador/' . $item['image']) }}"
                            style="cursor:pointer; width: 100%; height: 180px; object-fit: contain;"
                            onclick="showModal('{{ asset('images/cotizador/' . $item['image']) }}')">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input tipo-riel-radio" type="radio" name="tipo_riel"
                                    id="radio_riel_{{ $index }}" value="{{ $item['id_riel'] }}" {{
                                    $item['a_selected']==='true' ? 'checked' : '' }}>
                                <label class="form-check-label subtitulo" for="radio_riel_{{ $index }}">
                                    {{ $item['opcion_radio'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                {{-- Canvas medidas con imagen de fondo --}}
                <div class="text-center mb-4">
                    <label class="form-label fw-bold subtitulo text-uppercase"
                        style="display: block; text-align:left">Medidas (m)</label>
                    <div id="mensajeSeleccion">Selecciona primero el Riel y captura las medidas en metros.</div>
                </div>

                <div class="position-relative d-flex justify-content-center">
                    <canvas id="canvas" width="400" height="400" style="border:1px solid #ccc;"></canvas>

                    <!-- Inputs flotantes -->
                    <input type="text" id="inputLadoA" name="lado_a" class="medida-input" placeholder="Lado A">
                    <input type="text" id="inputLadoB" name="lado_b" class="medida-input" placeholder="Lado B">
                    <input type="text" id="inputAlto" name="alto" class="medida-input" placeholder="Alto">
                    <input type="text" id="inputAncho" name="ancho" class="medida-input" placeholder="Ancho">
                    <input type="text" id="inputRadio" name="radio" class="medida-input" placeholder="Radio">
                </div>
            </div>

            <div class="col-md-6">
                {{-- Selectpicker número de hojas --}}
                <div class="mb-4 text-start mt-4">
                    <label class="form-label fw-bold subtitulo text-uppercase"
                        style="display: block; text-align:left">Hojas</label>
                    <select name="numero_hojas" class="selectpicker form-control border-success" data-live-search="true"
                        required>
                        <option value="">-- Selecciona --</option>
                        <option value="1 Hoja">1 Hoja</option>
                        <option value="2 Hoja">2 Hojas</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Botones de navegación --}}
        <div class="col text-end">
            <a href="{{ route('tipo_confeccion') }}" class="btn btn-outline-success fw-bold me-2">Regresar</a>
            <input type="text" name="siguiente-vista" value="telas" hidden>
            <button type="submit" class="btn btn-success fw-bold">Siguiente</button>
        </div>
    </form>
</div>

@endsection

@section('page-script')
<script>
    $(document).ready(function () {
        $('.selectpicker').selectpicker();
        //hideInputs(); // Ocultar inputs al cargar la página

        // Inicializar canvas y contexto
        const canvas = document.getElementById("canvas");
        const ctx = canvas.getContext("2d");
        ctx.fillStyle = "#f0f0f0"; // Color de fondo del canvas
        ctx.fillRect(0, 0, canvas.width, canvas.height);
   
    //const canvas = document.getElementById("canvas");
    //const ctx = canvas.getContext("2d");
    const mensajeSeleccion = document.getElementById("mensajeSeleccion");
    const inputs = document.querySelectorAll('.medida-input');

    const imagenes_medidas = @json($imagenes_medidas);

    function hideInputs() {
        inputs.forEach(input => input.style.display = 'none');
    }

    function positionInputs(coordenadas) {
        const rectCanvas = canvas.getBoundingClientRect();
        for (const [id, pos] of Object.entries(coordenadas)) {
            const input = document.getElementById(id);
            if (input) {
                input.style.left = `${canvas.offsetLeft + pos.x}px`;
                input.style.top = `${canvas.offsetTop + pos.y}px`;
                input.style.display = 'block';
            }
        }
    }

    document.querySelectorAll('.tipo-riel-radio').forEach(radio => {
        radio.addEventListener('change', function () {
            const rielSeleccionado = this.value;
            const data = imagenes_medidas.find(i => i.id_riel == rielSeleccionado);

            if (!data) return;

            mensajeSeleccion.style.display = 'none'; // Oculta mensaje

            const img = new Image();
            img.src = assetapp+`/images/cotizador/${data.image}`;
            img.onload = () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                hideInputs();

                try {
                    const coordenadas = JSON.parse(data.coordenadas);
                    positionInputs(coordenadas);
                } catch (e) {
                    console.error('Error al parsear coordenadas:', e);
                }
            };
        });
    });

    // Si cambia el tamaño de ventana, se recolocan los inputs
    window.addEventListener('resize', () => {
        const checked = document.querySelector('.tipo-riel-radio:checked');
        if (checked) {
            const data = imagenes_medidas.find(i => i.id_riel == checked.value);
            if (data) {
                try {
                    const coordenadas = JSON.parse(data.coordenadas);
                    positionInputs(coordenadas);
                } catch (e) {}
            }
        }
    });
    
    document.getElementById('form_medidas').addEventListener('submit', function (e) {
        // Validar que se haya seleccionado un tipo de riel y número de hojas
    const rielSeleccionado = document.querySelector('.tipo-riel-radio:checked');
    const numeroHojas = document.querySelector('[name="numero_hojas"]').value;

    if (!rielSeleccionado) {
        e.preventDefault();
        // cambiar por sweetalert
         Swal.fire({
            icon: 'warning',
            title: 'Riel faltante',
            text: 'Por favor selecciona un tipo de riel.'
        });
        //alert('Por favor selecciona un tipo de riel.');
        return;
    }

    if (!numeroHojas) {
        e.preventDefault();
        // cambiar por sweetalert
         Swal.fire({
            icon: 'warning',
            title: 'Número de hojas faltante',
            text: 'Por favor selecciona el número de hojas.'
        });
        //alert('Por favor selecciona el número de hojas.');
        return;
    }

    const coordenadas = imagenes_medidas.find(i => i.id_riel == rielSeleccionado.value);
    if (!coordenadas) return;

    let camposFaltantes = [];

    try {
        const visibles = JSON.parse(coordenadas.coordenadas);
        for (const id in visibles) {
            const input = document.getElementById(id);
            if (input && input.style.display !== 'none' && input.value.trim() === '') {
                camposFaltantes.push(input.placeholder || id);
            }
        }
    } catch (error) {
        console.error('Error al validar coordenadas:', error);
    }

    if (camposFaltantes.length > 0) {
        e.preventDefault();
        // cambiar por sweetalert
         Swal.fire({
            icon: 'warning',
            title: 'Campos faltantes',
            text: 'Por favor completa los siguientes campos: ' + camposFaltantes.join(', ')
        });
        //alert('Por favor completa los siguientes campos: ' + camposFaltantes.join(', '));
    }
});

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
        //trigger change .tipo-riel-radio
        document.querySelector('.tipo-riel-radio:checked')?.dispatchEvent(new Event('change'));
        asignarValoresDesdeSesion(valoresSesion);
});
</script>
@endsection