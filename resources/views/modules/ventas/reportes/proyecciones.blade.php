@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-crystal-ball text-primary"></i> Proyecciones de Ventas
        </h1>
        <div>
            <button class="btn btn-outline-secondary" onclick="exportarExcel()">
                <i class="fas fa-file-excel"></i> Excel
            </button>
            <button class="btn btn-outline-danger" onclick="exportarPDF()">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
            <button class="btn btn-primary" onclick="actualizarProyecciones()">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Configuración de Proyecciones</h6>
        </div>
        <div class="card-body">
            <form id="proyeccionesForm" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha Base</label>
                            <input type="date" class="form-control" name="fecha_base" value="{{ request('fecha_base', date('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Período Proyección</label>
                            <select class="form-control" name="periodo">
                                <option value="3">3 Meses</option>
                                <option value="6" selected>6 Meses</option>
                                <option value="12">12 Meses</option>
                                <option value="24">24 Meses</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Método</label>
                            <select class="form-control" name="metodo">
                                <option value="lineal" selected>Regresión Lineal</option>
                                <option value="exponencial">Crecimiento Exponencial</option>
                                <option value="promedio">Promedio Móvil</option>
                                <option value="tendencia">Análisis de Tendencia</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Confianza</label>
                            <select class="form-control" name="confianza">
                                <option value="80">80%</option>
                                <option value="90" selected>90%</option>
                                <option value="95">95%</option>
                                <option value="99">99%</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calculator"></i> Calcular Proyecciones
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs de Proyección -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Proyección 6 Meses
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$2,156,890</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +8.7% vs histórico
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Crecimiento Esperado
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">12.3%</div>
                            <div class="text-xs text-success">
                                Anual proyectado
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Margen Proyectado
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$678,923</div>
                            <div class="text-xs text-info">
                                31.5% de rentabilidad
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Confianza
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">87%</div>
                            <div class="text-xs text-warning">
                                Nivel de precisión
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bullseye fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico Principal de Proyecciones -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Proyección de Ventas - 12 Meses</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Opciones:</div>
                    <a class="dropdown-item" href="#" onclick="cambiarVista('mensual')">Vista Mensual</a>
                    <a class="dropdown-item" href="#" onclick="cambiarVista('trimestral')">Vista Trimestral</a>
                    <a class="dropdown-item" href="#" onclick="exportarProyeccion()">Exportar Proyección</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-area">
                <canvas id="proyeccionChart"></canvas>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    Las proyecciones se basan en datos históricos de 24 meses usando regresión lineal con 90% de confianza.
                </small>
            </div>
        </div>
    </div>

    <!-- Escenarios de Proyección -->
    <div class="row">
        <!-- Escenarios Optimista/Pesimista -->
        <div class="col-xl-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Escenarios de Proyección</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Período</th>
                                    <th>Pesimista</th>
                                    <th>Base</th>
                                    <th>Optimista</th>
                                    <th>Crecimiento</th>
                                    <th>Probabilidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Q1 2024</strong></td>
                                    <td class="text-right">$485,230</td>
                                    <td class="text-right text-primary">$512,450</td>
                                    <td class="text-right">$548,920</td>
                                    <td><span class="text-success">+5.8%</span></td>
                                    <td><span class="badge badge-success">85%</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Q2 2024</strong></td>
                                    <td class="text-right">$502,340</td>
                                    <td class="text-right text-primary">$531,890</td>
                                    <td class="text-right">$567,123</td>
                                    <td><span class="text-success">+3.8%</span></td>
                                    <td><span class="badge badge-success">78%</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Q3 2024</strong></td>
                                    <td class="text-right">$518,450</td>
                                    <td class="text-right text-primary">$549,230</td>
                                    <td class="text-right">$586,890</td>
                                    <td><span class="text-success">+3.3%</span></td>
                                    <td><span class="badge badge-warning">72%</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Q4 2024</strong></td>
                                    <td class="text-right">$534,560</td>
                                    <td class="text-right text-primary">$567,340</td>
                                    <td class="text-right">$607,450</td>
                                    <td><span class="text-success">+3.3%</span></td>
                                    <td><span class="badge badge-warning">68%</span></td>
                                </tr>
                                <tr class="table-info">
                                    <td><strong>Total 2024</strong></td>
                                    <td class="text-right"><strong>$2,040,580</strong></td>
                                    <td class="text-right text-primary"><strong>$2,160,910</strong></td>
                                    <td class="text-right"><strong>$2,310,383</strong></td>
                                    <td><span class="text-success"><strong>+4.1%</strong></span></td>
                                    <td><span class="badge badge-success"><strong>78%</strong></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <i class="fas fa-arrow-down text-danger fa-2x"></i>
                                    <h6 class="mt-2">Escenario Pesimista</h6>
                                    <small class="text-muted">Crecimiento del 2.5% - Factores negativos</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <i class="fas fa-arrow-right text-primary fa-2x"></i>
                                    <h6 class="mt-2">Escenario Base</h6>
                                    <small class="text-muted">Crecimiento del 4.1% - Tendencia actual</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <i class="fas fa-arrow-up text-success fa-2x"></i>
                                    <h6 class="mt-2">Escenario Optimista</h6>
                                    <small class="text-muted">Crecimiento del 6.9% - Expansión acelerada</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Factores de Riesgo -->
        <div class="col-xl-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Análisis de Factores</h6>
                </div>
                <div class="card-body">
                    <h6 class="text-success">Factores Positivos</h6>
                    <ul class="list-unstyled mb-3">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success"></i> 
                            <small>Expansión de cartera de clientes (+15%)</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success"></i> 
                            <small>Nuevos productos médicos (+22%)</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success"></i> 
                            <small>Mejoras en proceso de ventas</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success"></i> 
                            <small>Tendencias del mercado favorables</small>
                        </li>
                    </ul>

                    <h6 class="text-danger">Factores de Riesgo</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-exclamation-triangle text-danger"></i> 
                            <small>Competencia en precios (-8%)</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-exclamation-triangle text-danger"></i> 
                            <small>Regulaciones del sector</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-exclamation-triangle text-danger"></i> 
                            <small>Posible recesión económica</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-exclamation-triangle text-danger"></i> 
                            <small>Rotación de personal ventas</small>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Recomendaciones -->
            <div class="card shadow mt-3">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recomendaciones</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb"></i> Estrategia Sugerida</h6>
                        <small>
                            Basándose en las proyecciones, se recomienda:
                        </small>
                    </div>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-chevron-right text-primary"></i> 
                            <small>Incrementar inventario para Q2-Q3</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-chevron-right text-primary"></i> 
                            <small>Capacitar equipo comercial</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-chevron-right text-primary"></i> 
                            <small>Expandir a nuevas regiones</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-chevron-right text-primary"></i> 
                            <small>Desarrollar productos premium</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Proyección por Categorías -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Proyección por Categorías de Productos</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-pie">
                        <canvas id="categoriaProyeccionChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Categoría</th>
                                    <th>Actual</th>
                                    <th>Proyección</th>
                                    <th>Crecimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Medicamentos</strong></td>
                                    <td class="text-right">$687,450</td>
                                    <td class="text-right">$734,890</td>
                                    <td><span class="text-success">+6.9%</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Dispositivos Médicos</strong></td>
                                    <td class="text-right">$523,200</td>
                                    <td class="text-right">$567,340</td>
                                    <td><span class="text-success">+8.4%</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Suplementos</strong></td>
                                    <td class="text-right">$312,890</td>
                                    <td class="text-right">$345,670</td>
                                    <td><span class="text-success">+10.5%</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Cuidado Personal</strong></td>
                                    <td class="text-right">$178,230</td>
                                    <td class="text-right">$189,450</td>
                                    <td><span class="text-warning">+6.3%</span></td>
                                </tr>
                                <tr class="table-info">
                                    <td><strong>TOTAL</strong></td>
                                    <td class="text-right"><strong>$1,701,770</strong></td>
                                    <td class="text-right"><strong>$1,837,350</strong></td>
                                    <td><span class="text-success"><strong>+8.0%</strong></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Histórico vs Proyección -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Comparativo Histórico y Proyección</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="proyeccionTable">
                    <thead class="thead-dark">
                        <tr>
                            <th>Mes</th>
                            <th>Histórico</th>
                            <th>Proyección Base</th>
                            <th>Proyección Optimista</th>
                            <th>Variación</th>
                            <th>Confianza</th>
                            <th>Tendencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Ene 2024</strong></td>
                            <td class="text-right">$142,890</td>
                            <td class="text-right text-primary">$149,340</td>
                            <td class="text-right">$156,780</td>
                            <td><span class="text-success">+4.5%</span></td>
                            <td><span class="badge badge-success">92%</span></td>
                            <td><i class="fas fa-arrow-up text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Feb 2024</strong></td>
                            <td class="text-right">$148,560</td>
                            <td class="text-right text-primary">$154,230</td>
                            <td class="text-right">$162,890</td>
                            <td><span class="text-success">+3.8%</span></td>
                            <td><span class="badge badge-success">89%</span></td>
                            <td><i class="fas fa-arrow-up text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Mar 2024</strong></td>
                            <td class="text-right">$156,890</td>
                            <td class="text-right text-primary">$160,120</td>
                            <td class="text-right">$168,450</td>
                            <td><span class="text-success">+2.1%</span></td>
                            <td><span class="badge badge-success">87%</span></td>
                            <td><i class="fas fa-arrow-up text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Abr 2024</strong></td>
                            <td class="text-right">$143,670</td>
                            <td class="text-right text-primary">$165,340</td>
                            <td class="text-right">$174,560</td>
                            <td><span class="text-success">+15.1%</span></td>
                            <td><span class="badge badge-warning">84%</span></td>
                            <td><i class="fas fa-arrow-up text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>May 2024</strong></td>
                            <td class="text-right">$152,340</td>
                            <td class="text-right text-primary">$170,890</td>
                            <td class="text-right">$181,230</td>
                            <td><span class="text-success">+12.2%</span></td>
                            <td><span class="badge badge-warning">82%</span></td>
                            <td><i class="fas fa-arrow-up text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Jun 2024</strong></td>
                            <td class="text-right">$161,780</td>
                            <td class="text-right text-primary">$176,890</td>
                            <td class="text-right">$188,670</td>
                            <td><span class="text-success">+9.3%</span></td>
                            <td><span class="badge badge-warning">79%</span></td>
                            <td><i class="fas fa-arrow-up text-success"></i></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    initializeDataTable();
});

