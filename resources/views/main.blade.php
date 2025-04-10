@extends('layouts/contentNavbarLayoutOnly' )

<script src="{{ URL::asset('js/cotizacionCortinas.js?v=' . $version) }}"></script>

<style>
  .color-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(25px, 1fr));
    gap: 5px;
    max-width: 120px;
    /* Ancho máximo para que sean 2 filas */
    justify-content: center;
  }

  .color-option {
    width: 25px;
    height: 25px;
    border-radius: 50%;
    border: 2px solid #ddd;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
  }

  .color-option.selected-color {
    border: 3px solid #4CAF50;
    /* Borde verde */
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
  }
</style>
@section('content')
<!-- Modal HTML -->
@csrf
<div id="imageModal"
  style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 1050; align-items: center; justify-content: center;"
  onclick="closeModal(event)">
  <span onclick="closeModal()"
    style="position: absolute; top: 10px; right: 20px; font-size: 30px; color: white; cursor: pointer;">&times;</span>
  <img id="modalImage" style="max-width: 90%; max-height: 90%; border-radius: 8px;" />
</div>

<div class="row">
  <div style="display: flex; align-items: center; justify-content: center; margin: 20px 0;">
    <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
    <h2
      style="color: #59981A; font-family: 'Arial', sans-serif; font-size: 36px; font-weight: bold; text-align: center; letter-spacing: 1px;">
      Cotizador de Cortinas
    </h2>
    <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
  </div>

</div>

<div class="nav-align-top">
  @include('cotizacion.tabs')
  <div class="tab-content">
    @include('cotizacion.cotizador')
    @include('cotizacion.resumen')
    @include('cotizacion.metodo_pago')
  </div>
</div>
<!-- end row -->
<!-- Hay que definir el Modal #catalogoModal -->

@include('modals.catalogoModal')

<script>
  //const telas = @json($telas_blackout); // Asegúrate de pasar las telas desde el backend
    const container = document.getElementById('telas-container');
    const telasBlackout = @json($telas_blackout); // Telas blackout desde el backend
    const telasSheer = @json($telas_sheer); // Telas sheer desde el backend

    function cargarCatalogo(tipo) {
      const container = document.getElementById('telas-container');
      container.innerHTML = ''; // Limpiar el contenedor

      // Seleccionar las telas según el tipo
      const telasFiltradas = tipo === 'blackout' ? telasBlackout : telasSheer;

      // Crear las tarjetas de las telas filtradas
      telasFiltradas.forEach(tela => {
        const imageUrl = `{{ asset('images/telas_resized/img_${tela.id}_${tela.Tipo}.png') }}`;
        const card = document.createElement('div');
        card.className = 'col-md'
        card.innerHTML = `
          <div class="card mb-4" data-id="${tela.id}" style="cursor: pointer;">
            <div class="row g-0">
              <div class="col-md-4">
                <img class="card-img card-img-left lazyload" data-src="${imageUrl}" alt="${tela.name}" />
              </div>
              <div class="col-md-8">
                <div class="card-body">
                  <h6 class="card-title">${tela.name}</h6>
                  <div class="col-12 d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal" onclick="selectTela(event)">Seleccionar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;
        container.appendChild(card);
      });

      // Lazy loading de imágenes
      if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('.lazyload');
        const imageObserver = new IntersectionObserver((entries, observer) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              const img = entry.target;
              img.src = img.dataset.src;
              img.classList.remove('lazyload');
              observer.unobserve(img);
            }
          });
        });

        lazyImages.forEach(img => {
          imageObserver.observe(img);
        });
      }
    }
</script>





@endsection