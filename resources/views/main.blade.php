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

      const stepperElement = document.querySelector("#wizard-property-listing");
      const stepper = new Stepper(stepperElement);

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

function updateCardImage() {
// Obtener el valor y el texto del select actualmente visible
const selectElement = document.querySelector('select[style="display: block;"]');
if (!selectElement) return; // Si no hay select visible, salir

const selectedValue = selectElement.value;
const selectedText = selectElement.options[selectElement.selectedIndex].text;

// Colocar en tarjeta_imagen el value del select (base64) con el prefijo adecuado
document.getElementById('tarjeta_imagen').src = `data:image/png;base64,${selectedValue}`;

// Colocar en tarjeta_titulo el texto seleccionado del select
document.getElementById('tarjeta_titulo').innerText = selectedText;
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

              <div class="row row-cols-1 row-cols-md-3 g-6 mb-6">
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
              <div class="row row-cols-1 row-cols-md-3 g-6 mb-6">
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
              <div class="row row-cols-1 row-cols-md-3 g-6 mb-6">
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
              <select id="sel_tela_bo" class="form-select form-select-lg" onchange="selectEligeTela(event)">

                @foreach ($telas_blackout as $item)
                <option value="{{ $item['image'] }}">{{ $item['name'] }}</option>
                @endforeach
              </select>

              <select id="sel_tela_sheer" class="form-select form-select-lg" onchange="selectEligeTela(event)">

                @foreach ($telas_sheer as $item)
                <option value="{{ $item['image'] }}">{{ $item['name'] }}</option>
                @endforeach
              </select>
              <!-- Tarjeta -->
              <div class="card" style="width: 18rem;">
                @if (count($telas_blackout) > 0)

                <img id="tarjeta_imagen" src="{{'data:image/png;base64,'.$telas_blackout[0]['image']}}"
                  class="card-img-top" style="border-radius: 8px 8px 0 0;" alt="Tela Image">
                <div class="card-body">
                  <h5 id="tarjeta_titulo" class="card-title">{{$telas_blackout[0]['name']}}</h5>
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
            <span class="bs-title">ESPECIFICA LAS MEDIDAS DEL ESPACIO TOTAL QUE OCUPARÁ LA
              CORTINA Y LAS HOJAS EN QUE ESTARÁ DIVIDIDA</span>
            <div class="row g-6">


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