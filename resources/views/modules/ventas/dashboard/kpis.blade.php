
@extends('layouts.app')

@section('title', 'KPIs de Ventas')

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
                            <li class="breadcrumb-item active">KPIs</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-chart-bar text-primary"></i>
                        KPIs de Ventas
                    </h1>
                    <p class="text-muted mb-0">Indicadores clave de rendimiento y métricas comerciales</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="exportarKPIs()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <a href="{{ route('ventas.dashboard.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Período -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-2">Período de Análisis</h6>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="periodo" id="hoy" value="hoy" autocomplete="off">
                                <label class="btn btn-outline-primary" for="hoy">Hoy</label>

                                <input type="radio" class="btn-check" name="periodo" id="semana" value="semana" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="semana">Esta Semana</label>

                                <input type="radio" class="btn-check" name="periodo" id="mes" value="mes" autocomplete="off">
                                <label class="btn btn-outline-primary" for="mes">Este Mes</label>

                                <input type="radio" class="btn-check" name="periodo" id="trimestre" value="trimestre" autocomplete="off">
                                <label class="btn btn-outline-primary" for="trimestre">Este Trimestre</label>

                                <input type="radio" class="btn-check" name="periodo" id="personalizado" value="personalizado" autocomplete="off">
                                <label class="btn btn-outline-primary" for="personalizado">Personalizado</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control" id="fechaInicio" value="{{ date('Y-m-01') }}">
                                <input type="date" class="form-control" id="fechaFin" value="{{ date('Y-m-t') }}">
                                <button class="btn btn-primary" onclick="actualizarKPIs()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Principales -->
    <div class="row mb-4">
        <!-- KPIs Financieros -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary bg-opacity-10 border-0">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-dollar-sign me-2"></i>
                        KPIs Financieros
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-money-bill-wave text-success fs-1"></i>
                                </div>
                                <h3 class="text-success mb-1">S/ 85,420</h3>
                                <p class="text-muted mb-2">Ventas Netas</p>
                                <div class="progress mb-2" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: 85%"></div>
                                </div>
                                <small class="text-success">
                                    <i class="fas fa-arrow-up"></i> +12.5%
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-percentage text-info fs-1"></i>
                                </div>
                                <h3 class="text-info mb-1">35.2%</h3>
                                <p class="text-muted mb-2">Margen Bruto</p>
                                <div class="progress mb-2" style="height: 4px;">
                                    <div class="progress-bar bg-info" style="width: 70%"></div>
                                </div>
                                <small class="text-info">
                                    <i class="fas fa-arrow-up"></i> +2.1%
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-chart-line text-warning fs-1"></i>
                                </div>
                                <h3 class="text-warning mb-1">S/ 108.59</h3>
                                <p class="text-muted mb-2">Ticket Promedio</p>
                                <div class="progress mb-2" style="height: 4px;">
                                    <div class="progress-bar bg-warning" style="width: 65%"></div>
                                </div>
                                <small class="text-warning">
                                    <i class="fas fa-arrow-up"></i> +3.2%
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-coins text-danger fs-1"></i>
                                </div>
                                <h3 class="text-danger mb-1">S/ 2,450</h3>
                                <p class="text-muted mb-2">Cuentas por Cobrar</p>
                                <div class="progress mb-2" style="height: 4px;">
                                    <div class="progress-bar bg-danger" style="width: 45%"></div>
                                </div>
                                <small class="text-danger">
                                    <i class="fas fa-arrow-down"></i> -5.2%
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs Operacionales -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info bg-opacity-10 border-0">
                    <h5 class="mb-0 text-info">
                        <i class="fas fa-cogs me-2"></i>
                        KPIs Operacionales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-shopping-cart text-primary fs-1"></i>
                                </div>
                                <h3 class="text-primary mb-1">786</h3>
                                <p class="text-muted mb-2">Total Ventas</p>
                                <div class="progress mb-2" style="height: 4px;">
                                    <div class="progress-bar bg-primary" style="width: 78%"></div>
                                </div>
                                <small class="text-primary">
                                    <i class="fas fa-arrow-up"></i> +8.5%
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-users text-success fs-1"></i>
                                </div>
                                <h3 class="text-success mb-1">623</h3>
                                <p class="text-muted mb-2">Clientes Únicos</p>
                                <div class="progress mb-2" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: 85%"></div>
                                </div>
                                <small class="text-success">
                                    <i class="fas fa-arrow-up"></i> +15.2%
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-handshake text-warning fs-1"></i>
                                </div>
                                <h3 class="text-warning mb-1">95.3%</h3>
                                <p class="text-muted mb-2">Satisfacción</p>
                                <div class="progress mb-2" style="height: 4px;">
                                    <div class="progress-bar bg-warning" style="width: 95%"></div>
                                </div>
                                <small class="text-warning">
                                    <i class="fas fa-arrow-up"></i> +1.8%
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-clock text-danger fs-1"></i>
                                </div>
                                <h3 class="text-danger mb-1">3.2 min</h3>
                                <p class="text-muted mb-2">Tiempo Promedio</p>
                                <div class="progress mb-2" style="height: 4px;">
                                    <div class="progress-bar bg-danger" style="width: 30%"></div>
                                </div>
                                <small class="text-danger">
                                    <i class="fas fa-arrow-down"></i> -0.5 min
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis Detallado -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line text-primary"></i>
                        Evolución de KPIs en el Tiempo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 400px;">
                        <canvas id="kpisChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-bullseye text-success"></i>
                        Cumplimiento de Metas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="space-y-4">
                        <div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ventas Mensuales</span>
                                <span class="text-success">95%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: 95%"></div>
                            </div>
                            <small class="text-muted">S/ 285,000 de S/ 300,000</small>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Clientes Nuevos</span>
                                <span class="text-primary">78%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: 78%"></div>
                            </div>
                            <small class="text-muted">156 de 200 clientes</small>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tiempo de Respuesta</span>
                                <span class="text-warning">62%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: 62%"></div>
                            </div>
                            <small class="text-muted">3.2 min de objetivo 2 min</small>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Satisfacción del Cliente</span>
                                <span class="text-success">96%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: 96%"></div>
                            </div>
                            <small class="text-muted">4.8 de 5.0 estrellas</small>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Recuperación de Cartera</span>
                                <span class="text-danger">45%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: 45%"></div>
                            </div>
                            <small class="text-muted">S/ 2,450 por recuperar</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ranking de Vendedores -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy text-warning"></i>
                        Ranking de Vendedores
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="rankingVendedores">
                            <thead>
                                <tr>
                                    <th>Posición</th>
                                    <th>Vendedor</th>
                                    <th>Ventas</th>
                                    <th>Monto</th>
                                    <th>Meta</th>
                                    <th>Performance</th>
                                    <th>Tendencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="badge bg-warning">1</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/40" class="rounded-circle me-3" alt="Vendedor">
                                            <div>
                                                <h6 class="mb-0">Ana García</h6>
                                                <small class="text-muted">Farmacéutica Senior</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><strong>234 ventas</strong></td>
                                    <td><strong class="text-success">S/ 45,680</strong></td>
                                    <td>S/ 50,000</td>
                                    <td>
                                        <div class="progress" style="height: 6px; width: 100px;">
                                            <div class="progress-bar bg-success" style="width: 91%"></div>
                                        </div>
                                        <small>91%</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-arrow-up text-success"></i>
                                        +15%
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">2</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/40" class="rounded-circle me-3" alt="Vendedor">
                                            <div>
                                                <h6 class="mb-0">Carlos López</h6>
                                                <small class="text-muted">Vendedor</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><strong>198 ventas</strong></td>
                                    <td><strong class="text-primary">S/ 38,920</strong></td>
                                    <td>S/ 45,000</td>
                                    <td>
                                        <div class="progress" style="height: 6px; width: 100px;">
                                            <div class="progress-bar bg-primary" style="width: 87%"></div>
                                        </div>
                                        <small>87%</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-arrow-up text-success"></i>
                                        +8%
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: #cd7f32;">3</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/40" class="rounded-circle me-3" alt="Vendedor">
                                            <div>
                                                <h6 class="mb-0">María Rodríguez</h6>
                                                <small class="text-muted">Farmacéutica</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><strong>176 ventas</strong></td>
                                    <td><strong class="text-info">S/ 32,450</strong></td>
                                    <td>S/ 40,000</td>
                                    <td>
                                        <div class="progress" style="height: 6px; width: 100px;">
                                            <div class="progress-bar bg-info" style="width: 81%"></div>
                                        </div>
                                        <small>81%</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-arrow-up text-success"></i>
                                        +12%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt text-warning"></i>
                        Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <button class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4" onclick="generarReporte()">
                                <i class="fas fa-file-excel mb-2 fs-2"></i>
                                <span>Generar Reporte</span>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <button class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4" onclick="enviarAlerta()">
                                <i class="fas fa-bell mb-2 fs-2"></i>
                                <span>Enviar Alertas</span>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <button class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4" onclick="revisarMetas()">
                                <i class="fas fa-target mb-2 fs-2"></i>
                                <span>Revisar Metas</span>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <button class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4" onclick="exportarDatos()">
                                <i class="fas fa-download mb-2 fs-2"></i>
                                <span>Exportar Datos</span>
                            </button>
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
    // Gráfico de Evolución de KPIs
    const kpisCtx = document.getElementById('kpisChart').getContext('2d');
    new Chart(kpisCtx, {
        type: 'line',
        data: {
            labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6', 'Sem 7', 'Sem 8'],
            datasets: [{
                label: 'Ventas (S/ 1000)',
                data: [65, 78, 82, 90, 85, 92, 88, 95],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Margen Bruto (%)',
                data: [32, 33, 34, 35, 34, 36, 35, 35],
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Satisfacción (%)',
                data: [92, 93, 94, 93, 95, 94, 95, 95],
                borderColor: '#fd7e14',
                backgroundColor: 'rgba(253, 126, 20, 0.1)',
                tension: 0.4,
                fill: true
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
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            }
        }
    });

    // Event listeners para período
    $('input[name="periodo"]').on('change', function() {
        const periodo = $(this).val();
        actualizarPeriodo(periodo);
    });
});

function actualizarPeriodo(periodo) {
    let fechaInicio, fechaFin;
    const hoy = new Date();
    
    switch(periodo) {
        case 'hoy':
            fechaInicio = fechaFin = hoy.toISOString().split('T')[0];
            break;
        case 'semana':
            const inicioSemana = new Date(hoy);
            inicioSemana.setDate(hoy.getDate() - hoy.getDay());
            fechaInicio = inicioSemana.toISOString().split('T')[0];
            fechaFin = hoy.toISOString().split('T')[0];
            break;
        case 'mes':
            fechaInicio = hoy.getFullYear() + '-' + String(hoy.getMonth() + 1).padStart(2, '0') + '-01';
            const ultimoDiaMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
            fechaFin = ultimoDiaMes.toISOString().split('T')[0];
            break;
        case 'trimestre':
            const trimestre = Math.floor(hoy.getMonth() / 3);
            fechaInicio = hoy.getFullYear() + '-' + String(trimestre * 3 + 1).padStart(2, '0') + '-01';
            fechaFin = hoy.getFullYear() + '-' + String(trimestre * 3 + 3).padStart(2, '0') + '-31';
            break;
    }
    
    $('#fechaInicio').val(fechaInicio);
    $('#fechaFin').val(fechaFin);
    
    actualizarKPIs();
}

function actualizarKPIs() {
    Swal.fire({
        title: 'Actualizando KPIs...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });

    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'KPIs actualizados',
            showConfirmButton: false,
            timer: 1500
        });
    }, 1500);
}

