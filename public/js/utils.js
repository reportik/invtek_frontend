/**
 * Asigna automáticamente los valores guardados en sesión (inyectados desde Blade)
 * a los elementos HTML del formulario, incluyendo radios, checkboxes y selectpickers.
 *
 * Requiere:
 * - Que en la vista se inyecte: const valoresSesion = @json(session()->all());
 * - Que los elementos tengan atributos "name" correctos.
 * - Que Selectpickers estén cargados antes de llamar a esta función.
 */

function asignarValoresDesdeSesion(valoresSesion = {}) {
    for (const [key, valor] of Object.entries(valoresSesion)) {
        const $elemento = document.querySelector(`[name="${key}"]`);
        //console.log(`Asignando valor para ${key}:`, valor);
        if (!$elemento) continue;

        // Radios y Checkboxes
        if ($elemento.type === 'radio' || $elemento.type === 'checkbox') {
            //console.log(`Asignando valor para ${key} (radio/checkbox):`, valor);
            const opciones = document.querySelectorAll(`[name="${key}"]`);
            opciones.forEach(op => {
                if (op.value == valor) {
                    console.log(`Marcando ${op.value} como seleccionado`);
                    op.checked = true;

                }
            });
        }

        // Text inputs, hidden, etc.
        else {
            //si es un input siguiente-vista
            if ($elemento.name !== 'siguiente-vista') {

                $elemento.value = valor;
            }
            if ($elemento.name === 'siguiente-vista' && $elemento.value === 'final') {
                console.log(`RES Asignando valor para ${key} (siguiente-vista):`, valor);
                $elemento.value = 'resumen';
            } else {
                console.log(`Asignando valor para ${key}: ${valor}`);

            }

            // Si es selectpicker (Bootstrap-select)
            if ($elemento.classList.contains('selectpicker')) {
                $($elemento).val(valor).selectpicker('refresh');
            }

        }
    }
}

//funcion que llama a la funcion getSelectorSiguiente por ajax para obtener el siguiente selector
// Hace fetch POST a FastAPI y llena un selectpicker y el modal de catálogo
function fetchAndFillProductosByCategory(materialId, selectContainer, modalContainer) {

    // Usar fetch, bloquear pantalla con blockUI
    $.blockUI({
        css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: 0.5,
            color: '#fff'
        }
    });
    fetch(routeapp + '/products/by-category/' + materialId, {
        method: 'GET',
    })
        .then(response => {
            if (!response.ok) throw new Error('Error en la petición: ' + response.status);
            //console.log('response fetchAndFillProductosByCategory: ', response);
            return response.json();
        })
        .then(data => {

            $.unblockUI();
            $('#div_materiales').show();
            // Normalizar: si la respuesta es {data: [...]} o {data: {...}}
            data = data.data;
            if (!Array.isArray(data)) data = [];
            // 1. Llenar el selectpicker
            let select = document.createElement('select');
            select.className = 'selectpicker form-control';
            select.setAttribute('data-live-search', 'true');
            select.name = 'producto_categoria';
            select.id = 'producto_categoria_selector';
            data.forEach((prod, idx) => {
                let opt = document.createElement('option');
                opt.value = prod.PCNT_PROD_id;
                opt.textContent = prod.PCNT_PROD_nombre;
                if (idx === 0) opt.selected = true;
                select.appendChild(opt);
            });
            // Limpiar y agregar el selectpicker al contenedor
            selectContainer.innerHTML = '';
            selectContainer.appendChild(select);
            $(select).selectpicker('refresh');
            // Seleccionar el primero
            if (data.length > 0) {
                $(select).selectpicker('val', data[0].id);
            }

            // 2. Llenar el modal de catálogo
            let html = '';
            data.forEach(prod => {

                html += `
            <div class="col col-md-3 mb-4">
                <div class="card h-100" data-id="${prod.PCNT_PROD_id}">
                    <img class="card-img-top lazyload" 
                    data-src="${assetapp + '/images/categories/' + prod.PCNT_PROD_id + '.png'}"
                     style="height:180px;">
                    <div class="card-body">
                        <h6 class="card-title">${prod.PCNT_PROD_nombre}</h6>
                        <div class="text-end">
                            <button class="btn btn-sm btn-primary" data-bs-dismiss="modal" onclick="selectMaterial(event)">Seleccionar</button>
                        </div>
                    </div>
                </div>
            </div>
            `;
            });
            modalContainer.innerHTML = html;

            // Lazyload con IntersectionObserver
            const lazyImages = document.querySelectorAll('.lazyload');
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.src = entry.target.dataset.src;
                        entry.target.classList.remove('lazyload');
                        obs.unobserve(entry.target);
                    }
                });
            });
            lazyImages.forEach(img => observer.observe(img));

            updateCardImage();
        })
        .catch(error => {
            console.error(error);
            $.unblockUI();
        });

}

