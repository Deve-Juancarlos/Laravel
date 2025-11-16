@extends('layouts.admin')

@section('title', 'Historial del Usuario')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Historial de Actividad: {{ $usuario }}</h1>
    <p class="text-muted mb-0">Registro completo de acciones del usuario</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.auditoria.index') }}">Auditoría</a></li>
<li class="breadcrumb-item active">{{ $usuario }}</li>
@endsection

@section('content')

<!-- Estadísticas del Usuario -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-primary mb-1">{{ number_format($estadisticasUsuario['total_acciones']) }}</h3>
                <p class="text-muted mb-0 small">Total Acciones</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-success mb-1">{{ number_format($estadisticasUsuario['acciones_mes']) }}</h3>
                <p class="text-muted mb-0 small">Este Mes</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1 small">Último Acceso</p>
                @if($estadisticasUsuario['ultimo_acceso'])
                <p class="mb-0 fw-bold">
                    {{ \Carbon\Carbon::parse($estadisticasUsuario['ultimo_acceso']->fecha)->format('d/m/Y') }}
                    {{ $estadisticasUsuario['ultimo_acceso']->hora }}
                </p>
                @else
                <p class="mb-0 text-muted">Sin registro</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Acciones por Tipo -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Distribución de Acciones del Mes</h5>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($estadisticasUsuario['acciones_por_tipo'] as $tipo)
            <div class="col-md-3 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="badge bg-primary">{{ $tipo->accion }}</span>
                    </div>
                    <div class="fw-bold">{{ $tipo->total }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Tabla de Eventos -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Historial de Eventos ({{ $eventos->count() }})</h5>
        <a href="{{ route('admin.auditoria.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventos as $evento)
                    <tr>
                        <td>
                            {{ \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y') }}<br>
                            <small class="text-muted">{{ $evento->hora }}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $evento->accion }}</span>
                        </td>
                        <td>{{ $evento->descripcion }}</td>
                        <td><code>{{ $evento->ip }}</code></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            No hay eventos registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
