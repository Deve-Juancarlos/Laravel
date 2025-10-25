@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line text-primary"></i> Reporte de Rentabilidad
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
                            <label>Categoría</label>
                            <select class="form-control" name="categoria">
                                <option value="">Todas las Categorías</option>
                                <option value="medicamentos">Medicamentos</option>
                                <option value="dispositivos">Dispositivos Médicos</option>
                                <option value="suplementos">Suplementos</option>
                                <option value="cuidado-personal">Cuidado Personal</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cliente</label>
                            <select class="form-control" name="cliente">
                                <option value="">Todos los Clientes</option>
                                <option value="hospitales">Hospitales</option>
                                <option value="farmacias">Farmacias</option>
                                <option value="clinicas">Clínicas</option>
                                <option value="laboratorios">Laboratorios</option>
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

    <!-- KPIs Principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Ingresos Totales
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$2,847,523.45</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +12.5% vs período anterior
                            </div>
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
                                Margen Bruto
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$892,341.25</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> 31.4% de rentabilidad
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
                                Costo de Ventas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$1,955,182.20</div>
                            <div class="text-xs text-info">
                                68.6% del total de ingresos
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
                                Utilidad Neta
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$654,892.15</div>
                            <div class="text-xs text-warning">
                                <i class="fas fa-arrow-down"></i> -2.1% vs mes anterior
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <!-- Evolución de Rentabilidad -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Evolución Mensual de Rentabilidad</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <div class="dropdown-header">Opciones:</div>
                            <a class="dropdown-item" href="#" onclick="exportarGrafico('rentabilidad')">Exportar Gráfico</a>
                            <a class="dropdown-item" href="#" onclick="cambiarVista('mensual')">Vista Mensual</a>
                            <a class="dropdown-item" href="#" onclick="cambiarVista('trimestral')">Vista Trimestral</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="rentabilidadChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribución por Categoría -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rentabilidad por Categoría</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="categoriaChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="font-weight-bold text-success">Medicamentos</div>
                                <div class="text-sm">34.2%</div>
                            </div>
                            <div class="col-4">
                                <div class="font-weight-bold text-info">Dispositivos</div>
                                <div class="text-sm">28.7%</div>
                            </div>
                            <div class="col-4">
                                <div class="font-weight-bold text-warning">Otros</div>
                                <div class="text-sm">37.1%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Detallada -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Análisis Detallado de Rentabilidad</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="rentabilidadTable">
                    <thead class="thead-dark">
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Unidades Vendidas</th>
                            <th>Ingresos</th>
                            <th>Costo</th>
                            <th>Margen Bruto</th>
                            <th>% Margen</th>
                            <th>ROI</th>
                            <th>Tendencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Amoxicilina 500mg</strong></td>
                            <td><span class="badge badge-primary">Medicamentos</span></td>
                            <td>2,847</td>
                            <td>$45,520.00</td>
                            <td>$28,470.00</td>
                            <td>$17,050.00</td>
                            <td><span class="text-success font-weight-bold">37.4%</span></td>
                            <td><span class="text-info">142.8%</span></td>
                            <td><i class="fas fa-arrow-up text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Termómetro Digital</strong></td>
                            <td><span class="badge badge-info">Dispositivos</span></td>
                            <td>1,523</td>
                            <td>$38,075.00</td>
                            <td>$22,845.00</td>
                            <td>$15,230.00</td>
                            <td><span class="text-success font-weight-bold">40.0%</span></td>
                            <td><span class="text-info">166.7%</span></td>
                            <td><i class="fas fa-arrow-up text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Vitamina C 1000mg</strong></td>
                            <td><span class="badge badge-warning">Suplementos</span></td>
                            <td>3,215</td>
                            <td>$32,150.00</td>
                            <td>$22,505.00</td>
                            <td>$9,645.00</td>
                            <td><span class="text-warning font-weight-bold">30.0%</span></td>
                            <td><span class="text-info">128.6%</span></td>
                            <td><i class="fas fa-minus text-muted"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Jeringas 5ml</strong></td>
                            <td><span class="badge badge-info">Dispositivos</span></td>
                            <td>8,547</td>
                            <td>$25,641.00</td>
                            <td>$17,094.00</td>
                            <td>$8,547.00</td>
                            <td><span class="text-warning font-weight-bold">33.3%</span></td>
                            <td><span class="text-info">150.0%</span></td>
                            <td><i class="fas fa-arrow-down text-danger"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Paracetamol 500mg</strong></td>
                            <td><span class="badge badge-primary">Medicamentos</span></td>
                            <td>5,234</td>
                            <td>$20,936.00</td>
                            <td>$13,084.00</td>
                            <td>$7,852.00</td>
                            <td><span class="text-warning font-weight-bold">37.5%</span></td>
                            <td><span class="text-info">160.0%</span></td>
                            <td><i class="fas fa-arrow-up text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Guantes Látex</strong></td>
                            <td><span class="badge badge-info">Dispositivos</span></td>
                            <td>12,450</td>
                            <td>$18,675.00</td>
                            <td>$12,450.00</td>
                            <td>$6,225.00</td>
                            <td><span class="text-warning font-weight-bold">33.3%</span></td>
                            <td><span class="text-info">150.0%</span></td>
                            <td><i class="fas fa-minus text-muted"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Alcohol 70%</strong></td>
                            <td><span class="badge badge-warning">Cuidado Personal</span></td>
                            <td>2,890</td>
                            <td>$14,450.00</td>
                            <td>$10,115.00</td>
                            <td>$4,335.00</td>
                            <td><span class="text-danger font-weight-bold">30.0%</span></td>
                            <td><span class="text-info">142.9%</span></td>
                            <td><i class="fas fa-arrow-up text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Gasas Estériles</strong></td>
                            <td><span class="badge badge-info">Dispositivos</span></td>
                            <td>4,567</td>
                            <td>$13,701.00</td>
                            <td>$9,567.00</td>
                            <td>$4,134.00</td>
                            <td><span class="text-warning font-weight-bold">30.2%</span></td>
                            <td><span class="text-info">143.2%</span></td>
                            <td><i class="fas fa-arrow-up text-success"></i></td>
                        </tr>
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <th colspan="3">TOTALES</th>
                            <th>$209,148.00</th>
                            <th>$135,130.00</th>
                            <th>$73,018.00</th>
                            <th><strong>34.9%</strong></th>
                            <th><strong>154.0%</strong></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Análisis de Rentabilidad por Cliente -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Clientes por Rentabilidad</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Ingresos</th>
                                    <th>Margen</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Hospital Central</td>
                                    <td>$287,450</td>
                                    <td>$89,234</td>
                                    <td class="text-success">31.0%</td>
                                </tr>
                                <tr>
                                    <td>Farmacia Principal</td>
                                    <td>$198,720</td>
                                    <td>$63,451</td>
                                    <td class="text-success">31.9%</td>
                                </tr>
                                <tr>
                                    <td>Clínica San José</td>
                                    <td>$176,340</td>
                                    <td>$52,902</td>
                                    <td class="text-success">30.0%</td>
                                </tr>
                                <tr>
                                    <td>Laboratorio Médico</td>
                                    <td>$143,890</td>
                                    <td>$47,084</td>
                                    <td class="text-success">32.7%</td>
                                </tr>
                                <tr>
                                    <td>Farmacia Salud</td>
                                    <td>$132,450</td>
                                    <td>$41,872</td>
                                    <td class="text-success">31.6%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Productos con Mayor Impacto</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Impacto</th>
                                    <th>Tendencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Medicamentos Rx</td>
                                    <td><span class="badge badge-success">Alto</span></td>
                                    <td><i class="fas fa-arrow-up text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>Dispositivos Médicos</td>
                                    <td><span class="badge badge-warning">Medio</span></td>
                                    <td><i class="fas fa-arrow-up text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>Suplementos</td>
                                    <td><span class="badge badge-warning">Medio</span></td>
                                    <td><i class="fas fa-minus text-muted"></i></td>
                                </tr>
                                <tr>
                                    <td>OTC</td>
                                    <td><span class="badge badge-info">Bajo</span></td>
                                    <td><i class="fas fa-arrow-down text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td>Cuidado Personal</td>
                                    <td><span class="badge badge-info">Bajo</span></td>
                                    <td><i class="fas fa-arrow-up text-success"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
