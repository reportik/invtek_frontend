var TBL = '';
var registroNuevo = 0;
var InicializaFunciones = (function () {
  'use strict';
  return {
    init: function () {
      InicializaComponentes();
    }
  };
})();

$(document).ready(function () {
  InicializaFunciones.init();
  $('.selectpicker').selectpicker();
  $('.dropdown-toggle').dropdown();
});

var InicializaComponentes = function () {
  //$('#ceco').val('1102000').selectpicker('refresh');
  $('#btn-enviar').prop('disabled', true);
  InicializaTablas();
};
tbl_cg_col_id = 0;
tbl_cg_col_eliminar = 1;
tbl_cg_col_xml = 2;
tbl_cg_col_pdf = 3;
tbl_cg_col_asistentes = 4;
tbl_cg_col_concepto = 5;
tbl_cg_col_fecha = 6;
tbl_cg_col_descripcion = 7;
tbl_cg_col_monto = 8;
tbl_cg_col_iva = 9;

function InicializaTablas() {
  console.log(concepto_items);
  TBL = $('#tbl_cg').DataTable({
    language: {
      url: '//cdn.datatables.net/plug-ins/2.1.8/i18n/es-MX.json'
    },
    searching: false,
    iDisplayLength: 100,
    aaSorting: [],
    deferRender: true,
    paging: false,
    dom: 'T<"clear">lfrtip',
    columns: [
      { data: 'ID', visible: false },
      { data: 'BTN_ELIMINAR' },
      { data: 'BTN_XML' },
      { data: 'BTN_PDF' },
      { data: 'ASISTENTES' },
      { data: 'CONCEPTO' },
      { data: 'FECHA_GASTO' },
      { data: 'DESCRIPCION' },
      { data: 'MONTO' },
      { data: 'IVA' }
    ],
    columnDefs: [
      {
        targets: [tbl_cg_col_id],
        orderable: false,
        render: function (row) {
          var tblVenta = document.getElementById('tbl_cg').getElementsByTagName('tbody')[0];
          var index = tblVenta.rows.length + 1;

          return index;
        }
      },
      {
        targets: [tbl_cg_col_eliminar],
        searchable: false,
        orderable: false,
        className: 'dt-body-center',
        render: function (data, type, row) {
          return '<button type="button" class="btn btn-danger control-usuario" id="btnEliminar"> <span class="bi bi-trash"></span> </button>';
        }
      },
      {
        targets: [tbl_cg_col_xml],
        searchable: false,
        orderable: false,
        className: 'dt-body-center',
        render: function (data, type, row, meta) {
          return (
            '<input data-partida=' +
            meta.row +
            ' id="input-xml' +
            meta.row +
            '" name="input-b2" type="file" class="file input-xml control-usuario" data-language="es" data-show-caption="false" accept="application/xml" data-show-cancel="false" data-show-remove="false" data-show-preview="false">'
          );
        }
      },
      {
        targets: [tbl_cg_col_pdf],
        searchable: false,
        orderable: false,
        className: 'dt-body-center',
        render: function (data, type, row, meta) {
          return (
            '<input data-partida=' +
            meta.row +
            ' id="input-pdf' +
            meta.row +
            '" name="input-b2" type="file" class="file input-pdf control-usuario" data-language="es" data-show-caption="false" accept="application/pdf" data-show-cancel="false" data-show-remove="false" data-show-preview="false">'
          );
        }
      },
      {
        targets: [tbl_cg_col_asistentes],
        orderable: false,
        render: function (data, type, row) {
          return (
            '<input id="input-cantidad-asistentes" style="width: 70px" class="form-control input-sm cantidad control-usuario" type="number" min="1" value="' +
            parseInt(row['ASISTENTES']) +
            '">'
          );
        }
      },
      {
        targets: [tbl_cg_col_concepto],
        orderable: false,
        render: function (data, type, row, meta) {
          return (
            '<select id="input-concepto-' +
            meta.row +
            '" class="form-control form-select mb-5 control-usuario" data-width="30px" data-size="5" data-window-padding="bottom" data-live-search="true">' +
            concepto_items +
            '</select>'
          );
        }
      },
      {
        targets: [tbl_cg_col_fecha],
        render: function (data, type, row) {
          var texto = row[tbl_cg_col_fecha];
          if (texto === undefined) {
            texto = new Date().toISOString().split('T')[0];
          }
          return (
            '<input id="input-fecha-gasto" style="text-align: right;" class="form-control input-sm control-usuario" type="date" value="' +
            texto +
            '">'
          );
        }
      },
      {
        targets: [tbl_cg_col_descripcion],
        render: function (data, type, row) {
          var texto = row[tbl_cg_col_descripcion];
          if (texto === undefined) {
            texto = '';
          }
          return (
            '<textarea rows="3" id="input-descripcion" style="text-align: left;" class="form-control input-sm control-usuario" type="text" value="' +
            texto +
            '"></textarea>'
          );
        }
      },
      {
        targets: [tbl_cg_col_monto],
        render: function (data, type, row) {
          return (
            '<input id="input-cantidad-monto" style="text-align: right;" onchange="campostexto()" class="input-total form-control input-sm control-usuario" type="number" value="' +
            parseFloat(row[tbl_cg_col_monto]).toFixed(2) +
            '">'
          );
        }
      },
      {
        targets: [tbl_cg_col_iva],
        render: function (data, type, row) {
          return (
            '<input id="input-cantidad-iva" style="text-align: right;" onchange="campostexto()" class="input-total form-control input-sm control-usuario" type="number" value="' +
            parseFloat(row[tbl_cg_col_iva]).toFixed(2) +
            '">'
          );
        }
      }
    ], // tableTools: {sSwfPath: "plugins/DataTables/swf/copy_csv_xls_pdf.swf"},
    footerCallback: function (row, data, start, end, display) {
      var api = this.api(),
        data;

      // Remove the formatting to get integer data for summation
      var intVal = function (i) {
        return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
      };

      // Total over all pages
      /* var total_iva = api
        .column(8)
        .data()
        .reduce(function (a, b) {
          return intVal(a) + intVal(b);
        }, 0); */
      var total_iva = api
        .column(tbl_cg_col_iva)
        .nodes()
        .to$()
        .find('input')
        .map(function () {
          return intVal($(this).val());
        })
        .get()
        .reduce(function (a, b) {
          return a + b;
        }, 0);

      // Update footer
      $(api.column(tbl_cg_col_iva).footer()).html(
        //'$'+pageTotal +' ( $'+ total +' total)'
        '$ ' + number_format(total_iva, 2, '.', ',')
      );

      /*  var total_monto = api
        .column(7)
        .data()
        .reduce(function (a, b) {
          return intVal(a) + intVal(b);
        }, 0); */

      var total_monto = api
        .column(tbl_cg_col_monto)
        .nodes()
        .to$()
        .find('input')
        .map(function () {
          return intVal($(this).val());
        })
        .get()
        .reduce(function (a, b) {
          return a + b;
        }, 0);

      // Update footer
      $(api.column(tbl_cg_col_monto).footer()).html(
        //'$'+pageTotal +' ( $'+ total +' total)'
        '$ ' + number_format(total_monto, 2, '.', ',')
      );
      $(api.column(tbl_cg_col_fecha).footer()).html(
        //'$'+pageTotal +' ( $'+ total +' total)'
        'Gran Total:'
      );
      $('#input-gran-total').val(total_monto + total_iva);
      $(api.column(tbl_cg_col_descripcion).footer()).html(
        //'$'+pageTotal +' ( $'+ total +' total)'
        '$ ' + number_format(total_monto + total_iva, 2, '.', ',')
      );
    },
    initComplete: function () {
      addrow();
    }
  });
}
function getFilas() {
  var table = $('#tbl_cg').DataTable();
  return table.rows().count();
}
// Función para eliminar una fila
$('#tbl_cg').on('click', 'button#btnEliminar', function (e) {
  e.preventDefault();

  var tabla = $('#tbl_cg').DataTable();
  //var fila = $(this).closest('tr');
  //var datos = tabla.row(fila).data();
  //var detalleId = datos['CXCPD_DetalleId'];

  //var fila = $(this).closest('tr').index() + 1;
  //console.log(fila);
  //console.log(getFilas());
  if (getFilas() == 1) {
    bootbox.alert({
      size: 'large',
      title: "<h4><i class= 'fa fa-info-circle'></i> Advertencia</h4>",
      message: "<div class='alert alert-warning m-b-0'> Mensaje : No se puede eliminar esta fila."
    });
  } else {
    var tabla = $('#tbl_cg').DataTable();
    tabla.row($(this).parents('tr')).remove().draw(true);
  }
});
function campostexto(e) {
  //console.log('data');
  var tabla = $('#tbl_cg').DataTable();
  tabla.draw(false);
}
function deshabilitarElementosPorClase(clase) {
  // Selecciona y deshabilita botones, inputs y selects con la clase dada
  $('.' + clase).each(function () {
    $(this).prop('disabled', true); // Deshabilita el elemento
  });
}
$('#btn-enviar')
  .off()
  .on('click', function (e) {
    Swal.fire({
      title: '¿Enviar comprobación a contraloría?',
      text: 'Una vez enviada no podrás modificar tu comprobación',
      icon: 'warning',
      confirmButtonText: 'Enviar'
    }).then(result => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
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
            id: $('#text_cg_id').text().replace('#', ''),
            _token: token
          },
          dataType: 'json',
          url: 'comprobacion-gastos/enviar',
          success: function (data) {
            $.unblockUI();
            Swal.fire({
              title: 'Exito.',
              text: 'Tu comprobación ha sido enviada',
              icon: 'success'
            });
            $('#text_cg_estatus').text('enviada');
            deshabilitarElementosPorClase('control-usuario');
          },
          error: function (xhr, ajaxOptions, thrownError) {
            $.unblockUI();
            var error = JSON.parse(xhr.responseText);
            bootbox.alert({
              size: 'large',
              title: "<h4><i class='fa fa-info-circle'></i> Alerta</h4>",
              message:
                "<div class='alert alert-danger m-b-0'> Mensaje : " +
                error['mensaje'] +
                '<br>' +
                (error['codigo'] != '' ? 'Código : ' + error['codigo'] + '<br>' : '') +
                (error['clase'] != '' ? 'Clase : ' + error['clase'] + '<br>' : '') +
                (error['linea'] != '' ? 'Línea : ' + error['linea'] + '<br>' : '') +
                '</div>'
            });
          }
        });
        $.unblockUI();
      }
    });
  });

