@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Tipo de Producto')

@section('content')

<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">
{{-- @php
$avance = Session::get('avance_temporal', []);
$avance = json_decode($avance, true);
dd($avance);
@endphp --}}
<div class="container text-center" style="max-width: 700px;">
    <div style="display: flex; align-items: center; justify-content: center; margin: 20px 0;">
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
        <h2 class="titulo">
            Tipo de Producto
        </h2>
        <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
    </div>
    <form action="{{ route('guardarAvance') }}" method="POST">
        {{-- Tipo de Producto --}}
        <div class="mb-3 text-start col-md-8 col-sm-12" id="div_tipo_producto" style="display: none;">
            @csrf
            @if(Auth::check() && Auth::user()->role_id == 1)
            <label for="tipo" class="form-label fw-bold text-uppercase">
                <a href="{{ route('opciones.show', 11) }}" target="_blank">TIPO DE PRODUCTO:</a>
            </label>
            @else
            <label for="tipo" class="form-label fw-bold text-uppercase">TIPO DE PRODUCTO:</label>
            @endif
            <select name="tipo" id="tipo" class="selectpicker form-control border-success" data-live-search="true">
                {{-- @foreach($tipo_producto as $tp)
                @if($loop->first)
                <option value="{{ $tp['id'] }}" selected>{{ $tp['valor'] }}</option>
                @else
                <option value="{{ $tp['id'] }}">{{ $tp['valor'] }}</option>
                @endif
                @endforeach --}}
            </select>
            <div class="descripcionSeleccion" id="descripcionTipoProducto">
                {{ $descripcion_tipo_producto[old('tipo',
                array_key_first($descripcion_tipo_producto))] ??
                '' }}
            </div>
        </div>
        {{-- Subproducto --}}
        <div class="mb-3 text-start col-md-6 col-sm-12" id="div_subproducto" style="display: none;">
            @csrf
            @if(Auth::check() && Auth::user()->role_id == 1)
            <label for="subproducto" class="form-label fw-bold text-uppercase">
                <a href="{{ route('opciones.show', 1) }}" target="_blank">SUB PRODUCTO:</a>
            </label>
            @else
            <label for="subproducto" class="form-label fw-bold text-uppercase">SUB PRODUCTO:</label>
            @endif
            <div class="ml-3" name="radio_subproducto">
                {{-- @foreach ($subproducto as $sp)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="subproducto" value="{{ $sp['id'] }}"
                        id="radio{{ $sp['id'] }}" {{ $sp['a_selected']=='true' ? 'checked' : '' }}>
                    <label class="form-check-label titulo" for="radio{{ $sp['id'] }}">
                        {{ $sp['valor'] }}
                    </label>
                </div>
                @endforeach --}}
            </div>

        </div>
        <input type="text" name="siguiente-vista" value="tipo_confeccion" hidden>
        <input type="text" name="actual-vista" value="tipo-producto" hidden>

        {{-- Botón Siguiente --}}
        <div class="text-end">
            <a href="#" name="anterior-vista" class="btn btn-outline-success fw-bold me-2">
                <i class="fas fa-arrow-left me-2"></i>Regresar
            </a>
            <button id="btnSiguiente" type="submit" class="btn btn-success fw-bold">Siguiente</button>
        </div>
    </form>
    <input type="text" id="pantalla_ubicacion" name="pantalla_ubicacion" value="2" hidden>


</div>

@endsection

