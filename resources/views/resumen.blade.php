@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Resumen Cotización')


<style>
    .resumen-linea {
        display: flex;
        justify-content: space-between;
        padding: 8px 12px;

        align-items: center;
    }

    .resumen-linea:last-child {
        border-bottom: none;
    }

    .opcion-titulo {
        color: #333;
        font-weight: 500;
        text-align: right;
    }

    .editar-link {
        font-size: 0.95rem;
        color: #007bff;
        text-decoration: underline;
        cursor: pointer;
    }

    /* Estilos para el modal de detalle */
    #modalDetalleCotizacion .card {
        transition: all 0.2s ease;
    }

    #modalDetalleCotizacion .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    #modalDetalleCotizacion .table-responsive {
        border-radius: 0.25rem;
    }

    #modalDetalleCotizacion .badge {
        padding: 0.5rem 1rem;
        font-weight: 500;
    }

    #modalDetalleCotizacion h6 {
        border-bottom: 2px solid #59981A;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem !important;
    }

    /* Animación de carga */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #modalDetalleCotizacion .detalle-cotizacion > div {
        animation: fadeIn 0.3s ease-out;
    }

    /* Estilos para las tarjetas de opciones */
    #modalDetalleCotizacion .card-body.p-2 {
        min-height: 60px;
        display: flex;
        align-items: center;
    }

    /* Scroll suave para el modal */
    #modalDetalleCotizacion .modal-body {
       
    }
</style>



@section('content')
@php
$user_auth = Auth::check();
$datos = session()->all(); // ya contiene las claves como 'sistema_apertura', 'color_riel_selector', etc.
$datos = $datos['avance_temporal'] ?? []; // estan en json
$datos = json_decode($datos, true); // decodificamos el json a un array asociativo


