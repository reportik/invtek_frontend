@extends('layouts/contentNavbarLayoutOnly' )

<script>
  /* function selectEligeTela(event) {
    //colocar en tarjeta_imagen el value del select, y en tarjeta_titulo el texto seleccionado del select
    let selectedValue = event.target.value;
    let selectedText = event.target.options[event.target.selectedIndex].text;
    // Colocar en tarjeta_imagen el value del select (base64) con el prefijo adecuado
    document.getElementById('tarjeta_imagen').src = `data:image/png;base64,${selectedValue}`;
    // Colocar en tarjeta_titulo el texto seleccionado del select
    document.getElementById('tarjeta_titulo').innerText = selectedText;
  } */
function actualizarListaResumen(step) {
  let listaResumen = document.getElementById("lista_resumen");
  let newLi = document.createElement("li");
  newLi.classList.add("list-group-item");

  // Dependiendo del paso, agregar el contenido correspondiente
  switch (step) {
    case 0:
      newLi.innerHTML = '<strong>Sistema de confección: </strong> <span id="resumen_sistema"></span>';
      listaResumen.appendChild(newLi);
      toggleSelect_2();
      break;
    case 1:
      newLi.innerHTML = '<strong>Tela: </strong> <span id="resumen_tela"></span>';
      listaResumen.appendChild(newLi);
      actualiza_resumen_tela();
      break;
    case 2:
      newLi.innerHTML = '<strong>Medidas: </strong> <span id="resumen_ancho"></span> (m) de Ancho x <span id="resumen_alto"></span> (m) de Alto';
      listaResumen.appendChild(newLi);
      newLi = document.createElement("li");
      newLi.classList.add("list-group-item");
      newLi.innerHTML = '<strong># Hojas: </strong> <span id="resumen_hojas"></span> Hojas';
      listaResumen.appendChild(newLi);
      newLi = document.createElement("li");
      newLi.classList.add("list-group-item");
      newLi.innerHTML = '<strong>Traslape: </strong> <span id="resumen_traslape"></span> cm';
      listaResumen.appendChild(newLi);
      actualiza_resumen_medidas();
      break;
    case 3:
      let resultado = actualiza_resumen_medidas();
      if (resultado) {

      } else {
      return false
      console.log("Faltan campos por capturar.");
      }

      newLi.innerHTML = '<strong>Bastón: </strong> <span id="resumen_baston"></span>';
      listaResumen.appendChild(newLi);
      newLi = document.createElement("li");
      newLi.classList.add("list-group-item");
      newLi.innerHTML = '<strong>Mecanismo de Apertura: </strong> <span id="resumen_mecanismo"></span>';
      listaResumen.appendChild(newLi);
      newLi = document.createElement("li");
      newLi.classList.add("list-group-item");
      actualiza_resumen_accesorios();
      document.getElementById("resumen_total").style.display = "block"; // O "flex" si es un contenedor flexible
      break;

    default:
    return false; // No agregar nada si el paso no está en la lista
  }
return true

}
function goToTab(tabId) {
var tab = new bootstrap.Tab(document.querySelector(`[data-bs-target="${tabId}"]`));
tab.show();
}
function changeValue(step) {
let input = document.getElementById("numericInput");
let newValue = parseInt(input.value) + step;
if (newValue >= 1) { // Evita valores menores a 1
input.value = newValue;
}
}
  document.addEventListener("DOMContentLoaded", function () {


      $('[data-toggle="tooltip"]').tooltip();
      let stepperElement = document.querySelector("#wizard-property-listing");
      let stepper = new Stepper(stepperElement);
      //ir a una seccion en el stepper
      //stepper.to(4);
      // Manejar el botón Siguiente
      let nextButtons = document.querySelectorAll(".btn-next");
      nextButtons.forEach((button) => {
      button.addEventListener("click", () => {
      // Obtener el paso actual antes de avanzar
      let currentStep = stepper._currentIndex; // Dependiendo de la librería del Stepper, este método puede variar
      console.log(currentStep);
      // Agregar el elemento correspondiente al resumen
      let rs = actualizarListaResumen(currentStep);
        if (rs) {
          // Ir al siguiente paso
          stepper.next();
        }
      });
      });

      // Manejar el botón Anterior
      let prevButtons = document.querySelectorAll(".btn-prev");
      prevButtons.forEach((button) => {
      button.addEventListener("click", () => {
      stepper.previous(); // Volver al paso anterior
      });
      });
});
function actualiza_resumen_accesorios() {
  let selectedValue = $('#baston').selectpicker('val');
  // Obtener el texto de la opción seleccionada
  let selectedText = $('#baston').find("option:selected").text();
  // Asignar el valor seleccionado al elemento con id 'resumen_tela'
  document.getElementById('resumen_baston').innerText = selectedText;
  selectedValue = $('#mecanismo').selectpicker('val');
  // Obtener el texto de la opción seleccionada
  selectedText = $('#mecanismo').find("option:selected").text();
  // Asignar el valor seleccionado al elemento con id 'resumen_tela'
  document.getElementById('resumen_mecanismo').innerText = selectedText;
}
function toggleSelect_3() {
  // Ocultar ambos selectpicker al principio
  $('#sel_tela_bo').selectpicker('hide');
  $('#sel_tela_sheer').selectpicker('hide');

  // Obtener el valor del radio button seleccionado
  let selectedValue = document.querySelector('input[name="radio_step_3"]:checked').value;

  // Mostrar el selectpicker correspondiente
  if (selectedValue === 'Blackout') {
  $('#sel_tela_bo').selectpicker('show');
  } else if (selectedValue === 'Sheer') {
  $('#sel_tela_sheer').selectpicker('show');
  }

  // Llamar a la función para actualizar la tarjeta al seleccionar un valor
  updateCardImage();
}

