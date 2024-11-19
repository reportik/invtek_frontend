var TBL = '';

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
  InicializaTablas();
};

function InicializaTablas() {
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
      { data: 'FECHA_GASTO' },
      { data: 'DESCRIPCION' },
      { data: 'MONTO' },
      { data: 'IVA' }
    ],
    columnDefs: [
      {
        targets: [0],
        orderable: false,
        render: function (row) {
          var tblVenta = document.getElementById('tbl_cg').getElementsByTagName('tbody')[0];
          var index = tblVenta.rows.length + 1;

          return index;
        }
      },
      {
        targets: [1],
        searchable: false,
        orderable: false,
        className: 'dt-body-center',
        render: function (data, type, row) {
          return '<button type="button" class="btn btn-danger" id="btnEliminar"> <span class="bi bi-trash"></span> </button>';
        }
      },
      {
        targets: [2],
        searchable: false,
        orderable: false,
        className: 'dt-body-center',
        render: function (data, type, row, meta) {
          return (
            '<input data-partida=' +
            meta.row +
            ' id="input-xml' +
            meta.row +
            '" name="input-b2" type="file" class="file input-xml" data-language="es" data-show-caption="false" accept="application/xml" data-show-cancel="false" data-show-remove="false" data-show-preview="false">'
          );
        }
      },
      {
        targets: [3],
        searchable: false,
        orderable: false,
        className: 'dt-body-center',
        render: function (data, type, row, meta) {
          return (
            '<input data-partida=' +
            meta.row +
            ' id="input-pdf' +
            meta.row +
            '" name="input-b2" type="file" class="file input-pdf" data-language="es" data-show-caption="false" accept="application/pdf" data-show-cancel="false" data-show-remove="false" data-show-preview="false">'
          );
        }
      },
      {
        targets: [4],
        orderable: false,
        render: function (data, type, row) {
          return (
            '<input id="input-cantidad-asistentes" style="width: 70px" class="form-control input-sm cantidad" type="number" min="1" value="' +
            parseInt(row['ASISTENTES']) +
            '">'
          );
        }
      },
      {
        targets: [5],
        render: function (data, type, row) {
          var texto = row[5];
          if (texto === undefined) {
            texto = new Date().toISOString().split('T')[0];
          }
          return (
            '<input id="input-fecha-gasto" style="text-align: right;" class="form-control input-sm" type="date" value="' +
            texto +
            '">'
          );
        }
      },
      {
        targets: [6],
        render: function (data, type, row) {
          var texto = row[6];
          if (texto === undefined) {
            texto = '';
          }
          return (
            '<textarea rows="3" id="input-descripcion" style="text-align: left;" class="form-control input-sm" type="text" value="' +
            texto +
            '"></textarea>'
          );
        }
      },
      {
        targets: [7],
        render: function (data, type, row) {
          return (
            '<input id="input-cantidad-monto" style="text-align: right;" onchange="campostexto()" class="input-total form-control input-sm" type="number" value="' +
            parseFloat(row[7]).toFixed(2) +
            '">'
          );
        }
      },
      {
        targets: [8],
        render: function (data, type, row) {
          return (
            '<input id="input-cantidad-iva" style="text-align: right;" onchange="campostexto()" class="input-total form-control input-sm" type="number" value="' +
            parseFloat(row[8]).toFixed(2) +
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
        .column(8)
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
      $(api.column(8).footer()).html(
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
        .column(7)
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
      $(api.column(7).footer()).html(
        //'$'+pageTotal +' ( $'+ total +' total)'
        '$ ' + number_format(total_monto, 2, '.', ',')
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
$('#btn-guardar')
  .off()
  .on('click', function (e) {
    console.log(getTbl());
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

      tbl[i] = {
        // referenciaId: datos_Tabla[i]['RCP_Id'],
        asistentes: asistentes,
        fecha: fecha,
        descripcion: descripcion,
        monto: monto,
        iva: iva
      };
    }
    return tbl;
  } else {
    return tbl;
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
