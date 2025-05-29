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

<div class="container text-center" style="max-width: 900px;">
    <div class="d-flex align-items-center justify-content-center my-4">
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #59981A;">
        <h2 class="text-success fw-bold">Sistema de Apertura</h2>
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #59981A;">
    </div>

    <form id="form_apertura" action="{{ route('guardarAvance') }}" method="POST">
        @csrf
        <input type="hidden" name="siguiente-vista" value="vista_siguiente">
        <div class="row">
            <div class="col-md-6">

                {{-- Sistema de apertura --}}
                <div class="mb-4 text-start">
                    <label class="form-label fw-bold">Sistema de apertura:</label>
                    <select id="sistema_apertua" name="sistema_apertua" class="selectpicker form-control border-success"
                        data-live-search="true" required>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                {{-- Tipo de instalación --}}
                <div class="mb-4 text-start">
                    <label class="form-label fw-bold">Superficie de Instalación:</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="instalacion" id="techo" value="techo"
                            required>
                        <label class="form-check-label" for="techo">Instalación a Techo</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="instalacion" id="muro" value="muro">
                        <label class="form-check-label" for="muro">Instalación a Muro</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                {{-- Sistema de riel--}}
                <div class="mb-4 text-start">
                    <label class="form-label fw-bold">Sistema de riel:</label>
                    <select id="sistema_selector" name="sistema" class="selectpicker form-control border-success"
                        data-live-search="true" required></select>
                </div>
            </div>
            <div class="col-md-6">
                {{-- Descripción sistema de riel --}}
                <div id="sistema_info" class="card-system mb-4 d-none">
                    <img id="sistema_img" src="" alt="Sistema">
                    <div class="text-start">
                        <h5 id="sistema_nombre" class="text-success fw-bold"></h5>
                        <p id="sistema_descripcion" class="mb-0">Lorem ipsum, dolor sit amet consectetur adipisicing
                            elit. Dicta
                            explicabo odit fuga ipsa voluptatum nobis quibusdam earum qui est repellat id in sunt beatae
                            sed,
                            distinctio reprehenderit similique tempora saepe.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                {{-- Riel --}}
                <div class="mb-4 text-start">
                    <label class="form-label fw-bold">Riel:</label>
                    <select id="riel_selector" name="riel" class="selectpicker form-control border-success"
                        data-live-search="true" required></select>
                </div>
            </div>
            <div class="col-md-6">
                {{-- Colores --}}
                <div class="mb-4 text-start">
                    <label class="form-label fw-bold">Color:</label>
                    <div id="color_selector" class="d-flex flex-wrap"></div>
                    <input type="hidden" name="color_riel" id="color_riel">
                </div>
            </div>

        </div>
        {{-- Botones --}}
        <div class="text-end mt-4">
            <a href="{{ route('telas') }}" class="btn btn-outline-success fw-bold me-2">Regresar</a>
            <button type="submit" class="btn btn-success fw-bold">Siguiente</button>
        </div>
    </form>
</div>
@endsection

@section('page-script')
<script>
    const sistemas = @json($sistemas_apertura);     // { apertura_id: [ {id, nombre, descripcion, image} ] }
    const rieles = @json($rieles);         // { apertura_id: [ {id, nombre} ] }
    const colores = @json($colores);       // { riel_id: [ {nombre, hex} ] }

    $('#sistema_apertua').on('changed.bs.select', function () {
        const aperturaId = $(this).val();
        cargarSelect('#sistema_selector', sistemas[aperturaId], 'nombre');
        $('#sistema_info').addClass('d-none');
        $('#riel_selector').empty().selectpicker('refresh');
        $('#color_selector').empty();
    });

    $('#sistema_selector').on('changed.bs.select', function () {
        const option = $(this).find('option:selected');
        const sistema = sistemas[$('#sistema_apertua').val()]?.find(s => s.id == option.val());
        if (!sistema) return;

        $('#sistema_nombre').text(sistema.nombre);
        $('#sistema_descripcion').text(sistema.descripcion || '');
        $('#sistema_img').attr('src', `/images/sistemas/${sistema.image}`);
        $('#sistema_info').removeClass('d-none');

        // Cargar rieles según apertura
        const aperturaId = $('#sistema_apertua').val();
        cargarSelect('#riel_selector', rieles[aperturaId], 'nombre');
        $('#color_selector').empty();
    });

    $('#riel_selector').on('changed.bs.select', function () {
        const rielId = $(this).val();
        const colorList = colores[rielId] || [];

        const contenedor = $('#color_selector');
        contenedor.empty();

        colorList.forEach(color => {
            const div = $(`<div class="color-option" style="background-color: ${color.hex}" data-color="${color.nombre}"></div>`);
            div.on('click', function () {
                $('.color-option').removeClass('selected');
                $(this).addClass('selected');
                $('#color_riel').val(color.nombre);
            });
            contenedor.append(div);
        });
    });

    $('#form_apertura').on('submit', function (e) {
        if (!$('#sistema_apertua').val() || !$('#sistema_selector').val() || !$('#riel_selector').val() || !$('#color_riel').val()) {
            e.preventDefault();
            alert('Por favor completa todos los campos.');
        }
    });

    // Utilidad para llenar selects
    function cargarSelect(selector, data, labelField) {
        const select = $(selector);
        select.empty().append('<option value="">-- Selecciona --</option>');
        (data || []).forEach(item => {
            select.append(`<option value="${item.id}">${item[labelField]}</option>`);
        });
        select.selectpicker('refresh');
    }
    $(document).ready(function () {
        // Cargar sistemas de apertura al inicio
        console.log(sistemas);
        cargarSelect('#sistema_apertua', sistemas , 'valor');
    });
</script>
@endsection