function actualiza_resumen_medidas() {
  // Obtener el valor de los inputs de ancho y alto
  let ancho = document.getElementById('width').value;
  let alto = document.getElementById('height').value;
  let hojas = document.getElementById('sheets').value;
  let traslape = $('#overlap').selectpicker('val');

  console.log('ancho ' + ancho, "alto :" + alto,'hojas '+ hojas, 'trasla '+ traslape);

  // Validar que todos los campos estén capturados
  if (!ancho || !alto || !hojas || !traslape) {
  Swal.fire({
  title: 'Error!',
  text: 'Capture las medidas de la cortina',
  icon: 'error',
  confirmButtonText: 'Aceptar'
  });
  return false; // Indicar que la operación no se completó
  }

  // Asignar los valores al elemento con id 'resumen_ancho' y 'resumen_alto'
  document.getElementById('resumen_ancho').innerText = ancho;
  document.getElementById('resumen_alto').innerText = alto;
  document.getElementById('resumen_hojas').innerText = hojas;
  document.getElementById('resumen_traslape').innerText = traslape;

  return true; // Indicar que la operación se completó correctamente
}

function actualiza_resumen_tela() {
  // Obtener el valor del selectpicker seleccionado
  let selectElement = getVisibleSelectpicker();

  // Obtener el valor seleccionado con Bootstrap Select
  let selectedValue = $(selectElement).selectpicker('val');
  console.log(selectedValue);
  // Obtener el texto de la opción seleccionada
  let selectedText = $(selectElement).find("option:selected").text();
  // Asignar el valor seleccionado al elemento con id 'resumen_tela'
  document.getElementById('resumen_tela').innerText = selectedText;
}
function toggleSelect_1() {
  // Obtener el valor del radio button seleccionado
  let selectedValue = document.querySelector('input[name="radio_step_1"]:checked').value;

  // Asignar el valor seleccionado al elemento con id 'resumen_cortina'
  document.getElementById('resumen_cortina').innerText = selectedValue;

}
function toggleSelect_2() {
  // Obtener el valor del radio button seleccionado
  let selectedValue = document.querySelector('input[name="radio_step_2"]:checked').value;

  // Asignar el valor seleccionado
  document.getElementById('resumen_sistema').innerText = selectedValue;

}

