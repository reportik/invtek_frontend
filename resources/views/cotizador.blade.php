@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Inicio Cotizador')


<style>
    .responsive-logo {
        height: 25%;
        margin-bottom: -85px;
    }

    /* agregar para tablets*/
    @media (max-width: 1024px) {
        .responsive-logo {
            height: 15vh;
            /* o usa un tamaño fijo como 80px si prefieres */
            margin-bottom: 80px;
        }
    }

    @media (max-width: 768px) {

        /*  */
        .responsive-logo {
            height: 8vh;
            /* o usa un tamaño fijo como 80px si prefieres */
            margin-bottom: 20px;
        }

        .btn-full-width {
            width: 100%;
        }
    }
</style>


@section('content')
<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">

<div class="container text-center" style="max-width: 600px;">
    <img src="{{ asset('images/img_cotizador.png') }}" alt="Logo" class="mb-3">

    <form action="{{ route('login') }}" method="POST">
        @csrf

        <div class="mb-3 text-start">
            <label for="nombre_proyecto" class="form-label fw-bold text-uppercase">
                NOMBRE DEL PROYECTO:
                <i class="fa fa-info-circle" title="Introduce un nombre para identificar el proyecto."></i>
            </label>
            <input type="text" name="nombre_proyecto" id="nombre_proyecto" class="form-control border-success"
                value="{{ old('nombre_proyecto') }}" required>
        </div>

        <div class="mb-3 text-start">
            <label for="calidad" class="form-label fw-bold text-uppercase">CALIDAD:</label>

            <select id="calidad" name="calidad" class="selectpicker form-control border-success" data-live-search="true"
                required>
                @foreach($opcionesCalidad as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            <small id="descripcionCalidad" class="form-text text-muted d-block mt-4" style="font-size: 1.1rem;">
                {{ $opcionesCalidadDescripcion[old('calidad', array_key_first($opcionesCalidadDescripcion))] ??
                'Seleccione una calidad para ver su descripción.' }}
            </small>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-outline-success fw-bold btn-full-width">Siguiente</button>
        </div>
    </form>
</div>
@endsection



@section('page-script')
<script>
    const descripciones = @json($opcionesCalidadDescripcion);

  $('#calidad').on('changed.bs.select', function () {
    const seleccion = $(this).val();
    $('#descripcionCalidad').text(descripciones[seleccion] ?? 'Seleccione una calidad para ver su descripción.');
  });
</script>
@endsection