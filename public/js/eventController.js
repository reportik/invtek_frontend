/**
 * Sistema de control de eventos para selectores
 * Evita que los eventos se activen hasta que los elementos estén completamente cargados
 */

// Variables globales para el control de eventos
let eventosActivados = false;
let selectoresCargados = new Set();
let ultimoSelectorCargado = null;
let eventosPendientes = new Map(); // Para almacenar eventos que se activarán después

/**
 * Activa todos los eventos de los selectores cargados
 */
function activarEventos() {
    if (eventosActivados) return;
    eventosActivados = true;
    console.log('🎯 Eventos activados');

    // Activar eventos para todos los selectores cargados
    selectoresCargados.forEach(selectorNombre => {
        activarEventosSelector(selectorNombre);
    });
}

/**
 * Desactiva todos los eventos
 */
function desactivarEventos() {
    eventosActivados = false;
}

/**
 * Activa eventos para un selector específico
 * @param {string} selectorNombre - Nombre del selector
 */
function activarEventosSelector(selectorNombre) {
    // Activar eventos según el tipo de selector
    const elemento = document.querySelector(`[name="${selectorNombre}"]`);
    if (!elemento) {
        return;
    }

    if (elemento.tagName === 'SELECT') {
        // Para selectpicker
        $(elemento).off('changed.bs.select').on('changed.bs.select', function () {
            if (!eventosActivados) return;
            const seleccion = $(this).val();
            getSelectorSiguiente(selectorNombre, seleccion);
        });

        // También activar evento change normal
        $(elemento).off('change').on('change', function () {
            if (!eventosActivados) return;
            const seleccion = $(this).val();
            getSelectorSiguiente(selectorNombre, seleccion);
        });

    } else if (elemento.type === 'radio' || elemento.type === 'checkbox') {
        // Para radios/checkboxes
        $(`input[name="${selectorNombre}"]`).off('change').on('change', function () {
            if (!eventosActivados) return;
            const seleccion = $(this).val();
            getSelectorSiguiente(selectorNombre, seleccion);
        });

    } else if (elemento.tagName === 'CANVAS') {
        // Para canvas
        $(elemento).off('click').on('click', function () {
            if (!eventosActivados) return;
            const seleccion = $(this).attr('data-value');
            getSelectorSiguiente(selectorNombre, seleccion);
        });

    } else if (elemento.classList.contains('color-option')) {
        // Para opciones de color
        $(elemento).off('click').on('click', function () {
            if (!eventosActivados) return;
            const seleccion = $(this).attr('data-value');
            getSelectorSiguiente(selectorNombre, seleccion);
        });
    }
}

/**
 * Marca un selector como cargado y activa sus eventos
 * @param {string} selectorNombre - Nombre del selector
 */
function marcarSelectorCargado(selectorNombre) {
    selectoresCargados.add(selectorNombre);
    ultimoSelectorCargado = selectorNombre;
    console.log(`📝 Cargado: ${selectorNombre}`);

    // Activar eventos solo para este selector
    setTimeout(() => {
        activarEventosSelector(selectorNombre);
    }, 50);
}

/**
 * Configura eventos para un selector específico sin activarlos inmediatamente
 * @param {string} selectorNombre - Nombre del selector
 * @param {string} tipo - Tipo de selector (select, radio, checkbox, canvas, etc.)
 */
function configurarEventosSelector(selectorNombre, tipo) {
    // Guardar configuración para activar después
    eventosPendientes.set(selectorNombre, tipo);
}

/**
 * Activa eventos solo para el último selector cargado
 */
function activarSoloUltimoSelector() {
    if (ultimoSelectorCargado) {
        activarEventosSelector(ultimoSelectorCargado);
    }
}

/**
 * Verifica si un selector está cargado
 * @param {string} selectorNombre - Nombre del selector
 * @returns {boolean}
 */
function esSelectorCargado(selectorNombre) {
    return selectoresCargados.has(selectorNombre);
}

/**
 * Obtiene el último selector cargado
 * @returns {string|null}
 */
function getUltimoSelectorCargado() {
    return ultimoSelectorCargado;
}

/**
 * Limpia el estado de eventos (útil para reiniciar)
 */
function limpiarEstadoEventos() {
    eventosActivados = false;
    selectoresCargados.clear();
    ultimoSelectorCargado = null;
    eventosPendientes.clear();
}

/**
 * Función para manejar eventos de forma segura
 * Solo ejecuta si los eventos están activados
 * @param {string} selectorNombre - Nombre del selector
 * @param {string} valor - Valor seleccionado
 */
function manejarEventoSeguro(selectorNombre, valor) {
    if (!eventosActivados) {
        return;
    }

    getSelectorSiguiente(selectorNombre, valor);
}

// Exportar funciones para uso global
window.activarEventos = activarEventos;
window.desactivarEventos = desactivarEventos;
window.activarEventosSelector = activarEventosSelector;
window.marcarSelectorCargado = marcarSelectorCargado;
window.configurarEventosSelector = configurarEventosSelector;
window.activarSoloUltimoSelector = activarSoloUltimoSelector;
window.esSelectorCargado = esSelectorCargado;
window.getUltimoSelectorCargado = getUltimoSelectorCargado;
window.limpiarEstadoEventos = limpiarEstadoEventos;
window.manejarEventoSeguro = manejarEventoSeguro;
