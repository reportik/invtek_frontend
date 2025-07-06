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
                    <img src="{{ asset('images/'.$opciones['tipo_riel']['valor'].'.png') }}" alt="Cortina"
                        class="img-fluid border" style="max-width: 400px; height: auto;">
                </div>


                <div class="col-md-6 col-sm-12 resumen-opciones border rounded bg-light p-3 mt-4">
                    @foreach($links_opciones_resumen as [$campo, $ruta])
                    <div class="resumen-linea">
                        <span class="opcion-titulo">
                            {{ $opciones[$campo]['valor'] ?? '-' }}
                        </span>
                        <a href="{{ route($ruta) }}" class="editar-link">Editar</a>
                    </div>
                    @endforeach
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
                <i class="fa fa-recycle"></i> &nbsp;Empezar Nueva
            </button>
            <button id="btn_agregar" onclick="agregar_cotizacion()" class="disabled btn btn-success fw-bold px-5">
                <i class="fa fa-plus"></i> &nbsp;Agregar
            </button>

            @if($cotizacion_status == 'cotizada' && Auth::check())
            {{-- Proceder a Pago --}}
            <button id="btn_cotizar" onclick="proceder_pago()" class="disabled btn btn-success fw-bold px-5">
                <i class="fa fa-credit-card"></i> &nbsp;Proceder a pago
            </button>
            @else
            {{-- Enviar Cotizacion --}}
            <button id="btn_cotizar" onclick="enviar_cotizacion()" class="btn btn-success fw-bold px-5">
                <i class="fa fa-paper-plane"></i> &nbsp;Enviar cotización
            </button>
            @endif
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
                text: 'Inicia sesión para cotizar con tus datos',
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
            error: function() {
                $.unblockUI();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cotizar',
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
            title: '¿Deseas crear una nueva cotización?',
            text: 'Se archivará la cotización actual',
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
</script>