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
  let listaResumen = document.getElementById('lista_resumen');
  let newLi = document.createElement('li');
  newLi.classList.add('list-group-item');

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
      newLi.innerHTML =
        '<strong>Medidas: </strong> <span id="resumen_ancho"></span> (m) de Ancho x <span id="resumen_alto"></span> (m) de Alto';
      listaResumen.appendChild(newLi);
      newLi = document.createElement('li');
      newLi.classList.add('list-group-item');
      newLi.innerHTML = '<strong># Hojas: </strong> <span id="resumen_hojas"></span> Hojas';
      listaResumen.appendChild(newLi);
      newLi = document.createElement('li');
      newLi.classList.add('list-group-item');
      newLi.innerHTML = '<strong>Traslape: </strong> <span id="resumen_traslape"></span> cm';
      listaResumen.appendChild(newLi);
      actualiza_resumen_medidas();
      break;
    case 3:
      let resultado = actualiza_resumen_medidas();
      if (resultado) {
      } else {
        return false;
        console.log('Faltan campos por capturar.');
      }

      newLi.innerHTML = '<strong>Bastón: </strong> <span id="resumen_baston"></span>';
      listaResumen.appendChild(newLi);
      newLi = document.createElement('li');
      newLi.classList.add('list-group-item');
      newLi.innerHTML = '<strong>Mecanismo de Apertura: </strong> <span id="resumen_mecanismo"></span>';
      listaResumen.appendChild(newLi);
      newLi = document.createElement('li');
      newLi.classList.add('list-group-item');
      actualiza_resumen_accesorios();
      document.getElementById('resumen_total').style.display = 'block'; // O "flex" si es un contenedor flexible
      break;

    default:
      return false; // No agregar nada si el paso no está en la lista
  }
  return true;
}

