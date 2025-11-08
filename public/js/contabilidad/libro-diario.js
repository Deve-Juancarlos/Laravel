// Funciones de exportación (requieren los parámetros de filtro)
function getFilterParams() {
    return new URLSearchParams(window.location.search);
}

function exportarExcel() {
    const params = getFilterParams();
    params.set('formato', 'excel');
    // Asegúrate de que la ruta 'contador.libro-diario.exportar' exista en tus rutas de Laravel
    window.location.href = `/contador/libro-diario/exportar?${params.toString()}`;
}

function exportarPDF() {
    const params = getFilterParams();
    params.set('formato', 'pdf');
    // Asegúrate de que la ruta 'contador.libro-diario.exportar' exista
    window.location.href = `/contador/libro-diario/exportar?${params.toString()}`;
}

// Animación de números al cargar
function animateValue(element, start, end, duration) {
    const range = end - start;
    if (range === 0) {
        let formattedValue = (end.toString().includes('.')) ? end.toFixed(2) : end.toString();
        if (element.textContent.startsWith('S/ ')) {
            element.textContent = 'S/ ' + formattedValue;
        } else {
            element.textContent = formattedValue;
        }
        return;
    }
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        
        const isDecimal = current.toString().includes('.') || end.toString().includes('.');
        const isCurrency = element.textContent.startsWith('S/ ');

        let formattedValue = isDecimal ? 
            current.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : 
            current.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        
        element.textContent = isCurrency ? 'S/ ' + formattedValue : formattedValue;
    }, 16);
}

// Listener para ejecutar las animaciones cuando la página cargue
window.addEventListener('load', function() {
    document.querySelectorAll('.stat-value').forEach(element => {
        const value = parseFloat(element.getAttribute('data-value')) || 0;
        const startValue = 0;
        
        if (element.textContent.startsWith('S/ ')) {
            element.textContent = 'S/ 0.00';
            animateValue(element, startValue, value, 1000);
        } else {
            element.textContent = '0';
            animateValue(element, startValue, value, 1000);
        }
    });
});


// Confirmación de eliminación (SweetAlert2)
function confirmarEliminacion(id) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción eliminará el asiento contable permanentemente. No se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Muestra el overlay de carga (función global de tu layout)
                if(typeof showLoading === 'function') showLoading();
                document.getElementById('delete-form-' + id).submit();
            }
        });
    } else {
        // Fallback si SweetAlert no carga
        if (confirm('¿Estás seguro de eliminar este asiento? Esta acción no se puede deshacer.')) {
            if(typeof showLoading === 'function') showLoading();
            document.getElementById('delete-form-' + id).submit();
        }
    }
}

/* Nota: En el JS he reemplazado las rutas de Blade {{ route(...) }} por rutas relativas
   como '/contador/libro-diario/exportar'. 
   Si tus rutas son diferentes, deberás ajustar estas URLs en el JS.
   La función 'confirmarEliminacion' no necesita cambios porque es llamada
   directamente desde el HTML de Blade.
*/