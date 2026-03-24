@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Mis Cotizaciones')

<style>
    :root {
        --brand-green: #74bb20;
        --brand-green-dark: #5d9519;
        --brand-green-soft: #f1f8e7;
    }
    .cotizacion-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #d9eac2;
        border-radius: 14px;
    }
    .cotizacion-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(116, 187, 32, 0.2);
    }
    .cotizacion-card .card-header {
        background: linear-gradient(135deg, var(--brand-green-dark), var(--brand-green));
        color: #fff;
        border-bottom: 0;
    }
    .btn-cargar {
        min-width: 120px;
    }
    .btn-cargar.btn-outline-success {
        color: var(--brand-green-dark);
        border-color: var(--brand-green);
    }
    .btn-cargar.btn-outline-success:hover {
        background-color: var(--brand-green);
        border-color: var(--brand-green);
        color: #fff;
    }
    .badge-estado {
        padding: 0.45rem 0.65rem;
        border-radius: 999px;
        font-weight: 600;
    }
    .badge-borrador {
        background-color: #eef0f2;
        color: #5d6670;
    }
    .badge-revision {
        background-color: #fff3cd;
        color: #8a6d00;
    }
    .badge-enviada {
        background-color: var(--brand-green-soft);
        color: var(--brand-green-dark);
        border: 1px solid #d9eac2;
    }
    .folio-odoo {
        background-color: #f8f9fa;
        border: 1px dashed #cfd8dc;
        border-radius: 10px;
        padding: 8px 10px;
    }
</style>

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #74bb20;">
        <h2 class="titulo fw-bold" style="color:#74bb20;">
            Mis Cotizaciones
        </h2>
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #74bb20;">
    </div>

    @if($cotizaciones->isEmpty())
        <div class="alert alert-info text-center">
            <i class="fa fa-info-circle fa-2x mb-2"></i>
            <p class="mb-0">No tienes cotizaciones activas.</p>
            <a href="{{ route('inicio') }}" class="btn text-white mt-3" style="background-color:#74bb20;border-color:#74bb20;">
                <i class="fa fa-plus me-2"></i>Crear Nueva Cotización
            </a>
        </div>
    @else
        <div class="row">
            @foreach($cotizaciones as $cotizacion)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card cotizacion-card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0" style="color:white;">
                                <i class="fa fa-file-invoice me-2"></i>{{ $cotizacion['nombre_proyecto'] }}
                            </h5>
                        </div>
                        <div class="card-body"><br>
                            <p class="card-text mb-2">
                                <strong>Artículo:</strong> {{ $cotizacion['nombre_articulo'] }}
                            </p>
                            @if(!empty($cotizacion['descripcion_cortina']))
                            <p class="card-text small text-body-secondary mb-2">
                                <strong class="text-success">Cortina:</strong>
                                {{ $cotizacion['descripcion_cortina'] }}
                            </p>
                            @endif
                            @if(!empty($cotizacion['descripcion_cortinero']))
                            <p class="card-text small text-body-secondary mb-2">
                                <strong class="text-success">Cortinero:</strong>
                                {{ $cotizacion['descripcion_cortinero'] }}
                            </p>
                            @endif
                            <p class="card-text text-muted">
                                <small>
                                    <i class="fa fa-calendar me-1"></i>
                                    {{ \Carbon\Carbon::parse($cotizacion['fecha'])->format('d/m/Y H:i') }}
                                </small>
                            </p>
                            <p class="card-text" >
                                <span class="badge badge-estado" 
                                    @if($cotizacion['estatus_clave'] === 'borrador') badge-borrador
                                    @elseif($cotizacion['estatus_clave'] === 'en_revision') badge-revision
                                    @elseif($cotizacion['estatus_clave'] === 'enviada') badge-enviada
                                    @else badge-borrador
                                    @endif">
                                    <span style="color:black;">{{ $cotizacion['estatus'] }}</span>
                                </span>
                            </p>
                            @if($cotizacion['estatus_clave'] !== 'borrador')
                                <div class="folio-odoo mt-3">
                                    <small class="text-muted d-block">Folio Odoo</small>
                                    <strong>{{ $cotizacion['odoo_cotizacion'] ?: 'Sin folio aún' }}</strong>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent d-flex justify-content-between">
                            @if($cotizacion['estatus_clave'] === 'borrador')
                                <button class="btn btn-outline-success btn-cargar" 
                                        onclick="cargarCotizacion({{ $cotizacion['id'] }})">
                                    <i class="fa fa-edit me-1"></i>Continuar
                                </button>
                            @else
                                <button class="btn btn-outline-primary btn-cargar"
                                        onclick="abrirCotizacionOdoo('{{ $cotizacion['odoo_cotizacion'] }}')">
                                    <i class="fa fa-eye me-1"></i>Ver en Odoo
                                </button>
                            @endif
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
        <a href="{{ route('inicio') }}" class="btn text-white" style="background-color:#74bb20;border-color:#74bb20;">
            <i class="fa fa-plus me-2"></i>Crear Nueva Cotización
        </a>
    </div>
</div>
@endsection

@section('page-script')
<script>
    (function () {
        function blockUiCotizaciones(msg) {
            if (typeof jQuery === 'undefined' || !jQuery.fn || typeof jQuery.fn.blockUI !== 'function') {
                return;
            }
            jQuery.blockUI({
                message: '<div class="text-white text-center p-3"><i class="fa fa-spinner fa-spin fa-2x d-block mb-2"></i><span>'
                    + (msg || 'Procesando…') + '</span></div>',
                css: {
                    border: 'none',
                    padding: '15px',
                    backgroundColor: 'transparent',
                    color: '#fff'
                },
                overlayCSS: {
                    backgroundColor: '#000',
                    opacity: 0.55
                }
            });
        }
        function unblockUiCotizaciones() {
            if (typeof jQuery !== 'undefined' && jQuery.fn && typeof jQuery.fn.unblockUI === 'function') {
                jQuery.unblockUI();
            }
        }
        window.blockUiCotizaciones = blockUiCotizaciones;
        window.unblockUiCotizaciones = unblockUiCotizaciones;
    })();

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
                blockUiCotizaciones('Cargando cotización…');
                window.location.href = '{{ route("cotizaciones.cargar", ["id" => "__ID__"]) }}'.replace('__ID__', cotizacionId);
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
                blockUiCotizaciones('Eliminando cotización…');
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
                    unblockUiCotizaciones();
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminada',
                            text: 'La cotización ha sido eliminada',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            blockUiCotizaciones('Actualizando lista…');
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
                .catch(function () {
                    unblockUiCotizaciones();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al eliminar la cotización'
                    });
                });
            }
        });
    }

    function abrirCotizacionOdoo(odooCotizacionId) {
        if (!odooCotizacionId) {
            Swal.fire({
                icon: 'info',
                title: 'Cotización sin folio Odoo',
                text: 'Aún no está disponible para consulta en Odoo.'
            });
            return;
        }

        const redirectPath = '/my/home/' + odooCotizacionId;
        const autologinUrl = '{{ route("odoo.autologin.redirect") }}?redirect=' + encodeURIComponent(redirectPath);
        window.open(autologinUrl, '_blank');
    }
</script>
@endsection