function changeValue(step) {
  let input = document.getElementById('numericInput');
  let newValue = parseInt(input.value) + step;
  if (newValue >= 1) {
    // Evita valores menores a 1
    input.value = newValue;
  }
}
document.addEventListener('DOMContentLoaded', function () {
  $('[data-toggle="tooltip"]').tooltip();
  let stepperElement = document.querySelector('#wizard-property-listing');
  let stepper = new Stepper(stepperElement);
  //ir a una seccion en el stepper
  //stepper.to(4);
  // Manejar el botón Siguiente
  let nextButtons = document.querySelectorAll('.btn-next');
  nextButtons.forEach(button => {
    button.addEventListener('click', () => {
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
  let prevButtons = document.querySelectorAll('.btn-prev');
  prevButtons.forEach(button => {
    button.addEventListener('click', () => {
      stepper.previous(); // Volver al paso anterior
    });
  });

  document.getElementById('resumen_btn').addEventListener('click', function () {
    // Capturar datos del formulario
    const resumenData = {
      cortina: document.getElementById('resumen_cortina').innerText.trim(),
      sistema: document.getElementById('resumen_sistema').innerText.trim(),
      tela: document.getElementById('resumen_tela').innerText.trim(),
      ancho: document.getElementById('resumen_ancho').innerText.trim(),
      alto: document.getElementById('resumen_alto').innerText.trim(),
      hojas: document.getElementById('resumen_hojas').innerText.trim(),
      traslape: document.getElementById('resumen_traslape').innerText.trim(),
      baston: document.getElementById('resumen_baston').innerText.trim(),
      mecanismo: document.getElementById('resumen_mecanismo').innerText.trim(),
      precio_unitario:
        parseFloat(
          document.getElementById('resumen_precio_unitario').innerText.replace('$', '').replace(',', '').trim()
        ) || 0,
      cantidad: parseInt(document.getElementById('numericInput').value, 10) || 0
    };

    // Validar que los datos no estén vacíos
    for (const key in resumenData) {
      if (!resumenData[key]) {
        alert(`El campo ${key} es obligatorio.`);
        return;
      }
    }

    // Enviar datos por AJAX
    let cotizacionId = $('#cotizacion_id').data('id') || 0; // Obtener ID de la cotización si existe

    $.ajax({
      type: 'POST',
      async: true,
      data: {
        _token: token,
        cotizacion_id: cotizacionId > 0 ? cotizacionId : null, // Enviar solo si existe
        ...resumenData // Enviamos el objeto directamente
      },
      dataType: 'json',
      url: routeapp + '/guardar-cotizacion',

      success: function (data) {
        if (data.success) {
          // Mostrar ID de la cotización en una etiqueta HTML
          $('#cotizacion_id')
            .data('id', data.cotizacion) // Guardar el ID en el elemento para futuras ediciones
            .text(' ID: ' + data.cotizacion);
          // Mostrar alerta de éxito
          Swal.fire({
            title: 'Éxito',
            text: 'Cotización guardada con éxito. ID: ' + data.cotizacion,
            icon: 'success'
          });
        } else {
          Swal.fire({
            title: 'Error',
            text: 'No se pudo guardar la cotización.',
            icon: 'error'
          });
        }
      },

      error: function (xhr) {
        try {
          var error = JSON.parse(xhr.responseText);
          var mensajeError = error.message || 'Ocurrió un error desconocido.';
          var detalleError = error.error ? `<br>Detalle: ${error.error}` : '';

          bootbox.alert({
            size: 'large',
            title: "<h4><i class='fa fa-info-circle'></i> Alerta</h4>",
            message: `<div class='alert alert-danger m-b-0'>
                    <strong>Error:</strong> ${mensajeError}
                    ${detalleError}
                  </div>`
          });
        } catch (e) {
          bootbox.alert({
            title: 'Error',
            message: 'Ocurrió un problema inesperado en la solicitud.'
          });
        }
      }
    });

    var tab = new bootstrap.Tab(document.querySelector(`[data-bs-target="#navs-top-resumen"]`));
    tab.show();
  });
});
function actualiza_resumen_accesorios() {
  let selectedValue = $('#baston').selectpicker('val');
  // Obtener el texto de la opción seleccionada
  let selectedText = $('#baston').find('option:selected').text();
  // Asignar el valor seleccionado al elemento con id 'resumen_tela'
  document.getElementById('resumen_baston').innerText = selectedText;
  selectedValue = $('#mecanismo').selectpicker('val');
  // Obtener el texto de la opción seleccionada
  selectedText = $('#mecanismo').find('option:selected').text();
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

  console.log('ancho ' + ancho, 'alto :' + alto, 'hojas ' + hojas, 'trasla ' + traslape);

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
  let selectedText = $(selectElement).find('option:selected').text();
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
window.onload = function () {
  toggleSelect_1();
  toggleSelect_3();
  updateCardImage();
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

  // Buscar el selectpicker visible (sin display: none)
  let selectElement = getVisibleSelectpicker();

  // Verificar si el select existe
  if (!selectElement) {
    console.log('No hay select visible.');
    //$('select.sel_tipo_tela.selectpicker').selectpicker('val', '1').selectpicker('refresh');
  }

  // Obtener el valor seleccionado con Bootstrap Select
  let selectedValue = $(selectElement).selectpicker('val');
  console.log(selectedValue);
  // Obtener el texto de la opción seleccionada
  let selectedText = $(selectElement).find('option:selected').text();

  if (document.getElementById('resumen_tela')) {
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
    console.error('Error al cargar la imagen:', error);
    document.getElementById('tarjeta_imagen').src = ''; // Limpiar la imagen si hay un error
  }
  $.unblockUI();
}

function selectEligeTela(event) {
  // Actualizar la tarjeta al cambiar el select
  updateCardImage();
}
function selectEligeMecanismo(event) {
  $('#mecanismo').selectpicker('val');
  let selectedText = $('#mecanismo').find('option:selected').text();
  document.getElementById('resumen_mecanismo').innerText = selectedText;
}
function selectEligeBaston(event) {
  $('#baston').selectpicker('val');
  let selectedText = $('#baston').find('option:selected').text();
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
