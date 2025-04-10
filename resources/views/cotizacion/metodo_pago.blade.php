<div class="tab-pane fade" id="navs-top-payment" role="tabpanel">
    <h5>Dirección / Método de Pago</h5>
    <p class="mb-0">
    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <!-- 1. Delivery Address -->
                            <h5 class="mb-4">1. Dirección de Entrega</h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" id="fullname" class="form-control" placeholder="John Doe" />
                                        <label for="fullname">Nombre completo</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-merge">
                                        <div class="form-floating form-floating-outline">
                                            <input class="form-control" type="text" id="email" name="email"
                                                placeholder="john.doe" aria-label="john.doe"
                                                aria-describedby="email3" />
                                            <label for="email">Email</label>
                                        </div>
                                        <span class="input-group-text" id="email3">@example.com</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" id="phone" class="form-control phone-mask"
                                            placeholder="658 799 8941" aria-label="658 799 8941" />
                                        <label for="phone">Número de contacto</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating form-floating-outline">
                                        <textarea name="address" class="form-control" id="address" rows="2"
                                            placeholder="" style="height: 65px;"></textarea>
                                        <label for="address">Calle</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" id="pincode" class="form-control" placeholder="658468" />
                                        <label for="pincode">CP</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" id="landmark" class="form-control" placeholder="" />
                                        <label for="landmark">Colonia</label>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" id="city" class="form-control" placeholder="Jackson" />
                                        <label for="city">Ciudad</label>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-floating form-floating-outline">
                                        <select id="state" class="select2 form-select" data-allow-clear="true">
                                            <option value="">Selecciona</option>

                                        </select>
                                        <label for="state">Estado</label>
                                    </div>
                                </div>


                                <label class="form-check-label">Tipo de Dirección</label>
                                <div class="col mt-2">
                                    <div class="form-check form-check-inline">
                                        <input name="collapsible-address-type" class="form-check-input" type="radio"
                                            value="" id="collapsible-address-type-home" checked="" />
                                        <label class="form-check-label" for="collapsible-address-type-home">Casa
                                            (Entrega todo el
                                            dia)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input name="collapsible-address-type" class="form-check-input" type="radio"
                                            value="" id="collapsible-address-type-office" />
                                        <label class="form-check-label" for="collapsible-address-type-office"> Oficina
                                            (Entrega de 9
                                            AM
                                            a 5 PM) </label>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <!-- 2. Delivery Type -->
                            <h5 class="my-4">2. Tipo de Entrega</h5>
                            <div class="row gy-3">
                                <div class="col-md">
                                    <div class="form-check custom-option custom-option-icon">
                                        <label class="form-check-label custom-option-content" for="customRadioIcon1">
                                            <span class="custom-option-body">
                                                <i class='ri-briefcase-line'></i>
                                                <span class="custom-option-title"> Standard </span>
                                                <small> Entrega de 3 a 5 dias. </small>
                                            </span>
                                            <input name="customRadioIcon" class="form-check-input" type="radio" value=""
                                                id="customRadioIcon1" checked />
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-check custom-option custom-option-icon">
                                        <label class="form-check-label custom-option-content" for="customRadioIcon2">
                                            <span class="custom-option-body">
                                                <i class='ri-send-plane-2-line'></i>
                                                <span class="custom-option-title"> Express </span>
                                                <small>Entrega de 2 a 3 dias.</small>
                                            </span>
                                            <input name="customRadioIcon" class="form-check-input" type="radio" value=""
                                                id="customRadioIcon2" />
                                        </label>
                                    </div>
                                </div>

                            </div>
                            <hr>

                            <hr>
                            <!-- 3. Payment Method -->
                            <h5 class="my-4">3. Metodo de Pago</h5>
                            <div class="row g-3">
                                <div class="mb-3">
                                    <div class="form-check form-check-inline">
                                        <input name="collapsible-payment" class="form-check-input" type="radio" value=""
                                            id="collapsible-payment-cc" checked="" />
                                        <label class="form-check-label" for="collapsible-payment-cc">
                                            Credit/Debit/ATM Card <i class="ri-bank-card-line"></i>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12 col-md-10 col-xxl-8">
                                    <div class="input-group input-group-merge mb-4">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" id="collapsible-payment-card" name="creditCardMask"
                                                class="form-control credit-card-mask" placeholder="1356 3215 6548 7898"
                                                aria-describedby="creditCardMask2" />
                                            <label for="collapsible-payment-card">Numero tarjeta</label>
                                        </div>
                                        <span class="input-group-text cursor-pointer p-1" id="creditCardMask2"><span
                                                class="card-type"></span></span>
                                    </div>
                                    <div class="row g-4 mb-3">
                                        <div class="col-12 col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" id="collapsible-payment-name" class="form-control"
                                                    placeholder="John Doe" />
                                                <label for="collapsible-payment-name">Nombre</label>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" id="collapsible-payment-expiry-date"
                                                    class="form-control expiry-date-mask" placeholder="MM/YY" />
                                                <label for="collapsible-payment-expiry-date"> Fecha Exp.</label>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="input-group input-group-merge">
                                                <div class="form-floating form-floating-outline">
                                                    <input type="text" id="collapsible-payment-cvv"
                                                        class="form-control cvv-code-mask" maxlength="3"
                                                        placeholder="654" />
                                                    <label for="collapsible-payment-cvv">CVV Code</label>
                                                </div>
                                                <span class="input-group-text cursor-pointer"
                                                    id="collapsible-payment-cvv2"><i class="ri-question-line"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Card Verification Value"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </p>
</div>