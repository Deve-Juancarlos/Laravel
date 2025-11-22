@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Timeline de Eventos')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Timeline de Eventos del Sistema</h1>
    <p class="text-muted mb-0">Vista cronológica de la actividad</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.auditoria.index') }}">Auditoría</a></li>
<li class="breadcrumb-item active">Timeline</li>
@endsection

@section('content')

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Usuario</label>
                <select name="usuario" class="form-select">
                    <option value="">Todos los usuarios</option>
                    @foreach(DB::table('libro_diario_auditoria')->distinct()->pluck('usuario') as $usr)
                    <option value="{{ $usr }}" {{ $usuario == $usr ? 'selected' : '' }}>
                        {{ $usr }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" class="form-control" 
                       value="{{ $fecha }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Timeline -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-stream me-2"></i>
            Eventos Cronológicos ({{ $timeline->count() }})
        </h5>
    </div>
    <div class="card-body">
        <div class="timeline">
            @forelse($timeline as $evento)
            <div class="timeline-item">
                <div class="timeline-marker 
                    @if(in_array($evento->accion, ['ELIMINAR', 'ANULAR', 'ACCESO_DENEGADO'])) bg-danger
                    @elseif(in_array($evento->accion, ['MODIFICAR'])) bg-warning
                    @elseif(in_array($evento->accion, ['CREAR'])) bg-success
                    @elseif(in_array($evento->accion, ['LOGIN'])) bg-info
                    @else bg-secondary
                    @endif">
                    @if(in_array($evento->accion, ['LOGIN']))
                        <i class="fas fa-sign-in-alt"></i>
                    @elseif(in_array($evento->accion, ['LOGOUT']))
                        <i class="fas fa-sign-out-alt"></i>
                    @elseif(in_array($evento->accion, ['CREAR']))
                        <i class="fas fa-plus"></i>
                    @elseif(in_array($evento->accion, ['MODIFICAR']))
                        <i class="fas fa-edit"></i>
                    @elseif(in_array($evento->accion, ['ELIMINAR', 'ANULAR']))
                        <i class="fas fa-trash"></i>
                    @else
                        <i class="fas fa-circle"></i>
                    @endif
                </div>
                <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge 
                                @if(in_array($evento->accion, ['ELIMINAR', 'ANULAR', 'ACCESO_DENEGADO'])) bg-danger
                                @elseif(in_array($evento->accion, ['MODIFICAR'])) bg-warning
                                @elseif(in_array($evento->accion, ['CREAR'])) bg-success
                                @elseif(in_array($evento->accion, ['LOGIN'])) bg-info
                                @else bg-secondary
                                @endif">
                                {{ $evento->accion }}
                            </span>
                            <span class="ms-2 text-muted small">
                                <i class="fas fa-user me-1"></i>{{ $evento->usuario }}
                            </span>
                        </div>
                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y') }} 
                            {{ $evento->hora }}
                        </small>
                    </div>
                    <p class="mb-1">{{ $evento->descripcion }}</p>
                    @if($evento->ip)
                    <small class="text-muted">
                        <i class="fas fa-network-wired me-1"></i>IP: <code>{{ $evento->ip }}</code>
                    </small>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                <p>No hay eventos para mostrar</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
    left: 20px;
}

.timeline-item {
    position: relative;
    padding-left: 60px;
    padding-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: 8px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    border-left: 3px solid #dee2e6;
}
</style>
@endpush
