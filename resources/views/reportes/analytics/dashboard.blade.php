@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-pie text-primary"></i> Dashboard Analytics
        </h1>
        <div>
            <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Reportes
            </a>
            <button class="btn btn-outline-primary" onclick="refrescarDashboard()">
                <i class="fas fa-sync-alt"></i> Refrescar
            </button>
            <button class="btn btn-outline-success" onclick="exportarDashboard()">
                <i class="fas fa-download"></i> Exportar
            </button>
        </div>
    </div>

    <!-- Filtros de Tiempo -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Configuración del Dashboard</h6>
        </div>
        <div class="card-body">
            <form id="dashboardForm" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Período</label>
                            <select class="form-control" name="periodo" id="periodo" onchange="cargarDatos()">
                                <option value="hoy">Hoy</option>
                                <option value="semana" selected>Esta Semana</option>
                                <option value="mes" selected>Este Mes</option>
                                <option value="trimestre">Este Trimestre</option>
                                <option value="año">Este Año</option>
                                <option value="personalizado">Personalizado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" 
                                   value="{{ date('Y-m-01') }}" id="fechaInicio">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha Fin</label>
                            <input type="date" class="form-control" name="fecha_fin" 
                                   value="{{ date('Y-m-t') }}" id="fechaFin">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Métrica Principal</label>
                            <select class="form-control" name="metrica">
                                <option value="ventas" selected>Ventas</option>
                                <option value="clientes">Clientes</option>
                                <option value="productos">Productos</option>
                                <option value="rentabilidad">Rentabilidad</option>
                            </select>
                        </div>
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
                                Ventas Totales
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">S/ 2,847,523</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +12.5% vs mes anterior
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="progress">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 85%"></div>
                        </div>
                        <small class="text-muted">85% de la meta mensual</small>
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
                                Clientes Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2,634</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +8.3% vs mes anterior
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 92%"></div>
                        </div>
                        <small class="text-muted">92% de retención de clientes</small>
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
                                Productos Vendidos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">45,789</div>
                            <div class="text-xs text-info">
                                <i class="fas fa-arrow-up"></i> +15.7% vs mes anterior
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="progress">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 78%"></div>
                        </div>
                        <small class="text-muted">78% de productos en stock</small>
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
                                Margen Promedio
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">31.4%</div>
                            <div class="text-xs text-warning">
                                <i class="fas fa-arrow-down"></i> -1.2% vs mes anterior
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 31.4%"></div>
                        </div>
                        <small class="text-muted">Margen objetivo: 35%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principales -->
    <div class="row">
        <!-- Gráfico de Tendencias -->
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Tendencia de Ventas y Rentabilidad</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <div class="dropdown-header">Opciones:</div>
                            <a class="dropdown-item" href="#" onclick="exportarGrafico('tendencias')">Exportar Gráfico</a>
                            <a class="dropdown-item" href="#" onclick="cambiarVista('diario')">Vista Diaria</a>
                            <a class="dropdown-item" href="#" onclick="cambiarVista('mensual')">Vista Mensual</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="tendenciasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribución por Categorías -->
        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ventas por Categoría</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="categoriaChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="font-weight-bold text-primary">Medicamentos</div>
                                <div class="text-sm">42.3%</div>
                                <div class="text-xs text-success">S/ 1,204,567</div>
                            </div>
                            <div class="col-6">
                                <div class="font-weight-bold text-success">Dispositivos</div>
                                <div class="text-sm">28.7%</div>
                                <div class="text-xs text-success">S/ 817,234</div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="font-weight-bold text-warning">Suplementos</div>
                                <div class="text-sm">18.2%</div>
                                <div class="text-xs text-success">S/ 518,456</div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="font-weight-bold text-info">Otros</div>
                                <div class="text-sm">10.8%</div>
                                <div class="text-xs text-success">S/ 307,266</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis Detallado -->
    <div class="row">
        <!-- Top Productos -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Productos Más Vendidos</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th>Unidades</th>
                                    <th>Ingresos</th>
                                    <th>Tendencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-warning">1</span></td>
                                    <td>Amoxicilina 500mg</td>
                                    <td class="text-right">2,847</td>
                                    <td class="text-right">S/ 45,520</td>
                                    <td><i class="fas fa-arrow-up text-success"></i></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-secondary">2</span></td>
                                    <td>Termómetro Digital</td>
                                    <td class="text-right">1,523</td>
                                    <td class="text-right">S/ 38,075</td>
                                    <td><i class="fas fa-arrow-up text-success"></i></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-info">3</span></td>
                                    <td>Vitamina C 1000mg</td>
                                    <td class="text-right">3,215</td>
                                    <td class="text-right">S/ 32,150</td>
                                    <td><i class="fas fa-minus text-muted"></i></td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>Jeringas 5ml</td>
                                    <td class="text-right">8,547</td>
                                    <td class="text-right">S/ 25,641</td>
                                    <td><i class="fas fa-arrow-down text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>Paracetamol 500mg</td>
                                    <td class="text-right">5,234</td>
                                    <td class="text-right">S/ 20,936</td>
                                    <td><i class="fas fa-arrow-up text-success"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clientes VIP -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Clientes VIP</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Total Compras</th>
                                    <th>Última Compra</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-warning">1</span></td>
                                    <td>Hospital Central S.A.</td>
                                    <td class="text-right">S/ 287,450</td>
                                    <td class="text-right">25/01/2024</td>
                                    <td><span class="badge badge-success">Activo</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-secondary">2</span></td>
                                    <td>Farmacia Principal</td>
                                    <td class="text-right">S/ 198,720</td>
                                    <td class="text-right">24/01/2024</td>
                                    <td><span class="badge badge-success">Activo</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-info">3</span></td>
                                    <td>Clínica San José</td>
                                    <td class="text-right">S/ 176,340</td>
                                    <td class="text-right">23/01/2024</td>
                                    <td><span class="badge badge-success">Activo</span></td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>Laboratorio Médico</td>
                                    <td class="text-right">S/ 143,890</td>
                                    <td class="text-right">22/01/2024</td>
                                    <td><span class="badge badge-success">Activo</span></td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>Farmacia Salud</td>
                                    <td class="text-right">S/ 132,450</td>
                                    <td class="text-right">21/01/2024</td>
                                    <td><span class="badge badge-warning">Regular</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis de Rendimiento -->
    <div class="row">
        <!-- Comparativo Mensual -->
        <div class="col-xl-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Comparativo de Rendimiento Mensual</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="comparativoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicadores Rápidos -->
        <div class="col-xl-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Indicadores Clave</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span>Tasa de Conversión</span>
                                <span class="font-weight-bold">68.5%</span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 68.5%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span>Ticket Promedio</span>
                                <span class="font-weight-bold">S/ 1,247</span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 82%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span>Frecuencia de Compra</span>
                                <span class="font-weight-bold">2.3 veces/mes</span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 76%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span>Valor de Vida del Cliente</span>
                                <span class="font-weight-bold">S/ 15,678</span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 89%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas y Notificaciones -->
            <div class="card shadow mt-3">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Alertas y Notificaciones</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Inventario Bajo:</strong> 15 productos por debajo del stock mínimo
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Meta del Mes:</strong> 85% completada, faltan S/ 423,567
                    </div>
                    <div class="alert alert-success">
                        <i class="fas fa-trophy"></i>
                        <strong>Cliente VIP:</strong> Hospital Central superó objetivo mensual
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
    // Gráfico de Tendencias
    const ctxTendencias = document.getElementById('tendenciasChart').getContext('2d');
    new Chart(ctxTendencias, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'Ventas',
                data: [234000, 245000, 267000, 278000, 289000, 298000, 312000, 289000, 298000, 312000, 298000, 285000],
                borderColor: 'rgb(78, 115, 223)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.1,
                fill: true
            }, {
                label: 'Rentabilidad',
                data: [72000, 76000, 83000, 86000, 90000, 93000, 97000, 90000, 93000, 97000, 93000, 89000],
                borderColor: 'rgb(72, 187, 120)',
                backgroundColor: 'rgba(72, 187, 120, 0.1)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Evolución de Ventas y Rentabilidad 2024'
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

    // Gráfico de Categorías
    const ctxCategoria = document.getElementById('categoriaChart').getContext('2d');
    new Chart(ctxCategoria, {
        type: 'doughnut',
        data: {
            labels: ['Medicamentos', 'Dispositivos', 'Suplementos', 'Otros'],
            datasets: [{
                data: [42.3, 28.7, 18.2, 10.8],
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

    // Gráfico Comparativo
    const ctxComparativo = document.getElementById('comparativoChart').getContext('2d');
    new Chart(ctxComparativo, {
        type: 'bar',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'Meta',
                data: [280000, 280000, 280000, 280000, 280000, 280000, 280000, 280000, 280000, 280000, 280000, 280000],
                backgroundColor: 'rgba(231, 74, 59, 0.3)',
                borderColor: 'rgb(231, 74, 59)',
                borderWidth: 1
            }, {
                label: 'Real',
                data: [234000, 245000, 267000, 278000, 289000, 298000, 312000, 289000, 298000, 312000, 298000, 285000],
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderColor: 'rgb(78, 115, 223)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Comparativo Meta vs Realizado (S/)'
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
}

function cargarDatos() {
    Swal.fire({
        title: 'Cargando datos...',
        text: 'Actualizando dashboard con nuevos parámetros',
        timer: 1500,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Actualizado!', 'Los datos del dashboard han sido actualizados.', 'success');
    });
}

function refrescarDashboard() {
    Swal.fire({
        title: 'Refrescando Dashboard...',
        text: 'Obteniendo datos más recientes',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Refrescado!', 'El dashboard ha sido actualizado con datos en tiempo real.', 'success');
    });
}

function exportarDashboard() {
    Swal.fire({
        title: 'Exportando Dashboard...',
        text: 'Generando reporte del dashboard analytics',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Exportado!',
            text: 'El dashboard ha sido exportado exitosamente.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Descargar',
            cancelButtonText: 'Cerrar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Descargando...', 'El archivo se está descargando.', 'info');
            }
        });
    });
}

function exportarGrafico(tipo) {
    Swal.fire({
        title: 'Exportando gráfico...',
        text: `Descargando gráfico de ${tipo}`,
        timer: 1500,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Exportado!', 'El gráfico ha sido descargado.', 'success');
    });
}

function cambiarVista(vista) {
    Swal.fire({
        title: 'Cambiando vista...',
        text: `Aplicando vista ${vista}`,
        timer: 1000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Vista actualizada!', `Se aplicó la vista ${vista}`, 'success');
    });
}
</script>
@endsection