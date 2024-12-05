@extends('layouts/contentNavbarLayout')

@section('title', 'Comprobación Gastos')

@section('page-script')
<script>
  concepto_items = `{!! $concepto_items !!}`;

</script>
<script src="{{ URL::asset('js/comprobacion-gastos.js?v=' . $version) }}"></script>
<script>
  // Función para agregar una nueva fila
$('#addRow').on('click', function () {

  addrow();
});
function addrow() {
  //console.log(new Date().toISOString().split('T')[0]);

  TBL.row.add({
  ID:'',
  BTN_ELIMINAR: '',
  BTN_XML: '',
  BTN_PDF: '',
  ASISTENTES: '1',
  CONCEPTO: '',
  FECHA_GASTO: '',
  DESCRIPCION: '',
  MONTO: '',
  IVA: ''
  }).draw(true);
  var id = TBL.rows().count() - 1;
  //$('#input-concepto-'+id).html(`{!! $concepto_items !!}`);
  //$('#input-concepto-'+id).selectpicker();

  $('#input-pdf'+ id)
  .fileinput({
  showUpload: false,
  language: 'es',
  dropZoneEnabled: false,
  maxFileCount: 1,
  inputGroupClass: 'input-group-sm',
  browseLabel: '',
  showUploadStats: true,
  browseIcon: '<i class="bi-file-pdf-fill"></i>',
  browseClass: 'btn btn-danger',
  })
  .on('fileloaded', function (event, file, previewId, index, reader) {
  event.preventDefault()
  var $input = $(this);
  var formData = new FormData();
  formData.append('file', file);
  formData.append('num_partida', $(this).attr('data-partida'));
  formData.append('btn_id', $(this).attr('id'));

  formData.append('_token', "{{ csrf_token() }}");
  $.ajax({ url: "{{ route('upload-pdf-cg') }}",
  type: 'POST',
  data: formData,
  processData: false,
  contentType: false,
  success: function(response) {
  console.log(response.btn_id)
  Swal.fire({
  title: 'Archivo cargado!',
  text: response.message,
  icon: 'success',
  confirmButtonText: 'Aceptar'
  })

  }, error: function(xhr, status, error) {

  Swal.fire({
  title: 'Error!',
  text: JSON.parse(xhr.responseText).message,
  icon: 'error',
  confirmButtonText: 'Aceptar'
  })
  console.log('Error en la subida del archivo: ', xhr.responseText); } });
  });

  $('#input-xml'+ id)
  .fileinput({
  showUpload: false,
  language: 'es',
  dropZoneEnabled: false,
  maxFileCount: 1,
  inputGroupClass: 'input-group-sm',
  browseLabel: '',
  showUploadStats: true,
  browseIcon: '<i class="bi-filetype-xml"></i>',
  browseClass: 'btn btn-primary',
  })
  .on('fileloaded', function (event, file, previewId, index, reader) {
  event.preventDefault()
  var $input = $(this);
  var formData = new FormData();
  formData.append('file', file);
  formData.append('num_partida', $(this).attr('data-partida'));
  formData.append('btn_id', $(this).attr('id'));

  formData.append('_token', "{{ csrf_token() }}");
  $.ajax({ url: "{{ route('upload-xml-cg') }}",
  type: 'POST',
  data: formData,
  processData: false,
  contentType: false,
  success: function(response) {

  Swal.fire({
  title: 'Archivo cargado!',
  text: response.message,
  icon: 'success',
  confirmButtonText: 'Aceptar'
  }).then((result) => {
  if (result.isConfirmed) {
  const data = response;

  // Acceder al campo descripcion_concatenada
  //const descripcion = data[0].descripcion_concatenada;
  // Obtener el índice de la fila
  const partida = $input.attr('data-partida');
  // Seleccionar la fila correspondiente en el DataTable
  var row = TBL.row(partida).node();

  // Actualizar los inputs de la fila correspondiente
  $(row).find('#input-descripcion').val(data[0].descripcion_concatenada);
  $(row).find('#input-cantidad-monto').val(parseFloat(data[0].importe_total).toFixed(2));
  $(row).find('#input-cantidad-iva').val(parseFloat(data[0].impuestos_total).toFixed(2));
  campostexto()
}
  });
  }, error: function(xhr, status, error) {
  Swal.fire({
  title: 'Error!',
  text: JSON.parse(xhr.responseText).message,
  icon: 'error',
  confirmButtonText: 'Aceptar'
  })
  console.log('Error en la subida del archivo: ', xhr.responseText); } });
  });
}
</script>
@endsection


@section('page-style')
<style>
  .file-input {
    width: 50px;
  }
</style>
@endsection

@section('content')
<div class="row mb-12 g-6">
  <div class="card">
    <div class="card-body">
      <form>
        <div class="row">
          <h5 class="card-title">Comprobación de Gastos <b id="text_cg_id">@isset($cg_id)
              # {{$cg_id}}
              @endisset</b>
            <b style="display:none" id="text_cg_estatus"></b>
          </h5>
          <input type="number" style="display: none" id="input-gran-total">
          <div class="col-4">
            <div class="form-group">
              <label for="usuario">Usuario</label>
              <input type="text" class="form-control" id="usuario" name="usuario" readonly
                value="{{ Auth::user()->name }}">
            </div>
          </div>

          <div class="col-3">
            <label for="ceco">Centro Costo (ceco)</label>
            <select data-live-search="true" class="form-control selectpicker control-usuario" id="ceco" name="ceco">
              <option value="">Selecciona una opción</option>
              @foreach ($cecos as $item)
              <option value="{{ $item->CEN_ceco }}">{{ $item->CEN_descripcion }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-2">
            <div class="form-group">
              <label for="dias_habiles">Días Hábiles</label>
              <input type="number" class="form-control control-usuario" id="dias_habiles" name="dias_habiles" value="1">
            </div>
          </div>
          <div class="col-3">
            <div class="form-group">
              <label for="sitio">Sitio</label>
              <select class="form-control selectpicker control-usuario" id="sitio" name="sitio">
                <option value="Toluca">Toluca</option>
                <option value="Quad">Quad</option>
              </select>
            </div>
          </div>
        </div>
      </form>

      <button type="button" class="btn btn-primary mt-3 control-usuario" id="addRow">Agregar un gasto</button>
      <button type="button" class="btn btn-primary mt-3 control-usuario" id="btn-guardar"><i
          class="bi-floppy"></i>&nbsp;
        Guardar</button>
      <button type="button" class="btn btn-success mt-3 control-usuario" id="btn-enviar"><i class="bi-send"></i>&nbsp;
        Enviar</button>
      <table class="table mt-3" id="tbl_cg">
        <thead>
          <tr>
            <th>Partida</th>
            <th style="width:5%">Quitar</th>
            <th style="width:5%">XML</th>
            <th style="width:5%">PDF</th>
            <th style="width:9%">Asistentes</th>
            <th style="width:11%">Concepto</th>
            <th style="width:10%">Fecha de Gasto</th>
            <th>Descripción</th>
            <th>Monto</th>
            <th>Iva</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <!-- Filas dinámicas -->
        </tbody>
        <tfoot>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th style="text-align: right"></th>
          <th></th>
          <th style="text-align: right"></th>
          <th style="text-align: right"></th>
        </tfoot>
      </table>
    </div>
  </div>
</div>

@endsection