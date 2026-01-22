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
  // Manejar errores de DataTables silenciosamente (sin alert)
  $.fn.dataTable.ext.errMode = 'none';
  
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
      error: function(xhr, error, thrown) {
        // Silenciar el error - no hacer nada, dejar la tabla vacía
        console.warn('DataTables Ajax error:', error, thrown);
      }
    },
    columns: [
      { data: 'acciones', orderable: false, searchable: false },
      { data: 'selector_padre', visible: false},
      { data: 'valor_padre', visible: false},
      { data: 'selector' },
      { data: 'valor' },
      { 
        data: 'activo',
        render: function(data, type, row) {
          return type === 'display' ? data : (data ? 'Sí' : 'No');
        }
      },
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

// Evento para editar fórmula de tela en opciones de Resumen
$(document).on('click', '.btn-editar-formula', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    var $btn = $(this);
    var opcionResumenId = $btn.data('opcion-resumen-id');
    var formulaActual = $btn.attr('data-formula') || '';
    
    Swal.fire({
        title: 'Editar Fórmula de Cálculo de Tela',
        html: `
            <div class="text-start">
                <p class="mb-3"><strong>Fórmula SQL para calcular la cantidad de tela</strong></p>
                <p class="text-muted small mb-2">Variables disponibles: @ancho, @alto, @anchoTela, @numeroHojas</p>
                <textarea id="formula-tela-edit" class="form-control font-monospace" rows="6" 
                    style="resize: vertical; font-size: 13px;"
                    placeholder="Ejemplo: SELECT CEILING((@alto + 0.45) * CEILING(@ancho * 2 / @anchoTela)) AS resultado">${formulaActual}</textarea>
                <small class="text-muted">Dejar vacío para usar la fórmula por defecto: CEILING(CEILING((@ancho * 2) / @anchoTela) * (@alto + 0.45))</small>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Guardar cambios',
        cancelButtonText: 'Cancelar',
        width: '600px',
        preConfirm: () => {
            return {
                formula: document.getElementById('formula-tela-edit').value
            };
        }
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
                url: routeapp + '/opciones/actualizar-formula',
                method: 'POST',
                data: {
                    opcion_id: opcionResumenId,
                    formula_tela: result.value.formula,
                    _token: '{{ csrf_token() }}'
                },
                success: function(resp) {
                    $.unblockUI();
                    $('#tabla_opciones').DataTable().ajax.reload();
                    Swal.fire('¡Fórmula actualizada!', 'La fórmula de cálculo de tela ha sido guardada.', 'success');
                },
                error: function(xhr) {
                    $.unblockUI();
                    var errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Error al actualizar la fórmula';
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    });
});

// Evento para editar descripción personalizada en opciones de Resumen
$(document).on('click', '.btn-editar-descripcion', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    var $btn = $(this);
    var opcionResumenId = $btn.data('opcion-resumen-id');
    var descripcionActual = $btn.attr('data-descripcion') || '';
    
    // Diccionario de variables disponibles
    var variablesHtml = `
        <div class="card bg-light mb-3">
            <div class="card-header py-2"><strong>Variables disponibles</strong> <small class="text-muted">(clic para copiar)</small></div>
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-4">
                          <p class="mb-1"><strong>Medidas:</strong></p>
                          <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ inputAncho }}</code>
                          <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ inputAlto }}</code>
                          <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ inputLadoA }}</code>
                          <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ inputLadoB }}</code>
                          <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ inputRadio }}</code>
                          <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ numeroHojas }}</code>
                          <p class="mb-1 mt-2"><strong>Proyecto:</strong></p>
                          <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ nombre_proyecto }}</code>
                          <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ nombre_articulo }}</code>
                          <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ material_descripcion }}</code>
                      </div>
                    <div class="col-4">
                        <p class="mb-1"><strong>Opciones:</strong></p>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Área de instalación }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Tipo de producto }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Subproducto }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Confección }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Estilo de confección / Fullness }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Instalación Riel }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Hojas }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Dirección de apertura }}</code>
                       
                    </div>
                    <div class="col-4">
                         <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Tipo de material }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Sistema de apertura }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Superficie de instalación }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Modelo del Riel }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Material de riel }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Color de riel }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Accesorio de apertura }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Material accesorio }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Modelo accesorio }}</code>
                        <code class="var-copy d-block mb-1" style="cursor:pointer">@{{ Largo accesorio }}</code>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Obtener imagen actual si existe
    var imagenActual = $btn.attr('data-imagen') || '';
    var imagenPreview = imagenActual ? `<img src="${assetapp}/images/cotizador/${imagenActual}" class="img-thumbnail mb-2" style="max-height: 100px;">` : '';
    
    Swal.fire({
        title: 'Editar Descripción Personalizada',
        html: `
            <div class="text-start">
                ${variablesHtml}
                <div class="row">
                    <div class="col-md-8">
                        <p class="mb-2"><strong>Texto de descripción:</strong></p>
                        <textarea id="descripcion-ruta-edit" class="form-control" rows="5" 
                            style="resize: vertical;"
                            placeholder="Ejemplo: @{{ Tipo de producto }} con confección @{{ Confección }}, medidas @{{ inputAncho }}m x @{{ inputAlto }}m, tela: @{{ material_descripcion }}">${descripcionActual}</textarea>
                        <small class="text-muted">Dejar vacío para usar la descripción automática.</small>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-2"><strong>Imagen para el resumen:</strong></p>
                        <div id="imagen-preview-container">
                            ${imagenPreview}
                        </div>
                        <input type="file" id="imagen-resumen-edit" class="form-control form-control-sm" accept="image/*">
                        <small class="text-muted">JPG, PNG, GIF. Máx 2MB</small>
                        ${imagenActual ? '<div class="form-check mt-2"><input type="checkbox" id="eliminar-imagen" class="form-check-input"><label class="form-check-label" for="eliminar-imagen">Eliminar imagen actual</label></div>' : ''}
                    </div>
                </div>
            </div>
        `,
        icon: 'edit',
        width: '900px',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Guardar cambios',
        cancelButtonText: 'Cancelar',
        didOpen: () => {
            // Agregar evento de clic para copiar variables
            document.querySelectorAll('.var-copy').forEach(function(el) {
                el.addEventListener('click', function() {
                    var texto = this.textContent;
                    var textarea = document.getElementById('descripcion-ruta-edit');
                    var cursorPos = textarea.selectionStart;
                    var textBefore = textarea.value.substring(0, cursorPos);
                    var textAfter = textarea.value.substring(cursorPos);
                    textarea.value = textBefore + texto + textAfter;
                    textarea.focus();
                    textarea.selectionStart = textarea.selectionEnd = cursorPos + texto.length;
                });
            });
            
            // Preview de imagen al seleccionar archivo
            document.getElementById('imagen-resumen-edit').addEventListener('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('imagen-preview-container').innerHTML = 
                            '<img src="' + e.target.result + '" class="img-thumbnail mb-2" style="max-height: 100px;">';
                    };
                    reader.readAsDataURL(file);
                }
            });
        },
        preConfirm: () => {
            var fileInput = document.getElementById('imagen-resumen-edit');
            var eliminarImagen = document.getElementById('eliminar-imagen');
            return {
                descripcion: document.getElementById('descripcion-ruta-edit').value,
                imagen: fileInput.files[0] || null,
                eliminar_imagen: eliminarImagen ? eliminarImagen.checked : false
            };
        }
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
            
            // Usar FormData para enviar archivo
            var formData = new FormData();
            formData.append('opcion_id', opcionResumenId);
            formData.append('descripcion_ruta', result.value.descripcion);
            formData.append('_token', '{{ csrf_token() }}');
            if (result.value.imagen) {
                formData.append('imagen_resumen', result.value.imagen);
            }
            if (result.value.eliminar_imagen) {
                formData.append('eliminar_imagen', '1');
            }
            
            $.ajax({
                url: routeapp + '/opciones/actualizar-descripcion',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(resp) {
                    $.unblockUI();
                    $('#tabla_opciones').DataTable().ajax.reload();
                    Swal.fire('¡Actualizado!', 'La descripción e imagen han sido guardadas.', 'success');
                },
                error: function(xhr) {
                    $.unblockUI();
                    var errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Error al actualizar';
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    });
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