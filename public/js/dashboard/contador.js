/**
 * JavaScript para el Dashboard del Contador
 * * Lee los datos del canvas y renderiza el gráfico de Ventas vs Cobranzas.
 * Asume que Chart.js (v3 o v4) ya está cargado en la página.
 */
document.addEventListener('DOMContentLoaded', function () {

    const ctx = document.getElementById('ventasChart');

    // Si el canvas no existe en esta página, no hacemos nada.
    if (!ctx) {
        console.warn('No se encontró el elemento canvas #ventasChart');
        return;
    }

    try {
        // 1. Leer los datos desde los atributos 'data-*'
        const labels = JSON.parse(ctx.dataset.labels);
        const ventasData = JSON.parse(ctx.dataset.ventas);
        const cobranzasData = JSON.parse(ctx.dataset.cobranzas);

        // 2. Definir los colores (puedes personalizarlos)
        const colorVentas = '#0d6efd'; // Azul (Primary)
        const colorCobranzas = '#198754'; // Verde (Success)

        // 3. Crear el gráfico
        new Chart(ctx, {
            type: 'bar', // Tipo de gráfico: barras
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Ventas',
                        data: ventasData,
                        backgroundColor: colorVentas,
                        borderColor: colorVentas,
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Cobranzas',
                        data: cobranzasData,
                        backgroundColor: colorCobranzas,
                        borderColor: colorCobranzas,
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Permite que el gráfico llene el 'chart-area'
                plugins: {
                    legend: {
                        position: 'top', // Leyenda arriba
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            // Formatear el tooltip para que muestre "S/"
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('es-PE', {
                                        style: 'currency',
                                        currency: 'PEN'
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            // Formatear el eje Y para que muestre "S/ 10k"
                            callback: function(value, index, values) {
                                if (value >= 1000) {
                                    return 'S/ ' + (value / 1000) + 'k';
                                }
                                return 'S/ ' + value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false // Ocultar líneas de la cuadrícula X
                        }
                    }
                }
            }
        });

    } catch (e) {
        console.error('Error al parsear o renderizar el gráfico:', e);
        ctx.parentElement.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h5>Error al cargar el gráfico</h5><p>Los datos no pudieron ser procesados.</p></div>';
    }
});