@extends('layouts/contentNavbarLayoutOnly' )

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
                        <input class="form-check-input" type="radio" name="espacio" value="{{$item['opcion_radio']}}">
                        <label class="form-check-label" for="muroInterior">{{$item['opcion_radio']}}</label>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>

              <div class="col-12 d-flex justify-content-between">

                <button class="btn btn-primary btn-next waves-effect waves-light"> <span
                    class="align-middle d-sm-inline-block me-sm-1">Next</span> <i
                    class="ri-arrow-right-line ri-16px"></i></button>
              </div>
            </div>
          </div>


          <div id="target_step_2" class="content fv-plugins-bootstrap5 fv-plugins-framework">
            <div class="row g-6">
              <div class="col-12">

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
        <h5 class="card-title text-muted fw-bold">Resumen de compra</h5>
        <hr>
        <p class="text-muted small">Aquí verás los importes de tu compra una vez que agregues productos.</p>
      </div>
    </div>
  </div>
</div>

@endsection