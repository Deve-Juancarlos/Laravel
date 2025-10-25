@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
                    <li class="breadcrumb-item active">Reportes Ejecutivos</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-area text-primary"></i> Reporte Ejecutivo Anual
            </h1>
        </div>
        <div>
            <button class="btn btn-outline-secondary" onclick="seleccionarAño()">
                <i class="fas fa-calendar"></i> Seleccionar Año
            </button>
            <button class="btn btn-outline-info" onclick="compararAño()">
                <i class="fas fa-balance-scale"></i> Comparar Años
            </button>
            <button class="btn btn-outline-success" onclick="exportarReporteAnual()">
                <i class="fas fa-download"></i> Exportar
            </button>
        </div>
    </div>

    <!-- Panel de Control Anual -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Configuración del Reporte Anual</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Año de Análisis</label>
                        <select class="form-control" id="añoAnalisis">
                            <option value="2024" selected>2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                            <option value="2021">2021</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Año de Comparación</label>
                        <select class="form-control" id="añoComparacion">
                            <option value="2023">2023</option>
                            <option value="2022" selected>2022</option>
                            <option value="2021">2021</option>
                            <option value="2020">2020</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tipo de Análisis</label>
                        <select class="form-control" id="tipoAnalisis">
                            <option value="completo" selected>Análisis Completo</option>
                            <option value="ventas">Solo Ventas</option>
                            <option value="rentabilidad">Solo Rentabilidad</option>
                            <option value="operativo">Operativo</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button class="btn btn-primary" onclick="generarReporteAnual()">
                        <i class="fas fa-chart-line"></i> Generar Reporte
                    </button>
                    <button class="btn btn-outline-warning" onclick="proyeccionesAnuales()">
                        <i class="fas fa-crystal-ball"></i> Proyecciones 2025
                    </button>
                    <button class="btn btn-outline-success" onclick="generarPresentacion()">
                        <i class="fas fa-presentation"></i> Generar Presentación
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Ejecutivos Anuales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Ventas Anuales 2024
                            </div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">S/ 31,245,678</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +18.5% vs 2023
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 112%"></div>
                            </div>
                            <small class="text-muted">112% de la meta anual</small>
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
                                EBITDA Acumulado
                            </div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">S/ 9,781,234</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> 31.3% margen
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 31.3%"></div>
                            </div>
                            <small class="text-muted">Margen superior al objetivo</small>
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
                                Crecimiento Anual
                            </div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">+18.5%</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-trophy"></i> Mejor performance en 5 años
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 92%"></div>
                            </div>
                            <small class="text-muted">Promedio industria: 12%</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-trend-up fa-2x text-gray-300"></i>
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
                                ROI Anual
                            </div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">26.7%</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +3.2pp vs 2023
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 89%"></div>
                            </div>
                            <small class="text-muted">Meta: 25%</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis Anual -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Resumen Ejecutivo 2024</h6>
        </div>
        <div class="card-body">
            <div class="alert alert-primary">
                <h6><i class="fas fa-star"></i> Logros Principales del Año 2024</h6>
                <div class="row">
                    <div class="col-md-8">
                        <ul class="mb-0">
                            <li><strong>Récord de Ventas:</strong> Superación histórica con S/ 31.2M (+18.5% vs 2023)</li>
                            <li><strong>Expansión Exitosa:</strong> Apertura de 12 nuevas sucursales en zonas estratégicas</li>
                            <li><strong>Transformación Digital:</strong> Lanzamiento de plataforma e-commerce con 15,000 usuarios</li>
                            <li><strong>Eficiencia Operativa:</strong> Reducción de costos operativos en 12%</li>
                            <li><strong>Reconocimientos:</strong> Premio "Mejor Farmacia del Año" - Asociación Nacional</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h2 class="text-success">+18.5%</h2>
                            <p class="text-muted">Crecimiento Anual</p>
                            <h4 class="text-primary">S/ 31.2M</h4>
                            <p class="text-muted">Ventas Totales</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evolución Trimestral -->
    <div class="row">
        <!-- Gráfico Principal -->
        <div class="col-xl-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Evolución Trimestral de Ventas 2024</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="evolucionAnualChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparativo de Años -->
        <div class="col-xl-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Comparativo Histórico</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="comparativoAnualChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="font-weight-bold text-success">2024</div>
                                <div class="text-sm">31.2M</div>
                            </div>
                            <div class="col-6">
                                <div class="font-weight-bold text-info">2023</div>
                                <div class="text-sm">26.4M</div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="font-weight-bold text-warning">2022</div>
                                <div class="text-sm">22.1M</div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="font-weight-bold text-primary">2021</div>
                                <div class="text-sm">18.9M</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis por Unidades de Negocio -->
    <div class="row mt-4">
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance por Unidad de Negocio</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Unidad</th>
                                    <th>2024</th>
                                    <th>2023</th>
                                    <th>Variación</th>
                                    <th>Participación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Farmacia</strong></td>
                                    <td class="text-right">S/ 14,567,890</td>
                                    <td class="text-right">S/ 12,234,567</td>
                                    <td class="text-success">+19.1%</td>
                                    <td>46.6%</td>
                                </tr>
                                <tr>
                                    <td><strong>Dispositivos Médicos</strong></td>
                                    <td class="text-right">S/ 9,234,567</td>
                                    <td class="text-right">S/ 7,890,123</td>
                                    <td class="text-success">+17.0%</td>
                                    <td>29.6%</td>
                                </tr>
                                <tr>
                                    <td><strong>Productos Especializados</strong></td>
                                    <td class="text-right">S/ 5,678,901</td>
                                    <td class="text-right">S/ 4,567,890</td>
                                    <td class="text-success">+24.3%</td>
                                    <td>18.2%</td>
                                </tr>
                                <tr>
                                    <td><strong>Servicios</strong></td>
                                    <td class="text-right">S/ 1,764,320</td>
                                    <td class="text-right">S/ 1,456,789</td>
                                    <td class="text-success">+21.1%</td>
                                    <td>5.6%</td>
                                </tr>
                                <tr class="table-info">
                                    <td><strong>TOTAL</strong></td>
                                    <td class="text-right"><strong>S/ 31,245,678</strong></td>
                                    <td class="text-right"><strong>S/ 26,149,369</strong></td>
                                    <td class="text-success"><strong>+19.5%</strong></td>
                                    <td><strong>100%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicadores Clave Anuales -->
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Indicadores Estratégicos</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Crecimiento Orgánico</strong></span>
                            <span class="text-success">+15.2%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 76%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Expansión Regional</strong></span>
                            <span class="text-info">+12 tiendas</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 60%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Satisfacción del Cliente</strong></span>
                            <span class="text-warning">4.7/5.0</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 94%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Rotación de Personal</strong></span>
                            <span class="text-primary">8.2%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 41%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Innovación</strong></span>
                            <span class="text-success">+25 productos</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 83%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis de Mercado -->
    <div class="row mt-4">
        <!-- Participación de Mercado -->
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Participación de Mercado por Región</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="mercadoChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Análisis</h6>
                            <p class="mb-0">
                                Mantenemos liderazgo en el norte (23.4%) y expansion exitosa en región centro.
                                Oportunidades de crecimiento en sur (15.2%).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proyecciones 2025 -->
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Proyecciones Estratégicas 2025</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Métrica</th>
                                    <th>Proyección</th>
                                    <th>Crecimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Ventas Proyectadas</strong></td>
                                    <td class="text-right">S/ 36,800,000</td>
                                    <td class="text-success">+17.8%</td>
                                </tr>
                                <tr>
                                    <td><strong>EBITDA</strong></td>
                                    <td class="text-right">S/ 11,752,000</td>
                                    <td class="text-success">+20.1%</td>
                                </tr>
                                <tr>
                                    <td><strong>Nuevas Sucursales</strong></td>
                                    <td class="text-right">15</td>
                                    <td class="text-info">+25%</td>
                                </tr>
                                <tr>
                                    <td><strong>Clientes Activos</strong></td>
                                    <td class="text-right">32,500</td>
                                    <td class="text-success">+23.3%</td>
                                </tr>
                                <tr>
                                    <td><strong>ROI Esperado</strong></td>
                                    <td class="text-right">28.5%</td>
                                    <td class="text-success">+1.8pp</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <div class="alert alert-success">
                            <h6><i class="fas fa-crystal-ball"></i> Estrategias 2025</h6>
                            <ul class="mb-0">
                                <li>Expansión a 4 nuevas ciudades</li>
                                <li>Lanzamiento de productos premium</li>
                                <li>Implementación de IA en inventario</li>
                                <li>Programa de fidelización digital</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conclusiones Estratégicas -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Conclusiones Estratégicas y Hoja de Ruta 2025</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="alert alert-success">
                        <h6><i class="fas fa-trophy"></i> Logros Clave 2024</h6>
                        <ul class="mb-0">
                            <li>Crecimiento del 18.5% superando mercado</li>
                            <li>Récord histórico en ventas y rentabilidad</li>
                            <li>Expansión exitosa a 12 nuevas ubicaciones</li>
                            <li>Liderazgo digital en el sector</li>
                            <li>Reconocimiento industria como líder</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Desafíos Identificados</h6>
                        <ul class="mb-0">
                            <li>Presión competitiva en precios</li>
                            <li>Regulaciones sanitarias cambiantes</li>
                            <li>Competencia de marketplaces digitales</li>
                            <li>Capacitación continua del equipo</li>
                            <li>Gestión de cadena de suministro</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-primary">
                        <h6><i class="fas fa-rocket"></i> Objetivos 2025</h6>
                        <ul class="mb-0">
                            <li>Meta: S/ 36.8M en ventas (+17.8%)</li>
                            <li>Expansión a 15 nuevas sucursales</li>
                            <li>Líder digital del sector salud</li>
                            <li>Sostenibilidad y responsabilidad social</li>
                            <li>ROI superior al 28%</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function initializeCharts() {
    // Gráfico de Evolución Anual
    const ctxEvolucion = document.getElementById('evolucionAnualChart').getContext('2d');
    new Chart(ctxEvolucion, {
        type: 'line',
        data: {
            labels: ['Q1', 'Q2', 'Q3', 'Q4'],
            datasets: [{
                label: '2024',
                data: [7234567, 7890123, 8456789, 7678900],
                borderColor: 'rgb(78, 115, 223)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.1,
                fill: true
            }, {
                label: '2023',
                data: [6123456, 6567890, 6789012, 6567890],
                borderColor: 'rgb(72, 187, 120)',
                backgroundColor: 'rgba(72, 187, 120, 0.1)',
                tension: 0.1,
                fill: true
            }, {
                label: '2022',
                data: [5123456, 5456789, 5678901, 5567890],
                borderColor: 'rgb(246, 194, 62)',
                backgroundColor: 'rgba(246, 194, 62, 0.1)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Evolución Trimestral de Ventas'
                },
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + (value/1000000).toFixed(1) + 'M';
                        }
                    }
                }
            }
        }
    });

    // Gráfico Comparativo
    const ctxComparativo = document.getElementById('comparativoAnualChart').getContext('2d');
    new Chart(ctxComparativo, {
        type: 'doughnut',
        data: {
            labels: ['2024', '2023', '2022', '2021'],
            datasets: [{
                data: [31245678, 26149369, 22123256, 18895432],
                backgroundColor: [
                    'rgb(78, 115, 223)',
                    'rgb(72, 187, 120)',
                    'rgb(246, 194, 62)',
                    'rgb(231, 74, 59)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': S/ ' + (context.parsed/1000000).toFixed(1) + 'M (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Mercado
    const ctxMercado = document.getElementById('mercadoChart').getContext('2d');
    new Chart(ctxMercado, {
        type: 'bar',
        data: {
            labels: ['Norte', 'Sur', 'Centro', 'Este', 'Oeste'],
            datasets: [{
                label: 'Nuestra Participación (%)',
                data: [23.4, 15.2, 18.7, 12.8, 16.9],
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderColor: 'rgb(78, 115, 223)',
                borderWidth: 1
            }, {
                label: 'Competidor Promedio (%)',
                data: [19.8, 18.5, 15.3, 14.2, 17.1],
                backgroundColor: 'rgba(231, 74, 59, 0.8)',
                borderColor: 'rgb(231, 74, 59)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Participación de Mercado por Región'
                },
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 30,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
}

function generarReporteAnual() {
    const año = document.getElementById('añoAnalisis').value;
    
    Swal.fire({
        title: 'Generando Reporte Anual...',
        text: `Procesando informe completo de ${año}`,
        timer: 4000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Reporte Anual Generado!',
            text: 'El reporte ejecutivo anual ha sido procesado exitosamente.',
            icon: 'success'
        });
    });
}

function proyeccionesAnuales() {
    Swal.fire({
        title: 'Proyecciones 2025...',
        text: 'Generando proyecciones estratégicas para 2025',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Proyecciones 2025',
            html: `
                <div class="text-left">
                    <h6>Proyecciones Estratégicas:</h6>
                    <ul>
                        <li><strong>Ventas:</strong> S/ 36.8M (+17.8%)</li>
                        <li><strong>EBITDA:</strong> S/ 11.8M (+20.1%)</li>
                        <li><strong>Expansión:</strong> 15 nuevas sucursales</li>
                        <li><strong>Digital:</strong> Plataforma IA implementada</li>
                        <li><strong>Sostenibilidad:</strong> Certificación verde</li>
                    </ul>
                </div>
            `,
            icon: 'info'
        });
    });
}

function generarPresentacion() {
    Swal.fire({
        title: 'Generando Presentación...',
        text: 'Creando presentación ejecutiva para junta directiva',
        timer: 3500,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Presentación Generada!',
            text: 'La presentación ejecutiva ha sido creada exitosamente.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Descargar',
            cancelButtonText: 'Abrir'
        });
    });
}

function exportarReporteAnual() {
    const año = document.getElementById('añoAnalisis').value;
    
    Swal.fire({
        title: 'Exportando Reporte...',
        text: `Generando reporte anual completo de ${año}`,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Reporte Exportado!',
            text: 'El reporte anual ha sido exportado exitosamente.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Descargar PDF',
            cancelButtonText: 'Descargar PowerPoint'
        });
    });
}

function seleccionarAño() {
    Swal.fire({
        title: 'Seleccionar Año',
        input: 'select',
        inputOptions: {
            '2024': '2024',
            '2023': '2023',
            '2022': '2022',
            '2021': '2021'
        },
        showCancelButton: true,
        confirmButtonText: 'Seleccionar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('añoAnalisis').value = result.value;
            generarReporteAnual();
        }
    });
}

function compararAño() {
    Swal.fire({
        title: 'Modo Comparativo',
        html: `
            <div class="text-left">
                <p>Seleccionando análisis comparativo entre:</p>
                <ul>
                    <li><strong>Año Principal:</strong> 2024</li>
                    <li><strong>Año de Comparación:</strong> 2023</li>
                    <li><strong>Tipo:</strong> Análisis completo con tendencias</li>
                </ul>
            </div>
        `,
        icon: 'info'
    });
}
</script>
@endsection