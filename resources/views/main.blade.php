@extends('layouts/contentNavbarLayoutOnly' )

<script>
  function selectEligeTela2(event) {
  //colocar en tarjeta_imagen el value del select, y en tarjeta_titulo el texto seleccionado del select
  const selectedValue = event.target.value;
  const selectedText = event.target.options[event.target.selectedIndex].text;
  // Colocar en tarjeta_imagen el value del select (base64) con el prefijo adecuado
  document.getElementById('tarjeta_imagen').src = `data:image/png;base64,${selectedValue}`;
  // Colocar en tarjeta_titulo el texto seleccionado del select
  document.getElementById('tarjeta_titulo').innerText = selectedText;
  }
</script>
@section('content')


<div class="row">
  <div class="col-md-8">
    <div id="wizard-property-listing" class="bs-stepper vertical mt-2 linear">
      <div class="bs-stepper-header gap-lg-2 border-end">
        @foreach ($steps as $item)

        <div class="step @if ($item['a_selected'] == 'true')
                        active
                    @endif" data-target="{{'#target_step_' . $item['number']}}">
          <button type="button" class="step-trigger" aria-selected="{{$item['a_selected']}}"
            @if($item['a_selected']=='false' ) disabled @endif>
            <span class="bs-stepper-circle"><i class="ri-check-line"></i></span>
            <span class="bs-stepper-label">
              <span class="bs-stepper-number">{{$item['number']}}</span>
              <span class="d-flex flex-column ms-2">
                <span class="bs-stepper-title">{{$item['title']}}</span>
              </span>
            </span>
          </button>
        </div>
        <div class="line"></div>
        @endforeach

      </div>
      <div class="bs-stepper-content">
        <form id="wizard-property-listing-form">


          <div id="target_step_1" class="content active dstepper-block fv-plugins-bootstrap5 fv-plugins-framework">
            <div class="row g-6">
              <div class="row row-cols-1 row-cols-md-3 g-6 mb-6">
                @foreach ($cards_1 as $item)
                <div class="col">
                  <div class="card">
                    <img class="card-img-top" src="{{ asset('images/' . $item['image'])}}" alt="Card image cap">
                    <div class="card-body">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="espacio" value="{{$item['opcion_radio']}}"
                          @if ($item['a_selected']=='true' ) checked @endif>
                        <label class="form-check-label" for="muroInterior">{{$item['opcion_radio']}}</label>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>

              <div class="col-12 d-flex justify-content-between">

                <button style="text-align: right;" class="btn btn-primary btn-next waves-effect waves-light"> <span
                    class="align-middle d-sm-inline-block me-sm-1">Next</span> <i
                    class="ri-arrow-right-line ri-16px"></i></button>
              </div>
            </div>
          </div>


          <div id="target_step_2" class="content fv-plugins-bootstrap5 fv-plugins-framework">
            <div class="row g-6">
              <div class="row row-cols-1 row-cols-md-3 g-6 mb-6">
                @foreach ($cards_2 as $item)
                <div class="col">
                  <div class="card">
                    <img class="card-img-top" src="{{ asset('images/' . $item['image'])}}" alt="Card image cap">
                    <div class="card-body">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="espacio" value="{{$item['opcion_radio']}}"
                          @if ($item['a_selected']=='true' ) checked @endif>
                        <label class="form-check-label" for="muroInterior">{{$item['opcion_radio']}}</label>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>

              <div class="col-12 d-flex justify-content-between">
                <button class="btn btn-outline-secondary btn-prev waves-effect"> <i
                    class="ri-arrow-left-line ri-16px me-sm-1 me-0"></i> <span
                    class="align-middle d-sm-inline-block d-none">Previous</span> </button>
                <button class="btn btn-primary btn-next waves-effect waves-light"> <span
                    class="align-middle d-sm-inline-block d-none me-sm-1">Next</span> <i
                    class="ri-arrow-right-line ri-16px"></i></button>
              </div>
            </div>
          </div>


          <div id="target_step_3" class="content fv-plugins-bootstrap5 fv-plugins-framework">
            <div class="row g-6">
              <div class="row row-cols-1 row-cols-md-3 g-6 mb-6">
                @foreach ($cards_3 as $item)
                <div class="col">
                  <div class="card">
                    <img class="card-img-top" src="{{ asset('images/' . $item['image'])}}" alt="Card image cap">
                    <div class="card-body">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="espacio" value="{{$item['opcion_radio']}}"
                          @if ($item['a_selected']=='true' ) checked @endif>
                        <label class="form-check-label" for="muroInterior">{{$item['opcion_radio']}}</label>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
              <label for="sel_tela_bo" class="form-label">Selecciona tu Tela:</label>
              <select id="sel_tela_bo" class="form-select form-select-lg" onchange="selectEligeTela(event)">

                @foreach ($telas as $item)
                <option value="{{ $item['image'] }}">{{ $item['name'] }}</option>
                @endforeach
              </select>
              <!-- Tarjeta -->
              <div class="card" style="width: 18rem;">
                @if (count($telas) > 0)

                <img id="tarjeta_imagen" src="{{'data:image/png;base64,'.$telas[0]['image']}}" class="card-img-top"
                  style="border-radius: 8px 8px 0 0;" alt="Tela Image">
                <div class="card-body">
                  <h5 id="tarjeta_titulo" class="card-title">{{$telas[0]['name']}}</h5>
                  <p class="card-text"></p>
                </div>
                @endif

              </div>
              <div class="col-12 d-flex justify-content-between">
                <button class="btn btn-outline-secondary btn-prev waves-effect"> <i
                    class="ri-arrow-left-line ri-16px me-sm-1 me-0"></i> <span
                    class="align-middle d-sm-inline-block d-none">Previous</span> </button>
                <button class="btn btn-primary btn-next waves-effect waves-light"> <span
                    class="align-middle d-sm-inline-block d-none me-sm-1">Next</span> <i
                    class="ri-arrow-right-line ri-16px"></i></button>
              </div>
            </div>
          </div>


          <div id="target_step_4" class="content fv-plugins-bootstrap5 fv-plugins-framework">
            <div class="row g-6">


              <div class="col-12 d-flex justify-content-between">
                <button class="btn btn-outline-secondary btn-prev waves-effect"> <i
                    class="ri-arrow-left-line ri-16px me-sm-1 me-0"></i> <span
                    class="align-middle d-sm-inline-block d-none">Previous</span> </button>
                <button class="btn btn-primary btn-next waves-effect waves-light"> <span
                    class="align-middle d-sm-inline-block d-none me-sm-1">Next</span> <i
                    class="ri-arrow-right-line ri-16px"></i></button>
              </div>
            </div>
          </div>


        </form>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card mt-4">
      <div class="card-body">
        <h5 class="card-title text-muted fw-bold">Resumen de Cotización</h5>
        <hr>
        <p class="text-muted small">Aquí verás los importes.</p>
      </div>
    </div>
  </div>
</div>

@endsection