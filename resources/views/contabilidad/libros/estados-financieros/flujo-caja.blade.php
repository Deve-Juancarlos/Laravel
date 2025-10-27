@extends('layouts.app')

@section('title', 'Estado de Flujo de Efectivo')

@section('additional_css')
<style>
    .cash-flow-card {
        border-left: 4px solid #007bff;
        transition: transform 0.2s;
    }
    .cash-flow-card:hover {
        transform: translateY(-2px);
    }
    .cash-inflow { border-left-color: #28a745; }
    .cash-outflow { border-left-color: #dc3545; }
    .net-cash-flow { border-left-color: #ffc107; }
    
    .period-selector {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        font-weight: 500;
    }
    
    .cash-flow-summary {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .activity-section {
        margin-bottom: 25px;
    }
    .activity-header {
        background: #f8f9fa;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-weight: 600;
        color: #495057;
    }
    
    .flow-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }
    .flow-item:last-child { border-bottom: none; }
    
    .flow-amount {
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.9em;
    }
    
    .amount-positive {
        background: #d4edda;
        color: #155724;
    }
    .amount-negative {
        background: #f8d7da;
        color: #721c24;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 20px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-dark mb-1">Estado de Flujo de Efectivo</h2>
            <p class="text-muted mb-0">Análisis de movimientos de efectivo por actividades</p>
        </div>
        <div>
            <select class="period-selector" id="periodSelect">
                <option value="enero">Enero 2025</option>
                <option value="febrero">Febrero 2025</option>
                <option value="marzo">Marzo 2025</option>
                <option value="trimestre1">Q1 2025</option>
                <option value="semestre1">Primer Semestre 2025</option>
            </select>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card cash-flow-card cash-inflow">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Efectivo Inicial</h6>
                            <h4 class="text-success mb-0">S/ 125,400.00</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-play-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card cash-flow-card cash-inflow">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Entradas de Efectivo</h6>
                            <h4 class="text-success mb-0">S/ 456,890.00</h4>
                            <small class="text-success">+12.5% vs mes anterior</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-up fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card cash-flow-card cash-outflow">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Salidas de Efectivo</h6>
                            <h4 class="text-danger mb-0">S/ 398,650.00</h4>
                            <small class="text-danger">+8.3% vs mes anterior</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-down fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card cash-flow-card net-cash-flow">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Efectivo Final</h6>
                            <h4 class="text-warning mb-0">S/ 183,640.00</h4>
                            <small class="text-success">+46.4% vs inicial</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Flow Summary -->
    <div class="cash-flow-summary">
        <div class="row text-center">
            <div class="col-md-4">
                <h5>Variación Neta</h5>
                <h3 class="mb-0">S/ +58,240.00</h3>
                <small>Incremento del 46.4%</small>
            </div>
            <div class="col-md-4">
                <h5>Flujo Operativo</h5>
                <h3 class="mb-0">S/ +72,150.00</h3>
                <small>Actividades principales</small>
            </div>
            <div class="col-md-4">
                <h5>Flujo Libre</h5>
                <h3 class="mb-0">S/ +45,890.00</h3>
                <small>Después de inversiones</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Flujo de Actividades Operativas -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs mr-2"></i>Flujo de Actividades Operativas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="activity-section">
                        <div class="activity-header">Entradas de Efectivo</div>
                        <div class="flow-item">
                            <span>Cobros de clientes</span>
                            <span class="flow-amount amount-positive">S/ 425,600.00</span>
                        </div>
                        <div class="flow-item">
                            <span>Otros cobros operativos</span>
                            <span class="flow-amount amount-positive">S/ 12,890.00</span>
                        </div>
                        <div class="flow-item">
                            <span>Devoluciones de impuestos</span>
                            <span class="flow-amount amount-positive">S/ 8,400.00</span>
                        </div>
                        <div class="flow-item">
                            <span><strong>Total Entradas Operativas</strong></span>
                            <span class="flow-amount amount-positive"><strong>S/ 446,890.00</strong></span>
                        </div>
                    </div>

                    <div class="activity-section">
                        <div class="activity-header">Salidas de Efectivo</div>
                        <div class="flow-item">
                            <span>Pagos a proveedores</span>
                            <span class="flow-amount amount-negative">S/ 285,400.00</span>
                        </div>
                        <div class="flow-item">
                            <span>Pagos de sueldos y beneficios</span>
                            <span class="flow-amount amount-negative">S/ 68,900.00</span>
                        </div>
                        <div class="flow-item">
                            <span>Pagos de gastos operativos</span>
                            <span class="flow-amount amount-negative">S/ 20,440.00</span>
                        </div>
                        <div class="flow-item">
                            <span>Pagos de impuestos</span>
                            <span class="flow-amount amount-negative">S/ 15,000.00</span>
                        </div>
                        <div class="flow-item">
                            <span><strong>Total Salidas Operativas</strong></span>
                            <span class="flow-amount amount-negative"><strong>S/ 389,740.00</strong></span>
                        </div>
                    </div>

                    <div class="activity-section">
                        <div class="flow-item">
                            <span><strong>FLUJO NETO OPERATIVO</strong></span>
                            <span class="flow-amount amount-positive"><strong>S/ +57,150.00</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Tendencia -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-area mr-2"></i>Tendencia Mensual
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="cashFlowChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Flujo de Actividades de Inversión -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line mr-2"></i>Flujo de Actividades de Inversión
                    </h5>
                </div>
                <div class="card-body">
                    <div class="activity-section">
                        <div class="activity-header">Entradas</div>
                        <div class="flow-item">
                            <span>Venta de activos fijos</span>
                            <span class="flow-amount amount-positive">S/ 10,000.00</span>
                        </div>
                        <div class="flow-item">
                            <span>Recuperación de inversiones</span>
                            <span class="flow-amount amount-positive">S/ 0.00</span>
                        </div>
                    </div>

                    <div class="activity-section">
                        <div class="activity-header">Salidas</div>
                        <div class="flow-item">
                            <span>Compra de equipos</span>
                            <span class="flow-amount amount-negative">S/ 15,200.00</span>
                        </div>
                        <div class="flow-item">
                            <span>Construcciones en curso</span>
                            <span class="flow-amount amount-negative">S/ 8,500.00</span>
                        </div>
                    </div>

                    <div class="activity-section">
                        <div class="flow-item">
                            <span><strong>FLUJO NETO DE INVERSIÓN</strong></span>
                            <span class="flow-amount amount-negative"><strong>S/ -13,700.00</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flujo de Actividades de Financiamiento -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-university mr-2"></i>Flujo de Actividades de Financiamiento
                    </h5>
                </div>
                <div class="card-body">
                    <div class="activity-section">
                        <div class="activity-header">Entradas</div>
                        <div class="flow-item">
                            <span>Nuevos préstamos</span>
                            <span class="flow-amount amount-positive">S/ 50,000.00</span>
                        </div>
                        <div class="flow-item">
                            <span>Capital adicional</span>
                            <span class="flow-amount amount-positive">S/ 0.00</span>
                        </div>
                    </div>

                    <div class="activity-section">
                        <div class="activity-header">Salidas</div>
                        <div class="flow-item">
                            <span>Pago de préstamos</span>
                            <span class="flow-amount amount-negative">S/ 25,000.00</span>
                        </div>
                        <div class="flow-item">
                            <span>Dividendos pagados</span>
                            <span class="flow-amount amount-negative">S/ 10,210.00</span>
                        </div>
                    </div>

                    <div class="activity-section">
                        <div class="flow-item">
                            <span><strong>FLUJO NETO DE FINANCIAMIENTO</strong></span>
                            <span class="flow-amount amount-positive"><strong>S/ +14,790.00</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis y Comentarios -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-comments mr-2"></i>Análisis del Flujo de Efectivo
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Observaciones Principales:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <strong>Flujo Operativo Positivo:</strong> Las actividades principales generaron S/ 57,150, indicando una operación eficiente.
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <strong>Crecimiento en Cobros:</strong> Los cobros aumentaron 12.5% respecto al mes anterior, mejorando la liquidez.
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                            <strong>Inversiones:</strong> Se realizaron inversiones en equipos por S/ 23,700, lo que impacta el flujo libre.
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-chart-line text-info mr-2"></i>
                            <strong>Liquidez Final:</strong> El efectivo final de S/ 183,640 representa un incremento del 46.4% vs inicial.
                        </li>
                    </ul>

                    <h6 class="mt-4">Recomendaciones:</h6>
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb mr-2"></i>
                        <strong>Sugerencia:</strong> Considerar reinvertir parte del exceso de liquidez en instrumentos de corto plazo para optimizar rendimientos.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-download mr-2"></i>Exportar
                    </h5>
                </div>
                <div class="card-body text-center">
                    <button class="btn btn-primary btn-block mb-2" onclick="exportPDF()">
                        <i class="fas fa-file-pdf mr-2"></i>Descargar PDF
                    </button>
                    <button class="btn btn-success btn-block mb-2" onclick="exportExcel()">
                        <i class="fas fa-file-excel mr-2"></i>Descargar Excel
                    </button>
                    <button class="btn btn-info btn-block" onclick="window.print()">
                        <i class="fas fa-print mr-2"></i>Imprimir
                    </button>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-purple text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog mr-2"></i>Acciones
                    </h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-outline-primary btn-block mb-2" onclick="generarProyeccion()">
                        <i class="fas fa-calculator mr-2"></i>Generar Proyección
                    </button>
                    <button class="btn btn-outline-warning btn-block mb-2" onclick="analizarVariaciones()">
                        <i class="fas fa-chart-bar mr-2"></i>Analizar Variaciones
                    </button>
                    <button class="btn btn-outline-success btn-block" onclick="compararPeriodos()">
                        <i class="fas fa-balance-scale mr-2"></i>Comparar Períodos
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional_js')
<script>
    // Initialize Cash Flow Chart
    function initCashFlowChart() {
        const ctx = document.getElementById('cashFlowChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Flujo Operativo',
                    data: [45000, 52000, 48000, 57150, 58000, 60000],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Flujo Total',
                    data: [35000, 42000, 39000, 58240, 59000, 61000],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Export Functions
    function exportPDF() {
        Swal.fire({
            title: 'Exportando PDF...',
            text: 'Por favor espere mientras se genera el documento',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        // Simulate PDF generation
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'PDF Generado',
                text: 'El estado de flujo de efectivo ha sido exportado exitosamente',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function exportExcel() {
        Swal.fire({
            title: 'Exportando Excel...',
            text: 'Por favor espere mientras se genera el archivo',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Excel Generado',
                text: 'El estado de flujo de efectivo ha sido exportado a Excel',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    // Analysis Functions
    function generarProyeccion() {
        Swal.fire({
            title: 'Generando Proyección',
            text: 'Analizando tendencias para proyección de flujo de efectivo',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            Swal.fire({
                icon: 'info',
                title: 'Proyección Generada',
                text: 'Se ha creado la proyección para los próximos 3 meses',
                timer: 3000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function analizarVariaciones() {
        Swal.fire({
            title: 'Analizando Variaciones',
            text: 'Comparando con períodos anteriores',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Análisis Completado',
                text: 'Se identificaron variaciones significativas en flujo operativo',
                timer: 3000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function compararPeriodos() {
        // Show period comparison modal
        Swal.fire({
            title: 'Comparación de Períodos',
            html: `
                <div class="text-left">
                    <p><strong>Enero 2025:</strong> S/ +45,200</p>
                    <p><strong>Febrero 2025:</strong> S/ +58,240</p>
                    <p><strong>Variación:</strong> <span class="text-success">+28.9%</span></p>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Cerrar'
        });
    }

    // Period change handler
    document.getElementById('periodSelect').addEventListener('change', function() {
        const selectedPeriod = this.value;
        // Update data based on selected period
        Swal.fire({
            title: 'Actualizando datos...',
            text: `Cargando datos para ${selectedPeriod}`,
            timer: 1000,
            showConfirmButton: false
        });
    });

    // Initialize charts when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initCashFlowChart();
        
        // Auto-refresh data every 5 minutes
        setInterval(() => {
            console.log('Refreshing cash flow data...');
            // In real implementation, this would fetch fresh data
        }, 300000);
    });
</script>
@endsection