function initializeCharts() {
    // Gráfico Principal de Proyecciones
    const ctxProyeccion = document.getElementById('proyeccionChart').getContext('2d');
    new Chart(ctxProyeccion, {
        type: 'line',
        data: {
            labels: ['Ene 23', 'Feb 23', 'Mar 23', 'Abr 23', 'May 23', 'Jun 23', 
                     'Jul 23', 'Ago 23', 'Sep 23', 'Oct 23', 'Nov 23', 'Dic 23',
                     'Ene 24', 'Feb 24', 'Mar 24', 'Abr 24', 'May 24', 'Jun 24'],
            datasets: [{
                label: 'Ventas Históricas',
                data: [135000, 142000, 148000, 145000, 152000, 156000, 
                       162000, 158000, 164000, 168000, 172000, 178000,
                       142890, 148560, 156890, null, null, null],
                borderColor: 'rgb(78, 115, 223)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.1,
                fill: false
            }, {
                label: 'Proyección Base',
                data: [null, null, null, null, null, null,
                       null, null, null, null, null, null,
                       149340, 154230, 160120, 165340, 170890, 176890],
                borderColor: 'rgb(72, 187, 120)',
                backgroundColor: 'rgba(72, 187, 120, 0.1)',
                borderDash: [5, 5],
                tension: 0.1,
                fill: false
            }, {
                label: 'Proyección Optimista',
                data: [null, null, null, null, null, null,
                       null, null, null, null, null, null,
                       156780, 162890, 168450, 174560, 181230, 188670],
                borderColor: 'rgb(246, 194, 62)',
                backgroundColor: 'rgba(246, 194, 62, 0.1)',
                borderDash: [10, 5],
                tension: 0.1,
                fill: false
            }, {
                label: 'Proyección Pesimista',
                data: [null, null, null, null, null, null,
                       null, null, null, null, null, null,
                       145230, 149560, 154890, 158670, 162340, 166890],
                borderColor: 'rgb(231, 74, 59)',
                backgroundColor: 'rgba(231, 74, 59, 0.1)',
                borderDash: [2, 2],
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Análisis de Tendencias y Proyecciones de Ventas'
                },
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });

    // Gráfico de Categorías Proyectadas
    const ctxCategoriaProy = document.getElementById('categoriaProyeccionChart').getContext('2d');
    new Chart(ctxCategoriaProy, {
        type: 'bar',
        data: {
            labels: ['Medicamentos', 'Dispositivos', 'Suplementos', 'Cuidado Personal'],
            datasets: [{
                label: 'Actual',
                data: [687450, 523200, 312890, 178230],
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderColor: 'rgb(78, 115, 223)',
                borderWidth: 1
            }, {
                label: 'Proyectado',
                data: [734890, 567340, 345670, 189450],
                backgroundColor: 'rgba(72, 187, 120, 0.8)',
                borderColor: 'rgb(72, 187, 120)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
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
                            return '$' + (value/1000).toFixed(0) + 'K';
                        }
                    }
                }
            }
        }
    });
}

