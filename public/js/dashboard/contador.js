/**
 * =================================================================
 * JAVASCRIPT PARA EL DASHBOARD DEL CONTADOR (contador.js)
 * =================================================================
 */
document.addEventListener('DOMContentLoaded', function () {

    // Inicia el gráfico del dashboard
    initVentasChart();
    
    // Inicia el botón de limpiar caché
    initCacheButton();

});

/**
 * Dibuja el gráfico de Ventas vs Cobranzas
 */
function initVentasChart() {
    const chartCanvas = document.getElementById('ventasChart');
    
    if (!chartCanvas) {
        // console.log('No se encontró el canvas #ventasChart en esta página.');
        return;
    }

    // 1. Leer los datos desde los atributos data-*
    // Usamos JSON.parse para convertir los strings de vuelta a arrays
    const labels = JSON.parse(chartCanvas.dataset.labels || '[]');
    const ventasData = JSON.parse(chartCanvas.dataset.ventas || '[]');
    const cobranzasData = JSON.parse(chartCanvas.dataset.cobranzas || '[]');

    const ctx = chartCanvas.getContext('2d');

    // Formateador para tooltips
    const tooltipLabelFormatter = (context) => {
        let label = context.dataset.label || '';
        if (label) label += ': ';
        if (context.parsed.y !== null) {
            label += context.parsed.y.toLocaleString('es-PE', { style: 'currency', currency: 'PEN' });
        }
        return label;
    };

    // Formateador para el eje Y
    const yAxisTickFormatter = (value) => 'S/ ' + value.toLocaleString('es-PE');

    // 2. Crear el Gráfico
    new Chart(ctx, {
        type: 'line', // Tipo de gráfico: línea
        data: {
            labels: labels, // Etiquetas del eje X (Meses)
            datasets: [
                {
                    label: 'Ventas (S/)',
                    data: ventasData,
                    borderColor: '#4F46E5', // Color Morado (Principal)
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.4, // Curva suave
                    borderWidth: 3,
                    pointBackgroundColor: '#4F46E5',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Cobranzas (S/)',
                    data: cobranzasData,
                    borderColor: '#10B981', // Color Verde (Éxito)
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4, // Curva suave
                    borderWidth: 2,
                    pointBackgroundColor: '#10B981',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderDash: [5, 5], // Línea punteada
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Permite que el gráfico llene el contenedor
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        // Formatear los números del eje Y como Moneda (S/)
                        callback: yAxisTickFormatter,
                        color: '#6c757d'
                    }
                },
                x: {
                    ticks: {
                        color: '#6c757d'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top', // Leyenda en la parte superior
                    labels: {
                        font: {
                            size: 14
                        }
                    }
                },
                tooltip: {
                    // Tooltips personalizados
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: tooltipLabelFormatter
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index', // Mostrar tooltips para ambos datasets al pasar el mouse
            },
        }
    });
}

/**
 * 🚀 ¡FUNCIÓN CRÍTICA! 🚀
 * Inicializa el botón para limpiar el caché.
 */
function initCacheButton() {
    const btnClearCache = document.getElementById('btnClearCache');
    if (!btnClearCache) {
        // console.log('No se encontró el botón #btnClearCache en esta página.');
        return;
    }

    const btnText = btnClearCache.querySelector('.btn-text');
    const btnSpinner = btnClearCache.querySelector('.btn-spinner');

    // 1. Obtenemos el Token CSRF que pusimos en el <head> de 'layouts/app.blade.php'
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    btnClearCache.addEventListener('click', function() {
        
        if (!csrfToken) {
            console.error('¡Error de CSRF! Token no encontrado. Asegúrate de tener la meta-tag "csrf-token" en tu layout.');
            alert('Error de seguridad. No se pudo limpiar el caché. (Token CSRF no encontrado)');
            return;
        }

        // 2. Mostrar spinner y deshabilitar
        btnText.classList.add('d-none');
        btnSpinner.classList.remove('d-none');
        btnClearCache.disabled = true;

        // 3. Llamar a la API que definimos en 'routes/web.php'
        fetch('/contador/contador/api/clear-cache', {

            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Respuesta del servidor no fue OK: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // 4. ¡Éxito! Recargar la página para ver los datos nuevos (tu venta).
                location.reload();
            } else {
                throw new Error(data.message || 'Error desconocido al limpiar caché');
            }
        })
        .catch(error => {
            console.error('Error al limpiar caché:', error);
            alert('Ocurrió un error al limpiar el caché. Revisa la consola.');
            
            // 5. En caso de error, restaurar el botón
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
            btnClearCache.disabled = false;
        });
    });
}