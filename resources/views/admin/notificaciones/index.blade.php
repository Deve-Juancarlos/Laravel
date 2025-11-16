@extends('layouts.admin')

@section('title', 'Notificaciones del Sistema')

@section('header-content')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0">Centro de Notificaciones</h1>
        <p class="text-muted mb-0">Gestión de alertas y notificaciones del sistema</p>
    </div>
    <div>
        <a href="{{ route('admin.notificaciones.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Notificación
        </a>
        <button onclick="generarAutomaticas()" class="btn btn-info">
            <i class="fas fa-sync me-2"></i>Generar Automáticas
        </button>
    </div>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item active">Notificaciones</li>
@endsection

@section('content')

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="fas fa-bell fa-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Total</h6>
                        <h3 class="mb-0">{{ number_format($estadisticas['total']) }}</h3>
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
                            <i class="fas fa-envelope fa-2x text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">No Leídas</h6>
                        <h3 class="mb-0">{{ number_format($estadisticas['no_leidas']) }}</h3>
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
                        <h3 class="mb-0">{{ number_format($estadisticas['criticas']) }}</h3>
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
                        <div class="bg-info bg-opacity-10 rounded p-3">
                            <i class="fas fa-calendar-day fa-2x text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Hoy</h6>
                        <h3 class="mb-0">{{ number_format($estadisticas['hoy']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="INFO" {{ $filtros['tipo'] == 'INFO' ? 'selected' : '' }}>Info</option>
                    <option value="ALERTA" {{ $filtros['tipo'] == 'ALERTA' ? 'selected' : '' }}>Alerta</option>
                    <option value="CRITICO" {{ $filtros['tipo'] == 'CRITICO' ? 'selected' : '' }}>Crítico</option>
                    <option value="EXITO" {{ $filtros['tipo'] == 'EXITO' ? 'selected' : '' }}>Éxito</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="leida" class="form-select">
                    <option value="">Todas</option>
                    <option value="0" {{ $filtros['leida'] === '0' ? 'selected' : '' }}>No Leídas</option>
                    <option value="1" {{ $filtros['leida'] === '1' ? 'selected' : '' }}>Leídas</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control" 
                       value="{{ $filtros['fecha_inicio'] }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" name="fecha_fin" class="form-control" 
                       value="{{ $filtros['fecha_fin'] }}">
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-search me-2"></i>Buscar
                </button>
                <button type="button" onclick="marcarTodasLeidas()" class="btn btn-success" title="Marcar todas como leídas">
                    <i class="fas fa-check-double"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Notificaciones -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Notificaciones ({{ $notificaciones->count() }})
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($notificaciones as $notif)
            <div class="list-group-item {{ $notif->leida == 0 ? 'bg-light' : '' }}">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="rounded-circle p-3 bg-{{ $notif->color }}-subtle">
                            <i class="fas {{ $notif->icono }} fa-2x text-{{ $notif->color }}"></i>
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1 {{ $notif->leida == 0 ? 'fw-bold' : '' }}">
                                    {{ $notif->titulo }}
                                    @if($notif->leida == 0)
                                        <span class="badge bg-{{ $notif->color }} ms-2">Nuevo</span>
                                    @endif
                                </h6>
                                <p class="mb-1 text-muted">{{ $notif->mensaje }}</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $notif->color }}">{{ $notif->tipo }}</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                                @if($notif->usuario_nombre)
                                    | <i class="fas fa-user me-1"></i>Para: {{ $notif->usuario_nombre }}
                                @else
                                    | <i class="fas fa-users me-1"></i>Para: Todos
                                @endif
                            </small>
                            <div class="btn-group btn-group-sm">
                                @if($notif->url)
                                    <a href="{{ $notif->url }}" class="btn btn-outline-primary" onclick="marcarComoLeida({{ $notif->id }})">
                                        <i class="fas fa-external-link-alt me-1"></i>Ver
                                    </a>
                                @endif
                                @if($notif->leida == 0)
                                    <button onclick="marcarComoLeida({{ $notif->id }})" class="btn btn-outline-success" title="Marcar como leída">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                <form method="POST" action="{{ route('admin.notificaciones.destroy', $notif->id) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" 
                                            onclick="return confirm('¿Eliminar esta notificación?')"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No hay notificaciones</h5>
                <p class="text-muted">No se encontraron notificaciones con los filtros aplicados</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function marcarComoLeida(id) {
    fetch(`/admin/notificaciones/${id}/marcar-leida`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            location.reload();
        }
    });
}

function marcarTodasLeidas() {
    if (confirm('¿Marcar todas las notificaciones como leídas?')) {
        fetch('/admin/notificaciones/marcar-todas-leidas', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(() => location.reload());
    }
}

function generarAutomaticas() {
    if (confirm('¿Generar notificaciones automáticas del sistema?')) {
        fetch('/admin/notificaciones/generar-automaticas', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(() => location.reload());
    }
}
</script>
@endpush
