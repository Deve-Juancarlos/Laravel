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
                <i class="fas fa-briefcase text-primary"></i> Reporte Ejecutivo Mensual
            </h1>
        </div>
        <div>
            <button class="btn btn-outline-secondary" onclick="vistaAnterior()">
                <i class="fas fa-arrow-left"></i> Mes Anterior
            </button>
            <button class="btn btn-outline-primary" onclick="siguienteVista()">
                <i class="fas fa-arrow-right"></i> Mes Siguiente
            </button>
            <button class="btn btn-outline-success" onclick="exportarReporteEjecutivo()">
                <i class="fas fa-download"></i> Exportar
            </button>
        </div>
    </div>

    <!-- Panel de Control -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Configuración del Reporte</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Año</label>
                        <select class="form-control" id="año">
                            <option value="2024" selected>2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Mes</label>
                        <select class="form-control" id="mes">
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4" selected>Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Unidad de Negocio</label>
                        <select class="form-control" id="unidadNegocio">
                            <option value="todas" selected>Todas las Unidades</option>
                            <option value="farmacia">Farmacia</option>
                            <option value="medicos">Médicos</option>
                            <option value="hospitales">Hospitales</option>
                            <option value="laboratorios">Laboratorios</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Nivel de Detalle</label>
                        <select class="form-control" id="detalle">
                            <option value="resumen" selected>Resumen Ejecutivo</option>
                            <option value="detallado">Detallado</option>
                            <option value="completo">Completo</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button class="btn btn-primary" onclick="generarReporteEjecutivo()">
                        <i class="fas fa-chart-line"></i> Generar Reporte
                    </button>
                    <button class="btn btn-outline-info" onclick="vistaPreviaEjecutiva()">
                        <i class="fas fa-eye"></i> Vista Previa
                    </button>
                    <button class="btn btn-outline-warning" onclick="programarReporte()">
                        <i class="fas fa-clock"></i> Programar Envío
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Ejecutivos -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Ingresos del Mes
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">S/ 2,847,523</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +12.5% vs mes anterior
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 108%"></div>
                            </div>
                            <small class="text-muted">108% de la meta mensual</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                EBITDA
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">S/ 892,341</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> 31.4% margen
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 31.4%"></div>
                            </div>
                            <small class="text-muted">Margen objetivo: 30%</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                Participación de Mercado
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">18.7%</div>
                            <div class="text-xs text-info">
                                <i class="fas fa-arrow-up"></i> +0.8% vs trimestre anterior
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 75%"></div>
                            </div>
                            <small class="text-muted">Meta: 20% al cierre del año</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
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
                                Clientes Activos
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">2,634</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +8.3% crecimiento
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 92%"></div>
                            </div>
                            <small class="text-muted">92% de retención</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen Ejecutivo -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Resumen Ejecutivo - Abril 2024</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="alert alert-primary">
                        <h6><i class="fas fa-star"></i> Highlights del Mes</h6>
                        <ul class="mb-0">
                            <li><strong>Récord de Ventas:</strong> Abril marcó un récord histórico con S/ 2.8M en ventas, superando la meta en 8%</li>
                            <li><strong>Expansión Regional:</strong> Apertura de 3 nuevas sucursales en zonas estratégicas del norte</li>
                            <li><strong>Innovación:</strong> Lanzamiento de plataforma digital para pedidos online con 1,200 usuarios</li>
                            <li><strong>Eficiencia Operativa:</strong> Reducción del 15% en costos de distribución</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h4 class="text-success">+12.5%</h4>
                            <p class="text-muted mb-0">Crecimiento vs Mes Anterior</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis por Unidad de Negocio -->
    <div class="row">
        <!-- Rendimiento por Unidad -->
        <div class="col-xl-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rendimiento por Unidad de Negocio</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="unidadesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicadores Clave -->
        <div class="col-xl-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Indicadores Ejecutivos</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span><strong>ROI</strong></span>
                                <span class="text-success">24.8%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 83%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span><strong>Rotación de Inventario</strong></span>
                                <span class="text-info">8.2 veces</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 68%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span><strong>Satisfacción Cliente</strong></span>
                                <span class="text-warning">4.6/5.0</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 92%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span><strong>Productividad Empleados</strong></span>
                                <span class="text-primary">+15.2%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 78%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis Detallado -->
    <div class="row mt-4">
        <!-- Top Clientes -->
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Clientes por Ingresos</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Ingresos</th>
                                    <th>Participación</th>
                                    <th>Variación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-warning">1</span></td>
                                    <td>Hospital Central S.A.</td>
                                    <td class="text-right">S/ 287,450</td>
                                    <td>10.1%</td>
                                    <td class="text-success">+15.2%</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-secondary">2</span></td>
                                    <td>Farmacia Principal</td>
                                    <td class="text-right">S/ 198,720</td>
                                    <td>7.0%</td>
                                    <td class="text-success">+8.7%</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-info">3</span></td>
                                    <td>Clínica San José</td>
                                    <td class="text-right">S/ 176,340</td>
                                    <td>6.2%</td>
                                    <td class="text-success">+12.3%</td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>Laboratorio Médico Plus</td>
                                    <td class="text-right">S/ 143,890</td>
                                    <td>5.1%</td>
                                    <td class="text-warning">+3.2%</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>Farmacia Salud Total</td>
                                    <td class="text-right">S/ 132,450</td>
                                    <td>4.7%</td>
                                    <td class="text-success">+18.9%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos Estrella -->
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Productos Estrella</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th>Ventas</th>
                                    <th>Margen</th>
                                    <th>Rotación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-warning">1</span></td>
                                    <td>Amoxicilina 500mg</td>
                                    <td class="text-right">S/ 45,520</td>
                                    <td>37.4%</td>
                                    <td>Alta</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-secondary">2</span></td>
                                    <td>Termómetro Digital</td>
                                    <td class="text-right">S/ 38,075</td>
                                    <td>40.0%</td>
                                    <td>Alta</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-info">3</span></td>
                                    <td>Vitamina C 1000mg</td>
                                    <td class="text-right">S/ 32,150</td>
                                    <td>30.0%</td>
                                    <td>Media</td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>Jeringas 5ml</td>
                                    <td class="text-right">S/ 25,641</td>
                                    <td>33.3%</td>
                                    <td>Alta</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>Paracetamol 500mg</td>
                                    <td class="text-right">S/ 20,936</td>
                                    <td>37.5%</td>
                                    <td>Alta</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Proyecciones y Metas -->
    <div class="row mt-4">
        <!-- Comparativo Mensual -->
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Comparativo Mensual</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Métrica</th>
                                    <th>Mar 2024</th>
                                    <th>Abr 2024</th>
                                    <th>Variación</th>
                                    <th>Meta May</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Ventas</strong></td>
                                    <td>S/ 2,531,245</td>
                                    <td>S/ 2,847,523</td>
                                    <td class="text-success">+12.5%</td>
                                    <td>S/ 2,900,000</td>
                                </tr>
                                <tr>
                                    <td><strong>EBITDA</strong></td>
                                    <td>S/ 789,456</td>
                                    <td>S/ 892,341</td>
                                    <td class="text-success">+13.0%</td>
                                    <td>S/ 850,000</td>
                                </tr>
                                <tr>
                                    <td><strong>Clientes</strong></td>
                                    <td>2,431</td>
                                    <td>2,634</td>
                                    <td class="text-success">+8.3%</td>
                                    <td>2,700</td>
                                </tr>
                                <tr>
                                    <td><strong>Margen %</strong></td>
                                    <td>31.2%</td>
                                    <td>31.4%</td>
                                    <td class="text-success">+0.2pp</td>
                                    <td>32.0%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proyecciones Trimestrales -->
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Proyecciones Q2 2024</h6>
                </div>
                <div class="card-body">
                    <div class="chart-line">
                        <canvas id="proyeccionesChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-lightbulb"></i> Proyección Q2</h6>
                            <p class="mb-0">
                                Se proyecta un crecimiento sostenido del 8-12% mensual para Q2 2024, 
                                impulsado por la expansión regional y nuevas líneas de productos.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conclusiones y Recomendaciones -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Conclusiones y Recomendaciones Estratégicas</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success"><i class="fas fa-check-circle"></i> Logros Principales</h6>
                    <ul>
                        <li><strong>Récord de Ventas:</strong> Superación de meta mensual por 8%</li>
                        <li><strong>Margen EBITDA:</strong> Mejora del 0.2pp alcanzando 31.4%</li>
                        <li><strong>Crecimiento de Clientes:</strong> +8.3% en base activa</li>
                        <li><strong>Eficiencia Operativa:</strong> Reducción de costos en 15%</li>
                        <li><strong>Participación de Mercado:</strong> Incremento de 0.8pp</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-warning"><i class="fas fa-exclamation-triangle"></i> Áreas de Atención</h6>
                    <ul>
                        <li><strong>Rotación de Inventario:</strong> Optimizar categorías de baja rotación</li>
                        <li><strong>Competencia:</strong> Monitorear movimientos de competidores principales</li>
                        <li><strong>Regulaciones:</strong> Adaptarse a nuevos requisitos sanitarios</li>
                        <li><strong>Recursos Humanos:</strong> Programa de capacitación continua</li>
                        <li><strong>Tecnología:</strong> Inversión en sistemas de automatización</li>
                    </ul>
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
    // Gráfico de Unidades de Negocio
    const ctxUnidades = document.getElementById('unidadesChart').getContext('2d');
    new Chart(ctxUnidades, {
        type: 'bar',
        data: {
            labels: ['Farmacia', 'Médicos', 'Hospitales', 'Laboratorios'],
            datasets: [{
                label: 'Abril 2024',
                data: [1245000, 892340, 567890, 234290],
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderColor: 'rgb(78, 115, 223)',
                borderWidth: 1
            }, {
                label: 'Marzo 2024',
                data: [1123000, 789560, 456230, 167890],
                backgroundColor: 'rgba(72, 187, 120, 0.8)',
                borderColor: 'rgb(72, 187, 120)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Ingresos por Unidad de Negocio (S/)'
                },
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
                            return 'S/ ' + (value/1000).toFixed(0) + 'K';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Proyecciones
    const ctxProyecciones = document.getElementById('proyeccionesChart').getContext('2d');
    new Chart(ctxProyecciones, {
        type: 'line',
        data: {
            labels: ['Abr', 'May', 'Jun'],
            datasets: [{
                label: 'Proyección Conservadora',
                data: [2847523, 2950000, 3050000],
                borderColor: 'rgb(231, 74, 59)',
                backgroundColor: 'rgba(231, 74, 59, 0.1)',
                tension: 0.1,
                borderDash: [5, 5]
            }, {
                label: 'Proyección Base',
                data: [2847523, 3100000, 3250000],
                borderColor: 'rgb(78, 115, 223)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.1
            }, {
                label: 'Proyección Optimista',
                data: [2847523, 3200000, 3450000],
                borderColor: 'rgb(72, 187, 120)',
                backgroundColor: 'rgba(72, 187, 120, 0.1)',
                tension: 0.1,
                borderDash: [10, 5]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Proyección Q2 2024'
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
}

function generarReporteEjecutivo() {
    const año = document.getElementById('año').value;
    const mes = document.getElementById('mes').selectedOptions[0].text;
    
    Swal.fire({
        title: 'Generando Reporte Ejecutivo...',
        text: `Procesando informe de ${mes} ${año}`,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Reporte Generado!',
            text: 'El reporte ejecutivo ha sido procesado exitosamente.',
            icon: 'success'
        });
    });
}

function vistaPreviaEjecutiva() {
    Swal.fire({
        title: 'Vista Previa',
        html: `
            <div class="text-left">
                <h6>Configuración del Reporte:</h6>
                <ul>
                    <li><strong>Período:</strong> Abril 2024</li>
                    <li><strong>Unidad:</strong> Todas las Unidades</li>
                    <li><strong>Nivel:</strong> Resumen Ejecutivo</li>
                    <li><strong>Incluye:</strong> KPIs, Análisis por Unidades, Top Clientes</li>
                </ul>
            </div>
        `,
        icon: 'info'
    });
}

function programarReporte() {
    Swal.fire({
        title: 'Programar Envío del Reporte',
        text: 'Configure la frecuencia de envío:',
        input: 'select',
        inputOptions: {
            'semanal': 'Semanal',
            'quincenal': 'Quincenal',
            'mensual': 'Mensual'
        },
        showCancelButton: true,
        confirmButtonText: 'Programar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: '¡Programado!',
                text: `El reporte se enviará ${result.value}.`,
                icon: 'success'
            });
        }
    });
}

function exportarReporteEjecutivo() {
    const año = document.getElementById('año').value;
    const mes = document.getElementById('mes').selectedOptions[0].text;
    
    Swal.fire({
        title: 'Exportando Reporte...',
        text: `Generando reporte ejecutivo de ${mes} ${año}`,
        timer: 2500,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Exportado!',
            text: 'El reporte ejecutivo ha sido exportado.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Descargar PDF',
            cancelButtonText: 'Descargar PowerPoint'
        });
    });
}

function vistaAnterior() {
    const mes = document.getElementById('mes');
    if (mes.selectedIndex > 0) {
        mes.selectedIndex--;
        generarReporteEjecutivo();
    }
}

function siguienteVista() {
    const mes = document.getElementById('mes');
    if (mes.selectedIndex < mes.options.length - 1) {
        mes.selectedIndex++;
        generarReporteEjecutivo();
    }
}
</script>
@endsection