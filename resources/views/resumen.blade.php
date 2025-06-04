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
$datos = session()->all(); // ya contiene las claves como 'sistema_apertura', 'color_riel_selector', etc.
$datos = $datos['avance_temporal'] ?? []; // estan en json
$datos = json_decode($datos, true); // decodificamos el json a un array asociativo
// Aquí podrías traer los valores amigables usando modelos si los ids apuntan a catálogos
// dd($datos); // Para depurar y ver qué datos tienes

//set siguiente-vista en session
//session(['siguiente-vista' => 'final']);

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
                <span class="text-danger fw-bold ms-2">COT.0012</span>
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
                    @foreach([
                    ['tipo', 'tipo_producto'],
                    ['tipo_confeccion', 'tipo_confeccion'],
                    ['radio_step_2', 'tipo_confeccion'],
                    ['tipo_riel', 'medidas'],
                    ['numero_hojas', 'medidas'],
                    ['tipo_tela', 'telas'],
                    ['tela', 'telas'],
                    ['sistema_apertura', 'sistema_apertura'],
                    ['superficie_instalacion_riel', 'sistema_apertura'],
                    ['sistema_riel_selector', 'sistema_apertura'],
                    ['material_riel_selector', 'sistema_apertura']
                    ] as [$campo, $ruta])
                    <div class="resumen-linea">
                        <span class="opcion-titulo">
                            {{ $opciones[$campo]['valor'] ?? '-' }}
                        </span>
                        <a href="{{ route($ruta) }}" class="editar-link">Editar</a>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    {{-- Totales --}}
    <div class="row mt-4">
        <div class="col-md-8"></div>
        <div class="col-md-4 text-end">
            <p><strong>Subtotal:</strong> $2,160.00</p>
            <p><strong>IVA:</strong> $345.60</p>
            <p class="h5 fw-bold">Total: $2,505.60</p>
        </div>
    </div>

    {{-- Acciones --}}
    <div class="row mt-4">
        <div class="col text-start">
            <a href="#" class="text-success">+ Agregar producto</a><br>
            <a href="#" class="text-success">+ Agregar producto recurrente</a>
        </div>
        <div class="col text-end">
            <a href="{{ route('create-quotation2') }}" class="btn btn-success fw-bold px-5">
                Cotizar
            </a>
        </div>
    </div>
</div>
@endsection