$('#btn-guardar')
  .off()
  .on('click', function (e) {
    e.preventDefault();
    if (validaDatosRequeridos()) {
      var Tbl;
      Tbl = getTbl();
      if (Tbl.error !== undefined) {
        bootbox.dialog({
          title: 'Validación',
          message: Tbl.error,
          buttons: {
            success: {
              label: 'Ok',
              className: 'btn-success m-r-5 m-b-5'
            }
          }
        });
      } else {
        if (validaDatosComprobacion()) {
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

          var ceco = $('#ceco').val();
          var dias_habiles = $('#dias_habiles').val();
          var sitio = $('#sitio').val();
          var grantotal = $('#input-gran-total').val();

          Tbl = JSON.stringify(Tbl);

          $.ajax({
            type: 'POST',
            async: true,
            data: {
              registroNuevo: $('#text_cg_id').text().includes('#') ? $('#text_cg_id').text().replace('#', '') : '-',
              grantotal: grantotal,
              ceco: ceco,
              dias_habiles: dias_habiles,
              sitio: sitio,
              Tbl: Tbl,
              _token: token
            },
            dataType: 'json',
            url: 'comprobacion-gastos/guardar',
            success: function (data) {
              var respuesta = JSON.parse(JSON.stringify(data));
              $('#text_cg_id').text(' #' + respuesta.cg_id);
              $('#text_cg_estatus').text(respuesta.estatus);
              Swal.fire({
                title: 'Comprobación #' + respuesta.cg_id + ' guardada.',
                text: 'Recuerda, por politica tienes 5 dias para realizar tu comprobacion de gastos',
                icon: 'success',
                confirmButtonText: 'Aceptar'
              }).then(result => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
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
                  $('#btn-enviar').prop('disabled', false);
                  var form = document.createElement('form');
                  form.target = '_blank';
                  form.method = 'GET';
                  form.action = 'comprobacion-gastos/exportar/' + respuesta.cg_id;
                  form.style.display = 'none';

                  document.body.appendChild(form);

                  form.submit();

                  $.unblockUI();
                }
              });
              $.unblockUI();
            },
            error: function (xhr, ajaxOptions, thrownError) {
              $.unblockUI();
              var error = JSON.parse(xhr.responseText);
              bootbox.alert({
                size: 'large',
                title: "<h4><i class='fa fa-info-circle'></i> Alerta</h4>",
                message:
                  "<div class='alert alert-danger m-b-0'> Mensaje : " +
                  error['message'] +
                  '<br>' +
                  (error['exception'] != '' ? 'Código : ' + error['exception'] + '<br>' : '') +
                  (error['file'] != '' ? 'Clase : ' + error['file'] + '<br>' : '') +
                  (error['line'] != '' ? 'Línea : ' + error['line'] + '<br>' : '') +
                  '</div>'
              });
            }
          });
        }
      }
    }
  });