//funcion que llama a la funcion getSelectorSiguiente por ajax para obtener el siguiente selector
// Función utilitaria para llenar cualquier tipo de selector
function fillSelectorElement({ container, element, tipo, data, nombre }) {
    if (tipo == 'radio') {
        element = document.querySelector(`div[name="radio_${nombre}"]`);
        console.log('element radio: ', element);
    }
    if (tipo == 'checkbox') {
        element = document.querySelector(`div[name="checkbox_${nombre}"]`);
    }
    if (tipo == 'card') {
        element = document.querySelector(`div[name="card_${nombre}"]`);
        console.log('element card: card_' + nombre, element);
    }
    if (tipo == 'select') {
        console.log('element select: ' + nombre, element);
    }
    if (tipo == 'div') {
        element = document.querySelector(`div[name="div_${nombre}"]`);
        console.log('element div: ' + nombre, element);
    }

    if (!element) return;
    if (!Array.isArray(data)) data = [];
    if (data.length == 0) {
        console.log('No hay datos para llenar el selector');
        let c = document.querySelector(`#${container}`);
        c.innerHTML = '';
        c.empty();
        return;
    };
    // Limpiar el contenido
    if (tipo == 'select') {
        //console.log(element.value);
        element.innerHTML = '';

        data.forEach((opt, idx) => {
            const option = document.createElement('option');
            option.value = opt.id_opcion;
            option.textContent = opt.valor;
            if (opt.imagen) option.setAttribute('data-img', opt.imagen);
            if (opt.descripcion) option.setAttribute('data-descripcion', opt.descripcion);
            if (opt.programacion) option.setAttribute('data-programacion', opt.programacion);
            if (idx === 0) option.selected = true;
            element.appendChild(option);
        });
        //console.log(element.innerHTML);
        // Si usa selectpicker de bootstrap, refrescar y seleccionar el primero
        if ($(element).hasClass('selectpicker')) {
            $(element).selectpicker('refresh');
            // Seleccionar el primero
            if (data.length > 0) {
                $(element).selectpicker('val', data[0].id_opcion);
            }
        }
        //trigger change selectpicker changed.bs.select
        $(element).trigger('changed.bs.select');
        $(element).trigger('change');
    } else if (tipo === 'radio' || tipo === 'checkbox') {
        // Suponemos que el elemento es un contenedor (div) y el name es igual a nombre 
        //element = document.querySelector(`div[name="radio_${nombre}"]`);
        element.innerHTML = '';
        //order by opt.valor
        data.sort((a, b) => a.valor.localeCompare(b.valor));
        data.forEach((opt, idx) => {
            const input = document.createElement('input');
            input.type = tipo;
            input.name = nombre;
            input.value = opt.id_opcion;
            input.id = `${tipo}_${nombre}_${idx}`;
            input.className = 'form-check-input';
            if (idx === 0) input.checked = true;//

            //icono si es fa, pero no lo carga como html
            let icono = '';
            if (opt.programacion) {
                if (opt.programacion.startsWith('fa')) {
                    icono = '<i class="fa ' + opt.programacion + '" title = "" ></i>';
                }
            }

            const label = document.createElement('label');
            label.htmlFor = input.id;
            label.className = 'form-check-label titulo';
            label.innerHTML = opt.valor + ' ' + icono;

            const wrapper = document.createElement('div');
            wrapper.className = 'form-check ml-4';
            wrapper.appendChild(input);
            wrapper.appendChild(label);

            // Descripción personalizada
            if (opt.descripcion) {
                const descDiv = document.createElement('div');
                descDiv.className = 'descripcionSeleccion';
                descDiv.textContent = opt.descripcion;
                wrapper.appendChild(descDiv);
            }

            element.appendChild(wrapper);
        });
        const seleccion = $('input[name="' + nombre + '"]:checked').val();
        console.log('...............SELECCIONADO ' + nombre + ' CON VALOR: ', seleccion);
        if (seleccion) {
            $('input[name="' + nombre + '"]:checked').trigger('change');

        }
        // renombrar el div del elemento tipo_nombre, que sea div solamente
        // document.querySelector(`div[name="${nombre}"]`).name = `${tipo}_${nombre}`;
        // document.querySelector(`div[name="${nombre}"]`).id = `${tipo}_${nombre}`;

    } else if (tipo === 'card') {
        // Cards con input, imagen y color
        element.innerHTML = '';
        //order by opt.valor
        data.sort((a, b) => a.valor.localeCompare(b.valor));
        data.forEach((opt, idx) => {
            // Columna para grid de Bootstrap
            const col = document.createElement('div');
            col.className = 'col-md-4 mb-2';

            // Card
            const card = document.createElement('div');
            card.className = 'card h-100';
            card.style.cursor = 'pointer';


            // Imagen arriba
            if (opt.imagen) {
                const img = document.createElement('img');
                img.src = `${typeof assetapp !== 'undefined' ? assetapp + '/images/cotizador/' : ''}${opt.imagen}`;
                img.className = 'card-img-top';
                img.style.width = '100%';
                img.style.height = '180px';
                img.style.objectFit = 'cover';
                img.style.cursor = 'pointer';
                img.onclick = () => showModal(img.src);
                card.appendChild(img);
            }

            // Card body
            const cardBody = document.createElement('div');
            cardBody.className = 'card-body';

            // Form check
            const formCheck = document.createElement('div');
            formCheck.className = 'form-check';

            // Input radio/checkbox
            const input = document.createElement('input');
            input.type = tipo.startsWith('card') ? 'radio' : 'checkbox';
            input.name = nombre;
            input.value = opt.id_opcion;
            input.id = `${tipo}_${nombre}_${idx}`;
            input.className = 'form-check-input';
            if (opt.programacion) input.setAttribute('data-programacion', opt.programacion);
            if (idx === 0) input.checked = true;

            // Label
            const label = document.createElement('label');
            label.htmlFor = input.id;
            label.className = 'subtitulo';
            label.textContent = opt.valor;

            formCheck.appendChild(input);
            formCheck.appendChild(label);

            // Descripción
            if (opt.descripcion) {
                const desc = document.createElement('span');
                desc.className = 'ms-2 small descripcionSeleccion';
                desc.textContent = opt.descripcion;
                formCheck.appendChild(desc);
            }

            cardBody.appendChild(formCheck);
            card.appendChild(cardBody);
            col.appendChild(card);
            element.appendChild(col);
        });
        const seleccion = $('input[name="' + nombre + '"]:checked').val();
        console.log('...............SELECCIONADO ' + nombre + ' CON VALOR: ', seleccion);
        if (seleccion) {
            $('div[name="card_' + nombre + '"]').trigger('change');

        }
    } else if (tipo === 'div') {
        // Limpiar el contenido
        element.innerHTML = '';
        // Puedes ordenar si lo necesitas, por ejemplo por nombre:
        data.sort((a, b) => a.valor.localeCompare(b.valor));
        data.forEach((opt, idx) => {
            // Crea el div como en tu ejemplo
            const colorDiv = document.createElement('div');
            colorDiv.className = 'color-option';
            colorDiv.style.backgroundColor = opt.programacion || '';
            colorDiv.setAttribute('data-value', opt.id_opcion);
            colorDiv.id = `${tipo}_${nombre}_${idx}`;
            // Evento click para seleccionar
            colorDiv.addEventListener('click', function () {
                // Remueve selección de todos
                element.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
                this.classList.add('selected');
                // Si tienes un input hidden para guardar el valor:
                document.querySelector(`[name="${nombre}"]`).value = this.getAttribute('data-value');

                getSelectorSiguiente(nombre, this.getAttribute('data-value'));
            });

            element.appendChild(colorDiv);
        });
    }

}
function getSelectorAndFill(nombreSelector, valor, pantalla) {
    //obtener el selector anterior
    $.ajax({
        url: routeapp + '/get-selector-actual',
        type: 'POST',
        data: {
            nombre_selector: nombreSelector,
            valor: valor,
            pantalla: pantalla,
        },
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (response) {
            //console.log('response get-selector-actual: ', response);

            // Llenar el selector anterior
            console.log('getSelectorAndFill selector: ', response.selector_nombre);
            $(`#${response.selector_container}`).show();
            //console.log(document.querySelector(`[name="${response.selector_nombre}"]`));
            fillSelectorElement({
                container: response.selector_container,
                element: document.querySelector(`[name="${response.selector_nombre}"]`),
                tipo: response.selector_tipo,
                data: response.data,
                nombre: response.selector_nombre
            });
        },
        error: function (xhr, status, error) {
            console.log(error);
        }
    });

}
function getSelectorSiguiente(nombreSelector, valor) {
    const url = routeapp + '/get-selector-siguiente';

    const data = {
        nombre_selector: nombreSelector,
        valor: valor,

    };
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (response) {
            //console.log('response get-selector-siguiente: ', response);
            //console.log('pantalla ubicacion: ', document.querySelector(`[name="pantalla_ubicacion"]`).value);
            //console.log('pantalla siguiente: ', response.pantalla_ubicacion);
            if (response.pantalla_ubicacion === undefined) {
                $(`#btnSiguiente`).attr('disabled', true);
            }
            //console.log('selector container: ', response.selector_container);
            //console.log('selector container: ', document.querySelector(`#${response.selector_container}`));
            if (document.querySelector(`#${response.selector_container}`)
                || parseInt(response.pantalla_ubicacion) >
                parseInt(document.querySelector(`[name="pantalla_ubicacion"]`).value)) {//si el selector existe o la pantalla siguiente es mayor que la pantalla actual

                //console.log('response get-selector-siguiente: ', response);
                //set input value siguiente-vista
                document.querySelector(`[name="siguiente-vista"]`).value = response.pantalla_siguiente;
                //console.log(response.pantalla_siguiente);
                //console.log(document.querySelector(`[name="siguiente-vista"]`));
                //console.log(document.querySelector(`[name="siguiente-vista"]`).value);
                //set input value anterior-vista
                if (document.querySelector(`[name="regresar"]`)) {
                    document.querySelector(`[name="regresar"]`).value = response.pantalla_anterior;

                }

                // Llenar el elemento según el tipo usando la función utilitaria
                console.log('llenando getSelectorSiguiente: ', response.selector_nombre);
                //console.log(document.querySelector(`[name="${response.selector_nombre}"]`));
                console.log('tipo: ', response.selector_tipo);
                console.log('data: ', response.data);
                //console.log('nombre: ', response.selector_nombre);
                //console.log('elemento: ', document.querySelector(`[name="${response.selector_nombre}"]`));
                fillSelectorElement({
                    container: response.selector_container,
                    element: document.querySelector(`[name="${response.selector_nombre}"]`),
                    tipo: response.selector_tipo,
                    data: response.data,
                    nombre: response.selector_nombre
                });
                //mostrar selector
                $(`#${response.selector_container}`).show();

                //ocultar el boton siguiente si el selector siguiente tiene PAS_Pantalla_Ubicacion <= input pantalla_ubicacion
                //obtener el orden del selector siguiente
                let selectorSiguiente = selectores.find(selector => selector.PAS_Html_name === response.selector_nombre);
                console.log('selector siguiente: ', selectorSiguiente);
                if (selectorSiguiente) {
                    //console.log('****************selector siguiente************: ', selectorSiguiente);
                    //ocultar selectores mayores que el actual nombreSelector
                    console.log('selector siguiente orden: ', selectorSiguiente.PAS_Orden);
                    console.log('selector siguiente pantalla ubicacion: ', selectorSiguiente.PAS_Pantalla_Ubicacion);
                    //console.log('pantalla ubicacion: ', document.querySelector(`[name="pantalla_ubicacion"]`).value);
                    selectores.forEach(selector => {
                        if (parseInt(selector.PAS_Orden) > parseInt(selectorSiguiente.PAS_Orden) && selector.PAS_Pantalla_Ubicacion == parseInt(document.querySelector(`[name="pantalla_ubicacion"]`).value)) {
                            //console.log('ocultando selector: ', selector.PAS_Container, selector.PAS_Orden);
                            //$(`#${selector.PAS_Container}`).hide();
                        }
                        /* else {
                            //si esta el nombre en avance_
                            if ($(`#${selector.PAS_Container}`) && !$(`#${selector.PAS_Container}`).is(':empty')) {//ocultar si el selector está vacío
                                console.log('mostrando selector: ', selector.PAS_Container, selector.PAS_Orden);//mostrar si el selector no está vacío
                                $(`#${selector.PAS_Container}`).show();
                            } else {
                                console.log('ocultando selector: ', selector.PAS_Container, selector.PAS_Orden);//ocultar si el selector está vacío
                                $(`#${selector.PAS_Container}`).hide();
                            }
                        } */
                    });
                }
                //console.log('selector siguiente pantalla ubicacion: ', selectorSiguiente.PAS_Pantalla_Ubicacion);
                //console.log('pantalla ubicacion: ', document.querySelector(`[name="pantalla_ubicacion"]`).value);
                if (parseInt(selectorSiguiente.PAS_Pantalla_Ubicacion) <= parseInt(document.querySelector(`[name="pantalla_ubicacion"]`).value)) {
                    //console.log('desactivando boton siguiente');
                    $(`#btnSiguiente`).attr('disabled', true);

                } else {
                    $(`#btnSiguiente`).attr('disabled', false);
                }
            }
            else {
                console.log('SELECTOR SIGUIENTE no encontrado');
                $(`#btnSiguiente`).attr('disabled', true);
                //console.log('selectores: ', selectores);
                //obtener el orden del selector actual
                let selectorActual = selectores.find(selector => selector.PAS_Html_name === nombreSelector);
                console.log('****************selector actual************: ', selectorActual);
                //ocultar selectores mayores que el actual nombreSelector
                console.log('selector actual: ', selectorActual.PAS_Orden);
                selectores.forEach(selector => {
                    //console.log('selector: ', selector.PAS_Orden);
                    console.log('selector orden: ', selector.PAS_Orden);
                    console.log('selector pantalla ubicacion: ', selector.PAS_Pantalla_Ubicacion);
                    console.log('selector pantalla ubicacion actual: ', $(`input[name="pantalla_ubicacion"]`).val());
                    if (parseInt(selector.PAS_Orden) > parseInt(selectorActual.PAS_Orden) && selector.PAS_Pantalla_Ubicacion == parseInt($(`input[name="pantalla_ubicacion"]`).val())) {
                        console.log('ocultando selector: ', selector.PAS_Container, selector.PAS_Orden);
                        $(`#${selector.PAS_Container}`).hide();
                    }
                    /* else {
                        //si no esta vacio
                        if ($(`#${selector.PAS_Container}`) && !$(`#${selector.PAS_Container}`).is(':empty')) {
                            console.log('mostrando selector: ', selector.PAS_Container, selector.PAS_Orden);
                            $(`#${selector.PAS_Container}`).show();
                        } else {
                            console.log('ocultando selector: ', selector.PAS_Container, selector.PAS_Orden);
                            $(`#${selector.PAS_Container}`).hide();
                        }
                    } */
                });

            }

        },
        error: function (xhr, status, error) {
            console.log(error);
        }
    });
}
