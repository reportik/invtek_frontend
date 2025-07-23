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
        <div class="mb-4 text-start" id="div_riel">
            @if(Auth::check() && Auth::user()->role_id == 1)
            <label class="form-label fw-bold subtitulo text-uppercase" style="display: block; text-align:left"><a
                    href="{{ route('opciones.show', 20) }}" target="_blank">Instalación
                    del riel:</a></label>
            @else
            <label class="form-label fw-bold subtitulo text-uppercase"
                style="display: block; text-align:left">Instalación
                del riel:</label>
            @endif
            <div class="row row-cols-1 row-cols-md-3 g-4 mb-4" id="contenedor_tarjetas_riel" name="card_tipo_riel">
                {{-- @foreach ($tiposRiel as $index => $item)
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
                @endforeach --}}
            </div>

        </div>

        <div class="row">
            <div class="col-md-6" id="div_medidas">
                {{-- Canvas medidas con imagen de fondo --}}
                <div class="text-center mb-4">
                    @if(Auth::check() && Auth::user()->role_id == 1)
                    <label class="form-label fw-bold subtitulo text-uppercase"
                        style="display: block; text-align:left"><a href="{{ route('opciones.show', 6) }}"
                            target="_blank">Medidas (m)</a></label>
                    @else
                    <label class="form-label fw-bold subtitulo text-uppercase"
                        style="display: block; text-align:left">Medidas
                        (m)</label>
                    @endif
                    <div class="descripcionSeleccion" id="mensajeSeleccion">Selecciona primero el Riel y captura las
                        medidas en metros.</div>
                </div>

                <div class="position-relative d-flex justify-content-center">
                    <canvas id="canvas" name="canvas" width="400" height="400" style="border:1px solid #ccc;"></canvas>

                    <!-- Inputs flotantes -->
                    <input type="text" id="inputLadoA" name="lado_a" class="medida-input" placeholder="Lado A">
                    <input type="text" id="inputLadoB" name="lado_b" class="medida-input" placeholder="Lado B">
                    <input type="text" id="inputAlto" name="alto" class="medida-input" placeholder="Alto">
                    <input type="text" id="inputAncho" name="ancho" class="medida-input" placeholder="Ancho">
                    <input type="text" id="inputRadio" name="radio" class="medida-input" placeholder="Radio">
                </div>
            </div>

            <div class="col-md-6 mb-4">
                {{-- Selectpicker número de hojas --}}

                <div id="contenedor_hojas" class="mb-4 text-start mt-4">
                    @if(Auth::check() && Auth::user()->role_id == 1)
                    <label class="form-label fw-bold subtitulo text-uppercase"
                        style="display: block; text-align:left"><a href="{{ route('opciones.show', 21) }}"
                            target="_blank">Hojas</a></label>
                    @else
                    <label class="form-label fw-bold subtitulo text-uppercase"
                        style="display: block; text-align:left">Hojas</label>
                    @endif
                    <select name="numero_hojas" class="selectpicker form-control border-success" data-live-search="true"
                        required>
                    </select>
                    {{-- Tarjeta estilo personalizada --}}
                    <div id="hojas_info_card" class="card d-none mt-4" style="position: absolute width: 100%;">
                        <img id="hojas_img" class="card-img-top" src="" alt="Sistema"
                            style="cursor:pointer; width: 100%; height: 180px; object-fit: contain;" onclick="">
                        <div class="card-body">
                            <div class="text-start">
                                <h5 id="hojas_nombre" class="titulo"></h5>
                                <p id="hojas_descripcion" class="mb-0 text-muted "></p>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Selectpicker dirección de apertura --}}
                <div id="contenedor_direccion_apertura" class="text-start">
                    @if(Auth::check() && Auth::user()->role_id == 1)
                    <label class="form-label fw-bold subtitulo text-uppercase"
                        style="display: block; text-align:left"><a href="{{ route('opciones.show', 23) }}"
                            target="_blank">Dirección de apertura:</a></label>
                    @else
                    <label class="form-label fw-bold subtitulo text-uppercase"
                        style="display: block; text-align:left">Dirección de
                        apertura:</label>
                    @endif

                    <div id="radio_direccion_apertura" name="radio_direccion_apertura">
                        {{-- @foreach ($direccion_apertura as $item)
                        <div class="form-check ml-4">
                            <input class="form-check-input" type="radio" name="direccion_apertura"
                                value="{{ $item['id'] }}" id="radio{{ $item['id'] }}" {{ $item['a_selected']=='true'
                                ? 'checked' : '' }}>
                            <label class="form-check-label titulo" for="radio{{ $item['id'] }}">
                                {{ $item['opcion_radio'] }} <i class="fa {{ $item['programacion'] }}" title=""></i>
                            </label>
                        </div>

                        @endforeach --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- Botones de navegación --}}
        <div class="col text-end">
            <a href="{{ route('tipo_confeccion') }}" class="btn btn-outline-success fw-bold me-2">Regresar</a>
            <input type="text" name="siguiente-vista" value="telas" hidden>
            <input type="text" name="pantalla_ubicacion" value="4" hidden>
            <button id="btnSiguiente" type="submit" class="btn btn-success fw-bold">Siguiente</button>
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
        //const mensajeSeleccion = document.getElementById("mensajeSeleccion");
        const inputs = document.querySelectorAll('.medida-input');

        const imagenes_medidas = @json($imagenes_medidas);
        const imagenes_medidas_array = Array.isArray(imagenes_medidas) ? imagenes_medidas : Object.values(imagenes_medidas);
        const hijos_imagenes_hojas = @json($hijos_imagenes_hojas);
            
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

        $('div[name="radio_direccion_apertura"]').on('change', function () {
            const seleccion = $('input[name="direccion_apertura"]:checked').val();
            console.log('...................SELECCIONADO DIRECCION APERTURA CON VALOR: ', seleccion);
            getSelectorSiguiente('direccion_apertura', seleccion);
        });

        $("select[name='numero_hojas']").on('change', function () {
            //console.log("Seleccionado hoja: ", this.value);
            //console.log("Seleccionado hoja: ", hijos_imagenes_hojas);
            // Mostrar tarjeta hojas_info_card si hay info
            const hojaSeleccionada = this.value;
            const hijos_imagenes_hojas_array = Array.isArray(hijos_imagenes_hojas) ? hijos_imagenes_hojas : Object.values(hijos_imagenes_hojas);
            const hoja = hijos_imagenes_hojas_array.find(h => h.id == hojaSeleccionada);
            if (hoja) {
                    $('#hojas_img').attr('src', assetapp+`/images/cotizador/${hoja.image}`);
                    $('#hojas_nombre').text(hoja.valor);
                    $('#hojas_descripcion').text('');
                    $('#hojas_info_card').removeClass('d-none');
                } else {
                    $('#hojas_info_card').addClass('d-none');
                }
            getSelectorSiguiente('numero_hojas', hojaSeleccionada);
        });
        
        $('div[name="card_tipo_riel"]').on('change', function () {
                const seleccion = $('input[name="tipo_riel"]:checked').val();
            
                const rielSeleccionado = seleccion;
                const data = imagenes_medidas_array.find(i => i.id_riel == rielSeleccionado);
                console.log("rielSeleccionado: ", rielSeleccionado);
                console.log("data imagenes ", data);
                if (!data) return;
                
                //mensajeSeleccion.style.display = 'none'; // Oculta mensaje
                
                const img = new Image();
                img.src = assetapp+`images/cotizador/${data.image}`;
                img.setAttribute('data-id', data.id_opcion);
                
                //console.log(data.coordenadas);
                console.log(img.src);
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
                
              /*   // Vaciar y recargar selectpicker numero_hojas
                const $numeroHojas = $("select[name='numero_hojas']");
                $numeroHojas.empty();
                
                const hijos_imagenes_hojas_array = Array.isArray(hijos_imagenes_hojas) ? hijos_imagenes_hojas : Object.values(hijos_imagenes_hojas);
                let hojasRiel = hijos_imagenes_hojas_array.filter(i => i.id_riel == rielSeleccionado);
                hojasRiel.forEach(h => {
                    //console.log("Agregar hoja: ", h.id_imagen);
                    $numeroHojas.append(`<option value="${h.id_imagen}">${h.valor}</option>`);
                });
                $numeroHojas.selectpicker('refresh'); */


                //$('#hojas_info_card').addClass('d-none');
                getSelectorSiguiente('tipo_riel', rielSeleccionado);
                console.log("CANVAS data.id_opcion: ", data.id_opcion);
                //timeout 100ms
                setTimeout(() => {
                getSelectorSiguiente('canvas', data.id_opcion);
                }, 200);
                //select[name='numero_hojas'] ha cambiado, trigger change
                //$("select[name='numero_hojas']").trigger('change');
            
        });

        // Si cambia el tamaño de ventana, se recolocan los inputs
        window.addEventListener('resize', () => {
            const checked = document.querySelector('input[name="tipo_riel"]:checked');
            if (checked) {
                const data = imagenes_medidas_array.find(i => i.id_riel == checked.value);
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
            const rielSeleccionado = document.querySelector('input[name="tipo_riel"]:checked');
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

            const coordenadas = imagenes_medidas_array.find(i => i.id_riel == rielSeleccionado.value);
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
        }); //fin submit

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
        /* //trigger change tipo_riel
        document.querySelector('input[name="tipo_riel"]:checked')?.dispatchEvent(new Event('change'));
        asignarValoresDesdeSesion(valoresSesion);
        $("select[name='numero_hojas']").trigger('change');

        //definir el valor de siguiente-vista
        const siguienteVista = valoresSesion['siguiente-vista'] || '';
        if (siguienteVista === 'resumen') {
            $('input[name="siguiente-vista"]').val('resumen');
            $('.btn-success').text('Resumen');
        } else {
            
            $('.btn-success').text('Siguiente');
        } */
    }); //fin document ready
</script>
@endsection