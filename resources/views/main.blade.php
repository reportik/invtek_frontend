@extends('layouts/contentNavbarLayoutOnly' )

<script>
  /* function selectEligeTela(event) {
    //colocar en tarjeta_imagen el value del select, y en tarjeta_titulo el texto seleccionado del select
    const selectedValue = event.target.value;
    const selectedText = event.target.options[event.target.selectedIndex].text;
    // Colocar en tarjeta_imagen el value del select (base64) con el prefijo adecuado
    document.getElementById('tarjeta_imagen').src = `data:image/png;base64,${selectedValue}`;
    // Colocar en tarjeta_titulo el texto seleccionado del select
    document.getElementById('tarjeta_titulo').innerText = selectedText;
  } */

  document.addEventListener("DOMContentLoaded", function () {
      $('[data-toggle="tooltip"]').tooltip();
      const stepperElement = document.querySelector("#wizard-property-listing");
      const stepper = new Stepper(stepperElement);
      //ir a una seccion en el stepper
        //stepper.to(5);
      // Manejar el botón Siguiente
      const nextButtons = document.querySelectorAll(".btn-next");
      nextButtons.forEach((button) => {
        button.addEventListener("click", () => {
        stepper.next(); // Ir al siguiente paso
      });
      });

      // Manejar el botón Anterior
      const prevButtons = document.querySelectorAll(".btn-prev");
      prevButtons.forEach((button) => {
      button.addEventListener("click", () => {
      stepper.previous(); // Volver al paso anterior
      });
      });
});

function toggleSelect() {
// Ocultar ambos select al principio
document.getElementById('sel_tela_bo').style.display = 'none';
document.getElementById('sel_tela_sheer').style.display = 'none';

// Obtener el valor del radio button seleccionado
const selectedValue = document.querySelector('input[name="radio_step_3"]:checked').value;

// Mostrar el select correspondiente
if (selectedValue === 'Blackout') {
document.getElementById('sel_tela_bo').style.display = 'block';
} else if (selectedValue === 'Sheer') {
document.getElementById('sel_tela_sheer').style.display = 'block';
}
// Llamar a la función para actualizar la tarjeta al seleccionar un valor
updateCardImage();
}

// Llamar a la función para asegurarnos de que el select correcto se muestre al cargar la página
window.onload = function() {
toggleSelect();
updateCardImage(); // Asegurarnos de que la tarjeta se actualice al cargar
};

async function updateCardImage() {
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

    // Obtener el select actualmente visible
    const selectElement = document.querySelector('select[style="display: block;"].sel_tipo_tela');
    console.log("Elemento select:", selectElement);
    console.log("Valor seleccionado:", selectElement.value);

    if (!selectElement) return; // Si no hay select visible, salir

    var selectedValue = selectElement.value; // ID de la tela
    var selectedText = selectElement.options[selectElement.selectedIndex].text;
    try {
    // Realizar la solicitud al endpoint FastAPI
    const response = await fetch(`http://itekniaapp.serveftp.com:3036/get-image/${selectedValue}`);
      console.log(response);
    if (!response.ok) {
    $.unblockUI();
    throw new Error(`HTTP error! Status: ${response.status}`);
    }

    const data = await response.json();
    console.log(data);
    // Actualizar la imagen y el título
    document.getElementById('tarjeta_imagen').src = `data:image/png;base64,${data.image}`;
    document.getElementById('tarjeta_titulo').innerText = selectedText;
  } catch (error) {
  $.unblockUI();
    console.error("Error al cargar la imagen:", error);
    document.getElementById('tarjeta_imagen').src = ""; // Limpiar la imagen si hay un error
    }
    $.unblockUI();
}

function selectEligeTela(event) {
// Actualizar la tarjeta al cambiar el select
updateCardImage();
}

function showModal(imageSrc) {
const modal = document.getElementById('imageModal');
const modalImage = document.getElementById('modalImage');
modalImage.src = imageSrc; // Set the image source
modal.style.display = 'flex'; // Show the modal
}

