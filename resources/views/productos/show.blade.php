@extends('layouts.contentNavbarLayoutOnly')
@section('title', 'Productos de la Opción: ' . $opcion->OPC_ValorOpcion)

@section('content')
<div id="modal-producto" class="modal fade" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Formulario de Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modal-producto-body">
        <!-- contenido AJAX -->
      </div>
    </div>
  </div>
</div>

<div class="">
  <div class="card-header d-flex">
    <h4>Productos de: <strong>{{ $opcion->OPC_ValorOpcion }}</strong></h4>
    <!-- Botón para regresar a la lista de opciones alinear a la derecha -->
    <div class="align-self-end ms-auto">
      <a href="{{ route('opciones.index', $opcion->OPC_OpcionId) }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Regresar
      </a>
      <button class="btn btn-primary btn-nuevo-producto"
        data-url="{{ route('productos.create', ['opcion_id' => $opcion->OPC_OpcionId]) }}">
        Nuevo Producto
      </button>
    </div>

  </div>
  <div class="card-body">
    <blockquote class="blockquote">
      <p>Cuando el Usuario seleccione esta opción se agregaran los siguientes productos a la cotización.</p>
    </blockquote>
    <table class="table table-bordered" id="tabla_productos">
      <thead>
        <tr>
          <th>Acciones</th>
          <th>ID</th>
          <th>Producto</th>
          <th>Ancho</th>
          <th>Cantidad</th>
          <th>Precio Unitario</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@section('page-script')
<script>
  $(document).ready(function () {
  const opcionId = "{{ $opcion->OPC_OpcionId }}";

  const tabla = $('#tabla_productos').DataTable({
    processing: true,
    ajax: {
      url: "{{ route('productos.ajax', $opcion->OPC_OpcionId) }}",
      type: 'POST',
      data: { _token: '{{ csrf_token() }}' }
    },
    columns: [
      { data: 'acciones', orderable: false, searchable: false },
      { data: 'PCNT_id' },
      { data: 'PCNT_PROD_nombre' },
      { data: 'PCNT_base_ancho' },
      { data: 'PCNT_base_cantidad' },
      { data: 'PCNT_precio_unitario' }
    ],
    language: {
      url: assetapp + '/plugins/DataTables/json/es-MX.json'
    },
    order: [[1, 'asc']],
    dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100]
  });

  // si cambia el valor de selectpicker PCNT_PROD_id movido dentro del form
  // $('select[name="PCNT_PROD_id"]').on('changed.bs.select', function () {
  //   const nombre = $(this).find(':selected').data('nombre');
  //   $('#PCNT_precio_unitario').val(nombre);
  // });

  // Botón nuevo producto
  $(document).on('click', '.btn-nuevo-producto', function (e) {
    e.preventDefault();
    $.blockUI({
        css: {  
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',

            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
        }
    });
    
    const url = $(this).data('url');
    $.get(url, function (html) {
      $('#modal-producto-body').html(html);
      $('#modal-producto').modal('show');
      $('#modal-producto').on('shown.bs.modal', function () {
        $.unblockUI();
      });
    }).fail(function () {
      $.unblockUI();
      Swal.fire('Error', 'No se pudo cargar el formulario', 'error');
    });
  });

  // Botón editar
  $(document).on('click', '.btn-editar-producto', function (e) {
    e.preventDefault();
    $.blockUI({
        css: {  
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',

            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
        }
    });
    const id = $(this).data('id');
    const url = "{{ url('productos') }}/" + id + "/edit";
    $.get(url, function (html) {
      $('#modal-producto-body').html(html);
      $('#modal-producto').modal('show');
      $('#modal-producto').on('shown.bs.modal', function () {
        $.unblockUI();
      });
    }).fail(function () {
      $.unblockUI();
      Swal.fire('Error', 'No se pudo cargar el formulario', 'error');
    });
  });

  // Enviar formulario
  $(document).on('submit', '#form-producto', function (e) {
    e.preventDefault();
    const form = $(this);
    const url = form.attr('action');

    $.ajax({
      url: url,
      method: 'POST',
      data: form.serialize(),
      beforeSend: function () {
        $.blockUI({
          css: {  
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',

            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
          }
        });
      },
      complete: function () {
        $.unblockUI();
      },
      success: function () {
        $('#modal-producto').modal('hide');
        tabla.ajax.reload(null, false);
        Swal.fire('Éxito', 'Producto guardado correctamente', 'success');
      },
      error: function (xhr) {
        let errors = xhr.responseJSON.errors;
        let messages = Object.values(errors).map(e => `<li>${e[0]}</li>`).join('');
        Swal.fire({ icon: 'error', title: 'Errores de validación', html: `<ul>${messages}</ul>` });
      }
    });
  });

  // Botón eliminar
  $(document).on('click', '.btn-eliminar-producto', function () {
    const id = $(this).data('id');
    Swal.fire({
      title: '¿Eliminar producto?',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar'
    }).then(result => {
      if (result.isConfirmed) {
        $.post("{{ url('productos') }}/" + id, {
          _method: 'DELETE',
          _token: '{{ csrf_token() }}'
        }, function () {
          tabla.ajax.reload(null, false);
          Swal.fire('Eliminado', 'Producto eliminado correctamente', 'success');
        });
      }
    });
  });
});
</script>
@endsection