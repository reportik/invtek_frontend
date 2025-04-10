<div class="tab-pane fade show active" id="navs-top-home" role="tabpanel">
    <div class="row">
        <div class="col-md-9">
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
                                    <span class="bs-stepper-title bs-stepper">{{$item['title']}}</span>
                                </span>
                            </span>
                        </button>
                    </div>
                    <div class="line"></div>
                    @endforeach

                </div>
                <div class="bs-stepper-content">
                    <div id="wizard-property-listing-form">

                        <div id="target_step_1"
                            class="content active dstepper-block fv-plugins-bootstrap5 fv-plugins-framework">
                            <span class="bs-title">SELECCIONA EL ESPACIO DONDE UBICARÁS TU CORTINA</span>
                            <div class="row g-6">

                                <div class="row row-cols-1 row-cols-md-3 g-6 mb-4">
                                    @foreach ($cards_1 as $item)
                                    <div class="col">
                                        <div class="card">
                                            <img class="card-img-top" src="{{ asset('images/' . $item['image'])}}"
                                                alt="Card image cap"
                                                onclick="showModal('{{ asset('images/' . $item['image']) }}')"
                                                style="cursor: pointer;">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="radio_step_1"
                                                        id="radio1_{{ $loop->index }}" onclick="toggleSelect_1()"
                                                        value="{{$item['opcion_radio']}}"
                                                        @if($item['a_selected']=='true' ) checked @endif>
                                                    <label class="form-check-label"
                                                        for="radio1_{{ $loop->index }}">{{$item['opcion_radio']}}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="col-12 d-flex justify-content-end">

                                    <button style="text-align: right;"
                                        class="btn btn-primary btn-next waves-effect waves-light">
                                        <span class="align-middle d-sm-inline-block me-sm-1">Siguiente</span> <i
                                            class="ri-arrow-right-line ri-16px"></i></button>
                                </div>
                            </div>
                        </div>

                        <div id="target_step_2" class="content fv-plugins-bootstrap5 fv-plugins-framework">
                            <span class="bs-title">ELIGE EL SISTEMA DE CONFECCIÓN QUE DESEAS</span>
                            <div class="row g-6">
                                <div class="row row-cols-1 row-cols-md-3 g-6 mb-4">
                                    @foreach ($cards_2 as $item)
                                    <div class="col">
                                        <div class="card">
                                            <img class="card-img-top" src="{{ asset('images/' . $item['image'])}}"
                                                alt="Card image cap"
                                                onclick="showModal('{{ asset('images/' . $item['image']) }}')"
                                                style="cursor: pointer;">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="radio_step_2"
                                                        id="radio2_{{ $loop->index }}" onclick="toggleSelect_2()"
                                                        value="{{$item['opcion_radio']}}"
                                                        @if($item['a_selected']=='true' ) checked @endif>
                                                    <label class="form-check-label"
                                                        for="radio2_{{ $loop->index }}">{{$item['opcion_radio']}}</label>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    @endforeach


                                </div>

                                @include('cotizacion.rieles')

                                <div class="col-12 d-flex justify-content-between">
                                    <button class="btn btn-outline-secondary btn-prev waves-effect"> <i
                                            class="ri-arrow-left-line ri-16px me-sm-1 me-0"></i> <span
                                            class="align-middle d-sm-inline-block d-none">Anterior</span> </button>
                                    <button class="btn btn-primary btn-next waves-effect waves-light"> <span
                                            class="align-middle d-sm-inline-block d-none me-sm-1">Siguiente</span> <i
                                            class="ri-arrow-right-line ri-16px"></i></button>
                                </div>
                            </div>
                        </div>

                        <div id="target_step_3" class="content fv-plugins-bootstrap5 fv-plugins-framework">
                            <span class="bs-title">ELIGE EL TIPO DE TELA EN QUE DESEAS CONFECCIONAR TU CORTINA</span>
                            <div class="row g-6">
                                <div class="row row-cols-1 row-cols-md-3 g-6 mb-4">
                                    @foreach ($cards_3 as $item)
                                    <div class="col">
                                        <div class="card">
                                            <img class="card-img-top" src="{{ asset('images/' . $item['image'])}}"
                                                alt="Card image cap"
                                                onclick="showModal('{{ asset('images/' . $item['image']) }}')"
                                                style="cursor: pointer;">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="radio_step_3"
                                                        id="radio3_{{ $loop->index }}" onclick="toggleSelect_3()"
                                                        value="{{$item['opcion_radio']}}"
                                                        @if($item['a_selected']=='true' ) checked @endif>
                                                    <label class="form-check-label"
                                                        for="radio3_{{ $loop->index }}">{{$item['opcion_radio']}}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="row">
                                    <div class="col-md-6 col-xs-12 align-self-center ">
                                        <div class="row">
                                            <div class="col-12">
                                                <label for="sel_tela_bo" class="form-label">Selecciona tu Tela:</label>
                                                <select data-width="auto" id="sel_tela_bo"
                                                    class="selectpicker sel_tipo_tela" data-live-search="true"
                                                    data-size="5" onchange="selectEligeTela(event)">

                                                    @foreach ($telas_blackout as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @endforeach
                                                </select>

                                                <select data-width="auto" id="sel_tela_sheer" style="display: block;"
                                                    class="selectpicker sel_tipo_tela" data-size="5"
                                                    data-live-search="true" onchange="selectEligeTela(event)">

                                                    @foreach ($telas_sheer as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <!-- boton para abrir un modal que muestre un catalogo -->
                                                <button type="button" class="form-control btn btn-primary mt-10"
                                                    data-bs-toggle="modal" data-bs-target="#catalogoModal">
                                                    Ver Catálogo
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-md-6 col-xs-12 col align-self-center ">
                                        <!-- Tarjeta -->
                                        <div class="card" style="width: 18rem;">
                                            @if (count($telas_blackout) > 0)

                                            <img id="tarjeta_imagen" src="" class="mt-3 card-img-top"
                                                style="border-radius: 8px 8px 0 0;" alt="Tela Image">
                                            <div class="card-body">
                                                <h6 id="tarjeta_titulo" class="card-title"></h6>
                                                <p class="card-text"></p>
                                            </div>
                                            @endif

                                        </div>
                                    </div>
                                </div>


                                <div class="col-12 d-flex justify-content-between">
                                    <button class="btn btn-outline-secondary btn-prev waves-effect"> <i
                                            class="ri-arrow-left-line ri-16px me-sm-1 me-0"></i> <span
                                            class="align-middle d-sm-inline-block d-none">Anterior</span> </button>
                                    <button class="btn btn-primary btn-next waves-effect waves-light"> <span
                                            class="align-middle d-sm-inline-block d-none me-sm-1">Siguiente</span> <i
                                            class="ri-arrow-right-line ri-16px"></i></button>
                                </div>
                            </div>
                        </div>

                        <div id="target_step_4" class="content fv-plugins-bootstrap5 fv-plugins-framework">
                            <span class="bs-title mb-2">ESPECIFICA LAS MEDIDAS DEL ESPACIO TOTAL QUE OCUPARÁ LA
                                CORTINA Y LAS HOJAS EN QUE ESTARÁ DIVIDIDA</span>
                            <hr>
                            <div class="row g-6 mt-1">
                                <div class="row row-cols-1 row-cols-md-3 g-6 mb-4">
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline mb-5">
                                            <input type="number" class="form-control" value="1" id="width" name="width"
                                                placeholder="" autocomplete="off">
                                            <label for="width">Ancho (m):</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline mb-5">
                                            <input type="number" class="form-control" value="1" id="height"
                                                name="height" placeholder="" autocomplete="off">
                                            <label for="height">Alto (m):</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="sheets">Hojas:
                                            <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top"
                                                title="Partes móviles que se pueden abrir y cerrar
                                            recorriendo a un lado o el otro, para
                                            permitir o bloquear la entrada de luz."></i>
                                        </label>
                                        <div class="form-floating form-floating-outline mb-5">
                                            <input step="1" min="1" value="1" type="number" class="form-control"
                                                id="sheets" name="sheets" placeholder="Hojas" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="overlap">
                                            Traslape:
                                            <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top"
                                                title="Cantidad de tela que se superpone
                                    cuando las cortinas están cerradas. Esta
                                    superposición ayuda a bloquear mejor la
                                    luz."></i>
                                        </label>
                                        <div class="form-floating form-floating-outline mb-5">

                                            <select class="form-control selectpicker control-usuario" id="overlap"
                                                name="overlap">
                                                <option value="10">Traslape corto (10 cm)</option>
                                                <option value="15">Traslape corto (15 cm)</option>
                                                <option value="20">Traslape medio (20 cm)</option>
                                                <option value="25">Traslape medio (25 cm)</option>
                                                <option value="30">Traslape largo (30 cm)</option>

                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 d-flex justify-content-between">
                                    <button class="btn btn-outline-secondary btn-prev waves-effect"> <i
                                            class="ri-arrow-left-line ri-16px me-sm-1 me-0"></i> <span
                                            class="align-middle d-sm-inline-block d-none">Anterior</span> </button>
                                    <button class="btn btn-primary btn-next waves-effect waves-light"> <span
                                            class="align-middle d-sm-inline-block d-none me-sm-1">Siguiente</span> <i
                                            class="ri-arrow-right-line ri-16px"></i></button>
                                </div>
                            </div>
                        </div>
                        <div id="target_step_5" class="content fv-plugins-bootstrap5 fv-plugins-framework">
                            <span class="bs-title mb-2">AGREGA ESPECIFICACIONES DE LOS ACCESORIOS</span>
                            <hr>

                            <div class="row g-6 mt-1">
                                <div class="form-group col-md-6 g-6">
                                    <label for="sheets">Bastón:
                                    </label>
                                    <div class="form-floating form-floating-outline mb-5">
                                        <select class="form-control selectpicker control-usuario" id="baston"
                                            name="baston" onchange="selectEligeBaston(event)">
                                            <option value="fibra_vidrio_negro">Fibra de vidrio en color negro</option>
                                            <option value="fibra_vidrio_blanco">Fibra de vidrio en color blanco</option>
                                        </select>

                                    </div>
                                </div>
                                <div class="form-group col-md-6 g-6">
                                    <label for="overlap">
                                        Mecanismo de Apertura:
                                    </label>
                                    <div class="form-floating form-floating-outline mb-5">

                                        <select class="form-control selectpicker control-usuario" id="mecanismo"
                                            name="mecanismo" onchange="selectEligeMecanismo(event)">
                                            <option value="manual">Manual</option>
                                            <option value="motorizado">Motorizado</option>

                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-between">
                                <button class="btn btn-outline-secondary btn-prev waves-effect"> <i
                                        class="ri-arrow-left-line ri-16px me-sm-1 me-0"></i> <span
                                        class="align-middle d-sm-inline-block d-none">Anterior</span> </button>

                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title text-muted fw-bold">Descripción</h5>
                    <hr>

                    <ul id="lista_resumen" class="list-group list-group-timeline collapsed" data-bs-toggle="collapse"
                        data-bs-target="#lista_resumen">
                        <li class="list-group-item list-group-timeline-success" style="display: none">
                            <span id="resumen_tela_id"></span>
                        </li>
                        <li class="list-group-item list-group-timeline-success">
                            <strong>Cortina para:</strong> <span id="resumen_cortina"></span>
                        </li>
                    </ul>

                </div>
                <div id="resumen_total" class="card-body" style="display: block;">
                    <hr>
                    <span class="text-muted fw-bold">Cantidad: </span>
                    <div class="d-flex col-md-12">
                        <button class="btn btn-outline-secondary waves-effect w-100"
                            onclick="changeValue(-1)">-</button>
                        <input type="number" id="numericInput" class="form-control text-center mx-2"
                            style="width: 100%;" value="1" min="1">
                        <button class="btn btn-outline-secondary waves-effect w-100" onclick="changeValue(1)">+</button>
                    </div>
                    <div class="card-body">
                        <button id="resumen_btn"
                            class="btn btn-primary mt-1 text-end waves-effect waves-light w-100">Ver
                            Cotización</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>