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
            console.log(`Asignando valor para ${key} (radio/checkbox):`, valor);
            const opciones = document.querySelectorAll(`[name="${key}"]`);
            opciones.forEach(op => {
                if (op.value == valor){
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
            }else{
                console.log(`Asignando valor para ${key} (siguiente-vista):`, valor);
                
            }

            // Si es selectpicker (Bootstrap-select)
            if ($elemento.classList.contains('selectpicker')) {
                $($elemento).val(valor).selectpicker('refresh');
            }
        }
    }
}
