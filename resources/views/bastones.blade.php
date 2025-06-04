@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Accesorios de Apertura')

@section('content')
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

        <div class="row mb-4">
            <div class="col-md-6 text-start">
                <label class="form-label fw-bold">
                    Accesorio de apertura:
                    <i id="info_accesorio" class="fa fa-info-circle text-muted ms-1 d-none"
                        title="Selecciona un tipo de accesorio."></i>
                </label>
                <select id="accesorio_selector" name="accesorio" class="selectpicker form-control border-success"
                    data-live-search="true" required>
                    <option value="">-- Selecciona --</option>
                    {{-- Opciones cargadas con JS --}}
                </select>
            </div>

            <div class="col-md-6 text-start">
                <label class="form-label fw-bold">
                    Material:
                    <i id="info_material" class="fa fa-info-circle text-muted ms-1 d-none"
                        title="Selecciona el material del accesorio."></i>
                </label>
                <select id="material_selector" name="material" class="selectpicker form-control border-success"
                    data-live-search="true" required>
                    <option value="">-- Selecciona --</option>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12 text-start">
                <label class="form-label fw-bold">
                    Modelo:
                    <i id="info_modelo" class="fa fa-info-circle text-muted ms-1 d-none"
                        title="Selecciona el modelo disponible."></i>
                </label>
                <select id="modelo_selector" name="modelo" class="selectpicker form-control border-success"
                    data-live-search="true" required>
                    <option value="">-- Selecciona --</option>
                </select>
            </div>
        </div>

        <div id="modelo_card" class="card d-none mb-4 text-start p-3">
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
                    <div class="mt-3">
                        <label class="form-label fw-bold">Largo:</label>
                        <select id="largo_selector" name="largo" class="selectpicker form-control border-success"
                            data-live-search="true" required>
                            <option value="">-- Selecciona --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botones --}}
        <div class="text-end">
            <a href="{{ route('sistema_apertura') }}" class="btn btn-outline-success fw-bold me-2">Regresar</a>
            <button type="submit" class="btn btn-success fw-bold">Siguiente</button>
        </div>
    </form>
</div>
@endsection

@section('page-script')
<script>
    //obtener de result array
    const accesorios = @json($result['accesorios']);     // id, valor
    const materiales = @json($result['materiales']);     // id, valor, id_padre
    const modelos = @json($result['modelos']);           // id, valor, imagen, precio, id_padre
    const largos = @json($result['largos']);             // id, valor, id_padre

    $(document).ready(() => {
        cargarSelect('#accesorio_selector', accesorios, 'valor');
            
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
        $('#accesorio_selector').trigger('changed.bs.select');
        asignarValoresDesdeSesion(valoresSesion);
        $('#material_selector').trigger('changed.bs.select');
        asignarValoresDesdeSesion(valoresSesion);
        $('#modelo_selector').trigger('changed.bs.select');
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

    $('#accesorio_selector').on('changed.bs.select', function () {
        const id = $(this).val();
        const filtrados = materiales.filter(m => m.id_padre == id);
        cargarSelect('#material_selector', filtrados, 'valor');
        $('#info_material').toggleClass('d-none', filtrados.length > 0);
        $('#modelo_selector').empty().selectpicker('refresh');
        $('#modelo_card').addClass('d-none');
    });

    $('#material_selector').on('changed.bs.select', function () {
        const id = $(this).val();
        const filtrados = modelos.filter(m => m.id_padre == id);
        cargarSelect('#modelo_selector', filtrados, 'valor');
        $('#info_modelo').toggleClass('d-none', filtrados.length > 0);
        $('#modelo_card').addClass('d-none');
    });

    $('#modelo_selector').on('changed.bs.select', function () {
        const id = $(this).val();
        const modelo = modelos.find(m => m.id == id);
        if (!modelo) return;

        $('#modelo_nombre').text(modelo.valor);
        //$('#modelo_precio').text(modelo.precio.toFixed(2));
        $('#modelo_img').attr('src', `${assetapp}/images/cotizador/${modelo.imagen}`);
        $('#modelo_card').removeClass('d-none');

        // Cargar largos
        const largosFiltrados = largos.filter(l => l.id_padre == modelo.id);
        cargarSelect('#largo_selector', largosFiltrados, 'valor');
    });

    $('#form_accesorios').on('submit', function (e) {
        e.preventDefault();
        if (!$('#accesorio_selector').val()) return mostrarError('Selecciona un accesorio.');
        if (!$('#material_selector').val()) return mostrarError('Selecciona un material.');
        if (!$('#modelo_selector').val()) return mostrarError('Selecciona un modelo.');
        if (!$('#largo_selector').val()) return mostrarError('Selecciona el largo.');
        this.submit();
    });

    function cargarSelect(selector, data, labelField) {
        const select = $(selector);
        select.empty().append('<option value="">-- Selecciona --</option>');
        data.forEach(item => {
            select.append(`<option value="${item.id}">${item[labelField]}</option>`);
        });
        select.selectpicker('refresh');
    }

    function mostrarError(msg) {
        Swal.fire({
            icon: 'warning',
            title: '¡Atención!',
            text: msg,
            confirmButtonText: 'Aceptar'
        });
    }
</script>
@endsection