/* function selectEligeTela(event) {
    //colocar en tarjeta_imagen el value del select, y en tarjeta_titulo el texto seleccionado del select
    let selectedValue = event.target.value;
    let selectedText = event.target.options[event.target.selectedIndex].text;
    // Colocar en tarjeta_imagen el value del select (base64) con el prefijo adecuado
    document.getElementById('tarjeta_imagen').src = `data:image/png;base64,${selectedValue}`;
    // Colocar en tarjeta_titulo el texto seleccionado del select
    document.getElementById('tarjeta_titulo').innerText = selectedText;
  } */
function selectTela(event) {
    // Obtener el botón que disparó el evento y la tarjeta correspondiente
    const button = event.target;
    const card = button.closest('.card'); // Buscar el elemento padre con la clase 'card'
    console.log('carfd: ' + card);  
    
    // Obtener el ID de la tela desde un atributo de la tarjeta (puedes agregar un atributo data-id a la tarjeta)
    const telaId = card.getAttribute('data-id');
    console.log('tela: ' + telaId);  
    // Buscar el selectpicker visible (sin display: none)
    let selectElement = getVisibleSelectpicker();
    
    // Verificar si el select existe
    if (!selectElement) {
      console.log('No hay select visible.');
      return;
    }
    
    // Asignar el valor seleccionado al selectpicker y refrescarlo
    $(selectElement).selectpicker('val', telaId).selectpicker('refresh');
    console.log('tela?selectpicker: ' + $(selectElement).selectpicker('val'));  

    updateCardImage();

    }

