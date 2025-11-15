@extends('layouts.contentNavbarLayoutOnly' )
@section('title', 'Opciones del Cotizador')

@section('content')
<div id="modal-opcion" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-lg">
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
    <!-- filtros selectpicker para mi tabla  -->
    <div class="row mb-3">
      <div class="col-md-12">
        @if(isset($rutaSelectores) && $rutaSelectores)
        <div class="alert alert-info mb-2">
          <strong>{{ $rutaSelectores }}</strong>
        </div>
        @endif

        {{-- <label for="filtro_paso">Filtrar por Selector:</label>
        <select class="form-control selectpicker" id="filtro_paso" data-live-search="true" data-size="5">
          <option value="-1">Todos</option>
          @foreach ($pasos as $paso => $nombre)
          <!-- $pasos = PasoCotizador::where('PAS_Eliminado', 0)->pluck('PAS_Nombre', 'PAS_PasoId'); -->
          <option value="{{ $paso }}" {{ $paso==$id ? 'selected' : '' }}>{{ $nombre }}</option>
          @endforeach
        </select> --}}
      </div>
    </div>
    <table class="table table-bordered" id="tabla_opciones">
      <thead>
        <tr>
          <th>Acciones</th>
          <th>Selector Padre</th>
          <th>Valor Padre</th>
          <th>Selector</th>
          <th>Valor</th>
          <th>Activo</th>
          <th>Imagen</th>
          <th>Selector siguiente</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@section('page-script')
