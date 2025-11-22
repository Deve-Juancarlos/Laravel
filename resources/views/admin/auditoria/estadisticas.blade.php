@extends('layouts.admin')

@section('title', 'Estadísticas de Auditoría')

@push('styles')
    <link href="{{ asset('css/admin/estadistica-auditoria.css') }}" rel="stylesheet">
@endpush

@section('header-content')
<div>
    <h1 class="h3 mb-0">Dashboard de Estadísticas de Auditoría</h1>
    <p class="text-muted mb-0">Análisis visual de eventos del sistema</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.auditoria.index') }}">Auditoría</a></li>
<li class="breadcrumb-item active">Estadísticas</li>
@endsection

@section('content')

<!-- Selector de Período -->
<div class="estadistica-auditoria-container">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Período de Análisis</label>
                    <select name="periodo" class="form-select" onchange="this.form.submit()">
                        <option value="dia" {{ $periodo == 'dia' ? 'selected' : '' }}>Hoy</option>
                        <option value="semana" {{ $periodo == 'semana' ? 'selected' : '' }}>Esta Semana</option>
                        <option value="mes" {{ $periodo == 'mes' ? 'selected' : '' }}>Este Mes</option>
                        <option value="anio" {{ $periodo == 'anio' ? 'selected' : '' }}>Este Año</option>
                    </select>
                </div>
                <div class="col-md-8 text-end">
                    <a href="{{ route('admin.auditoria.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <!-- Gráfico: Eventos por Acción -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Eventos por Tipo de Acción
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="eventosPorAccionChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico: Eventos por Día -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Evolución Temporal de Eventos
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="eventosPorDiaChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Usuarios Más Activos -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Top 10 Usuarios Más Activos
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Usuario</th>
                                    <th class="text-end">Total Acciones</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usuariosMasActivos as $index => $usuario)
                                <tr>
                                    <td>
                                        @if($index == 0)
                                            <span class="badge bg-warning">🥇</span>
                                        @elseif($index == 1)
                                            <span class="badge bg-secondary">🥈</span>
                                        @elseif($index == 2)
                                            <span class="badge bg-danger">🥉</span>
                                        @else
                                            <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <i class="fas fa-user me-2"></i>
                                        <strong>{{ $usuario->usuario }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary">{{ number_format($usuario->total_acciones) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.auditoria.por-usuario', $usuario->usuario) }}" 
                                        class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        No hay datos disponibles
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Críticas Recientes -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                        Acciones Críticas Recientes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover mb-0 table-sm">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accionesCriticas as $accion)
                                <tr>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($accion->fecha)->format('d/m H:i') }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $accion->usuario }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $accion->accion }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-success py-3">
                                        <i class="fas fa-check-circle me-2"></i>
                                        No hay acciones críticas recientes
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico: Actividad por Hora del Día -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Distribución de Actividad por Hora del Día
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="eventosPorHoraChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Gráfico: Eventos por Acción
const ctxAccion = document.getElementById('eventosPorAccionChart').getContext('2d');
new Chart(ctxAccion, {
    type: 'doughnut',
    data: {
        labels: [
            @foreach($estadisticas['por_accion'] as $item)
                '{{ $item->accion }}',
            @endforeach
        ],
        datasets: [{
            data: [
                @foreach($estadisticas['por_accion'] as $item)
                    {{ $item->total }},
                @endforeach
            ],
            backgroundColor: [
                '#3498db', '#e74c3c', '#f39c12', '#27ae60', '#9b59b6', 
                '#1abc9c', '#34495e', '#e67e22', '#95a5a6', '#16a085'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
            }
        }
    }
});

// Gráfico: Eventos por Día
const ctxDia = document.getElementById('eventosPorDiaChart').getContext('2d');
new Chart(ctxDia, {
    type: 'line',
    data: {
        labels: [
            @foreach($estadisticas['por_dia'] as $item)
                '{{ \Carbon\Carbon::parse($item->fecha)->format("d/m") }}',
            @endforeach
        ],
        datasets: [{
            label: 'Eventos',
            data: [
                @foreach($estadisticas['por_dia'] as $item)
                    {{ $item->total }},
                @endforeach
            ],
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Gráfico: Eventos por Hora
const ctxHora = document.getElementById('eventosPorHoraChart').getContext('2d');
new Chart(ctxHora, {
    type: 'bar',
    data: {
        labels: [
            @foreach($estadisticas['por_hora'] as $item)
                '{{ $item->hora }}:00',
            @endforeach
        ],
        datasets: [{
            label: 'Eventos por Hora',
            data: [
                @foreach($estadisticas['por_hora'] as $item)
                    {{ $item->total }},
                @endforeach
            ],
            backgroundColor: '#3498db',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush
