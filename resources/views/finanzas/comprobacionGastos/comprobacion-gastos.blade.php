@extends('layouts/contentNavbarLayout')

@section('title', 'Comprobación Gastos')

@section('page-script')

<script src="{{ URL::asset('js/comprobacion-gastos.js?v=' . $version) }}"></script>
<script>
  // Función para agregar una nueva fila
$('#addRow').on('click', function () {
  TBL.row.add({}).draw(false);
  var id = TBL.rows().count() - 1;

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
      formData.append('param1', 'ho');
      formData.append('param2', $(this).attr('id'));
      formData.append('_token',  "{{ csrf_token() }}");
      $.ajax({ url: "{{ route('upload') }}",
      type: 'POST',
      data: formData,
      processData: false,
       contentType: false,
       success: function(response) {
        console.log(response.btn_id)
        // $(this).closest('.file-input').find('.btn-file').removeClass('btn-primary').addClass('btn-success');
        // $('#'+response.btn_id).removeClass('btn-primary').addClass('btn-success');
        console.log('Archivo subido con éxito: ', response);
        alert('Archivo subido con éxito'+response.btn_id)
      }, error: function(xhr, status, error) {
         console.log('Error en la subida del archivo: ', xhr.responseText); } });
    })
    .on('fileuploaded', function (event, data, previewId, index) {
      console.log('Archivo subido: ', data);
    });

  /*  $('.file-xml-input').fileinput({
    showUpload: false,
    language: 'es',
    dropZoneEnabled: false,
    maxFileCount: 1,
    inputGroupClass: 'input-group-sm',
    browseLabel: '',
    browseIcon: '<i class="bi-filetype-xml"></i>'
  }); */
});
</script>
@endsection


@section('page-style')
<style>
  .file-input{
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
                        <div class="col-4">
                            <div class="form-group">
                                <label for="usuario">Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" readonly
                                    value="{{ Auth::user()->name }}">
                            </div>
                        </div>
                        <div class="col-3">
                            <label for="ceco">Ceco</label>
                            <select data-live-search="true" class="form-control selectpicker" id="ceco" name="ceco">
                                <option value="">Selecciona una opción</option>
                                @foreach ($cecos as $item)
                                    <option value="{{ $item->CEN_ceco }}">{{ $item->CEN_descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label for="dias_habiles">Días Hábiles</label>
                                <input type="number" class="form-control" id="dias_habiles" name="dias_habiles"
                                    value="1">
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for="sitio">Sitio</label>
                                <select class="form-control selectpicker" id="sitio" name="sitio">
                                    <option value="Toluca">Toluca</option>
                                    <option value="Quad">Quad</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
                <button type="button" class="btn btn-primary mt-3" id="addRow">Agregar Gasto</button>
                <table class="table mt-3" id="tbl_cg">
                    <thead>
                        <tr>
                            <th>Partida</th>
                            <th style="width:5%">Quitar</th>
                            <th style="width:5%">XML</th>
                            <th style="width:5%">PDF</th>
                            <th style="width:9%">Asistentes</th>
                            <th style="width:10%">Fecha de Gasto</th>
                            <th>Descripción</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Filas dinámicas -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