@section('page-script')
<script>
    const descripcionesTipoProducto = @json($descripcion_tipo_producto);
    let cargandoSelectores = true; // Variable global para controlar si se están cargando selectores
    //console.log(descripcionesTipoProducto);
    

    
    //trigger subproducto al terminar de cargar la pagina
    
    

    // Definir eventos después de que se cargue el DOM
    $(document).ready(function() {
        $('#tipo').on('changed.bs.select', function () {
            // Verificar si se están cargando selectores o asignando valores programáticamente
            if (cargandoSelectores ) {
                console.log('BLOQUE: Ignorando evento changed.bs.select durante carga/asignación programática');
                return;
            }
            
            const seleccion = $(this).val();
            $('#descripcionTipoProducto').text(descripcionesTipoProducto[seleccion] ?? '');        
            console.log('...................SELECCIONADO TIPO CON VALOR: ', seleccion);
            getSelectorSiguiente('tipo', seleccion);
        });

        //evento de radio subproducto
        $('div[name="radio_subproducto"]').on('change', function () {
            // Verificar si se están cargando selectores
            if (cargandoSelectores) {
                console.log('BLOQUE: Ignorando evento change de radio durante carga de selectores');
                return;
            }
            
            const seleccion = $('input[name="subproducto"]:checked').val();
            console.log('...................SELECCIONADO SUB PRODUCTO CON VALOR: ', seleccion);
            getSelectorSiguiente('subproducto', seleccion);
        });

        //1.- obtener valores de sesión
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

        /*
        bloque
        */
        //2.- validar si hay valores en la sesión para habilitar o deshabilitar el botón siguiente
        if (Object.keys(valoresSesion).length === 0 || valoresSesion === null) {
        $(`#btnSiguiente`).attr('disabled', true);
        }else{
        $(`#btnSiguiente`).attr('disabled', false);
        }
        

        //3.- obtener selector siguiente para mostrar el primer selector
        getSelectorSiguiente(null, null);

        
        // selectores.forEach(selector => {
        //             if (!valoresSesion[selector.PAS_Html_name]) {
        //                 console.log('ocultando selector: ', selector.PAS_Html_name);
        //                 $(`#${selector.PAS_Container}`).hide();
        //             } else {
        //                 console.log('mostrando selector: ', selector.PAS_Html_name);
        //                 $(`#${selector.PAS_Container}`).show();
        //             }
        //         });
        //4.- obtener selectores a cargar y llenarlos con los valores de la sesión
        const selectoresACargar = selectores.filter(selector => selector.PAS_Pantalla_Ubicacion == $('input[name="pantalla_ubicacion"]').val());
         //pila de selectores a cargar
         let pilaSelectores = selectoresACargar;
        console.log('BLOQUE selectores: ', selectoresACargar);
        
        // Función para cargar selectores de forma secuencial
        function cargarSelectores() {
            // Bloquear pantalla una sola vez al inicio de la carga
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
            
            let indice = 0;
            
            function cargarSiguiente() {
                if (indice >= selectoresACargar.length) {
                    // Marcar que terminó la carga de selectores
                    cargandoSelectores = false;
                    console.log('BLOQUE: Carga de selectores completada');
                    // Desbloquear pantalla al final de toda la carga
                    $.unblockUI();
                    return;
                }
                
                const selector = selectoresACargar[indice];
                
                if (selector.PAS_Pantalla_Ubicacion == $('input[name="pantalla_ubicacion"]').val() &&
                    valoresSesion[selector.PAS_Html_name]) {

                    if (indice === selectoresACargar.length - 1) {
                        getSelectorAndFill(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], selector.PAS_Pantalla_Ubicacion, false);
                        console.log('BLOQUE último selector: ', selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name]);
                        // Esperar un poco antes de marcar como completado
                        setTimeout(() => {
                            cargandoSelectores = false;
                            getSelectorSiguiente(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], false);
                            //asignarSelectores(pilaSelectores, valoresSesion, selectoresACargar); 
                            console.log('BLOQUE: Carga de selectores completada');
                            // Desbloquear pantalla al final de toda la carga
                            $.unblockUI();
                        }, 1000);
                    } else {
                        console.log('BLOQUE llenando selector: ', selector.PAS_Html_name);
                        getSelectorAndFill(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], selector.PAS_Pantalla_Ubicacion, false);
                        // Esperar un poco antes de cargar el siguiente
                        setTimeout(() => {
                            indice++;
                            cargarSiguiente();
                        }, 500);
                    }
                } else {
                    indice++;
                    cargarSiguiente();
                }
            }
            
            cargarSiguiente();
        }
        
        // Iniciar carga de selectores
        cargarSelectores();


       

        
        
        function asignarSelectores(pilaSelectores, valoresSesion, selectoresACargar) {
            selectoresACargar.forEach(selector => {
            //si el selecotr ya tiene ese valor, no asignar nuevamente
            //obtener el valor del selector tipo_confeccion
            console.log('ASIGNANDO VALOR A *** selector: ', selector.PAS_Html_name);
            const valor = obtenerValorSelectorPorTipo(selector.PAS_Html_name, selector.PAS_Tipo_Selector);
            if (valor != valoresSesion[selector.PAS_Html_name]) {
                setTimeout(() => {
                asignarValor(selector.PAS_Html_name, selector.PAS_Tipo_Selector, valor);
                }, 1000);
            }else{
                //sacar el selector de la pila
                pilaSelectores.shift();
            }
            
        });
        }
        
        /*
        /bloque
        */
        //$('.selectpicker').selectpicker();
        
        //asignarValoresDesdeSesion(valoresSesion);

        //5.- definir el valor de siguiente-vista
        const siguienteVista = valoresSesion['siguiente-vista'] || '';
        if (siguienteVista === 'resumen') {
            $('input[name="siguiente-vista"]').val('resumen');
            $('.btn-success').text('Resumen');
        } else {
            
            $('.btn-success').text('Siguiente');
        } 

    });
</script>
@endsection