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
      //habilitar boton resumen_btn
      document.getElementById('resumen_btn').disabled = false;
      //tambien la clase
      document.getElementById('resumen_btn').classList.remove('disabled');


      //document.getElementById('resumen_total').style.display = 'block'; // O "flex" si es un contenedor flexible
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
  //deshabilitar boton resumen_btn
      document.getElementById('resumen_btn').disabled = true;
      //tambien la clase
      document.getElementById('resumen_btn').classList.add('disabled');


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
    // Capturar datos del formulario y validar si existen los elementos
    const resumenData = {
        cortina: document.getElementById('resumen_cortina')?.innerText.trim() || '',
        sistema: document.getElementById('resumen_sistema')?.innerText.trim() || '',
        tela: document.getElementById('resumen_tela')?.innerText.trim() || '',
        tela_id: document.getElementById('resumen_tela_id')?.innerText.trim() || '',
        tela_tipo: document.querySelector('input[name="radio_step_3"]:checked')?.value || '',
        ancho: document.getElementById('resumen_ancho')?.innerText.trim() || '',
        alto: document.getElementById('resumen_alto')?.innerText.trim() || '',
        hojas: document.getElementById('resumen_hojas')?.innerText.trim() || '',
        traslape: document.getElementById('resumen_traslape')?.innerText.trim() || '',
        baston: document.getElementById('resumen_baston')?.innerText.trim() || '',
        mecanismo: document.getElementById('resumen_mecanismo')?.innerText.trim() || '',
        cantidad: parseInt(document.getElementById('numericInput')?.value, 10) || 0
    };

    // Lista de errores
    let errores = [];

    for (const key in resumenData) {
        if (!resumenData[key]) {
            errores.push(`El campo <b>${key}</b> es obligatorio.`);
        }
    }

    // Mostrar alerta con bootbox si hay errores
    if (errores.length > 0) {
        bootbox.alert({
            title: "Campos Requeridos",
            message: errores.join("<br>"),
            size: "small"
        });
        return;
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
      beforeSend: function () {
        // Bloquear la pantalla mientras se envían los datos
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
      complete: function () {
          $.unblockUI();
        },
      success: function (data) {
        if (data.success) {
          // Mostrar ID de la cotización en una etiqueta HTML
          $('#cotizacion_id')
            .data('id', data.cotizacion) // Guardar el ID en el elemento para futuras ediciones
            .text(data.cotizacion);
          // Mostrar alerta de éxito
          Swal.fire({
            title: 'Éxito',
            text: 'Cotización guardada con éxito. ID: ' + data.cotizacion,
            icon: 'success'
          });
          // quitar ls elementos de lista resumen, menos el primero y el stepper colocarlo en el primer paso

          stepper.to(0);
              //deshabilitar boton resumen_btn
          document.getElementById('resumen_btn').disabled = true;
          //tambien la clase
          document.getElementById('resumen_btn').classList.add('disabled');
          //limpiar lista resumen
          // Selecciona la lista
          const lista = document.getElementById('lista_resumen');
          // Obtén todos los elementos de la lista
          const items = lista.getElementsByTagName('li');
          // Itera sobre los elementos de la lista y elimina los que están después del segundo
          for (let i = items.length - 1; i >= 2; i--) {
            lista.removeChild(items[i]);
          }
          //document.getElementById('resumen_total').style.display = 'none'; // esto es para ocultar el total
         
          //recargar tabla
          $('#tabla_resumen_cotizacion').DataTable().ajax.reload();
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

  $('#tabla_resumen_cotizacion').DataTable({
    responsive: true,
    autoWidth: true,
    //un dom que muestre los botones, la tabla y la información sin busqueda
    dom: 'Brti',
    //definir un custom boton
    buttons: [
      {
        text: '<i class="fas fa-plus"></i> Cortina',
        className: 'btn btn-primary mb-2',
        action: function (e, dt, node, config) {
          var tab = new bootstrap.Tab(document.querySelector(`[data-bs-target="#navs-top-home"]`));
          tab.show();
        }
      }
    ],
    language: {
      url: assetapp + '/plugins/DataTables/json/es-MX.json'
    },
    ajax: {
      url: routeapp + '/get-cotizaciones',
      type: 'POST',
      data: function () {
        return {
          _token: token,
          id: parseInt($('#cotizacion_id').data('id') || 0, 10)
        };
      }
    },
    //ordenar siempre por la columna 0 y 6
    //order: [[0, 'asc'], [6, 'asc']],
    columns: [
      { data: 'cortinaId' },
      { data: 'Producto' },
      { data: 'Descripcion' },
      { data: 'Cantidad' },
      { data: 'Precio_Unitario' },
      { data: 'Total' },
      //la siguiente columna no se muestra.
      { data: 'Orden', visible: false },
    ],
    columnDefs: [
      {
        targets: [0],
        searchable: false,
        orderable: false,
        className: 'dt-body-center',
        render: function (data, type, row, meta) {          
          //si la columna Producto row[1] es null, se deja vacio, en otro caso se coloca el boton
          if (row.Producto == null) {
            return '';
          } else {
            return (
              '<button data-partida=' +
              meta.row +
              ' type="button" class="btn btn-danger control-usuario" id="btnEliminar"> <span class="bi bi-trash"></span> </button>'
            );
          }
        }
      },
      {
        targets: 1,
        render: function (data, type, row) {
          //columna producto es para mostrar una imagen, si es null, no se muestra nada
          if (data == null) {
            return '';
          } else {
            return `<img src="data:image/png;base64,${data}" width="70" height="70"/>`;
          }
        }
      },

      {
        targets: 3,
        className: 'text-end',
        render: function (data, type, row) {
          // Asegúrate de que el valor de monto sea válido
          var monto = parseInt(data);
          if (isNaN(monto)) {
            monto = 1; // Valor por defecto
          }
          if (row.Producto == null) {
            return data;
          }else{
            
            return (
              '<input id="input-cantidad" style="text-align: right;" onchange="actualiza_total()" class="input-total form-control input-sm control-usuario" type="number" step="1" min="1" value="' +
              data +
              '">'
            );
          }
        }
      },
      {
        targets: 4,
        className: 'text-end',
        render: function (data, type, row) {
          return '$ ' + number_format(data, 2, '.', ',');
        }
      },
      {
        targets: 5,
        className: 'text-end',
        render: function (data, type, row) {
          return '$ ' + number_format(data, 2, '.', ',');
        }
      }
    ],
    //footerCallback: total sobre precio_unitario, cantidad y total
    footerCallback: function (row, data, start, end, display) {
      var api = this.api(),
        data;

      // Remove the formatting to get integer data for summation
      // Función para limpiar los valores numéricos eliminando signos de dólar y comas
      var intVal = function (i) {
          if (typeof i === 'string') {
            return parseFloat(i.replace(/[\$,]/g, '').replace(/,/g, '')) || 0;
          }
          return typeof i === 'number' ? i : 0;
      };

      // Total sobre precio_unitario, si row.Producto es null, el valor no debe sumarse
       // Recorremos las filas visibles en la tabla
    let cantidad = 0;
    let subtotal = 0;
    let gran_total = 0;  
    api.rows().every(function () {
        //ibtenemos los datos de la fila
        let data = this.data();
        console.log(data);
        let producto = data.Producto; // Primera columna (Producto)
        let catidad_producto = intVal(data.Cantidad); 
        let precio_unitario = intVal(data.Precio_Unitario);
        let total = intVal(data.Total);
        // Si el producto no es null ni vacío, sumamos el precio unitario
        if (producto !== null && producto !== "") {
            cantidad += catidad_producto;
            subtotal += precio_unitario;
            gran_total += total;
        }
    });  

      // Update footer
      $(api.column(3).footer()).html('(' + cantidad + ') Cortina(s)');
      $(api.column(4).footer()).html('$ ' + number_format(subtotal, 2, '.', ','));
      $(api.column(5).footer()).html('$ ' + number_format(gran_total, 2, '.', ','));
    },
    rowCallback: function (row, data) {
        let producto = data.Producto; // Primera columna (Producto)

        if (producto !== null && producto !== "") {
            $(row).css('background-color', '#EDEFF5'); // Fondo gris
        }
    },
    drawCallback: function () {
      let table = $('#tabla_resumen_cotizacion').DataTable();
      let rowCount = table.rows().count();

      // Solo ejecutar si hay filas en la tabla
      //if (rowCount > 0) {
      if (false) {
        // Cargar las imágenes de los productos
        $('#tabla_resumen_cotizacion tbody tr').each(async function () {
          let row = $('#tabla_resumen_cotizacion').DataTable().row(this).data();
          let imgElement = $(this).find(`img[id="img_${row.producto}"]`);

          try {
            let response = await fetch(`http://itekniaapp.serveftp.com:3036/get-image/${row.producto}`);
            if (response.ok) {
              let data = await response.json();
              imgElement.attr('src', `data:image/png;base64,${data.image}`);
            } else {
              //imgElement.attr('src', '/images/default.png'); // Imagen por defecto
            }
          } catch (error) {
            //imgElement.attr('src', '/images/default.png');
          }
        });
      }
    }
  });

  /**
   * Actualiza el valor total de la partida al cambiar la cantidad
   * @event - change
   * @param -
   * @return  -
   */
  $('#tabla_resumen_cotizacion').on('change', 'input#input-cantidad', function (e) {
    var tabla = $('#tabla_resumen_cotizacion').DataTable();
    var fila = $(this).closest('tr');
    var datos = tabla.row(fila).data();

    //console.log(datos);
    //si el valor es menor a 1, se mandara un mensaje de error
    if (parseInt($(this).val()) < 1) {
      Swal.fire({
        title: 'Error',
        text: 'La cantidad no puede ser menor a 1.',
        icon: 'error'
      });
      $($(this).closest('tr'))
        .find('td:eq(' + 3 + ') input[id="input-cantidad"]')
        .val(1);
    } else {
      //bloquear pantalla
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

      $.ajax({
        type: 'POST',
        async: true,
        data: {
          _token: token,
          id: datos.cortinaId, //Eliminar tiene el id de la cotizacion, pues se coloca en el boton eliminar
          cantidad: $(this).val()
        },
        url: routeapp + '/update-cotizacion',

        success: function (data) {
          if (data.success) {
            $('#tabla_resumen_cotizacion').DataTable().ajax.reload();
          } else {
            Swal.fire({
              title: 'Error',
              text: 'No se pudo guardar la cotización.',
              icon: 'error'
            });
          }
        },
        complete: function () {
          $.unblockUI();
        },
        error: function (xhr) {
          $.unblockUI();
        }
      });
    }
    //desbloquear pantalla

    /* if (parseFloat($(this).val()) < 0) {
      bootbox.alert({
        size: 'large',
        title: "<h4><i class='fa fa-info-circle'></i> Alerta</h4>",
        message: "<div class='alert alert-danger m-b-0'> Mensaje : El porcentaje no puede ser menor a 0.00% ."
      });
      datos['CAG_PORCENTAJE'] = parseFloat('0').toFixed(PORCENTAJE_DECIMALES);
      datos['CAG_MONTO'] = parseFloat('0').toFixed(POLIZAS_DECIMALES);
      $(this).val(datos['CAG_PORCENTAJE']);
      $(this).focus();
      monto_porcentaje = datos['CAG_MONTO'];
    } else if (parseFloat($(this).val()) > 100) {
      bootbox.alert({
        size: 'large',
        title: "<h4><i class='fa fa-info-circle'></i> Alerta</h4>",
        message: "<div class='alert alert-danger m-b-0'> Mensaje : El porcentaje no puede ser mayor a 100.00% ."
      });
      datos['CAG_PORCENTAJE'] = parseFloat('0').toFixed(PORCENTAJE_DECIMALES);
      datos['CAG_MONTO'] = parseFloat('0').toFixed(POLIZAS_DECIMALES);
      $(this).val(datos['CAG_PORCENTAJE']);
      $(this).focus();
      monto_porcentaje = datos['CAG_MONTO'];
    } else {
      datos['CAG_PORCENTAJE'] = parseFloat($(this).val()).toFixed(PORCENTAJE_DECIMALES);

      $(this).val(parseFloat(datos['CAG_PORCENTAJE']).toFixed(PORCENTAJE_DECIMALES));

      monto_gasto = parseFloat(cadenaANumero(datos['CAG_MES'])).toFixed(POLIZAS_DECIMALES);
      porcentaje = parseFloat(datos['CAG_PORCENTAJE']);
      monto_porcentaje = parseFloat((parseFloat(monto_gasto) * porcentaje) / 100).toFixed(PORCENTAJE_DECIMALES);

      datos['CAG_MONTO'] = monto_porcentaje;
    }

    $($(this).closest('tr'))
      .find('td:eq(' + COL_GASTOS_MONTO + ') input[id="input-monto"]')
      .val(monto_porcentaje); */
  });
  /**
   * Actualiza cotizacion a eliminar
   * @event - change
   * @param -
   * @return  -
   */
  $('#tabla_resumen_cotizacion').on('click', 'button#btnEliminar', function (e) {
    var tabla = $('#tabla_resumen_cotizacion').DataTable();
    var fila = $(this).closest('tr');
    var datos = tabla.row(fila).data();

    console.log(datos);

    //bloquear pantalla
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

    $.ajax({
      type: 'POST',
      async: true,
      data: {
        _token: token,
        id: datos.cortinaId //Eliminar tiene el id de la cotizacion, pues se coloca en el boton eliminar
      },
      url: routeapp + '/eliminar-cotizacion',

      success: function (data) {
        if (data.success) {
          $('#tabla_resumen_cotizacion').DataTable().ajax.reload();
        } else {
          Swal.fire({
            title: 'Error',
            text: 'No se pudo borrar la cotización.',
            icon: 'error'
          });
        }
      },
      complete: function () {
        $.unblockUI();
      },
      error: function (xhr) {
        $.unblockUI();
      }
    });
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
 
  // Obtener el texto de la opción seleccionada
  let selectedText = $(selectElement).find('option:selected').text();

  if (document.getElementById('resumen_tela')) {
    // Asignar el valor seleccionado al elemento con id 'resumen_tela'
    document.getElementById('resumen_tela').innerText = selectedText;
  }
  document.getElementById('resumen_tela_id').innerText = selectedValue;

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

function number_format(number, decimals, dec_point, thousands_sep) {
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = typeof thousands_sep === 'undefined' ? ',' : thousands_sep,
    dec = typeof dec_point === 'undefined' ? '.' : dec_point,
    toFixedFix = function (n, prec) {
      // Fix for IE parseFloat(0.55).toFixed(0) = 0;
      var k = Math.pow(10, prec);
      return Math.round(n * k) / k;
    },
    s = (prec ? toFixedFix(n, prec) : Math.round(n)).toString().split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}

function actualiza_total(e) {
  //console.log('data');
  var tabla = $('#tabla_resumen_cotizacion').DataTable();
  tabla.draw(true);
  console.log('data');
}