@endphp
<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">
<div class="container text-center mt-4" style="max-width: 900px;">
    <div class="d-flex align-items-center justify-content-center my-4">
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #59981A;">
        <h2 class="titulo">Resumen de la Compra</h2>
        <hr class="flex-grow-1 mx-2" style="border-top: 4px solid #59981A;">
    </div>

    <div class="row">


        {{-- Info --}}
        <div class=" text-start">
            <p>
                <strong class="text-success">Proyecto:</strong>
                {{ $opciones['nombre_proyecto']['valor'] ?? '-' }}
                <span id="cotizacion_encabezado" class="text-info fw-bold ms-2">
                    @if(isset($odoo_cotizacion_numero) && $odoo_cotizacion_numero != '')
                    COT. # <span id="odoo_cotizacion_numero">{{ $odoo_cotizacion_numero }}</span> <span
                        id="cotizacion_status"> @if(isset($cotizacion_status)) {{ $cotizacion_status}} @endif </span>
                    @else
                    COT. <span id="odoo_cotizacion_numero"></span> <span id="cotizacion_status">
                        @if(isset($cotizacion_status)) {{ $cotizacion_status}} @endif </span>
                    @endif
                </span>
            </p>
            <p>
                <strong class="text-success">Artículo:</strong>
                {{ $opciones['nombre_articulo']['valor'] ?? '-' }}
                <a href="#"><small class="text-muted">Agregar a productos recurrentes</small></a>
            </p>
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    @php
                        
                        if (!empty($imagen_resumen)) {
                            $imagen_src = 'images/cotizador/' . $imagen_resumen;
                        } else {
                            $imagen_src = 'images/default.png';
                        }
                    @endphp
                    <img src="{{ asset($imagen_src) }}" alt="Cortina"
                        class="img-fluid border" style="max-width: 400px; height: auto;">
                </div>


                <div class="col-md-6 col-sm-12 resumen-opciones border rounded bg-light p-3 mt-4">
                    <h6 class="mb-3 text-success fw-bold">Cotizador:</h6>
                    @if(!empty($vistas_resumen))
                        @foreach($vistas_resumen as $vista)
                        <div class="resumen-linea">
                            <span class="opcion-titulo">
                                <i class="fas fa-link me-2 text-success"></i>
                                {{ $vista['nombre'] }}
                            </span>
                            <a href="{{ route($vista['ruta']) }}" class="editar-link">
                                <i class="fas fa-edit me-1"></i>Editar
                            </a>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted">No hay pantallas visitadas.</p>
                    @endif
                </div>
            </div>
            @if ($descripcion_cortina != null && $descripcion_cortina != '')
            <p class="mt-4">
                <strong class="text-success">Cortina:</strong>
                {{ $descripcion_cortina }}
            </p>
            @endif
            @if ($descripcion_cortinero != null && $descripcion_cortinero != '')
            <p class="mt-2">
                <strong class="text-success">Cortinero:</strong>
                {{ $descripcion_cortinero }}
            </p>
            @endif
        </div>
    </div>

    {{-- Totales --}}
    <div style="" id="totales" class="row mt-4">
        <div class="col-md-8"></div>
        <div class="col-md-4 text-end">
            <p id="subtotal"><strong>Subtotal:</strong>@if (isset($subtotal) && $subtotal != 0)
                ${{ number_format($subtotal, 2) }}
                @else
                $0.00
                @endif</p>
            <p id="iva"><strong>IVA:</strong>@if (isset($iva) && $iva != 0)
                ${{ number_format($iva, 2) }}
                @else
                $0.00
                @endif</p>
            <p id="total" class="h5 fw-bold">Total: @if (isset($total) && $total != 0)
                ${{ number_format($total, 2) }}
                @else
                $0.00
                @endif</p>
        </div>
    </div>

    {{-- Acciones --}}
    <div class="row mt-4">
        <div style="display: none;" class="col text-start">
            <a href="#" class="text-success">+ Agregar producto</a><br>
            <a href="#" class="text-success">+ Agregar producto recurrente</a>
        </div>
        <div class="col text-end">
            {{-- botones Empezar Nueva, Agregar --}}
            <button id="btn_nueva" onclick="nueva_cotizacion()" class="btn btn-success fw-bold px-5">
                <i class="fa fa-recycle"></i> &nbsp;Empezar Proyecto desde cero
            </button>
            <!-- <button style="display: none;" id="btn_agregar" onclick="agregar_cotizacion()" class="disabled btn btn-success fw-bold px-5">
                <i class="fa fa-plus"></i> &nbsp;Agregar
            </button> -->

            

            @if(strtoupper($cotizacion_status) == 'COTIZADA' && Auth::check())
            {{-- Proceder a Pago --}}
            <button id="btn_proceder_pago" onclick="proceder_pago()" class="btn btn-success fw-bold px-5">
                <i class="fa fa-credit-card"></i> &nbsp;Proceder a pago
            </button>
            @else
            {{-- Enviar Cotizacion --}}
            <button id="btn_cotizar" onclick="enviar_cotizacion()" class="btn btn-success fw-bold px-5">
                <i class="fa fa-paper-plane"></i> &nbsp;Crear cotización
            </button>
            @endif
            {{-- Ver Detalle de Cotización --}}
            @if(Auth::check() && Auth::user()->role_id == 1)
            <button id="btn_ver_detalle" onclick="ver_detalle_cotizacion()" class="btn btn-outline-success fw-bold px-4">
                <i class="fa fa-list-alt"></i> &nbsp;Ver Detalle
            </button>
            @endif
        </div>
    </div>

    {{-- Modal Detalle de Cotización --}}
    <div class="modal fade" id="modalDetalleCotizacion" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalDetalleLabel">
                        <i class="fa fa-list-alt me-2"></i>Detalle de la Cotización
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalDetalleContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-3">Cargando detalle de cotización...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    // Esto inyecta el valor true o false según si el usuario está autenticado
    const user_auth = {{ auth()->check() ? 'true' : 'false' }};
    function enviar_cotizacion() {
        if (user_auth) {
            cotizar_ajax();
        }else{
            Swal.fire({
                icon: 'info',
                title: '¿Eres cliente registrado?',
                text: 'Inicia sesión para crear la cotización con tus datos',
                showCancelButton: true,
                confirmButtonText: 'Iniciar Sesión',
                cancelButtonText: 'Cotizar como invitado'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = routeapp + '/login';
                }else{
                    cotizar_ajax();
                }
            });
        }
    }
    function cotizar_ajax() {

        $.ajax({
            url: routeapp + '/cotizar',
            type: 'GET',
            data: {
            },
            beforeSend: function() {
               $.blockUI({
                    css: {
                    border: 'none',
                    padding: '15px',
                    backgroundColor: '#000',
                    '-webkit-border-radius': '10px',
                    '-moz-border-radius': '10px',
                    opacity: 0.5,
                    color: '#fff'
                    }
                });
            },
            success: function(response) {
            
                $.unblockUI();
                // Actualizar los valores
                // $('#subtotal').text("Subtotal: " + new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(response.subtotal));
                // $('#iva').text("IVA: " + new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(response.taxes));
                // $('#total').text("Total: " + new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(response.total));
                
                //$('#totales').show();
                //$('#totales').style.display = 'block';
                //Actualizar estatus de la cotizacion
                $('#cotizacion_status').text(response.cotizacion_status);
                // RESPUESTA:
                // response()->json([
                // 'success' => true,
                // 'cotizacion_1' => $id_cotizacion_1,
                // 'cotizacion_2' => $id_cotizacion_2,
                // 'response_1' => $response->json(),
                // 'response_2' => $response_2->json()
                // ])
                $('#odoo_cotizacion_numero').text(response.cotizacion_1);
                Swal.fire({
                    icon: 'success',
                    title: 'Cotización recibida',
                    text: 'Cotización recibida correctamente',
                });
            },
            error: function(xhr, status, error) {
                $.unblockUI();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON.message,
                });
            }
        });
    }
    function proceder_pago() {
        
    }
    function agregar_cotizacion() {
        
    }
    function nueva_cotizacion() {
        Swal.fire({
            icon: 'info',
            title: '¿Deseas crear un nuevo proyecto?',
            text: 'Se guardará el proyecto actual',
            showCancelButton: true,
            confirmButtonText: 'Si',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: routeapp + '/nueva-cotizacion',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        $.blockUI({
                            css: {
                                border: 'none',
                                padding: '15px',
                                backgroundColor: '#000',
                                '-webkit-border-radius': '10px',
                                '-moz-border-radius': '10px',
                                opacity: 0.5,
                                color: '#fff'
                            }
                        });
                    },
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        window.location.href = routeapp + '/inicio';
                        $.unblockUI();
                    },
                    error: function() {
                        $.unblockUI();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al crear la cotización',
                        });
                    }
                });
            }else{
                //ocultar swal
                Swal.close();
            }
        });
    }

    function ver_detalle_cotizacion() {
        // Abrir el modal
        var modal = new bootstrap.Modal(document.getElementById('modalDetalleCotizacion'));
        modal.show();

        // Hacer petición AJAX para obtener el detalle
        $.ajax({
            url: routeapp + '/detalle-cotizacion',
            type: 'GET',
            data: {},
            success: function(response) {
                // DEBUG: Mostrar respuesta completa en consola
                /* console.log('=== DEBUG DETALLE COTIZACIÓN ===');
                console.log('Respuesta completa:', response);
                console.log('Productos:', response.productos);
                if (response.productos) {
                    response.productos.forEach(function(p, i) {
                        console.log('Producto ' + i + ':', p.nombre, '| Cantidad:', p.cantidad, '| Precio Unit:', p.precio_unitario, '| Total:', p.total);
                    });
                }
                console.log('================================'); */
                
                if (response.success) {
                    // Construir el HTML con el detalle
                    let html = '<div class="detalle-cotizacion">';
                    
                    // Información del proyecto
                    html += '<div class="mb-4">';
                    html += '<h6 class="text-success fw-bold mb-3"><i class="fa fa-info-circle me-2"></i>Información General</h6>';
                    html += '<div class="card border-success">';
                    html += '<div class="card-body">';
                    html += '<div class="row">';
                    html += '<div class="col-md-6"><strong class="text-success">Proyecto:</strong> ' + (response.proyecto || '-') + '</div>';
                    html += '<div class="col-md-6"><strong class="text-success">Artículo:</strong> ' + (response.articulo || '-') + '</div>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';

                    // Opciones seleccionadas
                    if (response.opciones_seleccionadas && response.opciones_seleccionadas.length > 0) {
                        html += '<div class="mb-4">';
                        html += '<h6 class="text-success fw-bold mb-3"><i class="fa fa-list-check me-2"></i>Opciones Seleccionadas</h6>';
                        html += '<div class="row g-2">';
                        
                        response.opciones_seleccionadas.forEach(function(opcion) {
                            html += '<div class="col-md-6">';
                            html += '<div class="card border-light shadow-sm h-100">';
                            html += '<div class="card-body p-2">';
                            html += '<div class="d-flex align-items-center">';
                            html += '<i class="fa ' + opcion.icono + ' text-success me-2" style="font-size: 1.2rem;"></i>';
                            html += '<div class="flex-grow-1">';
                            html += '<small class="text-muted d-block" style="font-size: 0.75rem;">' + opcion.categoria + '</small>';
                            html += '<strong style="font-size: 0.9rem;">' + opcion.valor + '</strong>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        });
                        
                        html += '</div>';
                        html += '</div>';
                    }

                    // Medidas
                    if (response.medidas && response.medidas.length > 0) {
                        html += '<div class="mb-4">';
                        html += '<h6 class="text-success fw-bold mb-3"><i class="fa fa-ruler-combined me-2"></i>Medidas</h6>';
                        html += '<div class="card border-success">';
                        html += '<div class="card-body">';
                        html += '<div class="row g-3">';
                        
                        response.medidas.forEach(function(medida) {
                            html += '<div class="col-auto">';
                            html += '<div class="d-flex align-items-center bg-light rounded p-2">';
                            html += '<i class="fa fa-arrows-alt text-success me-2"></i>';
                            html += '<div>';
                            html += '<small class="text-muted d-block" style="font-size: 0.75rem;">' + medida.label + '</small>';
                            html += '<strong class="text-success">' + medida.valor + '</strong>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        });
                        
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    }

                    // Tela seleccionada
                    if (response.nombre_tela) {
                        html += '<div class="mb-4">';
                        html += '<h6 class="text-success fw-bold mb-3"><i class="fa fa-swatchbook me-2"></i>Tela/Material</h6>';
                        html += '<div class="alert alert-success mb-0">';
                        html += '<i class="fa fa-check-circle me-2"></i>' + response.nombre_tela;
                        html += '</div>';
                        html += '</div>';
                    }

                    // Descripción de cortina
                    if (response.descripcion_cortina) {
                        html += '<div class="mb-4">';
                        html += '<h6 class="text-success fw-bold mb-3"><i class="fa fa-align-left me-2"></i>Descripción de Cortina</h6>';
                        html += '<div class="card border-info">';
                        html += '<div class="card-body">';
                        html += '<p class="mb-0 text-dark">' + response.descripcion_cortina + '</p>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    }

                    // Descripción de cortinero
                    if (response.descripcion_cortinero) {
                        html += '<div class="mb-4">';
                        html += '<h6 class="text-success fw-bold mb-3"><i class="fa fa-grip-lines me-2"></i>Descripción de Cortinero</h6>';
                        html += '<div class="card border-info">';
                        html += '<div class="card-body">';
                        html += '<p class="mb-0 text-dark">' + response.descripcion_cortinero + '</p>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    }

                    // Productos y cantidades
                    if (response.productos && response.productos.length > 0) {
                        html += '<div class="mb-4">';
                        html += '<h6 class="text-success fw-bold mb-3"><i class="fa fa-box me-2"></i>Productos Requeridos</h6>';
                        html += '<div class="table-responsive">';
                        html += '<table class="table table-hover table-bordered table-sm">';
                        html += '<thead class="table-success">';
                        html += '<tr>';
                        html += '<th>Producto</th>';
                        html += '<th class="text-center" style="width: 100px;">Cantidad</th>';
                        html += '<th class="text-end" style="width: 120px;">Precio Unit.</th>';
                        html += '<th class="text-end" style="width: 120px;">Total</th>';
                        html += '</tr>';
                        html += '</thead>';
                        html += '<tbody>';
                        
                        response.productos.forEach(function(producto) {
                            html += '<tr>';
                            html += '<td><small>' + producto.nombre + '</small></td>';
                            html += '<td class="text-center">' + producto.cantidad + '</td>';
                            html += '<td class="text-end">$' + parseFloat(producto.precio_unitario).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                            html += '<td class="text-end fw-bold">$' + parseFloat(producto.total).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                            html += '</tr>';
                        });
                        
                        html += '</tbody>';
                        html += '</table>';
                        html += '</div>';
                        html += '</div>';
                    }

                    // Desglose de costos
                    html += '<div class="mb-3">';
                    html += '<h6 class="text-success fw-bold mb-3"><i class="fa fa-calculator me-2"></i>Desglose de Costos</h6>';
                    html += '<div class="card border-success">';
                    html += '<div class="card-body">';
                    html += '<table class="table table-sm mb-0">';
                    html += '<tr><td class="text-end fw-bold">Subtotal:</td><td class="text-end" style="width: 150px;">$' + parseFloat(response.subtotal).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td></tr>';
                    html += '<tr><td class="text-end fw-bold">IVA (' + (response.iva_porcentaje || '16') + '%):</td><td class="text-end">$' + parseFloat(response.iva).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td></tr>';
                    html += '<tr class="table-success"><td class="text-end fw-bold fs-5">Total:</td><td class="text-end fw-bold fs-5">$' + parseFloat(response.total).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td></tr>';
                    html += '</table>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';

                    html += '</div>';

                    $('#modalDetalleContent').html(html);
                } else {
                    $('#modalDetalleContent').html('<div class="alert alert-warning"><i class="fa fa-exclamation-triangle me-2"></i>' + response.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                $('#modalDetalleContent').html(
                    '<div class="alert alert-danger">' +
                    '<i class="fa fa-exclamation-circle me-2"></i>' +
                    '<strong>Error al cargar el detalle:</strong> ' + 
                    (xhr.responseJSON?.message || 'Por favor, intente nuevamente.') +
                    '</div>'
                );
            }
        });
    }

    /**
     * Proceder a pago: Abre Odoo con autologin en la página de la cotización
     */
    function proceder_pago() {
        // Obtener el ID de la cotización de Odoo
        const odoo_cotizacion_id = document.getElementById('odoo_cotizacion_numero')?.textContent?.trim();
        
        if (!odoo_cotizacion_id || odoo_cotizacion_id === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Cotización no disponible',
                text: 'No se encontró el número de cotización de Odoo.'
            });
            return;
        }
        
        // Construir la URL de redirección a Odoo con autologin
        // El redirect lleva a /my/orders/{id} donde id es el número de la cotización
        const redirectPath = '/my/orders/' + odoo_cotizacion_id;
        const autologinUrl = routeapp + '/odoo/autologin?redirect=' + encodeURIComponent(redirectPath);
        
        // Abrir en nueva pestaña
        window.open(autologinUrl, '_blank');
    }
</script>