function exportarKPIs() {
    Swal.fire({
        title: 'Exportar KPIs',
        text: '¿Qué formato prefieres?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Excel',
        cancelButtonText: 'PDF',
        showDenyButton: true,
        denyButtonText: 'CSV'
    }).then((result) => {
        let formato = '';
        if (result.isConfirmed) formato = 'Excel';
        else if (result.isDenied) formato = 'CSV';
        else formato = 'PDF';

        Swal.fire({
            icon: 'success',
            title: `Reporte ${formato} generado`,
            text: 'El archivo se ha descargado exitosamente'
        });
    });
}

function generarReporte() {
    Swal.fire({
        title: 'Generando Reporte...',
        html: 'Procesando datos de KPIs',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });

    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Reporte generado',
            text: 'El reporte de KPIs está listo para descargar'
        });
    }, 2000);
}

function enviarAlerta() {
    Swal.fire({
        title: 'Enviar Alerta',
        input: 'select',
        inputOptions: {
            'bajo_rendimiento': 'Bajo Rendimiento',
            'meta_alcanzada': 'Meta Alcanzada',
            'revision_necesaria': 'Revisión Necesaria'
        },
        inputPlaceholder: 'Selecciona el tipo de alerta',
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Alerta enviada',
                text: 'La notificación se ha enviado a los equipos correspondientes'
            });
        }
    });
}

function revisarMetas() {
    window.location.href = '{{ route("ventas.dashboard.tendencias") }}';
}

function exportarDatos() {
    Swal.fire({
        title: 'Exportar Datos',
        html: `
            <div class="row">
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="datos_financieros" checked>
                        <label class="form-check-label" for="datos_financieros">Datos Financieros</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="datos_operacionales" checked>
                        <label class="form-check-label" for="datos_operacionales">Datos Operacionales</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="datos_vendedores" checked>
                        <label class="form-check-label" for="datos_vendedores">Datos de Vendedores</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="historicos">
                        <label class="form-check-label" for="historicos">Incluir Históricos (6 meses)</label>
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Exportar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const checks = {
                financieros: document.getElementById('datos_financieros').checked,
                operacionales: document.getElementById('datos_operacionales').checked,
                vendedores: document.getElementById('datos_vendedores').checked,
                historicos: document.getElementById('historicos').checked
            };
            return checks;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Datos exportados',
                text: 'Los datos seleccionados han sido exportados exitosamente'
            });
        }
    });
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

.progress {
    border-radius: 0.5rem;
}

.btn-check:checked + .btn {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.space-y-4 > * + * {
    margin-top: 1rem !important;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}
</style>
@endsection