function closeModal(event) {
const modal = document.getElementById('imageModal');
if (event.target === modal || event.target.tagName === 'SPAN') {
modal.style.display = 'none'; // Hide the modal
}
}
</script>

<style>

</style>
@section('content')
<!-- Modal HTML -->
<div id="imageModal"
  style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 1050; align-items: center; justify-content: center;"
  onclick="closeModal(event)">
  <span onclick="closeModal()"
    style="position: absolute; top: 10px; right: 20px; font-size: 30px; color: white; cursor: pointer;">&times;</span>
  <img id="modalImage" style="max-width: 90%; max-height: 90%; border-radius: 8px;" />
</div>

<div class="row">
  <div style="display: flex; align-items: center; justify-content: center; margin: 20px 0;">
    <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
    <h2
      style="color: #59981A; font-family: 'Arial', sans-serif; font-size: 36px; font-weight: bold; text-align: center; letter-spacing: 1px;">
      Cotizador de Cortinas
    </h2>
    <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
  </div>

</div>
<div class="row">
  <div class="col-md-9">
    <div id="wizard-property-listing" class="bs-stepper vertical mt-2 linear">
      <div class="bs-stepper-header gap-lg-2 border-end">
        @foreach ($steps as $item)

        <div class="step @if ($item['a_selected'] == 'true')
                        active
                    @endif" data-target="{{'#target_step_' . $item['number']}}">
          <button type="button" class="step-trigger" aria-selected="{{$item['a_selected']}}"
            @if($item['a_selected']=='false' ) disabled @endif>
            <span class="bs-stepper-circle"><i class="ri-check-line"></i></span>
            <span class="bs-stepper-label">
              <span class="bs-stepper-number">{{$item['number']}}</span>
              <span class="d-flex flex-column ms-2">
                <span class="bs-stepper-title bs-stepper">{{$item['title']}}</span>
              </span>
            </span>
          </button>
        </div>
        <div class="line"></div>
        @endforeach

      </div>
      <div class="bs-stepper-content">
        <div id="wizard-property-listing-form">

          <div id="target_step_1" class="content active dstepper-block fv-plugins-bootstrap5 fv-plugins-framework">
            <span class="bs-title">SELECCIONA EL ESPACIO DONDE UBICARÁS TU CORTINA</span>
            <div class="row g-6">

              <div class="row row-cols-1 row-cols-md-3 g-6 mb-4">
                @foreach ($cards_1 as $item)
                <div class="col">
                  <div class="card">
                    <img class="card-img-top" src="{{ asset('images/' . $item['image'])}}" alt="Card image cap"
                      onclick="showModal('{{ asset('images/' . $item['image']) }}')" style="cursor: pointer;">
                    <div class="card-body">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="radio_step_1" id="radio1_{{ $loop->index }}"
                          value="{{$item['opcion_radio']}}" @if ($item['a_selected']=='true' ) checked @endif>
                        <label class="form-check-label"
                          for="radio1_{{ $loop->index }}">{{$item['opcion_radio']}}</label>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>

              <div class="col-12 d-flex justify-content-end">

                <button style="text-align: right;" class="btn btn-primary btn-next waves-effect waves-light"> <span
                    class="align-middle d-sm-inline-block me-sm-1">Siguiente</span> <i
                    class="ri-arrow-right-line ri-16px"></i></button>
              </div>
            </div>
          </div>

          <div id="target_step_2" class="content fv-plugins-bootstrap5 fv-plugins-framework">
            <span class="bs-title">ELIGE EL SISTEMA DE CONFECCIÓN QUE DESEAS</span>
            <div class="row g-6">
              <div class="row row-cols-1 row-cols-md-3 g-6 mb-4">
                @foreach ($cards_2 as $item)
                <div class="col">
                  <div class="card">
                    <img class="card-img-top" src="{{ asset('images/' . $item['image'])}}" alt="Card image cap"
                      onclick="showModal('{{ asset('images/' . $item['image']) }}')" style="cursor: pointer;">
                    <div class="card-body">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="radio_step_2" id="radio2_{{ $loop->index }}"
                          value="{{$item['opcion_radio']}}" @if ($item['a_selected']=='true' ) checked @endif>
                        <label class="form-check-label"
                          for="radio2_{{ $loop->index }}">{{$item['opcion_radio']}}</label>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>

              <div class="col-12 d-flex justify-content-between">
                <button class="btn btn-outline-secondary btn-prev waves-effect"> <i
                    class="ri-arrow-left-line ri-16px me-sm-1 me-0"></i> <span
                    class="align-middle d-sm-inline-block d-none">Anterior</span> </button>
                <button class="btn btn-primary btn-next waves-effect waves-light"> <span
                    class="align-middle d-sm-inline-block d-none me-sm-1">Siguiente</span> <i
                    class="ri-arrow-right-line ri-16px"></i></button>
              </div>
            </div>
          </div>

          <div id="target_step_3" class="content fv-plugins-bootstrap5 fv-plugins-framework">
            <span class="bs-title">ELIGE EL TIPO DE TELA EN QUE DESEAS CONFECCIONAR TU CORTINA</span>
            <div class="row g-6">
              <div class="row row-cols-1 row-cols-md-3 g-6 mb-4">
                @foreach ($cards_3 as $item)
                <div class="col">
                  <div class="card">
                    <img class="card-img-top" src="{{ asset('images/' . $item['image'])}}" alt="Card image cap"
                      onclick="showModal('{{ asset('images/' . $item['image']) }}')" style="cursor: pointer;">
                    <div class="card-body">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="radio_step_3" id="radio3_{{ $loop->index }}"
                          onclick="toggleSelect()" value="{{$item['opcion_radio']}}" @if ($item['a_selected']=='true' )
                          checked @endif>
                        <label class="form-check-label"
                          for="radio3_{{ $loop->index }}">{{$item['opcion_radio']}}</label>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
              <label for="sel_tela_bo" class="form-label">Selecciona tu Tela:</label>
              <select id="sel_tela_bo" class="sel_tipo_tela form-select form-select-lg"
                onchange="selectEligeTela(event)">

                @foreach ($telas_blackout as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
              </select>

              <select id="sel_tela_sheer" style="display: block;" class="sel_tipo_tela form-select form-select-lg"
                onchange="selectEligeTela(event)">

                @foreach ($telas_sheer as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
              </select>
              <!-- Tarjeta -->
              <div class="card" style="width: 18rem;">
                @if (count($telas_blackout) > 0)

                <img id="tarjeta_imagen" src="" class="mt-3 card-img-top" style="border-radius: 8px 8px 0 0;"
                  alt="Tela Image">
                <div class="card-body">
                  <h5 id="tarjeta_titulo" class="card-title"></h5>
                  <p class="card-text"></p>
                </div>
                @endif

              </div>
              <div class="col-12 d-flex justify-content-between">
                <button class="btn btn-outline-secondary btn-prev waves-effect"> <i
                    class="ri-arrow-left-line ri-16px me-sm-1 me-0"></i> <span
                    class="align-middle d-sm-inline-block d-none">Anterior</span> </button>
                <button class="btn btn-primary btn-next waves-effect waves-light"> <span
                    class="align-middle d-sm-inline-block d-none me-sm-1">Siguiente</span> <i
                    class="ri-arrow-right-line ri-16px"></i></button>
              </div>
            </div>
          </div>

          <div id="target_step_4" class="content fv-plugins-bootstrap5 fv-plugins-framework">
            <span class="bs-title mb-2">ESPECIFICA LAS MEDIDAS DEL ESPACIO TOTAL QUE OCUPARÁ LA
              CORTINA Y LAS HOJAS EN QUE ESTARÁ DIVIDIDA</span>
            <hr>
            <div class="row g-6 mt-1">
              <div class="row row-cols-1 row-cols-md-3 g-6 mb-4">
                <div class="col-md-6">
                  <div class="form-floating form-floating-outline mb-5">
                    <input type="number" class="form-control" id="width" name="width" placeholder="" autocomplete="off">
                    <label for="width">Ancho (m):</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating form-floating-outline mb-5">
                    <input type="number" class="form-control" id="height" name="height" placeholder=""
                      autocomplete="off">
                    <label for="height">Alto (m):</label>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="sheets">Hojas:
                    <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Partes móviles que se pueden abrir y cerrar
                                      recorriendo a un lado o el otro, para
                                      permitir o bloquear la entrada de luz."></i>
                  </label>
                  <div class="form-floating form-floating-outline mb-5">
                    <input step="1" min="1" type="number" class="form-control" id="sheets" name="sheets"
                      placeholder="Hojas" autocomplete="off">
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="overlap">
                    Traslape:
                    <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Cantidad de tela que se superpone
                              cuando las cortinas están cerradas. Esta
                              superposición ayuda a bloquear mejor la
                              luz."></i>
                  </label>
                  <div class="form-floating form-floating-outline mb-5">

                    <select class="form-control selectpicker control-usuario" id="overlap" name="overlap">
                      <option value="10">Traslape corto (10 cm)</option>
                      <option value="15">Traslape corto (15 cm)</option>
                      <option value="20">Traslape medio (20 cm)</option>
                      <option value="25">Traslape medio (25 cm)</option>
                      <option value="30">Traslape largo (30 cm)</option>

                    </select>
                  </div>
                </div>
              </div>
              <div class="col-12 d-flex justify-content-between">
                <button class="btn btn-outline-secondary btn-prev waves-effect"> <i
                    class="ri-arrow-left-line ri-16px me-sm-1 me-0"></i> <span
                    class="align-middle d-sm-inline-block d-none">Anterior</span> </button>
                <button class="btn btn-primary btn-next waves-effect waves-light"> <span
                    class="align-middle d-sm-inline-block d-none me-sm-1">Siguiente</span> <i
                    class="ri-arrow-right-line ri-16px"></i></button>
              </div>
            </div>
          </div>
          <div id="target_step_5" class="content fv-plugins-bootstrap5 fv-plugins-framework">
            <span class="bs-title mb-2">AGREGA ESPECIFICACIONES DE LOS ACCESORIOS</span>
            <hr>

            <div class="row g-6 mt-1">
              <div class="form-group col-md-6 g-6">
                <label for="sheets">Bastón:
                </label>
                <div class="form-floating form-floating-outline mb-5">
                  <select class="form-control selectpicker control-usuario" id="baston" name="overlap">
                    <option value="fibra_vidrio_negro">Fibra de vidrio en color negro</option>
                    <option value="fibra_vidrio_blanco">Fibra de vidrio en color blanco</option>
                  </select>

                </div>
              </div>
              <div class="form-group col-md-6 g-6">
                <label for="overlap">
                  Mecanismo de Apertura:
                </label>
                <div class="form-floating form-floating-outline mb-5">

                  <select class="form-control selectpicker control-usuario" id="mecanismo" name="overlap">
                    <option value="manual">Manual</option>
                    <option value="motorizado">Motorizado</option>

                  </select>
                </div>
              </div>
            </div>

            <div class="col-12 d-flex justify-content-between">
              <button class="btn btn-outline-secondary btn-prev waves-effect"> <i
                  class="ri-arrow-left-line ri-16px me-sm-1 me-0"></i> <span
                  class="align-middle d-sm-inline-block d-none">Anterior</span> </button>
              <button class="btn btn-primary btn-next waves-effect waves-light"> <span
                  class="align-middle d-sm-inline-block d-none me-sm-1">Siguiente</span> <i
                  class="ri-arrow-right-line ri-16px"></i></button>
            </div>
          </div>
        </div>


      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card mt-4">
      <div class="card-body">
        <h5 class="card-title text-muted fw-bold">Resumen de Cotización</h5>
        <hr>
        <p class="text-muted small">Aquí verás los importes.</p>
      </div>
    </div>
  </div>
</div>



@endsection