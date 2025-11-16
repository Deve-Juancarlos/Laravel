@extends('layouts.admin')

@section('title', 'Estadísticas Bancarias')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Análisis de Flujo de Caja</h1>
    <p class="text-muted mb-0">Estadísticas y proyecciones bancarias</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.bancos.index') }}">Bancos</a></li>
<li class="breadcrumb-item active">Estadísticas</li>
@endsection

@section('content')

<style>
    .chart-container {
        position: relative;
        width: 100%;
    }
    
    .chart-container-line {
        height: 350px;
    }
    
    .chart-container-doughnut {
        height: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .chart-container-bar {
        height: 400px;
    }
    
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
    }
    
    .card-header {
        border-bottom: 2px solid #f8f9fa;
        padding: 1.25rem;
    }
    
    .card-header h5 {
        color: #2c3e50;
        font-weight: 600;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s;
    }
    
    .badge-periodo {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
    }
    
    .stat-card {
        border-left: 4px solid #3498db;
    }
    
    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #6c757d;
    }
    
    .form-select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
</style>

<!-- Selector de Período -->
<div class="card border-0 shadow-sm mb-4 stat-card">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Período de Análisis
                </label>
                <select name="periodo" class="form-select" onchange="this.form.submit()">
                    <option value="dia" {{ ($periodo ?? '') == 'dia' ? 'selected' : '' }}>Hoy</option>
                    <option value="semana" {{ ($periodo ?? '') == 'semana' ? 'selected' : '' }}>Esta Semana</option>
                    <option value="mes" {{ ($periodo ?? '') == 'mes' ? 'selected' : '' }}>Este Mes</option>
                    <option value="anio" {{ ($periodo ?? '') == 'anio' ? 'selected' : '' }}>Este Año</option>
                </select>
            </div>
            <div class="col-md-8 text-end">
                <a href="{{ route('admin.bancos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Gráficos -->
<div class="row mb-4">
    <!-- Flujo de Caja -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2 text-primary"></i>Flujo de Caja Acumulado
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container chart-container-line">
                    <canvas id="flujoCajaChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Movimientos por Cuenta -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2 text-primary"></i>Por Cuenta
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container chart-container-doughnut">
                    <canvas id="movimientosPorCuentaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla: Movimientos por Cuenta -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2 text-primary"></i>Resumen por Cuenta Bancaria
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Banco</th>
                                <th>Cuenta</th>
                                <th class="text-end">Ingresos</th>
                                <th class="text-end">Egresos</th>
                                <th class="text-end">Flujo Neto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($estadisticas['por_cuenta'] ?? [] as $cuenta)
                            <tr>
                                <td class="fw-semibold">{{ $cuenta->Banco }}</td>
                                <td><code class="text-muted">{{ $cuenta->Cuenta }}</code></td>
                                <td class="text-end text-success fw-bold">S/ {{ number_format($cuenta->total_ingresos, 2) }}</td>
                                <td class="text-end text-danger fw-bold">S/ {{ number_format($cuenta->total_egresos, 2) }}</td>
                                <td class="text-end fw-bold {{ ($cuenta->total_ingresos - $cuenta->total_egresos) >= 0 ? 'text-success' : 'text-danger' }}">
                                    S/ {{ number_format($cuenta->total_ingresos - $cuenta->total_egresos, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                    No hay datos disponibles
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <th colspan="2" class="text-end">TOTALES:</th>
                                <th class="text-end text-success">
                                    S/ {{ number_format(collect($estadisticas['por_cuenta'] ?? [])->sum('total_ingresos'), 2) }}
                                </th>
                                <th class="text-end text-danger">
                                    S/ {{ number_format(collect($estadisticas['por_cuenta'] ?? [])->sum('total_egresos'), 2) }}
                                </th>
                                <th class="text-end {{ (collect($estadisticas['por_cuenta'] ?? [])->sum('total_ingresos') - collect($estadisticas['por_cuenta'] ?? [])->sum('total_egresos')) >= 0 ? 'text-success' : 'text-danger' }}">
                                    S/ {{ number_format(collect($estadisticas['por_cuenta'] ?? [])->sum('total_ingresos') - collect($estadisticas['por_cuenta'] ?? [])->sum('total_egresos'), 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Evolución Diaria -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-area me-2 text-primary"></i>Evolución de Ingresos vs Egresos
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container chart-container-bar">
                    <canvas id="evolucionDiariaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {

    // Flujo de Caja Acumulado
    const ctxFlujo = document.getElementById('flujoCajaChart').getContext('2d');
    new Chart(ctxFlujo, {
        type: 'line',
        data: {
            labels: [
                @foreach($flujoCaja ?? [] as $flujo)
                    '{{ \Carbon\Carbon::parse($flujo->Fecha)->format("d/m") }}'@if(!$loop->last),@endif
                @endforeach
            ],
            datasets: [{
                label: 'Saldo Acumulado',
                data: [
                    @foreach($flujoCaja ?? [] as $flujo)
                        {{ $flujo->saldo_acumulado }}@if(!$loop->last),@endif
                    @endforeach
                ],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 14 },
                    bodyFont: { size: 13 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Movimientos por Cuenta
    const ctxCuenta = document.getElementById('movimientosPorCuentaChart').getContext('2d');
    new Chart(ctxCuenta, {
        type: 'doughnut',
        data: {
            labels: [
                @foreach($estadisticas['por_cuenta'] ?? [] as $cuenta)
                    '{{ $cuenta->Banco }}'@if(!$loop->last),@endif
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($estadisticas['por_cuenta'] ?? [] as $cuenta)
                        {{ $cuenta->total_ingresos + $cuenta->total_egresos }}@if(!$loop->last),@endif
                    @endforeach
                ],
                backgroundColor: ['#3498db', '#e74c3c', '#f39c12', '#27ae60', '#9b59b6', '#1abc9c', '#34495e'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12
                }
            }
        }
    });

    // Evolución Diaria
    const ctxEvolucion = document.getElementById('evolucionDiariaChart').getContext('2d');
    new Chart(ctxEvolucion, {
        type: 'bar',
        data: {
            labels: [
                @foreach($estadisticas['por_dia'] ?? [] as $dia)
                    '{{ \Carbon\Carbon::parse($dia->Fecha)->format("d/m") }}'@if(!$loop->last),@endif
                @endforeach
            ],
            datasets: [
                {
                    label: 'Ingresos',
                    data: [
                        @foreach($estadisticas['por_dia'] ?? [] as $dia)
                            {{ $dia->ingresos }}@if(!$loop->last),@endif
                        @endforeach
                    ],
                    backgroundColor: '#27ae60',
                    borderRadius: 4
                },
                {
                    label: 'Egresos',
                    data: [
                        @foreach($estadisticas['por_dia'] ?? [] as $dia)
                            {{ $dia->egresos }}@if(!$loop->last),@endif
                        @endforeach
                    ],
                    backgroundColor: '#e74c3c',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 15,
                        font: { size: 13 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12
                }
            }
        }
    });

});
</script>
@endpush