function initializeDataTable() {
    $('#proyeccionTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 12,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        columnDefs: [
            { targets: [1, 2, 3], className: 'text-right' }
        ]
    });
}

// Funciones de exportación
function exportarExcel() {
    const form = document.getElementById('proyeccionesForm');
    form.action = '{{ route("ventas.reportes.proyecciones.exportar") }}';
    form.method = 'POST';
    
    let token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = '{{ csrf_token() }}';
    form.appendChild(token);
    
    let formato = document.createElement('input');
    formato.type = 'hidden';
    formato.name = 'formato';
    formato.value = 'excel';
    form.appendChild(formato);
    
    form.submit();
}

function exportarPDF() {
    const form = document.getElementById('proyeccionesForm');
    form.action = '{{ route("ventas.reportes.proyecciones.exportar") }}';
    form.method = 'POST';
    
    let token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = '{{ csrf_token() }}';
    form.appendChild(token);
    
    let formato = document.createElement('input');
    formato.type = 'hidden';
    formato.name = 'formato';
    formato.value = 'pdf';
    form.appendChild(formato);
    
    form.submit();
}

function actualizarProyecciones() {
    document.getElementById('proyeccionesForm').submit();
}

function limpiarFiltros() {
    document.getElementById('proyeccionesForm').reset();
    window.location.href = '{{ route("ventas.reportes.proyecciones") }}';
}

function cambiarVista(vista) {
    alert('Cambiando a vista: ' + vista);
}

function exportarProyeccion() {
    alert('Exportando proyección detallada');
}
</script>
@endsection