// Configuración de gráficos
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    initializeDataTable();
});

function initializeCharts() {
    // Gráfico de Rentabilidad
    const ctxRentabilidad = document.getElementById('rentabilidadChart').getContext('2d');
    new Chart(ctxRentabilidad, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'Ingresos',
                data: [234000, 245000, 267000, 278000, 289000, 298000, 312000, 289000, 298000, 312000, 298000, 285000],
                borderColor: 'rgb(78, 115, 223)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.1
            }, {
                label: 'Costo',
                data: [162000, 169000, 184000, 192000, 199000, 205000, 215000, 199000, 205000, 215000, 205000, 196000],
                borderColor: 'rgb(246, 194, 62)',
                backgroundColor: 'rgba(246, 194, 62, 0.1)',
                tension: 0.1
            }, {
                label: 'Margen Bruto',
                data: [72000, 76000, 83000, 86000, 90000, 93000, 97000, 90000, 93000, 97000, 93000, 89000],
                borderColor: 'rgb(72, 187, 120)',
                backgroundColor: 'rgba(72, 187, 120, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Evolución de Rentabilidad 2024'
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

    // Gráfico de Categorías
    const ctxCategoria = document.getElementById('categoriaChart').getContext('2d');
    new Chart(ctxCategoria, {
        type: 'doughnut',
        data: {
            labels: ['Medicamentos', 'Dispositivos', 'Suplementos', 'Cuidado Personal'],
            datasets: [{
                data: [34.2, 28.7, 22.1, 15.0],
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
    $('#rentabilidadTable').DataTable({
        order: [[6, 'desc']],
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        columnDefs: [
            { targets: [2, 3, 4, 5], className: 'text-right' },
            { targets: [6, 7], className: 'text-center' }
        ]
    });
}

// Funciones de exportación
function exportarExcel() {
    const form = document.getElementById('filtrosForm');
    form.action = '{{ route("ventas.reportes.rentabilidad.exportar") }}';
    form.method = 'POST';
    
    // Agregar token CSRF
    let token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = '{{ csrf_token() }}';
    form.appendChild(token);
    
    // Agregar parámetro de formato
    let formato = document.createElement('input');
    formato.type = 'hidden';
    formato.name = 'formato';
    formato.value = 'excel';
    form.appendChild(formato);
    
    form.submit();
}

function exportarPDF() {
    const form = document.getElementById('filtrosForm');
    form.action = '{{ route("ventas.reportes.rentabilidad.exportar") }}';
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
    window.location.href = '{{ route("ventas.reportes.rentabilidad") }}';
}

function exportarGrafico(tipo) {
    alert('Función de exportación de gráfico: ' + tipo);
}

function cambiarVista(vista) {
    alert('Cambiando a vista: ' + vista);
}
</script>
@endsection