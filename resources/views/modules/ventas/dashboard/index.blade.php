@extends('layouts.app')

@section('title', 'Dashboard de Ventas')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-chart-line text-primary"></i>
                        Dashboard de Ventas
                    </h1>
                    <p class="text-muted mb-0">Panel principal de control de ventas y facturación</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="actualizarDatos()">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                        <a href="{{ route('ventas.kpis') }}" class="btn btn-primary">
                            <i class="fas fa-chart-bar"></i> Ver KPIs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Rápidos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form class="row g-3" id="filtrosForm">
                        <div class="col-md-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fechaInicio" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFin" value="{{ date('Y-m-t') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Vendedor</label>
                            <select class="form-select" id="vendedor">
                                <option value="">Todos los vendedores</option>
                                @foreach($vendedores ?? [] as $vendedor)
                                <option value="{{ $vendedor->$id }}">{{ $vendedor->$nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="estado">
                                <option value="">Todos los estados</option>
                                <option value="completada">Completada</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="anulada">Anulada</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Principales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Ventas del Día</p>
                            <h4 class="text-success mb-0">S/ 15,420.00</h4>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> +12.5% vs ayer
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-dollar-sign text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Ventas del Mes</p>
                            <h4 class="text-primary mb-0">S/ 285,650.00</h4>
                            <small class="text-primary">
                                <i class="fas fa-arrow-up"></i> +8.2% vs mes anterior
                            </small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-chart-bar text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Número de Ventas</p>
                            <h4 class="text-info mb-0">142</h4>
                            <small class="text-info">
                                <i class="fas fa-arrow-up"></i> +5 ventas vs ayer
                            </small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-shopping-cart text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Ticket Promedio</p>
                            <h4 class="text-warning mb-0">S/ 108.59</h4>
                            <small class="text-warning">
                                <i class="fas fa-arrow-up"></i> +S/ 3.25 vs ayer
                            </small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-calculator text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principales -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line text-primary"></i>
                        Evolución de Ventas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="ventasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie text-info"></i>
                        Top Productos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="productosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas de Datos -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-star text-warning"></i>
                        Mejores Vendedores
                    </h5>
                    <a href="{{ route('ventas.vendedores') }}" class="btn btn-sm btn-outline-primary">
                        Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Vendedor</th>
                                    <th>Ventas</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="Vendedor">
                                            <span>Ana García</span>
                                        </div>
                                    </td>
                                    <td>45 ventas</td>
                                    <td><strong>S/ 12,580</strong></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="Vendedor">
                                            <span>Carlos López</span>
                                        </div>
                                    </td>
                                    <td>38 ventas</td>
                                    <td><strong>S/ 10,240</strong></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="Vendedor">
                                            <span>María Rodríguez</span>
                                        </div>
                                    </td>
                                    <td>32 ventas</td>
                                    <td><strong>S/ 8,920</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock text-info"></i>
                        Ventas Recientes
                    </h5>
                    <a href="{{ route('ventas.facturacion.index') }}" class="btn btn-sm btn-outline-primary">
                        Ver Todas
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Factura</th>
                                    <th>Cliente</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong>F-001245</strong>
                                        <br>
                                        <small class="text-muted">10:30 AM</small>
                                    </td>
                                    <td>Juan Pérez</td>
                                    <td><strong>S/ 156.80</strong></td>
                                    <td>
                                        <span class="badge bg-success">Completada</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>F-001244</strong>
                                        <br>
                                        <small class="text-muted">10:15 AM</small>
                                    </td>
                                    <td>Luisa Martínez</td>
                                    <td><strong>S/ 89.50</strong></td>
                                    <td>
                                        <span class="badge bg-success">Completada</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>F-001243</strong>
                                        <br>
                                        <small class="text-muted">09:45 AM</small>
                                    </td>
                                    <td>Pedro Sánchez</td>
                                    <td><strong>S/ 234.20</strong></td>
                                    <td>
                                        <span class="badge bg-warning">Pendiente</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas y Notificaciones -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-bell text-warning"></i>
                        Alertas y Recordatorios
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <div class="alert alert-info border-0">
                                <div class="d-flex">
                                    <i class="fas fa-info-circle me-2 mt-1"></i>
                                    <div>
                                        <strong>Meta del Mes</strong>
                                        <p class="mb-0">Ya alcanzaste el 95% de tu meta mensual</p>
                                        <small>Proyección: S/ 300,000</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <div class="alert alert-warning border-0">
                                <div class="d-flex">
                                    <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                    <div>
                                        <strong>Cuentas por Cobrar</strong>
                                        <p class="mb-0">5 facturas vencidas requieren seguimiento</p>
                                        <small>Total: S/ 2,450.00</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <div class="alert alert-success border-0">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle me-2 mt-1"></i>
                                    <div>
                                        <strong>Excelente Rendimiento</strong>
                                        <p class="mb-0">Ventas 15% por encima del promedio</p>
                                        <small>Felicitaciones al equipo</small>
                                    </div>
                                </div>
                            </div>
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
    // Gráfico de Evolución de Ventas
    const ventasCtx = document.getElementById('ventasChart').getContext('2d');
    new Chart(ventasCtx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [{
                label: 'Ventas (S/)',
                data: [8200, 9500, 10200, 11800, 13600, 15400, 12800],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Meta (S/)',
                data: [10000, 10000, 10000, 10000, 10000, 10000, 10000],
                borderColor: '#dc3545',
                borderDash: [5, 5],
                fill: false
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
                            return 'S/ ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Top Productos
    const productosCtx = document.getElementById('productosChart').getContext('2d');
    new Chart(productosCtx, {
        type: 'doughnut',
        data: {
            labels: ['Paracetamol', 'Ibuprofeno', 'Amoxicilina', 'Omeprazol', 'Otros'],
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

    // Event listeners
    $('#fechaInicio, #fechaFin, #vendedor, #estado').on('change', function() {
        actualizarDatos();
    });
});

function actualizarDatos() {
    // Mostrar loading
    Swal.fire({
        title: 'Actualizando datos...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });

    // Simular actualización
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Datos actualizados',
            showConfirmButton: false,
            timer: 1500
        });
        
        // Aquí iría la lógica real de actualización
        console.log('Datos actualizados con filtros:', {
            fechaInicio: $('#fechaInicio').val(),
            fechaFin: $('#fechaFin').val(),
            vendedor: $('#vendedor').val(),
            estado: $('#estado').val()
        });
    }, 1500);
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

.alert {
    margin-bottom: 1rem;
}

.badge {
    font-size: 0.75rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-group .btn {
    border-radius: 0.375rem;
}
</style>
@endsection