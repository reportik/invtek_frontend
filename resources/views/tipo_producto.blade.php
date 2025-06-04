@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Tipo de Producto')

@section('content')

<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">

<div class="container text-center" style="max-width: 700px;">


    <div style="display: flex; align-items: center; justify-content: center; margin: 20px 0;">
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
        <h2 class="titulo">
            Tipo de producto
        </h2>
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
    </div>

    <div class="nav-align-top">
        {{-- Tabs tipo de producto --}}
        <ul class="nav nav-pills mb-4 nav-fill" role="tablist">
            <li class="nav-item mb-1 mb-sm-0">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                    data-bs-target="#tab-cortinas-tela" aria-controls="tab-cortinas-tela" aria-selected="true">
                    Cortinas de Tela
                </button>
            </li>
            <li class="nav-item mb-1 mb-sm-0">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-cortineros"
                    aria-controls="tab-cortineros" aria-selected="false">
                    Cortineros
                </button>
            </li>
            <li class="nav-item mb-1 mb-sm-0">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-accesorios"
                    aria-controls="tab-accesorios" aria-selected="false">
                    Accesorios
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-roller"
                    aria-controls="tab-roller" aria-selected="false">
                    Cortinas Roller
                </button>
            </li>
        </ul>

        {{-- Contenido de los tabs --}}
        <div class="tab-content">

            {{-- Tab Cortinas de Tela (activo) --}}
            <div class="tab-pane fade show active" id="tab-cortinas-tela" role="tabpanel">
                <form action="{{ route('guardarAvance') }}" method="POST">
                    @csrf

                    {{-- Nombre del artículo --}}
                    <div class="mb-3 text-start">
                        <label for="nombre_articulo" class="form-label fw-bold text-uppercase">
                            NOMBRE DEL ARTÍCULO:
                            <i class="fa fa-info-circle" title="Introduce un nombre para identificar el artículo."></i>
                        </label>
                        <input type="text" name="nombre_articulo" id="nombre_articulo"
                            class="form-control border-success" required>
                    </div>

                    {{-- Radios tipo de artículo --}}
                    <div class="mb-3 text-start ml-4">

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo" value="cortina_cortinero"
                                id="radioCortinaCortinero" checked>
                            <label class="form-check-label titulo" for="radioCortinaCortinero">
                                Cortina + Cortinero
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo" value="solo_cortina"
                                id="radioSoloCortina">
                            <label class="form-check-label titulo" for="radioSoloCortina">
                                Solo Cortina
                            </label>
                        </div>
                    </div>

                    {{-- Área de instalación (Selectpicker) --}}
                    <div class="mb-4 text-start">
                        <label for="area_instalacion" class="form-label fw-bold text-uppercase">
                            ÁREA DE INSTALACIÓN:
                        </label>
                        <select name="area_instalacion" id="area_instalacion"
                            class="selectpicker form-control border-success" data-live-search="true" required>
                            <option value="interior">Interior</option>
                            <option value="exterior">Exterior</option>
                        </select>
                    </div>

                    <input type="text" name="siguiente-vista" value="tipo_confeccion" hidden>

                    {{-- Botón Siguiente --}}
                    <div class="text-end">
                        <a href="{{ route('inicio') }}" class="btn btn-outline-success fw-bold me-2">Regresar</a>
                        <button type="submit" class="btn btn-outline-success fw-bold">Siguiente</button>
                    </div>
                </form>
            </div>

            {{-- Tabs vacíos por ahora --}}
            <div class="tab-pane fade" id="tab-cortineros" role="tabpanel">
                <p class="text-muted">Próximamente configuración de cortineros...</p>
            </div>
            <div class="tab-pane fade" id="tab-accesorios" role="tabpanel">
                <p class="text-muted">Próximamente configuración de accesorios...</p>
            </div>
            <div class="tab-pane fade" id="tab-roller" role="tabpanel">
                <p class="text-muted">Próximamente configuración de roller...</p>
            </div>

        </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
    $(document).ready(function () {
        
        
        $('.selectpicker').selectpicker();
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
    });
</script>
@endsection