function actualizarListaResumen(step) {
  let listaResumen = document.getElementById('lista_resumen');
  let newLi = document.createElement('li');
  newLi.classList.add('list-group-item');
 
  // Dependiendo del paso, agregar el contenido correspondiente
  switch (step) {
    case 0:
      newLi.innerHTML = '<strong>Sistema de confección: </strong> <span id="resumen_sistema"></span> </strong> <span id="resumen_sistema_riel"></span>';
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

function selectColor(element, index) {
    // 1. Detectar si está activo el tradicional o el ripplefold
    let visibleGroup = '';
    if ($('#rielesTradicional').is(':visible')) {
        visibleGroup = 'Tradicional';
    } else if ($('#rielesRipplefold').is(':visible')) {
        visibleGroup = 'Ripplefold';
    } else {
        console.warn('Ningún grupo de rieles está visible');
        return;
    }

    document.querySelectorAll('input[name^="radio_riel_"]').forEach(r => {
        r.checked = false;
    });

    document.querySelectorAll('.color-option').forEach(c => {
        c.classList.remove('selected-color');
    });

    // 4. Seleccionar el radio correspondiente dentro del grupo visible
    const targetRadio = document.querySelector(`#radioRiel${visibleGroup}_${index}`);
    if (targetRadio) {
        targetRadio.checked = true;
    }

    // 5. Marcar como seleccionado el color actual
    element.classList.add('selected-color');

    // 6. Mostrar resultado (opcional)
    const result = getSelectedColorForRadio();
    if (result) {
        console.log(`Opción Riel: ${result.opcion_riel}`);
        console.log(`Color: ${result.color_name} (${result.color_value})`);
        document.getElementById('resumen_sistema_riel').innerText = " con Riel " + result.opcion_riel + " (Color: " + result.color_name + ")";
    }
}

function handleRielChange(tipo, index) {
    // Remover todos los colores seleccionados en ese grupo
    document.querySelectorAll(`.selected-color`).forEach(el => {
        el.classList.remove('selected-color');
    });

    // Seleccionar el primer color de este nuevo riel
    const firstColor = document.querySelector(`#rieles${tipo} [data-group="color-group-${index}"]`);
    if (firstColor) {
        firstColor.classList.add('selected-color');
    }

    // Actualizar resumen
    const result = getSelectedColorForRadio();
    if (result) {
        console.log(`Opción: ${result.opcion}`);
        console.log(`Opción Riel: ${result.opcion_riel}`);
        console.log(`Color: ${result.color_name} (${result.color_value})`);
        document.getElementById('resumen_sistema').innerText = result.opcion;
        document.getElementById('resumen_sistema_riel').innerText = " con Riel " + result.opcion_riel + " (Color: " + result.color_name + ")";
    }
}

 

function getSelectedColorForRadio() {
    // Encuentra el radio seleccionado
    const selectedRadio = document.querySelector('input[name="radio_step_2"]:checked');
    if (!selectedRadio) return null;

    // Encuentra el radio seleccionado
    console.log('selectedRadio: ' + `input[name="radio_riel_${selectedRadio.value}"]:checked`);
    
    // Encuentra el radio seleccionado del grupo visible
    const selectedRiel = document.querySelector(`input[name="radio_riel_${selectedRadio.value}"]:checked`);
    if (!selectedRiel) return null;

    // Obtener el índice del radio seleccionado desde su ID
    const radioId = selectedRiel.id; // ejemplo: "radio2_1"
    const index = radioId.split("_")[1]; // extraemos el número

    // Buscar el color marcado como seleccionado en ese grupo
    const selectedColor = document.querySelector(`[data-group="color-group-${index}"].selected-color`);
    console.log('selectedColor: ' + `[data-group="color-group-${index}"].selected-color`);
    
    if (selectedColor) {
        const colorName = selectedColor.getAttribute('data-color');
        const colorValue = selectedColor.getAttribute('data-value');
        return {
            opcion: selectedRadio.value,
            opcion_riel: selectedRiel.value,
            color_name: colorName,
            color_value: colorValue
        };
    }

    return null;
}


// Seleccionar el primer color de cada grupo al cargar la página

function changeValue(step) {
  let input = document.getElementById('numericInput');
  let newValue = parseInt(input.value) + step;
  if (newValue >= 1) {
    // Evita valores menores a 1
    input.value = newValue;
  }
}
document.addEventListener('DOMContentLoaded', function () {
   /* document.querySelectorAll(".color-container").forEach(container => {
        let firstColor = container.querySelector(".color-option");
        if (firstColor) {
            firstColor.classList.add("selected-color");
        }
    }) */;
    
  //deshabilitar boton resumen_btn
      document.getElementById('resumen_btn').disabled = true;
      //tambien la clase
      document.getElementById('resumen_btn').classList.add('disabled');


  $('[data-toggle="tooltip"]').tooltip();
  let stepperElement = document.querySelector('#wizard-property-listing');
  let stepper = new Stepper(stepperElement);
  //ir a una seccion en el stepper
  //stepper.to(3);
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
        //sistema_riel: document.getElementById('resumen_sistema_riel')?.innerText.trim() || '',
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
            text: 'Cotización guardada con éxito.',
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
    $('#tabla_resumen_cotizacion').DataTable().buttons().enable();
    $('#tabla_resumen_cotizacion button').prop('disabled', false);
    $('#tabla_resumen_cotizacion input').prop('disabled', false);
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
      },
      //un boton para enviar la cotizacion 
      {
        //icono enviar
        text: '<i class="fas fa-paper-plane"></i> Enviar Cotización',
        className: 'btn btn-primary mb-2',
        action: function (e, dt, node, config) {
          //obtener el id de la cotizacion
          var cotizacion_id = $('#cotizacion_id').data('id') || 0;
          //si el id es 0, se manda un mensaje de error
          if (cotizacion_id == 0) {
            Swal.fire({
              title: 'Error',
              text: 'No hay elementos en el Resumen. Agrega al menos una cortina.',
              icon: 'error'
            });
          } else {
            //se envia el id mediante ajax post y se abre una nueva ventana con el path de la respuesta
            $.blockUI({ 
                message: '<h3>Generando cotización...<br>Por favor, espera.</h3>',
                css: { backgroundColor: '#000', opacity: 0.5, color: '#fff' } 
            });

            $.ajax({
                type: 'POST',
                async: true,
                url: routeapp + '/create_quotation',
                data: { _token: token, id: cotizacion_id },
                success: function (response) {
                    $.unblockUI();
                    // Bloquear todos los botones de la tabla
                    $('#tabla_resumen_cotizacion').DataTable().buttons().disable();
                    $('#tabla_resumen_cotizacion button').prop('disabled', true);
                    $('#tabla_resumen_cotizacion input').prop('disabled', true);

                    Swal.fire({
                        title: '¡Cotización enviada!',
                        text: "ID de Cotización: " + response.order_id,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Ver PDF',
                        cancelButtonText: 'Cerrar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            generatePDF(response.order_id);  // Llamar a la función para generar el PDF
                        }
                    });
                },
                error: function () {
                    $.unblockUI();
                    Swal.fire('Error', 'Error en la solicitud.', 'error');
                }
            });
            
          }
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
  async function generatePDF(orderId) {
    try {
        $.blockUI({ 
            message: '<h3>Generando PDF...<br>Por favor, espera.</h3>',
            css: { backgroundColor: '#000', opacity: 0.5, color: '#fff' } 
        });

        let response = await fetch(`http://itekniaapp.serveftp.com:3036/generate-quotation-pdf/${orderId}`);
        let pdfResponse = await response.json();

        $.unblockUI();

        if (pdfResponse.status === "success") {
            window.open(routeapp + '/pdfs/' + pdfResponse.pdf_name, '_blank');
        } else {
            Swal.fire('Error', 'No se pudo generar el PDF.', 'error');
        }
    } catch (error) {
        $.unblockUI();
        Swal.fire('Error', 'Error al generar el PDF.', 'error');
    }
}

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
    //console.log(datos);
    //si hay solo una fila no se puede eliminar, enviar mensaje de que no puede quedar vacio 
    let totalBotones = $('#tabla_resumen_cotizacion button.control-usuario').length;

    if (totalBotones < 2) {
      Swal.fire({
        title: 'Error',
        text: 'No se puede quedar vacía la cotización.',
        icon: 'error'
      });
      return false;
    } else {
      //preguntar si se desea eliminar la cotizacion
      Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Deseas eliminar esta cortina?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',

        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí',
        cancelButtonText: 'No'
      }).then((result) => {
        if (result.isConfirmed) {

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
          }); //end ajax
        } //end if
      }); //end Swal
    }
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

  // Mostrar el selectpicker correspondiente y cargar el catálogo de telas
  if (selectedValue === 'Blackout') {
    // Mostrar el selectpicker de telas blackout
    $('#sel_tela_bo').selectpicker('show');
    // Cargar el catálogo de telas blackout en el modal
    cargarCatalogo('blackout');
  } else if (selectedValue === 'Sheer') {
    // Mostrar el selectpicker de telas sheer
    $('#sel_tela_sheer').selectpicker('show');
    // Cargar el catálogo de telas sheer en el modal
    cargarCatalogo('sheer');
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

  // Mostrar el grupo correspondiente
  let targetGroup = '';
  if (selectedValue === 'Tradicional') {
    $('#rielesTradicional').show();
    $('#rielesRipplefold').hide();
    targetGroup = 'Tradicional';
  } else if (selectedValue === 'Ripplefold') {
    $('#rielesRipplefold').show();
    $('#rielesTradicional').hide();
    targetGroup = 'Ripplefold';
  } else {
    $('#rielesRipplefold').hide();
    $('#rielesTradicional').hide();
    return;
  }

  
    
  //////////////
  // Seleccionar primer riel del grupo visible
  const firstRadio = document.querySelector(`input[name="radio_riel_${targetGroup}"]`);
  if (firstRadio) {
    firstRadio.checked = true;

    // Obtener índice desde su ID
    const radioId = firstRadio.id; // ej: radioRielTradicional_0
    const index = radioId.split("_")[1];

    // Remover selección de colores previos en ese grupo
    document.querySelectorAll(`.selected-color`).forEach(el => {
      el.classList.remove('selected-color');
      //remover checked
      const colorInput = el.querySelector('input[type="radio"]');
      if (colorInput) {
        colorInput.checked = false;
      }      
    });

    // Seleccionar el primer color de este nuevo riel
    const firstColor = document.querySelector(`#rieles${targetGroup} [data-group="color-group-0"]`);
    if (firstColor) {
        firstColor.classList.add('selected-color');
    
      //set checked
      const colorInput = firstColor.querySelector('input[type="radio"]');
      if (colorInput) {
        colorInput.checked = true;
      }

    }
  }

  // Actualizar resumen
  const result = getSelectedColorForRadio();
  if (result) {
    console.log(`Opción: ${result.opcion}`);
    console.log(`Opción Riel: ${result.opcion_riel}`);
    console.log(`Color: ${result.color_name} (${result.color_value})`);
    document.getElementById('resumen_sistema').innerText = result.opcion;
    document.getElementById('resumen_sistema_riel').innerText = " con Riel " + result.opcion_riel + " (Color: " + result.color_name + ")";
  }
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
  //$(selectElement).selectpicker('val', id).selectpicker('refresh');
  //$('select.sel_tipo_tela.selectpicker').selectpicker('val', '1').selectpicker('refresh');

  // Verificar si el select existe
  if (!selectElement) {
    console.log('No hay select visible.');
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
