@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Medidas')

@section('content')

<style>
  .responsive-logo {
    height: 100px;
    margin-bottom: -100px;
  }

  .medida-input {
    position: absolute;
    border: 2px solid red;
    padding: 4px;
    width: 80px;
    font-size: 14px;
    background-color: white;
    box-shadow: 0 0 4px rgba(0, 0, 0, 0.3);
    display: none;
  }
</style>
<img class="logo-image responsive-logo" alt="Invtek" src="{{ asset('images/image_box.png') }}">
<div class="container text-center" style="max-width: 900px;">
  <div style="display: flex; align-items: center; justify-content: center; margin: 20px 0;">
    <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
    <h2 class="titulo">
      Configuración y medidas
    </h2>
    <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
  </div>

  <form id="form_medidas" action="{{ route('guardarAvance') }}" method="POST">
    @csrf

    {{-- Selección tipo de riel --}}
    <div class="mb-4 text-start" id="div_riel">
      @if(Auth::check() && Auth::user()->role_id == 1)
      <label class="form-label fw-bold subtitulo text-uppercase" style="display: block; text-align:left"><a
          href="{{ route('opciones.show', 20) }}" target="_blank">Instalación
          del riel:</a></label>
      @else
      <label class="form-label fw-bold subtitulo text-uppercase" style="display: block; text-align:left">Instalación
        del riel:</label>
      @endif
      <div class="row row-cols-1 row-cols-md-3 g-4 mb-4" id="contenedor_tarjetas_riel" name="card_tipo_riel">
        {{-- @foreach ($tiposRiel as $index => $item)
        <div class="col">
          <div class="card">
            <img class="card-img-top" src="{{ asset('images/cotizador/' . $item['image']) }}"
              style="cursor:pointer; width: 100%; height: 180px; object-fit: contain;"
              onclick="showModal('{{ asset('images/cotizador/' . $item['image']) }}')">
            <div class="card-body">
              <div class="form-check">
                <input class="form-check-input tipo-riel-radio" type="radio" name="tipo_riel"
                  id="radio_riel_{{ $index }}" value="{{ $item['id_riel'] }}" {{ $item['a_selected']==='true'
                  ? 'checked' : '' }}>
                <label class="form-check-label subtitulo" for="radio_riel_{{ $index }}">
                  {{ $item['opcion_radio'] }}
                </label>
              </div>
            </div>
          </div>
        </div>
        @endforeach --}}
      </div>

    </div>

    <div class="row">
      <div class="col-md-6" id="div_riel">
        {{-- Canvas medidas con imagen de fondo --}}
        <div class="text-center mb-4">
          @if(Auth::check() && Auth::user()->role_id == 1)
          <label class="form-label fw-bold subtitulo text-uppercase" style="display: block; text-align:left"><a
              href="{{ route('opciones.show', 6) }}" target="_blank">Medidas (m)</a></label>
          @else
          <label class="form-label fw-bold subtitulo text-uppercase" style="display: block; text-align:left">Medidas
            (m)</label>
          @endif
          <div class="descripcionSeleccion" id="mensajeSeleccion"></div>
        </div>
        {{-- Canvas medidas con imagen de fondo --}}
        <div id="div_medidas" class="position-relative d-flex justify-content-center">
          <canvas id="canvas" name="canvas" width="400" height="400" style="border:1px solid #ccc;"></canvas>

          <!-- Inputs flotantes -->
          <input type="text" id="inputLadoA" name="inputLadoA" class="medida-input" placeholder="Lado A">
          <input type="text" id="inputLadoB" name="inputLadoB" class="medida-input" placeholder="Lado B">
          <input type="text" id="inputAlto" name="inputAlto" class="medida-input" placeholder="Alto">
          <input type="text" id="inputAncho" name="inputAncho" class="medida-input" placeholder="Ancho">
          <input type="text" id="inputRadio" name="inputRadio" class="medida-input" placeholder="Radio">
        </div>
      </div>
      <div class="col-md-6 mb-4">
        {{-- Selectpicker número de hojas --}}

        <div id="contenedor_hojas" class="mb-4 text-start mt-4">
          @if(Auth::check() && Auth::user()->role_id == 1)
          <label class="form-label fw-bold subtitulo text-uppercase" style="display: block; text-align:left"><a
              href="{{ route('opciones.show', 21) }}" target="_blank">Hojas</a></label>
          @else
          <label class="form-label fw-bold subtitulo text-uppercase"
            style="display: block; text-align:left">Hojas</label>
          @endif
          <select name="numero_hojas" class="selectpicker form-control border-success" data-live-search="true" required>
          </select>
          {{-- Tarjeta estilo personalizada --}}
          <div id="hojas_info_card" class="card d-none mt-4" style="position: absolute width: 100%;">
            <img id="hojas_img" class="card-img-top" src="" alt="Sistema"
              style="cursor:pointer; width: 100%; height: 180px; object-fit: contain;" onclick="">
            <div class="card-body">
              <div class="text-start">
                <h5 id="hojas_nombre" class="titulo"></h5>
                <p id="hojas_descripcion" class="mb-0 text-muted "></p>
              </div>
            </div>
          </div>
        </div>
        {{-- Selectpicker dirección de apertura --}}
        <div id="contenedor_direccion_apertura" class="text-start">
          @if(Auth::check() && Auth::user()->role_id == 1)
          <label class="form-label fw-bold subtitulo text-uppercase" style="display: block; text-align:left"><a
              href="{{ route('opciones.show', 23) }}" target="_blank">Dirección de apertura:</a></label>
          @else
          <label class="form-label fw-bold subtitulo text-uppercase" style="display: block; text-align:left">Dirección
            de
            apertura:</label>
          @endif

          <div id="radio_direccion_apertura" name="radio_direccion_apertura">
            {{-- @foreach ($direccion_apertura as $item)
            <div class="form-check ml-4">
              <input class="form-check-input" type="radio" name="direccion_apertura" value="{{ $item['id'] }}"
                id="radio{{ $item['id'] }}" {{ $item['a_selected']=='true' ? 'checked' : '' }}>
              <label class="form-check-label titulo" for="radio{{ $item['id'] }}">
                {{ $item['opcion_radio'] }} <i class="fa {{ $item['programacion'] }}" title=""></i>
              </label>
            </div>

            @endforeach --}}
          </div>
        </div>
      </div>
    </div>

    {{-- Botones de navegación --}}
    <div class="col text-end">
      <a href="#" name="anterior-vista" class="btn btn-outline-success fw-bold me-2">
        <i class="fas fa-arrow-left me-2"></i>Regresar
      </a>
      <input type="text" name="siguiente-vista" value="telas" hidden>
      <input type="text" name="actual-vista" value="configuracion-medidas" hidden>
      <button id="btnSiguiente" type="submit" class="btn btn-success fw-bold">Siguiente</button>
    </div>
  </form>
