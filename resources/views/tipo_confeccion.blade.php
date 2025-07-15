@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Tipo de Producto')

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
        <div class="row" id="div_confeccion">
            <div class="mb-4 col-md-6 text-start">
                @if(Auth::check() && Auth::user()->role_id == 1)
                <label for="tipo_confeccion" class="form-label fw-bold text-uppercase">
                    <a href="{{ route('opciones.show', 4) }}" target="_blank">TIPO DE CONFECCIÓN:</a>
                </label>
                @else
                <label for="tipo_confeccion" class="form-label fw-bold text-uppercase">TIPO DE CONFECCIÓN:</label>
                @endif
                <select id="tipo_confeccion" name="tipo_confeccion" class="selectpicker form-control border-success"
                    data-live-search="true" required>
                    <option value="">-- Selecciona una opción --</option>
                    @foreach ($tiposConfeccion as $item )
                    {{-- 0 => array:5 [▼
                    "id" => 8
                    "valor" => "Tradicional"
                    "descripcion" => null
                    "imagen" => "1749696536.jpg"
                    "id_padre" => null
                    ] --}}
                    <option value="{{ $item['id'] }}" data-descripcion="{{ $item['descripcion'] }}"
                        data-img="{{ $item['imagen'] }}">{{ $item['valor'] }}</option>
                    @endforeach

                </select>
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
                <input type="text" name="siguiente-vista" value="medidas" hidden>

                <div id="contenedor_tarjetas_confeccion" class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                    {{-- Tarjetas serán insertadas aquí dinámicamente --}} </div>
            </div>
            <div class="col text-end mt-4">
                {{-- Botón de cancelar --}}
                {{-- Botón de regresar route('tipo_producto') --}}
                <a href="{{ route('tipo_producto') }}" class="btn btn-outline-success fw-bold me-2">Regresar</a>
                {{-- Botón de siguiente --}}
                <button type="submit" class="btn btn-outline-success fw-bold">Siguiente</button>
            </div>
        </div>
    </form>
</div>

@endsection

@section('page-script')
<script>
    const tarjetasConfeccion = @json($cards_confeccion);
    
    
    $('#tipo_confeccion').on('changed.bs.select', function () {
        console.log('Tipo de confección seleccionado:', $(this).val());
        const tipoSeleccionado = $(this).val();
        // texto de la opción seleccionada
        const textoSeleccionado = $(this).find('option:selected').text();
        const contenedor = $('#contenedor_tarjetas_confeccion');
        contenedor.empty(); // Limpiar tarjetas anteriores

        const filtradas = tarjetasConfeccion.filter(t => t.tipo === tipoSeleccionado);// Filtrar tarjetas por tipo de confección

        if (filtradas.length === 0) {
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
        }

        filtradas.forEach((item, index) => {
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
        });
       
    });

    //// Validar que se haya seleccionado una opción de confección
    $('#form_confeccion').on('submit', function (e) {
        const tipoSeleccionado = $('#tipo_confeccion').val();
        const opcionSeleccionada = $('input[name="radio_step_2"]:checked').val();

        if (!tipoSeleccionado || !opcionSeleccionada) {
            e.preventDefault(); // Evitar el envío del formulario
            //cambiar por sweetalert
            //alert('Por favor, selecciona una opción de confección.');
            Swal.fire({
                icon: 'warning',
                title: '¡Atención!',
                text: 'Por favor, selecciona un Estilo de confección ó Fullness',
                confirmButtonText: 'Aceptar'
            });
            //alert('Por favor, selecciona una opción de confección.');
        }
    });

    
        $('#tipo_confeccion').on('changed.bs.select', function () {
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
        });


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
        //trigger change $('#tipo_confeccion').on('changed.bs.select'
        $('#tipo_confeccion').trigger('changed.bs.select');
        asignarValoresDesdeSesion(valoresSesion);

        //definir el valor de siguiente-vista
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