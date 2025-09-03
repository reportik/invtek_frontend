// public/js/navegacion.js
async function manejarRegreso() {
    const botonesRegresar = document.querySelectorAll('a[name="anterior-vista"]');
    if (botonesRegresar.length === 0) return;

    const valoresSesion = await obtenerValoresSesion() || {};
    console.log('Valores de sesión en manejarRegreso:', valoresSesion);

    // Obtener pila desde valoresSesion.ruta_pantallas o desde valoresSesion.avance_temporal.ruta_pantallas
    let pila = valoresSesion.ruta_pantallas;
    if ((!pila || !Array.isArray(pila)) && valoresSesion.avance_temporal) {
        try {
            const av = typeof valoresSesion.avance_temporal === 'string'
                ? JSON.parse(valoresSesion.avance_temporal)
                : valoresSesion.avance_temporal;
            if (av && Array.isArray(av.ruta_pantallas)) pila = av.ruta_pantallas;
            else if (av && Array.isArray(av['ruta-pantallas'])) pila = av['ruta-pantallas'];
        } catch (e) { console.warn('No se pudo parsear avance_temporal:', e); }
    }
    console.log('Pila en manejarRegreso 1 desde valoresSesion:', pila);
    // Si aún no hay pila, intentar obtenerla desde el servidor
    if (!pila || !Array.isArray(pila)) {
        try {
            const sesionActual = await obtenerSesion('avance_temporal');
            if (sesionActual.success && sesionActual.valor) {
                const av = typeof sesionActual.valor === 'string' ? JSON.parse(sesionActual.valor) : sesionActual.valor;
                if (av && Array.isArray(av.ruta_pantallas)) pila = av.ruta_pantallas;
                else if (av && Array.isArray(av['ruta-pantallas'])) pila = av['ruta-pantallas'];
            }
        } catch (e) { console.warn('No se pudo obtener avance_temporal del servidor:', e); }
    }
    console.log('Pila en manejarRegreso 2 desde obtenerSesion:', pila);
    // Si no hay pila de navegación o está vacía, regresar a inicio
    if (!pila || pila.length <= 1) {
        const rutaInicio = `${routeapp}/inicio`;
        botonesRegresar.forEach(boton => {
            boton.href = rutaInicio;
            boton.onclick = (e) => {
                e.preventDefault();
                window.location.href = rutaInicio;
            };
        });
        return;
    }
    console.log('Pila en manejarRegreso 3:', pila);
    const rutaAnterior = pila[pila.length - 1] || 'inicio';
    const rutaCompleta = `${routeapp}/${rutaAnterior}`;
    console.log('Ruta completa:', rutaCompleta);
    botonesRegresar.forEach(boton => {
        boton.href = rutaCompleta;
        boton.onclick = async (e) => {
            e.preventDefault();
            try {
                // Obtener la pila actual desde el servidor para asegurar que esté fresca
                const sesionActual = await obtenerSesion('avance_temporal');
                let nuevasRutas = pila;
                if (sesionActual.success && sesionActual.valor) {
                    const av = typeof sesionActual.valor === 'string' ? JSON.parse(sesionActual.valor) : sesionActual.valor;
                    if (av && Array.isArray(av.ruta_pantallas)) {
                        nuevasRutas = [...av.ruta_pantallas];
                    } else if (av && Array.isArray(av['ruta-pantallas'])) {
                        nuevasRutas = [...av['ruta-pantallas']];
                    }
                }
                if (nuevasRutas.length > 1) {
                    const pantallaDestino = nuevasRutas[nuevasRutas.length - 2]; // La pantalla a la que vamos
                    nuevasRutas.pop(); // Elimina la pantalla actual de la pila

                    // Limpia la sesión y luego actualiza la pila de rutas
                    await limpiarSesion(pantallaDestino);

                    // Obtiene el estado más reciente de la sesión (ya limpia)
                    const sesionAv = await obtenerSesion('avance_temporal');
                    let av = (sesionAv.success && sesionAv.valor) ? (typeof sesionAv.valor === 'string' ? JSON.parse(sesionAv.valor) : sesionAv.valor) : {};
                    if (!av || typeof av !== 'object') av = {};

                    // Actualiza la pila de rutas
                    av.ruta_pantallas = nuevasRutas;
                    if (av['ruta-pantallas']) delete av['ruta-pantallas']; // Mantenimiento

                    await actualizarSesion('avance_temporal', JSON.stringify(av));
                }
                window.location.href = rutaCompleta;
            } catch (error) {
                console.error('Error al actualizar la navegación:', error);
                window.location.href = rutaCompleta;
            }
        };
    });
}

// Función para actualizar la sesión avance_temporal
async function actualizarSesionAvanceTemporal(clave, valor) {
    try {
        const sesionAv = await obtenerSesion('avance_temporal');
        let av = (sesionAv.success && sesionAv.valor) ? (typeof sesionAv.valor === 'string' ? JSON.parse(sesionAv.valor) : sesionAv.valor) : {};
        av[clave] = valor;
        await actualizarSesion('avance_temporal', JSON.stringify(av));

    } catch (error) {
        console.error('Error en actualizarSesion:', error);
        throw error;
    }
}

// Función para actualizar la sesión vía AJAX
async function actualizarSesion(clave, valor) {
    try {
        const response = await fetch(`${routeapp}/actualizar-sesion`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ clave, valor })
        });

        if (!response.ok) {
            throw new Error('Error al actualizar la sesión');
        }

        return await response.json();
    } catch (error) {
        console.error('Error en actualizarSesion:', error);
        throw error;
    }
}

// Función para obtener un valor de sesión
async function obtenerSesion(clave) {
    try {
        const response = await fetch(`${routeapp}/obtener-sesion?clave=${encodeURIComponent(clave)}`);
        return await response.json();
    } catch (error) {
        console.error('Error al obtener sesión:', error);
        return { success: false };
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', manejarRegreso);

//Funcion para limpiar la sesion hasta la pantalla actual, solo permanecen los valores de la pantalla actual y los anteriores
async function limpiarSesion(nombrePantalla) {
    try {
        // 1. Obtener los selectores a limpiar desde el backend
        const response = await fetch(`${routeapp}/get-selectores-posteriores/${nombrePantalla}`);
        if (!response.ok) {
            throw new Error('No se pudieron obtener los selectores a limpiar.');
        }
        const data = await response.json();

        if (!data.success || !data.selectores || data.selectores.length === 0) {
            console.log('No hay selectores posteriores para limpiar.');
            return; // No hay nada que limpiar
        }

        const selectoresALimpiar = data.selectores;

        // 2. Obtener el estado actual de la sesión
        const avance = await obtenerValoresSesion();
        if (Object.keys(avance).length === 0) return; // La sesión está vacía

        let modificado = false;

        // 3. Eliminar los selectores de la sesión
        selectoresALimpiar.forEach(selector => {
            if (avance.hasOwnProperty(selector)) {
                delete avance[selector];
                modificado = true;
                console.log(`Selector '${selector}' eliminado de la sesión.`);
            }
        });

        // 4. Si se hicieron cambios, actualizar la sesión en el servidor
        if (modificado) {
            await actualizarSesion('avance_temporal', JSON.stringify(avance));
            console.log('Sesión actualizada después de la limpieza.');
        }

    } catch (error) {
        console.error('Error en limpiarSesion:', error);
    }
}