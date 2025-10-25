@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users text-primary"></i> Reporte de Vendedores
        </h1>
        <div>
            <button class="btn btn-outline-secondary" onclick="exportarExcel()">
                <i class="fas fa-file-excel"></i> Excel
            </button>
            <button class="btn btn-outline-danger" onclick="exportarPDF()">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
            <button class="btn btn-primary" onclick="actualizarReporte()">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros de Consulta</h6>
        </div>
        <div class="card-body">
            <form id="filtrosForm" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" value="{{ request('fecha_inicio', date('Y-m-01')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha Fin</label>
                            <input type="date" class="form-control" name="fecha_fin" value="{{ request('fecha_fin', date('Y-m-t')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Vendedor</label>
                            <select class="form-control" name="vendedor">
                                <option value="">Todos los Vendedores</option>
                                <option value="1">María González</option>
                                <option value="2">Carlos Rodríguez</option>
                                <option value="3">Ana Martínez</option>
                                <option value="4">Luis Pérez</option>
                                <option value="5">Carmen López</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Región</label>
                            <select class="form-control" name="region">
                                <option value="">Todas las Regiones</option>
                                <option value="norte">Norte</option>
                                <option value="sur">Sur</option>
                                <option value="este">Este</option>
                                <option value="oeste">Oeste</option>
                                <option value="centro">Centro</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Generar Reporte
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs de Ventas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Vendedores
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">24</div>
                            <div class="text-xs text-info">
                                Activos este mes
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Ventas Promedio
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$118,647</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +8.3% vs mes anterior
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Mejor Vendedor
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$347,892</div>
                            <div class="text-xs text-info">
                                María González
                            </div>
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
                                Meta Cumplida
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">67%</div>
                            <div class="text-xs text-warning">
                                Vendedores en meta
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

    <!-- Ranking y Gráficos -->
    <div class="row">
        <!-- Ranking de Vendedores -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ranking de Vendedores - Top 10</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Vendedor</th>
                                    <th>Región</th>
                                    <th>Clientes</th>
                                    <th>Ventas</th>
                                    <th>Meta</th>
                                    <th>% Cumplimiento</th>
                                    <th>Comisión</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-warning">1</span></td>
                                    <td><strong>María González</strong></td>
                                    <td>Norte</td>
                                    <td>45</td>
                                    <td class="text-right">$347,892</td>
                                    <td class="text-right">$320,000</td>
                                    <td><span class="text-success font-weight-bold">108.7%</span></td>
                                    <td class="text-right">$17,395</td>
                                    <td><span class="badge badge-success">Excelente</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-secondary">2</span></td>
                                    <td><strong>Carlos Rodríguez</strong></td>
                                    <td>Sur</td>
                                    <td>38</td>
                                    <td class="text-right">$298,456</td>
                                    <td class="text-right">$280,000</td>
                                    <td><span class="text-success font-weight-bold">106.6%</span></td>
                                    <td class="text-right">$14,923</td>
                                    <td><span class="badge badge-success">Excelente</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-info">3</span></td>
                                    <td><strong>Ana Martínez</strong></td>
                                    <td>Este</td>
                                    <td>42</td>
                                    <td class="text-right">$267,823</td>
                                    <td class="text-right">$280,000</td>
                                    <td><span class="text-warning font-weight-bold">95.7%</span></td>
                                    <td class="text-right">$13,391</td>
                                    <td><span class="badge badge-warning">Bueno</span></td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td><strong>Luis Pérez</strong></td>
                                    <td>Oeste</td>
                                    <td>35</td>
                                    <td class="text-right">$234,567</td>
                                    <td class="text-right">$240,000</td>
                                    <td><span class="text-warning font-weight-bold">97.7%</span></td>
                                    <td class="text-right">$11,728</td>
                                    <td><span class="badge badge-warning">Bueno</span></td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td><strong>Carmen López</strong></td>
                                    <td>Centro</td>
                                    <td>41</td>
                                    <td class="text-right">$198,734</td>
                                    <td class="text-right">$200,000</td>
                                    <td><span class="text-warning font-weight-bold">99.4%</span></td>
                                    <td class="text-right">$9,937</td>
                                    <td><span class="badge badge-warning">Bueno</span></td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td><strong>Roberto Silva</strong></td>
                                    <td>Norte</td>
                                    <td>33</td>
                                    <td class="text-right">$176,892</td>
                                    <td class="text-right">$180,000</td>
                                    <td><span class="text-warning font-weight-bold">98.3%</span></td>
                                    <td class="text-right">$8,845</td>
                                    <td><span class="badge badge-warning">Bueno</span></td>
                                </tr>
                                <tr>
                                    <td>7</td>
                                    <td><strong>Patricia Ruiz</strong></td>
                                    <td>Sur</td>
                                    <td>29</td>
                                    <td class="text-right">$156,423</td>
                                    <td class="text-right">$160,000</td>
                                    <td><span class="text-warning font-weight-bold">97.8%</span></td>
                                    <td class="text-right">$7,821</td>
                                    <td><span class="badge badge-warning">Bueno</span></td>
                                </tr>
                                <tr>
                                    <td>8</td>
                                    <td><strong>Fernando Castro</strong></td>
                                    <td>Este</td>
                                    <td>31</td>
                                    <td class="text-right">$145,678</td>
                                    <td class="text-right">$150,000</td>
                                    <td><span class="text-warning font-weight-bold">97.1%</span></td>
                                    <td class="text-right">$7,284</td>
                                    <td><span class="badge badge-warning">Bueno</span></td>
                                </tr>
                                <tr>
                                    <td>9</td>
                                    <td><strong>Isabel Mendoza</strong></td>
                                    <td>Oeste</td>
                                    <td>27</td>
                                    <td class="text-right">$134,234</td>
                                    <td class="text-right">$140,000</td>
                                    <td><span class="text-danger font-weight-bold">95.9%</span></td>
                                    <td class="text-right">$6,712</td>
                                    <td><span class="badge badge-danger">Regular</span></td>
                                </tr>
                                <tr>
                                    <td>10</td>
                                    <td><strong>Diego Morales</strong></td>
                                    <td>Centro</td>
                                    <td>25</td>
                                    <td class="text-right">$123,456</td>
                                    <td class="text-right">$130,000</td>
                                    <td><span class="text-danger font-weight-bold">94.9%</span></td>
                                    <td class="text-right">$6,173</td>
                                    <td><span class="badge badge-danger">Regular</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribución por Región -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ventas por Región</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="regionChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="font-weight-bold text-primary">Norte</div>
                                <div class="text-sm">28.3%</div>
                            </div>
                            <div class="col-6">
                                <div class="font-weight-bold text-success">Sur</div>
                                <div class="text-sm">24.7%</div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="font-weight-bold text-info">Este</div>
                                <div class="text-sm">22.1%</div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="font-weight-bold text-warning">Oeste</div>
                                <div class="text-sm">24.9%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Meta vs Realizado -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Progreso de Metas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-sm font-weight-bold">Meta Total</span>
                            <span class="text-sm">$3,456,789 / $3,200,000</span>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 108%"></div>
                        </div>
                        <small class="text-success">108.0% cumplido</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-sm font-weight-bold">Comisiones</span>
                            <span class="text-sm">$172,839</span>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 5%"></div>
                        </div>
                        <small class="text-info">5.0% de las ventas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evolución de Ventas por Vendedor -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Evolución Mensual de Ventas - Top 5 Vendedores</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Opciones:</div>
                    <a class="dropdown-item" href="#" onclick="exportarGrafico()">Exportar Gráfico</a>
                    <a class="dropdown-item" href="#" onclick="cambiarPeriodo('mensual')">Mensual</a>
                    <a class="dropdown-item" href="#" onclick="cambiarPeriodo('trimestral')">Trimestral</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-area">
                <canvas id="vendedoresChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabla Detallada de Rendimiento -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Análisis Detallado de Rendimiento</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="vendedoresTable">
                    <thead class="thead-dark">
                        <tr>
                            <th>Vendedor</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Región</th>
                            <th>Clientes Activos</th>
                            <th>Nuevos Clientes</th>
                            <th>Ventas Totales</th>
                            <th>Ticket Promedio</th>
                            <th>Conversión</th>
                            <th>Días Inactivos</th>
                            <th>Evaluación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>María González</strong></td>
                            <td>+1 234-567-8901</td>
                            <td>m.gonzalez@empresa.com</td>
                            <td><span class="badge badge-primary">Norte</span></td>
                            <td class="text-center">45</td>
                            <td class="text-center text-success">8</td>
                            <td class="text-right">$347,892</td>
                            <td class="text-right">$7,731</td>
                            <td class="text-center"><span class="text-success">78.5%</span></td>
                            <td class="text-center text-danger">0</td>
                            <td><span class="badge badge-success">A+</span></td>
                        </tr>
                        <tr>
                            <td><strong>Carlos Rodríguez</strong></td>
                            <td>+1 234-567-8902</td>
                            <td>c.rodriguez@empresa.com</td>
                            <td><span class="badge badge-success">Sur</span></td>
                            <td class="text-center">38</td>
                            <td class="text-center text-success">6</td>
                            <td class="text-right">$298,456</td>
                            <td class="text-right">$7,854</td>
                            <td class="text-center"><span class="text-success">75.2%</span></td>
                            <td class="text-center text-success">1</td>
                            <td><span class="badge badge-success">A+</span></td>
                        </tr>
                        <tr>
                            <td><strong>Ana Martínez</strong></td>
                            <td>+1 234-567-8903</td>
                            <td>a.martinez@empresa.com</td>
                            <td><span class="badge badge-info">Este</span></td>
                            <td class="text-center">42</td>
                            <td class="text-center text-warning">4</td>
                            <td class="text-right">$267,823</td>
                            <td class="text-right">$6,377</td>
                            <td class="text-center"><span class="text-warning">68.3%</span></td>
                            <td class="text-center text-warning">3</td>
                            <td><span class="badge badge-warning">B+</span></td>
                        </tr>
                        <tr>
                            <td><strong>Luis Pérez</strong></td>
                            <td>+1 234-567-8904</td>
                            <td>l.perez@empresa.com</td>
                            <td><span class="badge badge-warning">Oeste</span></td>
                            <td class="text-center">35</td>
                            <td class="text-center text-warning">5</td>
                            <td class="text-right">$234,567</td>
                            <td class="text-right">$6,702</td>
                            <td class="text-center"><span class="text-warning">70.8%</span></td>
                            <td class="text-center text-warning">2</td>
                            <td><span class="badge badge-warning">B+</span></td>
                        </tr>
                        <tr>
                            <td><strong>Carmen López</strong></td>
                            <td>+1 234-567-8905</td>
                            <td>c.lopez@empresa.com</td>
                            <td><span class="badge badge-secondary">Centro</span></td>
                            <td class="text-center">41</td>
                            <td class="text-center text-warning">3</td>
                            <td class="text-right">$198,734</td>
                            <td class="text-right">$4,847</td>
                            <td class="text-center"><span class="text-warning">72.1%</span></td>
                            <td class="text-center text-warning">4</td>
                            <td><span class="badge badge-warning">B</span></td>
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
    // Gráfico de Evolución por Vendedores
    const ctxVendedores = document.getElementById('vendedoresChart').getContext('2d');
    new Chart(ctxVendedores, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'María González',
                data: [28000, 29000, 31000, 28000, 29500, 31000, 28500, 29000, 29500, 31000, 28500, 27000],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
            }, {
                label: 'Carlos Rodríguez',
                data: [25000, 26000, 24000, 27000, 28000, 24500, 25000, 26500, 27000, 25500, 26000, 24500],
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1
            }, {
                label: 'Ana Martínez',
                data: [22000, 23000, 21000, 24000, 22500, 23500, 22000, 21000, 23000, 24000, 22000, 21000],
                borderColor: 'rgb(255, 206, 86)',
                backgroundColor: 'rgba(255, 206, 86, 0.1)',
                tension: 0.1
            }, {
                label: 'Luis Pérez',
                data: [19000, 20000, 21000, 18000, 19500, 20000, 18500, 19000, 20000, 19500, 19000, 17500],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }, {
                label: 'Carmen López',
                data: [16000, 17000, 18000, 15000, 16500, 17000, 15500, 16000, 17000, 16500, 16000, 14500],
                borderColor: 'rgb(153, 102, 255)',
                backgroundColor: 'rgba(153, 102, 255, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Evolución de Ventas por Vendedor (2024)'
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
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Regiones
    const ctxRegion = document.getElementById('regionChart').getContext('2d');
    new Chart(ctxRegion, {
        type: 'doughnut',
        data: {
            labels: ['Norte', 'Sur', 'Este', 'Oeste', 'Centro'],
            datasets: [{
                data: [28.3, 24.7, 22.1, 24.9, 0],
                backgroundColor: [
                    'rgb(78, 115, 223)',
                    'rgb(72, 187, 120)',
                    'rgb(72, 187, 255)',
                    'rgb(246, 194, 62)',
                    'rgb(153, 102, 255)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + '%';
                        }
                    }
                }
            }
        }
    });
}

function initializeDataTable() {
    $('#vendedoresTable').DataTable({
        order: [[6, 'desc']],
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        columnDefs: [
            { targets: [4, 5, 6, 7, 8, 9], className: 'text-right' }
        ]
    });
}

// Funciones de exportación
function exportarExcel() {
    const form = document.getElementById('filtrosForm');
    form.action = '{{ route("ventas.reportes.vendedores.exportar") }}';
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
    const form = document.getElementById('filtrosForm');
    form.action = '{{ route("ventas.reportes.vendedores.exportar") }}';
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

function actualizarReporte() {
    document.getElementById('filtrosForm').submit();
}

function limpiarFiltros() {
    document.getElementById('filtrosForm').reset();
    window.location.href = '{{ route("ventas.reportes.vendedores") }}';
}

function exportarGrafico() {
    alert('Función de exportación de gráfico');
}

function cambiarPeriodo(periodo) {
    alert('Cambiando a período: ' + periodo);
}
</script>
@endsection