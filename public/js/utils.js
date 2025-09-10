async function obtenerValoresSesion() {
  try {
    const response = await fetch(`${routeapp}/obtener-sesion?clave=avance_temporal`);
    const data = await response.json();

    if (data.success && data.valor) {
      // Si el valor es un string, intenta parsearlo a JSON.
      if (typeof data.valor === 'string') {
        try {
          return JSON.parse(data.valor);
        } catch (e) {
          console.error('Error al parsear el valor de la sesión (avance_temporal):', e);
          return {}; // Devuelve un objeto vacío si el parseo falla.
        }
      }
      return data.valor; // Devuelve el valor si ya es un objeto.
    }
    return {}; // Devuelve un objeto vacío si no hay datos.
  } catch (error) {
    console.error('Error al obtener los valores de sesión:', error);
    return {}; // Devuelve un objeto vacío en caso de error de red.
  }
}

/**
 * Asigna automáticamente los valores guardados en sesión (inyectados desde Blade)
 * a los elementos HTML del formulario, incluyendo radios, checkboxes y selectpickers.
 *
 * Requiere:
 * - Que en la vista se inyecte: const valoresSesion = @json(session()->all());
 * - Que los elementos tengan atributos "name" correctos.
 * - Que Selectpickers estén cargados antes de llamar a esta función.
 *
 * @param {Object} valoresSesion - Objeto con los valores de sesión
 * @param {string} selectorEspecifico - Nombre del selector específico al que asignar el valor (opcional)
 * @param {string} valorEspecifico - Valor específico a asignar al selector (opcional)
 */

