@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Tipo de Producto')

@section('content')

<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">

<div class="container text-center" style="max-width: 700px;">
    <div style="display: flex; align-items: center; justify-content: center; margin: 20px 0;">
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
        <h2
            style="color: #59981A; font-family: 'Arial', sans-serif; f font-weight: bold; text-align: center; letter-spacing: 1px;">
            Tipo de confección
        </h2>
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
    </div>
    <form id="form_confeccion" action="{{ route('guardarAvance') }}" method="POST">
        @csrf
        <div class="nav-align-top">
            <div class="mb-4">
                <label class="form-label fw-bold">CONFECCIÓN:</label>
                <select id="tipo_confeccion" class="selectpicker form-control border-success" data-live-search="true"
                    required>
                    <option value="">-- Selecciona una opción --</option>
                    @foreach ($tiposConfeccion as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <input type="text" name="siguiente-vista" value="medidas" hidden>

            <div id="contenedor_tarjetas_confeccion" class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                {{-- Tarjetas serán insertadas aquí dinámicamente --}} </div>
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
            contenedor.append(`<div class="col-md-12">
                <div class="alert alert-success">Selecciona una opción de confección ${ textoSeleccionado }.</div>
            </div>`);
        }

        filtradas.forEach((item, index) => {
            const checked = item.a_selected === 'true' ? 'checked' : '';
            const tarjeta = `
                <div class="col">
                    <div class="card">
                        <img class="card-img-top" src="${assetapp}/images/cotizador/${item.image}" style="cursor:pointer; width: 100%; height: 180px; object-fit: cover;"
                            onclick="showModal('${assetapp}/images/cotizador/${item.image}')">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="radio_step_2"
                                    id="radio2_${index}" value="${item.opcion_radio}" ${checked}
                                    onclick="toggleSelect_2()">
                                <label class="form-check-label fw-bold text-success" for="radio2_${index}">
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
                text: 'Por favor, selecciona una opción de confección.',
                confirmButtonText: 'Aceptar'
            });
            //alert('Por favor, selecciona una opción de confección.');
        }
    });

</script>
@endsection