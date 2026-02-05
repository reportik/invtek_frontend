@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Cotizaciones Guardadas')

<style>
    .cotizacion-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .cotizacion-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    .btn-cargar {
        min-width: 120px;
    }
</style>

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #59981A;">
        <h2 class="titulo text-success fw-bold">
            <i class="fa fa-file-alt me-2"></i>Cotizaciones Guardadas
        </h2>
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #59981A;">
    </div>

    @if($cotizaciones->isEmpty())
        <div class="alert alert-info text-center">
            <i class="fa fa-info-circle fa-2x mb-2"></i>
            <p class="mb-0">No tienes cotizaciones guardadas.</p>
            <a href="{{ route('inicio') }}" class="btn btn-success mt-3">
                <i class="fa fa-plus me-2"></i>Crear Nueva Cotización
            </a>
        </div>
    @else
        <div class="row">
            @foreach($cotizaciones as $cotizacion)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card cotizacion-card h-100 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="fa fa-file-invoice me-2"></i>{{ $cotizacion['nombre_proyecto'] }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                <strong>Artículo:</strong> {{ $cotizacion['nombre_articulo'] }}
                            </p>
                            <p class="card-text text-muted">
                                <small>
                                    <i class="fa fa-calendar me-1"></i>
                                    {{ \Carbon\Carbon::parse($cotizacion['fecha'])->format('d/m/Y H:i') }}
                                </small>
                            </p>
                            <p class="card-text">
                                <span class="badge bg-warning text-dark">
                                    {{ ucfirst($cotizacion['estatus']) }}
                                </span>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent d-flex justify-content-between">
                            <button class="btn btn-outline-success btn-cargar" 
                                    onclick="cargarCotizacion({{ $cotizacion['id'] }})">
                                <i class="fa fa-edit me-1"></i>Continuar
                            </button>
                            <button class="btn btn-outline-danger" 
                                    onclick="eliminarCotizacion({{ $cotizacion['id'] }})">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('inicio') }}" class="btn btn-success">
            <i class="fa fa-plus me-2"></i>Crear Nueva Cotización
        </a>
    </div>
</div>
@endsection

<script>
    /**
     * Cargar una cotización guardada para continuar editándola
     */
    function cargarCotizacion(cotizacionId) {
        Swal.fire({
            icon: 'question',
            title: '¿Continuar con esta cotización?',
            text: 'Se cargará la cotización para que puedas editarla',
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ url("cargar-cotizacion") }}/' + cotizacionId;
            }
        });
    }

    /**
     * Eliminar una cotización guardada
     */
    function eliminarCotizacion(cotizacionId) {
        Swal.fire({
            icon: 'warning',
            title: '¿Eliminar cotización?',
            text: 'Esta acción no se puede deshacer',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            confirmButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ url("eliminar-cotizacion") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ cotizacion_id: cotizacionId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminada',
                            text: 'La cotización ha sido eliminada',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'No se pudo eliminar la cotización'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al eliminar la cotización'
                    });
                });
            }
        });
    }
</script>