<script>
  $('#tabla_opciones').DataTable({
    processing: true,
    
    ajax: {
      url: "{{ route('opciones.ruta.ajax') }}",
      type: 'POST',
      data: function(d) {
        return {
          _token: '{{ csrf_token() }}',
          selector: {{ $id }} //$('#filtro_paso').val() //se comento el selector mas arriba
        };
      },
    },
    columns: [
      { data: 'acciones', orderable: false, searchable: false },
      { data: 'selector_padre', visible: false},
      { data: 'valor_padre', visible: false},
      { data: 'selector' },
      { data: 'valor' },
      { data: 'activo' },
      { data: 'imagen',
        render: function (data, type, row) {
          if (data) {
            if (row.selector == 'Telas') {
              return '<img src="{{ asset('images/telas') }}/' + data + '" alt="Imagen" style="width: 50px; height: 50px;">';
            } else {
              return '<img src="{{ asset('images/cotizador') }}/' + data + '" alt="Imagen" style="width: 50px; height: 50px;">';
            }
          } else {
            return 'Sin imagen';
          }
        }
      },
      // Columna Selector siguiente
      {
        data: 'selector_siguiente',
        render: function(data, type, row) {
          return type === 'display' ? data : '';
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

  
  $('#filtro_paso').on('change', function() {
    
    console.log($(this).val()); //imprime el valor seleccionado correctamente
    
    $('#tabla_opciones').DataTable().ajax.reload(); // en el payload siempre manda cero ¿porque?
  });



 // Botón nueva opción
 $(document).on('click', '.btn-nueva-opcion', function (e) {
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
   $.get("{{ route('opciones.create') }}", function (html) {
     $('#modal-opcion-body').html(html);
     //$('#selector').val($('#filtro_paso').val()).selectpicker('refresh'); //se comento el selector mas arriba
     $('#modal-opcion #selector').val({{$id}}).selectpicker('refresh');
     $('#modal-opcion').modal('show');
     $.unblockUI();
   }).fail(function() {
     $.unblockUI();
     alert('Error al cargar el formulario');
   });
 });
 
 // Botón editar
 $(document).on('click', '.btn-editar-opcion', function (e) {
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
   let url = $(this).attr('href');
   $.get(url, function (html) {
     $('#modal-opcion-body').html(html);
     //$('#selector').val($('#filtro_paso').val()).selectpicker('refresh'); //se comento el selector mas arriba
     $('#modal-opcion').modal('show');
     $.unblockUI();
   }).fail(function() {
     $.unblockUI();
     alert('Error al cargar el formulario');
   });
 });

  // Enviar formulario con AJAX
  $(document).on('submit', '#form-opcion', function (e) {
    e.preventDefault();
    $('[disabled]').removeAttr('disabled');
    let form = $(this);
    form.append($("<input>").attr("type", "hidden").attr("name", "id").val({{$id}}));
    let url = form.attr('action');
    //agregar el $id al formulario
    let formData = new FormData(form[0]); // Cambia esto
    //si no hay avance_temporal, no hay sesion, no se envia
    let avance = "{{ session()->get('avance_temporal') }}";
    if (!avance) {
      Swal.fire({
      title: 'Error!',
      text: 'No se puede guardar la opción sin un avance temporal. Quiza la sesion ha expirado.',
      icon: 'error',
      confirmButtonText: 'OK'
      });
    
    } else {
    
    $.ajax({
    url: url,
    method: 'POST',
    beforeSend: function() {
      //bloquea el modal para que no se pueda interactuar con el formulario bloqueo sobre el modal
      $.blockUI({
        css: {  
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',

            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff',
            zIndex: 20000,
            baseZ: 20000,
        }
    });
    },
    complete: function() {
      $.unblockUI();
    },  
    data: formData, // Cambia esto
    processData: false, // Añade esto
    contentType: false, // Añade esto
    cache: false,
    
      success: function (res) {
        $('#modal-opcion').modal('hide');
        $('#tabla_opciones').DataTable().ajax.reload(null, false);
        Swal.fire('Éxito', res.success, 'success');
        
      },
      error: function (xhr) {
        Swal.fire('Error', xhr.responseJSON.error, 'error');
      }
    });
  }
});

// Evento para crear opción en blanco con selector siguiente
$(document).on('change', '.selector-siguiente', function() {
    var $select = $(this);
    var pasoId = $select.val();
    var opcionId = $select.data('opcion-id');
    
    if (pasoId) {
        Swal.fire({
            title: '¿Cambiar selector siguiente?',
            text: "Esto eliminará las opciones siguientes con la misma ruta y creará una nueva opción.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
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
                
                $.ajax({
                    url: routeapp + '/opciones/crear-blanco',
                    method: 'POST',
                    data: {
                        selector: {{ $id }},
                        opcion_id: opcionId,
                        paso_id: pasoId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(resp) {
                        $.unblockUI();
                        $('#tabla_opciones').DataTable().ajax.reload();
                        Swal.fire('¡Opción actualizada!', 'Opcion ID: ' + resp.opcion_id + '. Las opciones siguientes han sido eliminadas.', 'success');
                    },
                    error: function(xhr) {
                        $.unblockUI();
                        var errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Error al crear la opción';
                        Swal.fire('Error', errorMsg, 'error');
                        // Recargar la tabla para restaurar el valor anterior
                        $('#tabla_opciones').DataTable().ajax.reload();
                    }
                });
            } else {
                // Si cancela, recargar la tabla para restaurar el valor anterior del select
                $('#tabla_opciones').DataTable().ajax.reload();
            }
        });
    }
});
// Refrescar selectpicker tras cada draw
$('#tabla_opciones').on('draw.dt', function() {
  $('.selectpicker').selectpicker('refresh');
});

// Delegación de eventos para el formulario de eliminación
$(document).on('submit', '.form-eliminar', function(e) {
    e.preventDefault();
    var form = $(this);
    var url = form.attr('action');

    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ¡eliminar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                method: 'POST', // Usar POST y el campo _method='DELETE'
                data: form.serialize(),
                success: function(res) {
                    $('#tabla_opciones').DataTable().ajax.reload(null, false);
                    Swal.fire('¡Eliminado!', res.success, 'success');
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON.error, 'error');
                }
            });
        }
    });
});
</script>
@endsection