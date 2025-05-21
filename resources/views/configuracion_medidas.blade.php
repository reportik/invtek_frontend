@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Medidas')

@section('content')


<style>
    .medida-input {
        position: absolute;
        border: 2px solid red;
        padding: 4px;
        width: 80px;
        font-size: 14px;
        background-color: white;
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.3);
    }
</style>
<div class="container text-center" style="max-width: 900px;">
    <div style="display: flex; align-items: center; justify-content: center; margin: 20px 0;">
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
        <h2
            style="color: #59981A; font-family: 'Arial', sans-serif; font-weight: bold; text-align: center; letter-spacing: 1px;">
            Configuración y medidas
        </h2>
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
    </div>

    <form id="form_medidas" action="{{ route('guardarAvance') }}" method="POST">
        @csrf
        <input type="hidden" name="siguiente-vista" value="final">

        {{-- Selección tipo de riel --}}
        <div class="mb-4">
            <h5 class="text-success fw-bold">Instalación del riel:</h5>
            <div class="row row-cols-1 row-cols-md-3 g-4 mb-4" id="contenedor_tarjetas_riel">
                @foreach ($tiposRiel as $index => $item)
                <div class="col">
                    <div class="card">
                        <img class="card-img-top" src="{{ asset('images/cotizador/' . $item['image']) }}"
                            style="cursor:pointer; width: 100%; height: 180px; object-fit: cover;"
                            onclick="showModal('{{ asset('images/cotizador/' . $item['image']) }}')">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_riel"
                                    id="radio_riel_{{ $index }}" value="{{ $item['opcion_radio'] }}" {{
                                    $item['a_selected']==='true' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-success" for="radio_riel_{{ $index }}">
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
                    <h5 class="text-success fw-bold">Medidas</h5>
                    <p>NOTA: dentro de un canvas cargaremos la imagen con los inputs dibujados</p>
                </div>

                <div class="position-relative d-flex justify-content-center">
                    <canvas id="canvas" width="400" height="400" style="border:1px solid #ccc;"></canvas>

                    <!-- Inputs flotantes -->
                    {{-- <input type="text" id="inputLadoA" name="lado_a" class="medida-input" placeholder="Lado A">
                    <input type="text" id="inputLadoB" name="lado_b" class="medida-input" placeholder="Lado B">
                    <input type="text" id="inputAlto" name="alto" class="medida-input" placeholder="Alto"> --}}
                </div>
            </div>
            <div class="col-md-6">
                {{-- Selectpicker número de hojas --}}
                <div class="mb-4 text-start">
                    <h5 class="text-success fw-bold">Hojas</h5>
                    <select name="numero_hojas" class="selectpicker form-control border-success" data-live-search="true"
                        required>
                        <option value="">-- Selecciona --</option>
                        <option value="1 Hoja">1 Hoja</option>
                        <option value="2 Hoja">2 Hoja</option>
                        <option value="3 Hoja">3 Hoja</option>
                    </select>
                </div>
            </div>
        </div>




        {{-- Botones de navegación --}}
        <div class="col text-end">
            <a href="{{ route('tipo_confeccion') }}" class="btn btn-outline-success fw-bold me-2">Regresar</a>
            <button type="submit" class="btn btn-success fw-bold">Siguiente</button>
        </div>
    </form>
</div>

@endsection

@section('page-script')
<script>
    const canvas = document.getElementById("canvas");
    const ctx = canvas.getContext("2d");

    const img = new Image();
    img.src = "{{ asset('images/image35.png') }}"; // Ruta real de tu imagen

    img.onload = function () {
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        positionInputs();
    };

    function positionInputs() {
        const rectCanvas = canvas.getBoundingClientRect();

        const positions = {
            inputLadoA: { x: 0, y: 0 },
            inputLadoB: { x: 290, y: 30 },
            inputAlto: { x: 400, y: 220 }
        };

        for (const [id, pos] of Object.entries(positions)) { // Iterar sobre las posiciones
            const input = document.getElementById(id); // Obtener el input
            input.style.left = `${rectCanvas.left + pos.x}px`; // Calcular la posición
            input.style.top = `${rectCanvas.top + pos.y}px`; // Calcular la posición
        }
    }

    window.onresize = positionInputs;
</script>
@endsection