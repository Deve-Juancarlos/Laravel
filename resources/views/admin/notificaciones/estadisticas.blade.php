@extends('layouts.admin')

@section('title', 'Estadísticas de Notificaciones')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Dashboard de Notificaciones</h1>
    <p class="text-muted mb-0">Análisis y estadísticas del sistema de alertas</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.notificaciones.index') }}">Notificaciones</a></li>
<li class="breadcrumb-item active">Estadísticas</li>
@endsection

@section('content')

<!-- KPIs Principales -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="fas fa-chart-bar fa-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Total Generadas</h6>
                        <h3 class="mb-0">{{ number_format($estadisticas['por_tipo']->sum('total')) }}</h3>
                        <small class="text-muted">Este mes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded p-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Leídas</h6>
                        <h3 class="mb-0">{{ number_format(DB::table('Notificaciones')->where('leida', 1)->whereMonth('created_at', now()->month)->count()) }}</h3>
                        <small class="text-muted">Este mes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-danger bg-opacity-10 rounded p-3">
                            <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Críticas</h6>
                        <h3 class="mb-0">{{ number_format($porTipo->where('tipo', 'CRITICO')->first()->total ?? 0) }}</h3>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 rounded p-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Alertas</h6>
                        <h3 class="mb-0">{{ number_format($porTipo->where('tipo', 'ALERTA')->first()->total ?? 0) }}</h3>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row mb-4">
    <!-- Gráfico: Notificaciones por Tipo -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Notificaciones por Tipo
                </h5>
            </div>
            <div class="card-body">
                <canvas id="porTipoChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfico: Evolución Diaria -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Evolución Diaria (Este Mes)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="evolucionChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabla: Resumen por Tipo -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Resumen por Tipo
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">% del Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalGeneral = $porTipo->sum('total'); @endphp
                            @foreach($porTipo as $tipo)
                            <tr>
                                <td>
                                    @if($tipo->tipo == 'INFO')
                                        <span class="badge bg-primary">
                                            <i class="fas fa-info-circle me-1"></i>INFO
                                        </span>
                                    @elseif($tipo->tipo == 'ALERTA')
                                        <span class="badge bg-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>ALERTA
                                        </span>
                                    @elseif($tipo->tipo == 'CRITICO')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-circle me-1"></i>CRÍTICO
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>ÉXITO
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <strong>{{ number_format($tipo->total) }}</strong>
                                </td>
                                <td class="text-end">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar 
                                            {{ $tipo->tipo == 'INFO' ? 'bg-primary' : 
                                               ($tipo->tipo == 'ALERTA' ? 'bg-warning' : 
                                               ($tipo->tipo == 'CRITICO' ? 'bg-danger' : 'bg-success')) }}" 
                                             role="progressbar" 
                                             style="width: {{ ($tipo->total / $totalGeneral) * 100 }}%">
                                            {{ number_format(($tipo->total / $totalGeneral) * 100, 1) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th>TOTAL</th>
                                <th class="text-center">{{ number_format($totalGeneral) }}</th>
                                <th class="text-end">100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Notificaciones Recientes -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Notificaciones Recientes
                </h5>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <div class="list-group list-group-flush">
                    @foreach($recientes as $notif)
                    <div class="list-group-item">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <i class="fas {{ $notif->icono }} fa-lg text-{{ $notif->color }}"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ $notif->titulo }}</h6>
                                <p class="mb-1 text-muted small">{{ Str::limit($notif->mensaje, 60) }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                                </small>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="badge bg-{{ $notif->color }}">{{ $notif->tipo }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Botón Volver -->
<div class="text-center">
    <a href="{{ route('admin.notificaciones.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver al Listado
    </a>
</div>

@endsection

@push('scripts')
<script>
// Gráfico: Notificaciones por Tipo
const ctxTipo = document.getElementById('porTipoChart').getContext('2d');
new Chart(ctxTipo, {
    type: 'doughnut',
    data: {
        labels: [
            @foreach($porTipo as $tipo)
                '{{ $tipo->tipo }}',
            @endforeach
        ],
        datasets: [{
            data: [
                @foreach($porTipo as $tipo)
                    {{ $tipo->total }},
                @endforeach
            ],
            backgroundColor: [
                '#3498db', // INFO
                '#f39c12', // ALERTA
                '#e74c3c', // CRITICO
                '#27ae60'  // EXITO
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// Gráfico: Evolución Diaria
const ctxEvolucion = document.getElementById('evolucionChart').getContext('2d');
new Chart(ctxEvolucion, {
    type: 'line',
    data: {
        labels: [
            @foreach($estadisticas['por_dia'] as $dia)
                '{{ \Carbon\Carbon::parse($dia->fecha)->format("d/m") }}',
            @endforeach
        ],
        datasets: [{
            label: 'Notificaciones',
            data: [
                @foreach($estadisticas['por_dia'] as $dia)
                    {{ $dia->total }},
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
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
@endpush
