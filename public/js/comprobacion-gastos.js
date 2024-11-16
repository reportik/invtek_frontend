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
    // language: {
    //   url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json'
    // },
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
          return (
            '<input id="input-fecha-gasto" style="text-align: right;" class="form-control input-sm" type="date" value="' +
            row[5] +
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
            '<input id="input-cantidad-monto" style="text-align: right;" class="form-control input-sm" type="number" value="' +
            parseFloat(row[7]).toFixed(2) +
            '">'
          );
        }
      },
      {
        targets: [8],
        render: function (data, type, row) {
          return (
            '<input id="input-cantidad-iva" style="text-align: right;" class="form-control input-sm" type="number" value="' +
            parseFloat(row[8]).toFixed(2) +
            '">'
          );
        }
      }
    ]
  });
}

// Función para eliminar una fila
function deleteRow(button) {
  let row = $(button).closest('tr');
  $('#tert($(this).val());bl_cg').DataTable().row(row).remove().draw();
}
