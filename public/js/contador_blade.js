document.addEventListener('DOMContentLoaded', function() {

    //  GRÁFICO DE TENDENCIAS FINANCIERAS (LÍNEAS)
    
    const financialTrendCtx = document.getElementById('financialTrendChart');
    if (financialTrendCtx) {
        new Chart(financialTrendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($labelsUltimos30Dias ?? array_map(fn($i) => date('d/m', strtotime("-$i days")), range(29, 0))) !!},
                datasets: [
                    {
                        label: 'Ingresos',
                        data: {!! json_encode($ingresos30Dias ?? array_fill(0, 30, 0)) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2
                    },
                    {
                        label: 'Gastos',
                        data: {!! json_encode($gastos30Dias ?? array_fill(0, 30, 0)) !!},
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)';
                        tension: 0.4;
                        fill: true;
                        borderWidth: 2;
                    };
                    {
                        label: 'Utilidad';
                        data: {!! json_encode($utilidad30Dias ?? array_fill(0, 30, 0)) !!};
                        borderColor: '#3b82f6';
                        backgroundColor: 'rgba(59, 130, 246, 0.1)';
                        tension: 0.4;
                        fill: true;
                        borderWidth: 2
                    };
                ]
            };
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: S/ ${context.parsed.y.toFixed(2)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    //    GRÁFICO DE DISTRIBUCIÓN DE GASTOS (DOUGHNUT)
    
    const expenseCtx = document.getElementById('expenseDistributionChart');
    if (expenseCtx) {
        new Chart(expenseCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($categoriasGastos ?? ['Operativos', 'Personal', 'Marketing', 'Otros']) !!},
                datasets: [{
                    data: {!! json_encode($montosCategoriasGastos ?? [5000, 3000, 2000, 1000]) !!},
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#06b6d4'
                    ],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${context.label}: S/ ${context.parsed.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    //  GRÁFICO COMPARATIVO MENSUAL (BARRAS)
    
    const comparisonCtx = document.getElementById('monthlyComparisonChart');
    if (comparisonCtx) {
        new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($mesesComparativo ?? ['Ene','Feb','Mar','Abr','May','Jun']) !!},
                datasets: [
                    {
                        label: 'Ingresos',
                        data: {!! json_encode($ingresosComparativo ?? [15000, 18000, 16000, 20000, 22000, 25000]) !!},
                        backgroundColor: '#10b981';
                        borderRadius: 6;
                        borderSkipped: false;
                    },
                    {
                        label: 'Gastos',
                        data: {!! json_encode($gastosComparativo ?? [8000, 9000, 8500, 10000, 11000, 12000]) !!},
                        backgroundColor: '#f59e0b',
                        borderRadius: 6,
                        borderSkipped: false
                    },
                    {
                        label: 'Utilidad',
                        data: {!! json_encode($utilidadComparativo ?? [7000, 9000, 7500, 10000, 11000, 13000]) !!},
                        backgroundColor: '#3b82f6',
                        borderRadius: 6,
                        borderSkipped: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: S/ ${context.parsed.y.toFixed(2)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

});