</div>
<input type="text" name="pantalla_ubicacion" value="4" hidden>
@endsection
@section('page-script')
<script>
  // Validación por campo visible en el formulario de medidas
  let cargandoSelectores = true; // Variable global para controlar si se están cargando selectores

  function handleMedidaInputChange(nombre, valor) {
      // Verificar si se están cargando selectores
      if (cargandoSelectores) {
          console.log('BLOQUE: Ignorando evento de input de canvas durante carga de selectores');
          return;
      }

      // Obtener el data-value del canvas
      const canvasValue = document.getElementById('canvas').getAttribute('data-value');
      console.log('Valor del canvas:', canvasValue);
      // Llamar a la función getSelectorSiguiente con el valor del canvas
      if (canvasValue) {
          getSelectorSiguiente('canvas', canvasValue);
      } else {
          console.warn('No se encontró data-value en el canvas');
      }
  }

    // Definir eventos después de que se cargue el DOM
    $(document).ready(function() {
        // Evento unico para inputs de canvas
        $('#inputLadoA, #inputLadoB, #inputAlto, #inputAncho, #inputRadio').on('input change', function() {
            handleMedidaInputChange($(this).attr('name'), $(this).val());
        });
        $('.selectpicker').selectpicker();
        //hideInputs(); // Ocultar inputs al cargar la página
        const imagenes_medidas = @json($imagenes_medidas);
        const imagenes_medidas_array = Array.isArray(imagenes_medidas) ? imagenes_medidas : Object.values(imagenes_medidas);
        const hijos_imagenes_hojas = @json($hijos_imagenes_hojas);

        function hideInputs() {
            inputs.forEach(input => input.style.display = 'none');
        }

        function positionInputs(coordenadas) {
            const rectCanvas = canvas.getBoundingClientRect();
            for (const [id, pos] of Object.entries(coordenadas)) {
                const input = document.getElementById(id);
                if (input) {
                    input.style.left = `${canvas.offsetLeft + pos.x}px`;
                    input.style.top = `${canvas.offsetTop + pos.y}px`;
                    input.style.display = 'block';
                }
            }
        }

        $('div[name="radio_direccion_apertura"]').on('change', function () {
            // Verificar si se están cargando selectores
            if (cargandoSelectores) {
                console.log('BLOQUE: Ignorando evento change de radio durante carga de selectores');
                return;
            }
            
            const seleccion = $('input[name="direccion_apertura"]:checked').val();
            console.log('...................SELECCIONADO DIRECCION APERTURA CON VALOR: ', seleccion);
            getSelectorSiguiente('direccion_apertura', seleccion);
        });

        $("select[name='numero_hojas']").on('change', function () {
            // Verificar si se están cargando selectores o asignando valores programáticamente
            if (cargandoSelectores || window.asignandoValoresProgramaticamente) {
                console.log('BLOQUE: Ignorando evento change de select durante carga/asignación programática');
                return;
            }
            
            //console.log("Seleccionado hoja: ", this.value);
            //console.log("Seleccionado hoja: ", hijos_imagenes_hojas);
            // Mostrar tarjeta hojas_info_card si hay info
            const hojaSeleccionada = this.value;
            const hijos_imagenes_hojas_array = Array.isArray(hijos_imagenes_hojas) ? hijos_imagenes_hojas : Object.values(hijos_imagenes_hojas);
            const hoja = hijos_imagenes_hojas_array.find(h => h.id == hojaSeleccionada);
            if (hoja) {
                    $('#hojas_img').attr('src', assetapp+`/images/cotizador/${hoja.image}`);
                    $('#hojas_nombre').text(hoja.valor);
                    $('#hojas_descripcion').text('');
                    $('#hojas_info_card').removeClass('d-none');
                } else {
                    $('#hojas_info_card').addClass('d-none');
                }
            getSelectorSiguiente('numero_hojas', hojaSeleccionada);
        });

        $('div[name="card_tipo_riel"]').on('change', function () {
            // Verificar si se están cargando selectores
            if (cargandoSelectores) {
                console.log('BLOQUE: Ignorando evento change de card durante carga de selectores');
                return;
            }
            
            const seleccion = $('input[name="tipo_riel"]:checked').val();

            const rielSeleccionado = seleccion;
            //const data = imagenes_medidas_array.find(i => i.id_riel == rielSeleccionado);
            console.log("rielSeleccionado: ", rielSeleccionado);
            //console.log("data imagenes ", data);
            getSelectorSiguiente('tipo_riel', rielSeleccionado);

            //if (!data) return;

            //mensajeSeleccion.style.display = 'none'; // Oculta mensaje

        });

        // Si cambia el tamaño de ventana, se recolocan los inputs
        window.addEventListener('resize', () => {
            const checked = document.querySelector('input[name="tipo_riel"]:checked');
            if (checked) {
                const data = imagenes_medidas_array.find(i => i.id_riel == checked.value);
                if (data) {
                    try {
                        const coordenadas = JSON.parse(data.coordenadas);
                        positionInputs(coordenadas);
                    } catch (e) {}
                }
            }
        });


        $('#form_medidas').on('submit', function(e) {
            // Validación separada y por visibilidad
            const rielSeleccionado = $('input[name="tipo_riel"]:checked');
            const $divRiel = $("div[name='card_tipo_riel']");
            if ($divRiel.is(':visible') && rielSeleccionado.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Riel faltante',
                    text: 'Por favor selecciona un tipo de riel.'
                });
                return;
            }

            const $selectHojas = $("select[name='numero_hojas']");
            const numeroHojas = $selectHojas.val();
            if ($selectHojas.is(':visible') && !numeroHojas) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Número de hojas faltante',
                    text: 'Por favor selecciona el número de hojas.'
                });
                return;
            }

            // Validar inputs de canvas SOLO si están visibles
            const idsInputs = ['inputLadoA', 'inputLadoB', 'inputAlto', 'inputAncho', 'inputRadio'];
            for (const id of idsInputs) {
                const $input = $('#' + id);
                if ($input.is(':visible') && !$input.val()) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campo faltante',
                        text: 'Por favor completa el campo ' + ($input.attr('placeholder') || id) + '.',
                        confirmButtonText: 'Aceptar'
                    });
                    return;
                }
            }
        });

        /*
        bloque
        */
       //1.- obtener valores de sesión
       let valoresSesion = @json(session()->get('avance_temporal'));
        console.log('BLOQUE valoresSesion: ', valoresSesion);
        //convertir valoresSesion a json
        if (typeof valoresSesion === 'string') {
            try {
                valoresSesion = JSON.parse(valoresSesion);
            } catch (e) {
                console.error('Error al parsear valoresSesion:', e);
                valoresSesion = {};
            }
        }
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
        selectoresACargar = selectores.filter(selector => selector.PAS_Pantalla_Ubicacion == $('input[name="pantalla_ubicacion"]').val());
        console.log('BLOQUE selectores a cargar: ', selectoresACargar);
        
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
                            console.log('BLOQUE: Carga de selectores completada');
                            // Desbloquear pantalla al final de toda la carga
                            $.unblockUI();
                        }, 500);
                    } else {
                        console.log('BLOQUE llenando selector: ', selector.PAS_Html_name);
                        getSelectorAndFill(selector.PAS_Html_name, valoresSesion[selector.PAS_Html_name], selector.PAS_Pantalla_Ubicacion, false);
                        // Esperar un poco antes de cargar el siguiente
                        setTimeout(() => {
                            indice++;
                            cargarSiguiente();
                        }, 300);
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
        
        //5.- definir el valor de siguiente-vista
        const siguienteVista = valoresSesion['siguiente-vista'] || '';
        if (siguienteVista === 'resumen') {
            $('input[name="siguiente-vista"]').val('resumen');
            $('.btn-success').text('Resumen');
        } else {
            $('.btn-success').text('Siguiente');
        }
    }); //fin document ready
</script>
@endsection