function getVisibleSelectpicker() {
  // Obtener todos los selectpicker
  let selectElements = document.querySelectorAll('select.sel_tipo_tela.selectpicker');

  // Iterar sobre los selectpicker y devolver el que está visible
  for (let selectElement of selectElements) {
  if ($(selectElement).is(':visible')) {
  return selectElement;
  }
  }

  // Si no se encuentra ninguno visible, devolver null
  return $('#sel_tela_bo');
}
// Llamar a la función para asegurarnos de que el select correcto se muestre al cargar la página
window.onload = function() {
  toggleSelect_1();
  toggleSelect_3();
  updateCardImage();
}

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

    // Buscar el selectpicker visible (sin display: none)
    let selectElement = getVisibleSelectpicker();

    // Verificar si el select existe
    if (!selectElement) {
    console.log("No hay select visible.");
    //$('select.sel_tipo_tela.selectpicker').selectpicker('val', '1').selectpicker('refresh');

    }

    // Obtener el valor seleccionado con Bootstrap Select
    let selectedValue = $(selectElement).selectpicker('val');
    console.log(selectedValue);
    // Obtener el texto de la opción seleccionada
    let selectedText = $(selectElement).find("option:selected").text();

    if (document.getElementById('resumen_tela')){
      // Asignar el valor seleccionado al elemento con id 'resumen_tela'
      document.getElementById('resumen_tela').innerText = selectedText;
    }

    try {
    // Realizar la solicitud al endpoint FastAPI
    let response = await fetch(`http://itekniaapp.serveftp.com:3036/get-image/${selectedValue}`);
      console.log(response);
    if (!response.ok) {
    $.unblockUI();
    throw new Error(`HTTP error! Status: ${response.status}`);
    }

    let data = await response.json();
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
function selectEligeMecanismo(event) {
  $('#mecanismo').selectpicker('val');
  let selectedText = $('#mecanismo').find("option:selected").text();
  document.getElementById('resumen_mecanismo').innerText = selectedText;
}
function selectEligeBaston(event) {
  $('#baston').selectpicker('val');
  let selectedText = $('#baston').find("option:selected").text();
  document.getElementById('resumen_baston').innerText = selectedText;
}

function showModal(imageSrc) {
let modal = document.getElementById('imageModal');
let modalImage = document.getElementById('modalImage');
modalImage.src = imageSrc; // Set the image source
modal.style.display = 'flex'; // Show the modal
}

function closeModal(event) {
let modal = document.getElementById('imageModal');
if (event.target === modal || event.target.tagName === 'SPAN') {
modal.style.display = 'none'; // Hide the modal
}
}
</script>

<style>
  .selectpicker .dropdown-menu {}
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

<div class="nav-align-top">
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
      <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-home"
        aria-controls="navs-top-home" aria-selected="true">Cotizador</button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-resumen"
        aria-controls="navs-top-resumen" aria-selected="false">Resumen Cotización</button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-payment"
        aria-controls="navs-top-payment" aria-selected="false">Pago</button>
    </li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane fade show active" id="navs-top-home" role="tabpanel">
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

                <div id="target_step_1"
                  class="content active dstepper-block fv-plugins-bootstrap5 fv-plugins-framework">
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
                              <input class="form-check-input" type="radio" name="radio_step_1"
                                id="radio1_{{ $loop->index }}" onclick="toggleSelect_1()"
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

                      <button style="text-align: right;" class="btn btn-primary btn-next waves-effect waves-light">
                        <span class="align-middle d-sm-inline-block me-sm-1">Siguiente</span> <i
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
                              <input class="form-check-input" type="radio" name="radio_step_2"
                                id="radio2_{{ $loop->index }}" onclick="toggleSelect_2()"
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
                              <input class="form-check-input" type="radio" name="radio_step_3"
                                id="radio3_{{ $loop->index }}" onclick="toggleSelect_3()"
                                value="{{$item['opcion_radio']}}" @if ($item['a_selected']=='true' ) checked @endif>
                              <label class="form-check-label"
                                for="radio3_{{ $loop->index }}">{{$item['opcion_radio']}}</label>
                            </div>
                          </div>
                        </div>
                      </div>
                      @endforeach
                    </div>

                    <div class="row">
                      <div class="col-6">
                        <label for="sel_tela_bo" class="form-label">Selecciona tu Tela:</label>
                        <select id="sel_tela_bo" class="selectpicker sel_tipo_tela" data-live-search="true"
                          data-size="5" onchange="selectEligeTela(event)">

                          @foreach ($telas_blackout as $item)
                          <option value="{{ $item->id }}">{{ $item->name }}</option>
                          @endforeach
                        </select>

                        <select id="sel_tela_sheer" style="display: block;" class="selectpicker sel_tipo_tela"
                          data-size="5" data-live-search="true" onchange="selectEligeTela(event)">

                          @foreach ($telas_sheer as $item)
                          <option value="{{ $item->id }}">{{ $item->name }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-6">
                        <!-- Tarjeta -->
                        <div class="card" style="width: 18rem;">
                          @if (count($telas_blackout) > 0)

                          <img id="tarjeta_imagen" src="" class="mt-3 card-img-top" style="border-radius: 8px 8px 0 0;"
                            alt="Tela Image">
                          <div class="card-body">
                            <h6 id="tarjeta_titulo" class="card-title"></h6>
                            <p class="card-text"></p>
                          </div>
                          @endif

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

                <div id="target_step_4" class="content fv-plugins-bootstrap5 fv-plugins-framework">
                  <span class="bs-title mb-2">ESPECIFICA LAS MEDIDAS DEL ESPACIO TOTAL QUE OCUPARÁ LA
                    CORTINA Y LAS HOJAS EN QUE ESTARÁ DIVIDIDA</span>
                  <hr>
                  <div class="row g-6 mt-1">
                    <div class="row row-cols-1 row-cols-md-3 g-6 mb-4">
                      <div class="col-md-6">
                        <div class="form-floating form-floating-outline mb-5">
                          <input type="number" class="form-control" value="1" id="width" name="width" placeholder=""
                            autocomplete="off">
                          <label for="width">Ancho (m):</label>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-floating form-floating-outline mb-5">
                          <input type="number" class="form-control" value="1" id="height" name="height" placeholder=""
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
                          <input step="1" min="1" value="1" type="number" class="form-control" id="sheets" name="sheets"
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
                        <select class="form-control selectpicker control-usuario" id="baston" name="baston"
                          onchange="selectEligeBaston(event)">
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

                        <select class="form-control selectpicker control-usuario" id="mecanismo" name="mecanismo"
                          onchange="selectEligeMecanismo(event)">
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

                  </div>
                </div>
              </div>


            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card mt-4">
            <div class="card-body">
              <h5 class="card-title text-muted fw-bold">Detalle</h5>
              <hr>

              <ul id="lista_resumen" class="list-group list-group-timeline collapsed" data-bs-toggle="collapse"
                data-bs-target="#lista_resumen">
                <li class="list-group-item list-group-timeline-success">
                  <strong>Cortina para:</strong> <span id="resumen_cortina"></span>
                </li>
              </ul>



            </div>
            <div id="resumen_total" class="card-body" style="display: none">
              <div class="mt-3">
                <span class="text-muted fw-bold">Precio Unitario: </span>
                <span id="resumen_precio_unitario">$5,000</span>
              </div>
              <hr>

              <span class="text-muted fw-bold">Cantidad: </span>
              <div class="d-flex align-items-center">
                <button class="btn btn-outline-secondary" onclick="changeValue(-1)">-</button>
                <input type="number" id="numericInput" class="form-control text-center mx-2" style="width: 70px;"
                  value="1" min="1">
                <button class="btn btn-outline-secondary" onclick="changeValue(1)">+</button>
              </div>

              <div class="card-body text-end">

                <button onclick="goToTab('#navs-top-resumen')" id="resumen_btn"
                  class="btn btn-primary mt-1 text-end">Resumen Cotización</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="navs-top-resumen" role="tabpanel">
      <p>
        Resumen / Vista de Final de compra
      </p>
    </div>
    <div class="tab-pane fade" id="navs-top-payment" role="tabpanel">

      <p class="mb-0">
      <div class="row">
        <div class="col-12">
          <div class="card">

            <div class="card-body">
              <div class="row">
                <div class="col-lg-8 mx-auto">
                  <!-- 1. Delivery Address -->
                  <h5 class="mb-4">1. Dirección de Entrega</h5>
                  <div class="row g-4">
                    <div class="col-md-6">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="fullname" class="form-control" placeholder="John Doe" />
                        <label for="fullname">Nombre completo</label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="input-group input-group-merge">
                        <div class="form-floating form-floating-outline">
                          <input class="form-control" type="text" id="email" name="email" placeholder="john.doe"
                            aria-label="john.doe" aria-describedby="email3" />
                          <label for="email">Email</label>
                        </div>
                        <span class="input-group-text" id="email3">@example.com</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="phone" class="form-control phone-mask" placeholder="658 799 8941"
                          aria-label="658 799 8941" />
                        <label for="phone">Número de contacto</label>
                      </div>
                    </div>

                    <div class="col-12">
                      <div class="form-floating form-floating-outline">
                        <textarea name="address" class="form-control" id="address" rows="2" placeholder=""
                          style="height: 65px;"></textarea>
                        <label for="address">Calle</label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="pincode" class="form-control" placeholder="658468" />
                        <label for="pincode">CP</label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="landmark" class="form-control" placeholder="" />
                        <label for="landmark">Colonia</label>
                      </div>
                    </div>
                    <div class="col-md">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="city" class="form-control" placeholder="Jackson" />
                        <label for="city">Ciudad</label>
                      </div>
                    </div>
                    <div class="col-md">
                      <div class="form-floating form-floating-outline">
                        <select id="state" class="select2 form-select" data-allow-clear="true">
                          <option value="">Selecciona</option>

                        </select>
                        <label for="state">Estado</label>
                      </div>
                    </div>


                    <label class="form-check-label">Tipo de Dirección</label>
                    <div class="col mt-2">
                      <div class="form-check form-check-inline">
                        <input name="collapsible-address-type" class="form-check-input" type="radio" value=""
                          id="collapsible-address-type-home" checked="" />
                        <label class="form-check-label" for="collapsible-address-type-home">Casa (Entrega todo el
                          dia)</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input name="collapsible-address-type" class="form-check-input" type="radio" value=""
                          id="collapsible-address-type-office" />
                        <label class="form-check-label" for="collapsible-address-type-office"> Oficina (Entrega de 9 AM
                          a 5 PM) </label>
                      </div>
                    </div>
                  </div>
                  <hr>
                  <!-- 2. Delivery Type -->
                  <h5 class="my-4">2. Tipo de Entrega</h5>
                  <div class="row gy-3">
                    <div class="col-md">
                      <div class="form-check custom-option custom-option-icon">
                        <label class="form-check-label custom-option-content" for="customRadioIcon1">
                          <span class="custom-option-body">
                            <i class='ri-briefcase-line'></i>
                            <span class="custom-option-title"> Standard </span>
                            <small> Entrega de 3 a 5 dias. </small>
                          </span>
                          <input name="customRadioIcon" class="form-check-input" type="radio" value=""
                            id="customRadioIcon1" checked />
                        </label>
                      </div>
                    </div>
                    <div class="col-md">
                      <div class="form-check custom-option custom-option-icon">
                        <label class="form-check-label custom-option-content" for="customRadioIcon2">
                          <span class="custom-option-body">
                            <i class='ri-send-plane-2-line'></i>
                            <span class="custom-option-title"> Express </span>
                            <small>Entrega de 2 a 3 dias.</small>
                          </span>
                          <input name="customRadioIcon" class="form-check-input" type="radio" value=""
                            id="customRadioIcon2" />
                        </label>
                      </div>
                    </div>

                  </div>
                  <hr>

                  <hr>
                  <!-- 3. Payment Method -->
                  <h5 class="my-4">3. Metodo de Pago</h5>
                  <div class="row g-3">
                    <div class="mb-3">
                      <div class="form-check form-check-inline">
                        <input name="collapsible-payment" class="form-check-input" type="radio" value=""
                          id="collapsible-payment-cc" checked="" />
                        <label class="form-check-label" for="collapsible-payment-cc">
                          Credit/Debit/ATM Card <i class="ri-bank-card-line"></i>
                        </label>
                      </div>
                    </div>

                    <div class="col-12 col-md-10 col-xxl-8">
                      <div class="input-group input-group-merge mb-4">
                        <div class="form-floating form-floating-outline">
                          <input type="text" id="collapsible-payment-card" name="creditCardMask"
                            class="form-control credit-card-mask" placeholder="1356 3215 6548 7898"
                            aria-describedby="creditCardMask2" />
                          <label for="collapsible-payment-card">Numero tarjeta</label>
                        </div>
                        <span class="input-group-text cursor-pointer p-1" id="creditCardMask2"><span
                            class="card-type"></span></span>
                      </div>
                      <div class="row g-4 mb-3">
                        <div class="col-12 col-md-6">
                          <div class="form-floating form-floating-outline">
                            <input type="text" id="collapsible-payment-name" class="form-control"
                              placeholder="John Doe" />
                            <label for="collapsible-payment-name">Nombre</label>
                          </div>
                        </div>
                        <div class="col-6 col-md-3">
                          <div class="form-floating form-floating-outline">
                            <input type="text" id="collapsible-payment-expiry-date"
                              class="form-control expiry-date-mask" placeholder="MM/YY" />
                            <label for="collapsible-payment-expiry-date"> Fecha Exp.</label>
                          </div>
                        </div>
                        <div class="col-6 col-md-3">
                          <div class="input-group input-group-merge">
                            <div class="form-floating form-floating-outline">
                              <input type="text" id="collapsible-payment-cvv" class="form-control cvv-code-mask"
                                maxlength="3" placeholder="654" />
                              <label for="collapsible-payment-cvv">CVV Code</label>
                            </div>
                            <span class="input-group-text cursor-pointer" id="collapsible-payment-cvv2"><i
                                class="ri-question-line" data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Card Verification Value"></i></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      </p>
    </div>
  </div>
</div>






@endsection