function asignarValoresDesdeSesion(valoresSesion = {}, selectorEspecifico = null, valorEspecifico = null) {
  // Si se especifica un selector y valor específico, asignar solo ese
  // if (selectorEspecifico && valorEspecifico !== null) {
  //     console.log(`AsignandoValor(): para ${selectorEspecifico}:`, valorEspecifico);
  //     asignarValorAElemento(selectorEspecifico, valorEspecifico);
  //     return;
  // }

  // Comportamiento original: asignar todos los valores
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
          console.log(`AsignandoValor(): ${op.value} como seleccionado`);
          op.checked = true;

        }
      });
    }

    // Canvas
    else if ($elemento.tagName === 'CANVAS') {
      $elemento.setAttribute('data-value', valor);
      console.log(`Asignando data-value al canvas ${key}: ${valor}`);
      //LLENAR INPUTS DEL CANVAS QUE ESTEN VISIBLES, LOS VALORES ESTAN EN LA SESION valoresSesion
      //document.querySelectorAll('.medida-input').forEach(el => el.style.display = 'none');
      document.querySelectorAll('.medida-input').forEach(el => {
        if (valoresSesion[el.name]) {
          el.value = valoresSesion[el.name];
        } else {
          // Si no está en la sesión, asignar valor por defecto de 1
          el.value = '1';
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

/**
 * Función auxiliar para asignar un valor específico a un elemento específico
 * @param {string} nombreSelector - Nombre del selector
 * @param {string|number} valor - Valor a asignar
 */
// function asignarValorAElemento(nombreSelector, valor) {
//   const $elemento = document.querySelector(`[name="${nombreSelector}"]`);
//   if (!$elemento) {
//     console.warn(`No se encontró elemento con name="${nombreSelector}"`);
//     return;
//   }

//   //console.log(`Asignando valor específico para ${nombreSelector}:`, valor);

//   // Radios y Checkboxes
//   if ($elemento.type === 'radio' || $elemento.type === 'checkbox') {
//     const opciones = document.querySelectorAll(`[name="${nombreSelector}"]`);
//     opciones.forEach(op => {
//       if (op.value == valor) {
//         console.log(`Marcando ${op.value} como seleccionado`);
//         op.checked = true;
//       }
//     });
//   }
//   // Canvas
//   else if ($elemento.tagName === 'CANVAS') {
//     $elemento.setAttribute('data-value', valor);
//     console.log(`Asignando data-value al canvas ${nombreSelector}: ${valor}`);
//     //LLENAR INPUTS DEL CANVAS QUE ESTEN VISIBLES CON VALORES POR DEFECTO
//     //document.querySelectorAll('.medida-input').forEach(el => el.style.display = 'none'); //
//     document.querySelectorAll('.medida-input').forEach(el => {
//       // Asignar valor por defecto de 1 para todos los inputs del canvas
//       el.value = '1';
//     });
//   }
//   // Text inputs, hidden, etc.
//   else {
//     //si es un input siguiente-vista
//     if ($elemento.name !== 'siguiente-vista') {
//       $elemento.value = valor;
//     }
//     if ($elemento.name === 'siguiente-vista' && $elemento.value === 'final') {
//       console.log(`RES Asignando valor para ${nombreSelector} (siguiente-vista):`, valor);
//       $elemento.value = 'resumen';
//     } else {
//       //console.log(`Asignando valor para ${nombreSelector}: ${valor}`);
//     }

//     // Si es selectpicker (Bootstrap-select)
//     if ($elemento.classList.contains('selectpicker')) {
//       $($elemento).val(valor).selectpicker('refresh');
//     }
//   }
// }

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
async function fillSelectorElement({ container, element, tipo, data, nombre, triggerSelector }) {
  if (tipo == 'radio') {
    element = document.querySelector(`div[name="radio_${nombre}"]`);
  }
  if (tipo == 'checkbox') {
    element = document.querySelector(`div[name="checkbox_${nombre}"]`);
  }
  if (tipo == 'card') {
    element = document.querySelector(`div[name="card_${nombre}"]`);
  }
  if (tipo == 'select') {
    // Element ya viene correcto para select
  }
  if (tipo == 'div') {
    element = document.querySelector(`div[name="div_${nombre}"]`);
  }

  if (!element) return;
  if (!Array.isArray(data)) data = [];
  if (data.length == 0) {
    let c = document.querySelector(`#${container}`);
    c.innerHTML = '';
    c.empty();
    return;
  };

  // Obtener valores de sesión para determinar qué opción seleccionar
  let valoresSesion = await obtenerValoresSesion();
  let valorSeleccionado = valoresSesion[nombre] || null;
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
      // Seleccionar el valor de la sesión si existe, sino el primero
      if (valorSeleccionado && opt.id_opcion == valorSeleccionado) {
        //el evento no se dispara
        option.selected = true;
      } else if (!valorSeleccionado && idx === 0) {
        //el evento si se dispara al asignar el valor por defecto
        option.selected = true;
      }
      element.appendChild(option);
    });
    //si la opcion seleccionada tiene imagen, entonces mostrar la imagen
    if (data.length > 0) {
      const option = element.querySelector(`option[value="${valorSeleccionado}"]`);
      //si el selector es tipo_confeccion
      if (nombre === 'tipo_confeccion') {
        if (option && option.dataset.img) {

          $('#confeccion_info_card').removeClass('d-none');
          $('#confeccion_nombre').text(option.textContent);
          $('#confeccion_descripcion').text(option.dataset.descripcion || '');
          $('#confeccion_img')
            .attr('src', `${assetapp}/images/cotizador/${option.dataset.img}`)
            .attr('onclick', `showModal('${assetapp}/images/cotizador/${option.dataset.img}')`);

        } else {
          $('#confeccion_info_card').addClass('d-none');

        }
      }
      if (nombre === 'numero_hojas') {
        if (option && option.dataset.img) {
          $('#hojas_img').attr('src', `${assetapp}/images/cotizador/${option.dataset.img}`);
          $('#hojas_nombre').text(option.textContent);
          $('#hojas_descripcion').text(option.dataset.descripcion || '');
          $('#hojas_info_card').removeClass('d-none');
        } else {
          $('#hojas_info_card').addClass('d-none');
        }
      }
    }

    //console.log(element.innerHTML);
    // Si usa selectpicker de bootstrap, refrescar y seleccionar el valor correcto
    if ($(element).hasClass('selectpicker')) {
      $(element).selectpicker('refresh');
      // Seleccionar el valor de la sesión si existe, sino el primero
      if (data.length > 0) {
        let valorAseleccionar = valorSeleccionado || data[0].id_opcion;
        let esValorPorDefecto = !valorSeleccionado; // Si no hay valor de sesión, es por defecto

        // Solo bloquear eventos si es un valor de la sesión (no por defecto)
        if (!esValorPorDefecto) {
          window.asignandoValoresProgramaticamente = true;
        }

        $(element).selectpicker('val', valorAseleccionar);

        // Restaurar el estado después de un pequeño delay solo si se bloqueó
        if (!esValorPorDefecto) {
          setTimeout(() => {
            window.asignandoValoresProgramaticamente = false;
          }, 200);
        }

        //actualizarSesionAvanceTemporal(nombre, valorAseleccionar);
      }
    }
    if (triggerSelector) {
      //add
      $(element).trigger('change');


    }
    //guardar el valor en la sesion avance_temporal

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
      // Seleccionar el valor de la sesión si existe, sino el primero
      console.log('valorSeleccionado: ', valorSeleccionado);
      if (valorSeleccionado && opt.id_opcion == valorSeleccionado) {
        input.checked = true;
      } else if (!valorSeleccionado && idx === 0) {
        input.checked = true;
      }

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

    // Solo activar eventos si es un valor por defecto (no de sesión)
    let esValorPorDefecto = !valorSeleccionado;
    if (esValorPorDefecto || triggerSelector) {
      $('input[name="' + nombre + '"]:checked').trigger('change');
    }
    // if (seleccion) {
    //     // No activar eventos automáticamente, solo marcar como cargado
    //     marcarSelectorCargado(nombre);
    // }
    // renombrar el div del elemento tipo_nombre, que sea div solamente
    // document.querySelector(`div[name="${nombre}"]`).name = `${tipo}_${nombre}`;
    // document.querySelector(`div[name="${nombre}"]`).id = `${tipo}_${nombre}`;

  } else if (tipo === 'canvasx') {
    console.log('FillSelectorElement(): Iniciando configuración de canvas existente');

    // Usar el canvas existente en lugar de crear uno nuevo
    const canvas = document.getElementById("canvas");
    if (!canvas) {
      console.error('1.1.- No se encontró el elemento canvas en el DOM');
      return;
    }

    const ctx = canvas.getContext("2d");
    // Limpiar el canvas existente
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = "#f0f0f0";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Ocultar todos los inputs de medidas primero
    document.querySelectorAll('.medida-input').forEach(el => el.style.display = 'none');

    // Procesar la primera opción (asumimos que viene con datos de imagen y coordenadas)
    if (data.length > 0) {
      //console..log('2.- Datos recibidos:', data);
      const opt = data[0];

      const img = new Image();
      const imgSrc = `${typeof assetapp !== 'undefined' ? assetapp : ''}images/cotizador/${opt.imagen}`;


      // Asignar data-value al canvas de inmediato
      canvas.setAttribute('data-value', opt.id_opcion);


      img.src = imgSrc;
      img.setAttribute('data-id', opt.id_opcion);

      img.onload = () => {

        // Verificar/actualizar data-value por si acaso
        canvas.setAttribute('data-value', opt.id_opcion);


        // Limpiar canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Dibujar imagen centrada

        const scale = Math.min(canvas.width / img.width, canvas.height / img.height);
        const x = (canvas.width - img.width * scale) / 2;
        const y = (canvas.height - img.height * scale) / 2;



        ctx.drawImage(img, x, y, img.width * scale, img.height * scale);

        // Posicionar inputs si hay coordenadas
        if (opt.programacion) {
          //console..log('9.- Coordenadas encontradas:', opt.programacion);
          try {
            const coordenadas = typeof opt.programacion === 'string' ?
              JSON.parse(opt.programacion) : opt.programacion;
            //console..log('10.- Coordenadas parseadas:', coordenadas);
            positionCanvasInputs(coordenadas, valoresSesion);
          } catch (e) {
            console.error('10.- Error al parsear coordenadas:', e);
            console.error('10.1.- Coordenadas que causaron el error:', opt.coordenadas);
          }
        }
      };
      $('#mensajeSeleccion').html(opt.descripcion);
      //getSelectorSiguiente('canvas', opt.id_opcion);
    }

    // Función para posicionar inputs sobre el canvas
    async function positionCanvasInputs(coordenadas, valoresSesion) {
      //console..log('11.- Iniciando  con coordenadas:', coordenadas);

      const canvas = document.getElementById('canvas');
      if (!canvas) {
        console.error('11.1.- No se encontró el elemento canvas');
        return;
      }

      // Obtener los inputs existentes
      const allInputs = document.querySelectorAll('.medida-input');
      //console..log(`12.- Se encontraron ${allInputs.length} inputs existentes`);

      // Ocultar todos los inputs primero
      allInputs.forEach(el => el.style.display = 'none');

      // Mapeo de nombres de inputs a sus IDs
      const inputMap = {
        'inputAlto': 'inputAlto',
        'inputAncho': 'inputAncho',
        'inputLadoA': 'inputLadoA',
        'inputLadoB': 'inputLadoB',
        'inputRadio': 'inputRadio'
      };

      //console..log('12.1.- Mapeo de inputs:', inputMap);

      // Posicionar los inputs
      //console..log('13.- Posicionando inputs');

      let bnd_inSesion = false;
      Object.entries(coordenadas).forEach(([key, pos]) => {
        const inputId = inputMap[key];
        if (!inputId) {
          console.warn(`No se encontró mapeo para la coordenada: ${key}`);
          return;
        }

        const input = document.getElementById(inputId);
        if (!input) {
          console.warn(`No se encontró el input con ID: ${inputId}`);
          return;
        }

        //console..log(`14.- Posicionando input '${key}' (${inputId})`, { x: pos.x, y: pos.y });

        // Aplicar posición usando el enfoque original
        input.style.position = 'absolute';
        input.style.left = `${canvas.offsetLeft + pos.x}px`;
        input.style.top = `${canvas.offsetTop + pos.y}px`;
        input.style.width = '60px';
        input.style.zIndex = '10';
        input.style.display = 'block';

        // Llenar el input con el valor de la sesión si existe valoresSesion
        if (valoresSesion[input.name]) {
          input.value = valoresSesion[input.name];
          bnd_inSesion = true;
        } else {
          // Si no está en la sesión, asignar valor por defecto de 1
          input.value = '1';
        }


        //console..log(`15.- Input '${key}' posicionado en:`, {
        //   left: input.style.left,
        //     top: input.style.top,
        //       canvasOffset: { left: canvas.offsetLeft, top: canvas.offsetTop },
        //   inputPosition: { left: pos.x, top: pos.y }
        // });
      });

      const canvasValue = document.getElementById('canvas').getAttribute('data-value');
      //console.log('Valor del canvas:', canvasValue);
      // Llamar a la función getSelectorSiguiente con el valor del canvas
      if (canvasValue && !bnd_inSesion) { //si el valor del canvas no estába en la sesión, llamar a la función getSelectorSiguiente
        getSelectorSiguiente('canvas', canvasValue);
      }

      //console..log('16.- Todos los inputs han sido posicionados');
      //actualizarValoresCanvas();

      /* // Agregar event listener para reposicionar en resize
      window.addEventListener('resize', () => {
         //console..log('17.- Redimensionando ventana, reposicionando inputs...');
          positionCanvasInputs(coordenadas);
      }); */
    }


  } else if (tipo === 'card') {
    // Cards con input, imagen y color
    element.innerHTML = '';
    //order by opt.valor
    data.sort((a, b) => a.valor.localeCompare(b.valor));
    const valorSel = valorSeleccionado;
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
      // Seleccionar el valor de la sesión si existe, sino el primero
      console.log('**valorSeleccionado: ', valorSel);
      console.log('**opt.id_opcion: ', opt.id_opcion);
      if (opt.id_opcion == valorSel) {
        input.checked = true;
      } else if (!valorSel && idx === 0) {
        input.checked = true;
      }

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
    console.log('**nombre: ', nombre);
    console.log('**seleccion: ', seleccion);
    if (nombre == 'tipo_material') {
      window.cargandoSelectores = false;
      triggerSelector = true;
      $(element).trigger('change');
      // let selectContainer = document.getElementById('div_sel_material');
      // let modalContainer = document.getElementById('telas-container');
      // fetchAndFillProductosByCategory(seleccion, selectContainer, modalContainer);
      //$('#producto_categoria_selector').selectpicker('val', seleccion);
      $('#producto_categoria_selector').val(seleccion).selectpicker('refresh');
    }

    // Solo activar eventos si es un valor por defecto (no de sesión)
    let esValorPorDefecto = !valorSeleccionado;
    //trigger
    console.log('**triggerSelector: ', triggerSelector);
    if (esValorPorDefecto || triggerSelector) {
      console.log('**activando evento change: ', nombre);
      window.cargandoSelectores = false;
      $('input[name="' + nombre + '"]:checked').trigger('change');
      //$('div[name="' + nombre + '"]:checked').trigger('change');


    }

    // if (seleccion) {
    //     // No activar eventos automáticamente, solo marcar como cargado
    //     marcarSelectorCargado(nombre);
    // }
  } else if (tipo === 'div') {
    // Limpiar el contenido
    element.innerHTML = '';
    // Ordenar por nombre
    data.sort((a, b) => a.valor.localeCompare(b.valor));

    // Crear contenedor para los colores
    const container = document.createElement('div');
    container.className = 'd-flex flex-wrap gap-2';

    // Contenedor para la descripción
    const descripcionContainer = document.createElement('div');
    descripcionContainer.className = 'mt-2 text-muted small';
    descripcionContainer.id = `descripcion-${nombre}`;

    data.forEach((opt, idx) => {
      // Crear el div del color
      const colorDiv = document.createElement('div');
      colorDiv.className = 'color-option position-relative';
      colorDiv.style.backgroundColor = opt.programacion || '';
      colorDiv.setAttribute('data-value', opt.id_opcion);
      colorDiv.setAttribute('data-descripcion', opt.descripcion || opt.valor);
      colorDiv.setAttribute('title', opt.descripcion || opt.valor);
      colorDiv.id = `${tipo}_${nombre}_${idx}`;
      //actualizarSesionAvanceTemporal(nombre, opt.id_opcion);
      // Seleccionar el valor de la sesión si existe, sino el primero
      if ((valorSeleccionado && opt.id_opcion == valorSeleccionado) || (!valorSeleccionado && idx === 0)) {
        colorDiv.classList.add('selected');
        document.querySelector(`[name="${nombre}"]`).value = opt.id_opcion;
        descripcionContainer.textContent = opt.descripcion || opt.valor;

        setTimeout(() => {

          // Solo activar eventos si es un valor por defecto (no de sesión)
          let esValorPorDefecto = !valorSeleccionado;
          if (esValorPorDefecto || triggerSelector) {
            getSelectorSiguiente(nombre, opt.id_opcion);
          }

        }, 100);
      }

      // Evento click para seleccionar
      colorDiv.addEventListener('click', function () {

        // Remueve selección de todos
        element.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
        this.classList.add('selected');
        // Actualizar el valor del input hidden
        document.querySelector(`[name="${nombre}"]`).value = this.getAttribute('data-value');
        // Actualizar la descripción
        descripcionContainer.textContent = this.getAttribute('data-descripcion');
        // Cargar dependencias
        getSelectorSiguiente(nombre, this.getAttribute('data-value'));

      });

      // Mostrar descripción al pasar el cursor
      colorDiv.addEventListener('mouseenter', function () {
        descripcionContainer.textContent = this.getAttribute('data-descripcion');
      });

      // Restaurar la descripción del elemento seleccionado al salir
      colorDiv.addEventListener('mouseleave', function () {
        const selected = element.querySelector('.color-option.selected');
        if (selected) {
          descripcionContainer.textContent = selected.getAttribute('data-descripcion');
        }
      });

      container.appendChild(colorDiv);
    });

    // Agregar los elementos al contenedor principal
    element.appendChild(container);
    element.appendChild(descripcionContainer);
  }

}
function getSelectorAndFill(nombreSelector, valor, pantalla) {
  //obtener el selector anterior
  //console.log('playload getSelectorAndFill: ', nombreSelector, valor, pantalla);
  $.ajax({
    url: routeapp + '/get-selector-actual',
    type: 'POST',
    data: {
      nombre_selector: nombreSelector,
      valor: valor,
      pantalla: pantalla,
    },
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    success: async function (response) {
      //console.log('getSelectorAndFill(): ', response);

      // Llenar el selector anterior
      //console.log('getSelectorAndFill selector: ', response.selector_nombre);
      $(`#${response.selector_container}`).show();
      //console.log(document.querySelector(`[name="${response.selector_nombre}"]`));
      await fillSelectorElement({
        container: response.selector_container,
        element: document.querySelector(`[name="${response.selector_nombre}"]`),
        tipo: response.selector_tipo,
        data: response.data,
        nombre: response.selector_nombre,
        triggerSelector: false
      });

      // Ya no es necesario asignar valores de sesión aquí porque fillSelectorElement lo hace directamente
      // let valoresSesion = await obtenerValoresSesion();
      // if (valoresSesion[response.selector_nombre]) {
      //   console.log(`getSelectorAndFill(): Asignando valor de sesión al selector recién llenado: ${response.selector_nombre} = ${valoresSesion[response.selector_nombre]}`);
      //   asignarValoresDesdeSesion(valoresSesion, response.selector_nombre, valoresSesion[response.selector_nombre]);
      // }
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
  console.log('**Iniciando getSelectorSiguiente: ', nombreSelector, valor);
  $.ajax({
    url: url,
    type: 'POST',
    data: data,
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    success: async function (response) {
      // Desactivar eventos temporalmente durante la carga
      //desactivarEventos();

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
        //console.log('llenando getSelectorSiguiente: ', response.selector_nombre);
        //console.log(document.querySelector(`[name="${response.selector_nombre}"]`));
        //console..log('tipo: ', response.selector_tipo);
        //console..log('data: ', response.data);
        //console.log('nombre: ', response.selector_nombre);
        //console.log('elemento: ', document.querySelector(`[name="${response.selector_nombre}"]`));
        await fillSelectorElement({
          container: response.selector_container,
          element: document.querySelector(`[name="${response.selector_nombre}"]`),
          tipo: response.selector_tipo,
          data: response.data,
          nombre: response.selector_nombre,
          triggerSelector: true
        });

        console.log('SELECTOR_SIGUIENTE(): response.selector_nombre: ', response.selector_nombre);
        let selectorSiguiente = selectores.find(selector => selector.PAS_Html_name === response.selector_nombre);
        /* //ocultar el boton siguiente si el selector siguiente tiene PAS_Pantalla_Ubicacion <= input pantalla_ubicacion
        //obtener el orden del selector siguiente
        console.log('selector siguiente: ', selectorSiguiente);
        if (selectorSiguiente) {
            //console.log('****************selector siguiente************: ', selectorSiguiente);
            //ocultar seletores mayores que el actual nombreSelector
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
                } /*
            });
        } */
        //console.log('selector siguiente: ', selectorSiguiente);
        //console.log('pantalla ubicacion: ', document.querySelector(`[name="pantalla_ubicacion"]`).value);

        if (selectorSiguiente && parseInt(selectorSiguiente.PAS_Pantalla_Ubicacion) <= parseInt(document.querySelector(`[name="pantalla_ubicacion"]`).value)) {
          //console.log('desactivando boton siguiente');
          $(`#btnSiguiente`).attr('disabled', true);

        } else {
          $(`#btnSiguiente`).attr('disabled', false);
        }


        // Ya no es necesario asignar valores de sesión aquí porque fillSelectorElement lo hace directamente
        // let valoresSesion = await obtenerValoresSesion();
        // console.log('valores de sesion: ', valoresSesion);
        // if (valoresSesion[response.selector_nombre]) {
        //   console.log(`getSelectorAndFill(): Asignando valor de sesión al selector recién llenado: ${response.selector_nombre} = ${valoresSesion[response.selector_nombre]}`);
        //   asignarValoresDesdeSesion(valoresSesion, response.selector_nombre, valoresSesion[response.selector_nombre]);
        // }

        // Obtener valores de sesión para mostrar/ocultar selectores
        let valoresSesion = await obtenerValoresSesion();
        selectores.forEach(selector => {
          if (!valoresSesion[selector.PAS_Html_name]) {
            //console.log('SEL SIG ocultando selector: ', selector.PAS_Html_name);
            $(`#${selector.PAS_Container}`).hide();
          } else {
            console.log('SELECTOR_SIGUIENTE(): mostrando selector: ', selector.PAS_Html_name);
            $(`#${selector.PAS_Container}`).show();
          }
        });
        //mostrar selector
        $(`#${response.selector_container}`).show();


      }
      else {
        console.log('SELECTOR_SIGUIENTE():  no encontrado');
        $(`#btnSiguiente`).attr('disabled', true);
        //obtener el orden del selector actual
        let selectorActual = selectores.find(selector => selector.PAS_Html_name === nombreSelector);
        console.log('****************selector actual************: ', selectorActual);
        //ocultar Los seletores mayores que el actual nombreSelector
        console.log('Ocultar selectores mayores que el actual: ', selectorActual.PAS_Orden);
        selectores.forEach(selector => {

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
      // // Reactivar eventos en caso de error
      // setTimeout(() => {
      //     activarEventos();
      // }, 100);
    }


  });
}

/**
 * Función específica para asignar valores desde sesión SIN lanzar eventos
 * Se usa durante la carga inicial de la página
 */
function asignarValoresDesdeSesionSinEventos(valoresSesion = {}) {
  for (const [key, valor] of Object.entries(valoresSesion)) {
    const $elemento = document.querySelector(`[name="${key}"]`);
    if (!$elemento) continue;
    console.log('asignarValoresDesdeSesion(): ', $elemento, $elemento.tagName, $elemento.type);
    // Radios y Checkboxes
    if ($elemento.type === 'radio' || $elemento.type === 'checkbox') {
      const opciones = document.querySelectorAll(`[name="${key}"]`);
      opciones.forEach(op => {
        if (op.value == valor) {
          op.checked = true;
        }
      });
    }
    // Selectpicker
    else if ($elemento.tagName === 'SELECT') {
      $(`[name="${key}"]`).selectpicker('val', valor);
    }
    // Inputs de texto
    else if ($elemento.tagName === 'INPUT' && $elemento.type === 'text') {
      $elemento.value = valor;
    }
    // Canvas
    else if ($elemento.tagName === 'CANVAS') {
      $elemento.setAttribute('data-value', valor);
    }
  }
}

// Exportar función para uso global
window.asignarValoresDesdeSesionSinEventos = asignarValoresDesdeSesionSinEventos;