function getTbl() {
  var tabla = $('#tbl_cg').DataTable();
  var fila = $('#tbl_cg tbody tr').length;
  var datos_Tabla = tabla.rows().data();
  var tbl = new Array();
  //datos["RCP_Id"]
  if (datos_Tabla.length != 0) {
    let asistentes, fecha, descripcion, monto, iva;
    for (var i = 0; i < fila; i++) {
      asistentes = $('input#input-cantidad-asistentes', tabla.row(i).node()).val();
      fecha = $('input#input-fecha-gasto', tabla.row(i).node()).val();
      descripcion = $('textarea#input-descripcion', tabla.row(i).node()).val();
      monto = $('input#input-cantidad-monto', tabla.row(i).node()).val();
      iva = $('input#input-cantidad-iva', tabla.row(i).node()).val();
      xml_file = $('#input-xml' + i, tabla.row(i).node()).prop('files')[0];
      pdf_file = $('#input-pdf' + i, tabla.row(i).node()).prop('files')[0];
      concepto = $('select#input-concepto-' + i, tabla.row(i).node()).val();
      xml = '';
      pdf = '';

      if (xml_file) {
        xml = xml_file.name;
      } else {
        return (tbl['error'] = { error: 'Error: La linea #' + (i + 1) + ' no tiene XML cargado.' });
      }

      if (pdf_file) {
        pdf = pdf_file.name;
      } else {
        return (tbl['error'] = { error: 'Error: La linea #' + (i + 1) + ' no tiene PDF cargado.' });
      }

      if (concepto == '') {
        return (tbl['error'] = { error: 'Error: La linea #' + (i + 1) + ' no tiene capturado el concepto' });
      }

      var fechaActual = new Date().toISOString().split('T')[0]; // Obtiene la fecha actual en formato YYYY-MM-DD
      if (fecha > fechaActual) {
        return (tbl['error'] = {
          error: 'Error: La linea #' + (i + 1) + ' tiene una fecha inválida, no puede ser mayor al dia de hoy.'
        });
      }

      if (descripcion == '') {
        return (tbl['error'] = { error: 'Error: La linea #' + (i + 1) + ' no tiene capturada la descripción' });
      }

      if (monto == '') {
        return (tbl['error'] = { error: 'Error: La linea #' + (i + 1) + ' no tiene capturado el monto' });
      }

      if (iva == '') {
        return (tbl['error'] = { error: 'Error: La linea #' + (i + 1) + ' no tiene capturado el iva' });
      }

      if (esUnico(tbl, xml, pdf)) {
        tbl[i] = {
          // referenciaId: datos_Tabla[i]['RCP_Id'],
          xml: xml,
          pdf: pdf,
          asistentes: asistentes,
          concepto: concepto,
          fecha: fecha,
          descripcion: descripcion,
          monto: monto,
          iva: iva
        };
      } else {
        return (tbl['error'] = { error: 'Error: En linea #' + (i + 1) + ' el XML o PDF ya existe.' });
      }
    }
    return tbl;
  } else {
    return tbl;
  }
}
function esUnico(tbl, nuevoXml, nuevoPdf) {
  return !tbl.some(item => item.xml === nuevoXml || item.pdf === nuevoPdf);
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

function validaDatosRequeridos() {
  if ($('#ceco').val() == '') {
    bootbox.dialog({
      title: 'Validación',
      message: 'El centro de costo un dato obligatorio.',
      buttons: {
        success: {
          label: 'Ok',
          className: 'btn-success m-r-5 m-b-5'
        }
      }
    });
    return false;
  }
  if ($('#dias_habiles').val() == '') {
    bootbox.dialog({
      title: 'Validación',
      message: 'Dias hábiles es un dato obligatorio.',
      buttons: {
        success: {
          label: 'Ok',
          className: 'btn-success m-r-5 m-b-5'
        }
      }
    });
    return false;
  }
  if ($('#sitio').val() == '') {
    bootbox.dialog({
      title: 'Validación',
      message: 'La sitio es un dato obligatorio.',
      buttons: {
        success: {
          label: 'Ok',
          className: 'btn-success m-r-5 m-b-5'
        }
      }
    });
    return false;
  }
  /* if (getLengthTblVenta() == 0 && getLengthTblPromocion() == 0 && getLengthTblDegustacion() == 0) {
    bootbox.dialog({
      title: 'Factura',
      message: 'Debes ingresar detalles en la factura.',
      buttons: {
        success: {
          label: 'Ok',
          className: 'btn-success m-r-5 m-b-5'
        }
      }
    });
    return false;
  }*/

  return true;
}
function validaDatosComprobacion() {
  return true;
  if ($('#factura-nueva #cboCliente').val() == '') {
    bootbox.dialog({
      title: 'Factura',
      message: 'El cliente es un dato obligatorio.',
      buttons: {
        success: {
          label: 'Ok',
          className: 'btn-success m-r-5 m-b-5'
        }
      }
    });
    return false;
  }
  if ($('#input-fecha-factura').val() == '') {
    bootbox.dialog({
      title: 'Factura',
      message: 'La fecha es un dato obligatorio.',
      buttons: {
        success: {
          label: 'Ok',
          className: 'btn-success m-r-5 m-b-5'
        }
      }
    });
    return false;
  }
  if ($('#factura-nueva #cboMoneda').val() == '') {
    bootbox.dialog({
      title: 'Factura',
      message: 'La moneda es un dato obligatorio.',
      buttons: {
        success: {
          label: 'Ok',
          className: 'btn-success m-r-5 m-b-5'
        }
      }
    });
    return false;
  }
  if (getLengthTblVenta() == 0 && getLengthTblPromocion() == 0 && getLengthTblDegustacion() == 0) {
    bootbox.dialog({
      title: 'Factura',
      message: 'Debes ingresar detalles en la factura.',
      buttons: {
        success: {
          label: 'Ok',
          className: 'btn-success m-r-5 m-b-5'
        }
      }
    });
    return false;
  }

  return true;
}
