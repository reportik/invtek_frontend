@extends('layouts.contentNavbarLayoutOnly' )
@section('title', 'Opciones del Cotizador')

@section('content')
<div id="modal-opcion" class="modal fade" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Formulario de Opción</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modal-opcion-body">
        <!-- contenido AJAX -->
      </div>
    </div>
  </div>
</div>
<div class="">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4>Opciones</h4>
    <a href="" class="btn btn-primary btn-nueva-opcion">Nueva Opción</a>
  </div>
  <div class="card-body">
    <table class="table table-bordered" id="tabla_opciones">
      <thead>
        <tr>
          <th>Acciones</th>
          <th>ID</th>
          <th>Paso</th>
          <th>Padre</th>
          <th>Valor</th>
          <th>Activo</th>
          <th>Imagen</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@section('page-script')
<script>
  $(document).ready(function () {
  $('#tabla_opciones').DataTable({
    processing: true,
    
    ajax: {
      url: "{{ route('opciones.ajax') }}",
      type: 'POST',
      data: { _token: '{{ csrf_token() }}' }
    },
    columns: [
      { data: 'acciones', orderable: false, searchable: false },
      { data: 'OPC_OpcionId'},
      { data: 'paso' },
      { data: 'padre' },
      { data: 'OPC_ValorOpcion' },
      { data: 'OPC_Activo' },
      { data: 'OPC_Imagen',
      render: function (data) {
      if (data) {
      return '<img src="{{ asset('images/cotizador') }}/' + data + '" alt="Imagen" style="width: 50px; height: 50px;">';
      } else {
      return 'Sin imagen';
      }
      }
       }
    ],

    language: {
    url: assetapp + '/plugins/DataTables/json/es-MX.json'
    },
    order: [[2, 'asc'], [1, 'asc']],
    //dom
    dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6'f>>" +  // length + search
       "<'row'<'col-sm-12'tr>>" +                  // table
       "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",   // info + pagination

    //lengthMenu: [10, 25, 50, 100],
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    //buttons
    buttons: [
      {
        extend: 'excelHtml5',
        text: '<i class="fa-solid fa-file-excel"></i> Excel',
        className: 'btn btn-success',
        titleAttr: 'Exportar a Excel',
        exportOptions: {
          columns: [1, 2, 3, 4, 5]
        }
      }
    ]
  });
});
$(function () {
  // Botón nueva opción
  $(document).on('click', '.btn-nueva-opcion', function (e) {
    e.preventDefault();
    $.get("{{ route('opciones.create') }}", function (html) {
      $('#modal-opcion-body').html(html);
      $('#modal-opcion').modal('show');
    });
  });

  // Botón editar
  $(document).on('click', '.btn-editar-opcion', function (e) {
    e.preventDefault();
    let url = $(this).attr('href');
    $.get(url, function (html) {
      $('#modal-opcion-body').html(html);
      $('#modal-opcion').modal('show');
    });
  });

  // Enviar formulario con AJAX
  $(document).on('submit', '#form-opcion', function (e) {
    e.preventDefault();
    let form = $(this);
    let url = form.attr('action');

    let formData = new FormData(form[0]); // Cambia esto
    
    $.ajax({
    url: url,
    method: 'POST',
    data: formData, // Cambia esto
    processData: false, // Añade esto
    contentType: false, // Añade esto
    cache: false,
    
      success: function (res) {
        $('#modal-opcion').modal('hide');
        $('#tabla_opciones').DataTable().ajax.reload(null, false);
        Swal.fire('Éxito', 'Opción guardada correctamente', 'success');
      },
      error: function (xhr) {
        let errors = xhr.responseJSON.errors;
        let messages = Object.values(errors).map(e => `<li>${e[0]}</li>`).join('');
        Swal.fire({ icon: 'error', title: 'Errores de validación', html: `<ul>${messages}</ul>` });
      }
    });
  });
});
</script>
@endsection