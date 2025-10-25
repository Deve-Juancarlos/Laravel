@extends('layouts.app')

@section('title', 'Tendencias de Ventas')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('ventas.dashboard.index') }}">Ventas</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('ventas.dashboard.index') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Tendencias</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-chart-area text-primary"></i>
                        Análisis de Tendencias
                    </h1>
                    <p class="text-muted mb-0">Análisis predictivo y patrones de comportamiento en ventas</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="generarProyeccion()">
                            <i class="fas fa-crystal-ball"></i> Proyectar
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="compararPeriodos()">
                            <i class="fas fa-balance-scale"></i> Comparar
                        </button>
                        <a href="{{ route('ventas.dashboard.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Selector de Análisis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h6 class="mb-2">Tipo de Análisis</h6>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="analisis" id="ventas" value="ventas" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="ventas">Ventas</label>

                                <input type="radio" class="btn-check" name="analisis" id="productos" value="productos" autocomplete="off">
                                <label class="btn btn-outline-primary" for="productos">Productos</label>

                                <input type="radio" class="btn-check" name="analisis" id="clientes" value="clientes" autocomplete="off">
                                <label class="btn btn-outline-primary" for="clientes">Clientes</label>

                                <input type="radio" class="btn-check" name="analisis" id="temporal" value="temporal" autocomplete="off">
                                <label class="btn btn-outline-primary" for="temporal">Temporal</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">Período de Comparación</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <select class="form-select" id="periodoBase">
                                        <option value="mes_actual" selected>Mes Actual</option>
                                        <option value="mes_anterior">Mes Anterior</option>
                                        <option value="trimestre">Trimestre</option>
                                        <option value="año">Año</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select class="form-select" id="periodoComparacion">
                                        <option value="mes_anterior">vs Mes Anterior</option>
                                        <option value="año_anterior">vs Año Anterior</option>
                                        <option value="promedio">vs Promedio</option>
                                        <option value="meta">vs Meta</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicadores de Tendencia -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Tendencia Ventas</p>
                            <h4 class="text-success mb-0">↗ Creciente</h4>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> +15.2% mensual
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-trending-up text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Crecimiento Clientes</p>
                            <h4 class="text-info mb-0">↗ Acelerado</h4>
                            <small class="text-info">
                                <i class="fas fa-arrow-up"></i> +22.5% mensual
                            </small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-users text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Rotación Productos</p>
                            <h4 class="text-warning mb-0">→ Estable</h4>
                            <small class="text-warning">
                                <i class="fas fa-minus"></i> ±0% variación
                            </small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-sync text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Estacionalidad</p>
                            <h4 class="text-danger mb-0">↘ Decreciente</h4>
                            <small class="text-danger">
                                <i class="fas fa-arrow-down"></i> -8.3% semanal
                            </small>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="fas fa-calendar-times text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Tendencias -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line text-primary"></i>
                        Evolución y Proyección de Ventas
                    </h5>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary active" data-periodo="mensual">Mensual</button>
                        <button class="btn btn-sm btn-outline-secondary" data-periodo="semanal">Semanal</button>
                        <button class="btn btn-sm btn-outline-secondary" data-periodo="diario">Diario</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 400px;">
                        <canvas id="tendenciasVentasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-percentage text-info"></i>
                        Análisis de Crecimiento
                    </h5>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Crecimiento Mensual</span>
                            <div class="text-end">
                                <strong class="text-success">+15.2%</strong>
                                <i class="fas fa-arrow-up text-success ms-1"></i>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 76%"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Tendencia 3 Meses</span>
                            <div class="text-end">
                                <strong class="text-primary">+38.5%</strong>
                                <i class="fas fa-arrow-up text-primary ms-1"></i>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: 85%"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Proyección Trimestre</span>
                            <div class="text-end">
                                <strong class="text-info">+45.8%</strong>
                                <i class="fas fa-arrow-up text-info ms-1"></i>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: 92%"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Meta Anual</span>
                            <div class="text-end">
                                <strong class="text-warning">78%</strong>
                                <i class="fas fa-bullseye text-warning ms-1"></i>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: 78%"></div>
                        </div>

                        <hr class="my-3">

                        <div class="text-center">
                            <h6 class="text-muted mb-2">Confianza en Proyección</h6>
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="progress-circle me-3">
                                    <span class="progress-circle-text">87%</span>
                                </div>
                                <div class="text-start">
                                    <small class="text-muted">Basado en 12 meses de datos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis Comparativo -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar text-warning"></i>
                        Comparación por Productos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="productosComparacionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie text-success"></i>
                        Distribución de Ventas por Categoría
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="categoriasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patrones Estacionales -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt text-primary"></i>
                        Patrones Estacionales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="estacionalidadChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb text-warning"></i>
                        Insights de Tendencias
                    </h5>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        <div class="alert alert-success border-0">
                            <div class="d-flex">
                                <i class="fas fa-check-circle me-2 mt-1"></i>
                                <div>
                                    <strong>Peak de Ventas</strong>
                                    <p class="mb-1">Domingos muestran 23% mayor volumen</p>
                                    <small>Recomendación: Incrementar stock</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info border-0">
                            <div class="d-flex">
                                <i class="fas fa-info-circle me-2 mt-1"></i>
                                <div>
                                    <strong>Producto Estrella</strong>
                                    <p class="mb-1">Paracetamol crece 45% mensual</p>
                                    <small>Aprovechar temporada alta</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning border-0">
                            <div class="d-flex">
                                <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                <div>
                                    <strong>Atención Requerida</strong>
                                    <p class="mb-1">Ventas de antibióticos bajan 12%</p>
                                    <small>Revisar estrategias promocionales</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-primary border-0">
                            <div class="d-flex">
                                <i class="fas fa-rocket me-2 mt-1"></i>
                                <div>
                                    <strong>Oportunidad</strong>
                                    <p class="mb-1">Clientes nuevos +28% vs mes anterior</p>
                                    <small>Potencial de fidelización alto</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Predicciones y Recomendaciones -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-brain text-purple"></i>
                        Predicciones y Recomendaciones IA
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="text-center p-4 bg-light rounded">
                                <i class="fas fa-crystal-ball text-primary fs-1 mb-3"></i>
                                <h5 class="text-primary">Próximo Mes</h5>
                                <h3 class="text-success mb-2">S/ 325,000</h3>
                                <p class="text-muted mb-3">Proyección basada en tendencias actuales</p>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: 92%"></div>
                                </div>
                                <small class="text-success">Confianza: 92%</small>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="text-center p-4 bg-light rounded">
                                <i class="fas fa-chart-line text-info fs-1 mb-3"></i>
                                <h5 class="text-info">Trimestre</h5>
                                <h3 class="text-info mb-2">+38%</h3>
                                <p class="text-muted mb-3">Crecimiento esperado vs trimestre anterior</p>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: 85%"></div>
                                </div>
                                <small class="text-info">Confianza: 85%</small>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="text-center p-4 bg-light rounded">
                                <i class="fas fa-bullseye text-warning fs-1 mb-3"></i>
                                <h5 class="text-warning">Meta Anual</h5>
                                <h3 class="text-warning mb-2">98%</h3>
                                <p class="text-muted mb-3">Probabilidad de alcanzar objetivo anual</p>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: 98%"></div>
                                </div>
                                <small class="text-warning">Confianza: 98%</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Recomendaciones Automáticas</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-arrow-up text-success me-2"></i>
                                    <strong>Incrementar inventario:</strong> Paracetamol, Ibuprofeno
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-tag text-info me-2"></i>
                                    <strong>Promoción sugerida:</strong> Vitaminas (temporada baja)
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    <strong>Capacitación:</strong> Productos nuevos para vendedores
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-calendar text-warning me-2"></i>
                                    <strong>Planificar:</strong> Campaña estacional próxima semana
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Alertas Inteligentes</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                    <strong>Stock crítico:</strong> Amoxicilina (5 unidades restantes)
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-clock text-danger me-2"></i>
                                    <strong>Vencimientos:</strong> 3 productos con 30 días
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-chart-line text-success me-2"></i>
                                    <strong>Tendencia positiva:</strong> Productos naturales +45%
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-bell text-info me-2"></i>
                                    <strong>Recordatorio:</strong> Revisar metas deQ4
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Gráfico de Tendencias de Ventas
    const tendenciasCtx = document.getElementById('tendenciasVentasChart').getContext('2d');
    new Chart(tendenciasCtx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'Ventas Reales',
                data: [180, 195, 210, 225, 245, 260, 275, 285, 290, 305, 320, 335],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Proyección',
                data: [null, null, null, null, null, null, null, null, null, 305, 325, 350],
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                borderDash: [5, 5],
                tension: 0.4,
                fill: false
            }, {
                label: 'Meta',
                data: [200, 200, 220, 220, 240, 240, 260, 260, 280, 280, 300, 300],
                borderColor: '#dc3545',
                borderDash: [10, 5],
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + value + 'k';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Comparación por Productos
    const productosCtx = document.getElementById('productosComparacionChart').getContext('2d');
    new Chart(productosCtx, {
        type: 'bar',
        data: {
            labels: ['Paracetamol', 'Ibuprofeno', 'Amoxicilina', 'Omeprazol', 'Loratadina'],
            datasets: [{
                label: 'Mes Anterior',
                data: [45, 38, 32, 28, 22],
                backgroundColor: 'rgba(13, 110, 253, 0.7)'
            }, {
                label: 'Mes Actual',
                data: [65, 42, 38, 32, 25],
                backgroundColor: 'rgba(25, 135, 84, 0.7)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Categorías
    const categoriasCtx = document.getElementById('categoriasChart').getContext('2d');
    new Chart(categoriasCtx, {
        type: 'doughnut',
        data: {
            labels: ['Analgésicos', 'Antibióticos', 'Digestivos', 'Respiratorios', 'Otros'],
            datasets: [{
                data: [35, 25, 20, 15, 5],
                backgroundColor: [
                    '#0d6efd',
                    '#6610f2',
                    '#6f42c1',
                    '#d63384',
                    '#fd7e14'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Gráfico de Estacionalidad
    const estacionalidadCtx = document.getElementById('estacionalidadChart').getContext('2d');
    new Chart(estacionalidadCtx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [{
                label: 'Ventas Promedio',
                data: [85, 92, 88, 95, 102, 118, 125],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });

    // Event listeners
    $('input[name="analisis"]').on('change', function() {
        const tipo = $(this).val();
        actualizarAnalisis(tipo);
    });

    $('[data-periodo]').on('click', function() {
        $('[data-periodo]').removeClass('active');
        $(this).addClass('active');
        const periodo = $(this).data('periodo');
        actualizarPeriodo(periodo);
    });
});

function generarProyeccion() {
    Swal.fire({
        title: 'Generando Proyección con IA...',
        html: `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>Analizando patrones de ventas...</p>
                <small class="text-muted">Esto puede tomar unos segundos</small>
            </div>
        `,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });

    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Proyección generada',
            html: `
                <div class="text-left">
                    <h6>Próximos 3 meses:</h6>
                    <ul>
                        <li>Noviembre: S/ 325,000 (+8.5%)</li>
                        <li>Diciembre: S/ 350,000 (+7.7%)</li>
                        <li>Enero: S/ 315,000 (-10.0%)</li>
                    </ul>
                    <p class="mb-0"><small>Confianza: 87%</small></p>
                </div>
            `,
            confirmButtonText: 'Ver Detalles'
        });
    }, 3000);
}

function compararPeriodos() {
    const comparaciones = [
        'Mes actual vs Mes anterior',
        'Trimestre actual vs Trimestre anterior',
        'Año actual vs Año anterior',
        'Período personalizado'
    ];

    Swal.fire({
        title: 'Comparar Períodos',
        input: 'select',
        inputOptions: Object.fromEntries(comparaciones.map(item => [item, item])),
        inputPlaceholder: 'Selecciona una comparación',
        showCancelButton: true,
        confirmButtonText: 'Comparar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Comparación generada',
                html: `
                    <div class="text-left">
                        <h6>Resultados de la comparación:</h6>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between">
                                <span>Ventas:</span>
                                <span class="text-success">+15.2% ↗</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Clientes:</span>
                                <span class="text-success">+22.5% ↗</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Tickets promedio:</span>
                                <span class="text-success">+3.8% ↗</span>
                            </div>
                        </div>
                    </div>
                `,
                confirmButtonText: 'Ver Reporte Completo'
            });
        }
    });
}

function actualizarAnalisis(tipo) {
    console.log('Actualizando análisis para:', tipo);
    // Aquí iría la lógica para actualizar los gráficos según el tipo
}

function actualizarPeriodo(periodo) {
    console.log('Actualizando período a:', periodo);
    // Aquí iría la lógica para actualizar los datos según el período
}
</script>
@endsection

@section('styles')
<style>
.chart-container {
    position: relative;
    height: 100%;
    width: 100%;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.btn-check:checked + .btn {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.space-y-3 > * + * {
    margin-top: 0.75rem !important;
}

.progress-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: conic-gradient(#28a745 0deg 314deg, #e9ecef 314deg);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.progress-circle::before {
    content: '';
    position: absolute;
    width: 45px;
    height: 45px;
    background: white;
    border-radius: 50%;
}

.progress-circle-text {
    position: relative;
    z-index: 1;
    font-size: 14px;
    font-weight: bold;
    color: #28a745;
}

.alert {
    margin-bottom: 1rem;
}

.text-purple {
    color: #6f42c1 !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>
@endsection