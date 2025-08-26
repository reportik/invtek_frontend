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
                    nuevasRutas.pop();
                    // Actualizar la sesión en el servidor sobre avance_temporal completo
                    const sesionAv = await obtenerSesion('avance_temporal');
                    let av = (sesionAv.success && sesionAv.valor) ? (typeof sesionAv.valor === 'string' ? JSON.parse(sesionAv.valor) : sesionAv.valor) : {};
                    if (!av || typeof av !== 'object') av = {};
                    av.ruta_pantallas = nuevasRutas;
                    // Mantener consistencia eliminando la clave con guión si existe
                    if (av['ruta-pantallas']) delete av['ruta-pantallas'];
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