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
// Función utilitaria para llenar cualquier tipo de selector
function fillSelectorElement({ element, tipo, data, nombre }) {
    if (!element) return;
    if (!Array.isArray(data)) data = [];
    console.log('fillSelectorElement ......');
    console.log('elemento: ', element);
    console.log('tipo: ', tipo);
    console.log('data: ', data);
    console.log('nombre: ', nombre);
    console.log('........');
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
    } else if (tipo === 'radio' || tipo === 'checkbox') {
        // Suponemos que el elemento es un contenedor (div) y el name es igual a nombre
        element.innerHTML = '';
        data.forEach((opt, idx) => {
            const input = document.createElement('input');
            input.type = tipo;
            input.name = nombre;
            input.value = opt.id_opcion;
            input.id = `${tipo}_${nombre}_${idx}`;
            input.className = 'form-check-input';
            if (idx === 0) input.checked = true;
            const label = document.createElement('label');
            label.htmlFor = input.id;
            label.className = 'form-check-label subtitulo';
            label.textContent = opt.valor;
            const wrapper = document.createElement('div');
            wrapper.className = 'form-check';
            wrapper.appendChild(input);
            wrapper.appendChild(label);
            element.appendChild(wrapper);
        });
    } else if (tipo === 'radio_card' || tipo === 'checkbox_card') {
        // Cards con input, imagen y color
        element.innerHTML = '';
        data.forEach((opt, idx) => {
            const card = document.createElement('div');
            card.className = 'card mb-2';
            card.style.cursor = 'pointer';
            if (opt.programacion && /^#([A-Fa-f0-9]{6})$/.test(opt.programacion)) {
                card.style.borderColor = opt.programacion;
            }
            const cardBody = document.createElement('div');
            cardBody.className = 'card-body d-flex align-items-center';
            // Imagen si existe
            if (opt.imagen) {
                const img = document.createElement('img');
                img.src = `${typeof assetapp !== 'undefined' ? assetapp + '/images/cotizador/' : ''}${opt.imagen}`;
                img.className = 'card-img-top me-3';
                img.style.width = '60px';
                img.style.height = '60px';
                img.style.objectFit = 'contain';
                cardBody.appendChild(img);
            }
            // Input radio/checkbox
            const input = document.createElement('input');
            input.type = tipo.startsWith('radio') ? 'radio' : 'checkbox';
            input.name = nombre;
            input.value = opt.id_opcion;
            input.id = `${tipo}_${nombre}_${idx}`;
            input.className = 'form-check-input ms-2';
            if (idx === 0) input.checked = true;
            // Label
            const label = document.createElement('label');
            label.htmlFor = input.id;
            label.className = 'form-check-label ms-2';
            label.textContent = opt.valor;
            // Descripción
            const desc = document.createElement('span');
            if (opt.descripcion) {
                desc.className = 'text-muted ms-2 small';
                desc.textContent = opt.descripcion;
            }
            // Color de fondo si programacion es color
            if (opt.programacion && /^#([A-Fa-f0-9]{6})$/.test(opt.programacion)) {
                cardBody.style.backgroundColor = opt.programacion;
            }
            cardBody.appendChild(input);
            cardBody.appendChild(label);
            if (opt.descripcion) cardBody.appendChild(desc);
            card.appendChild(cardBody);
            element.appendChild(card);
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
            console.log('response get-selector-actual: ', response);

            // Llenar el selector anterior
            console.log('llenando selector actual: ', response.selector_nombre);
            //console.log(document.querySelector(`[name="${response.selector_nombre}"]`));
            fillSelectorElement({
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
            if (document.querySelector(`[name="${response.selector_nombre}"]`) && response.data.length > 0) {
                console.log('response get-selector-siguiente: ', response);
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
                console.log('llenando selector: ', response.selector_nombre);
                //console.log(document.querySelector(`[name="${response.selector_nombre}"]`));
                fillSelectorElement({
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
                selectores.forEach(selector => {
                    if (selector.PAS_Orden > selectorSiguiente.PAS_Orden) {
                        console.log('ocultando selector: ', selector.PAS_Container);
                        $(`#${selector.PAS_Container}`).hide();
                    }
                });
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
                console.log('selector no encontrado');
                //obtener el orden del selector actual
                let selectorActual = selectores.find(selector => selector.PAS_Html_name === nombreSelector);
                //ocultar selectores mayores que el actual nombreSelector
                selectores.forEach(selector => {
                    if (selector.PAS_Orden > selectorActual.PAS_Orden) {
                        $(`#${selector.PAS_Container}`).hide();
                    }
                });
                //ocultar el boton siguiente si el selector actual PAS_Pantalla_Ubicacion <= input pantalla_ubicacion
                if (parseInt(selectorActual.PAS_Pantalla_Ubicacion) <= parseInt(document.querySelector(`[name="pantalla_ubicacion"]`).value)) {
                    $(`#btnSiguiente`).attr('disabled', true);
                } else {
                    $(`#btnSiguiente`).attr('disabled', false);
                }
            }

        },
        error: function (xhr, status, error) {
            console.log(error);
        }
    });
}
