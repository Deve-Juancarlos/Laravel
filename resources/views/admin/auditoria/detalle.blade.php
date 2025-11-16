@extends('layouts.admin')

@section('title', 'Detalle de Evento')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Detalle del Evento #{{ $evento->id }}</h1>
    <p class="text-muted mb-0">Información completa del evento de auditoría</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.auditoria.index') }}">Auditoría</a></li>
<li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Información del Evento
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small">ID del Evento</label>
                        <p class="mb-0 fw-bold">#{{ $evento->id }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Fecha y Hora</label>
                        <p class="mb-0 fw-bold">
                            {{ \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y') }} 
                            a las {{ $evento->hora }}
                        </p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small">Usuario</label>
                        <p class="mb-0">
                            <i class="fas fa-user me-2 text-primary"></i>
                            <strong>{{ $evento->usuario }}</strong>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Dirección IP</label>
                        <p class="mb-0">
                            <i class="fas fa-network-wired me-2 text-info"></i>
                            <code>{{ $evento->ip ?? 'No registrada' }}</code>
                        </p>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-muted small">Tipo de Acción</label>
                    <p class="mb-0">
                        @if(in_array($evento->accion, ['LOGIN']))
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-sign-in-alt me-2"></i>{{ $evento->accion }}
                            </span>
                        @elseif(in_array($evento->accion, ['MODIFICAR']))
                            <span class="badge bg-warning fs-6">
                                <i class="fas fa-edit me-2"></i>{{ $evento->accion }}
                            </span>
                        @elseif(in_array($evento->accion, ['ELIMINAR', 'ANULAR']))
                            <span class="badge bg-danger fs-6">
                                <i class="fas fa-trash me-2"></i>{{ $evento->accion }}
                            </span>
                        @else
                            <span class="badge bg-info fs-6">{{ $evento->accion }}</span>
                        @endif
                    </p>
                </div>

                <div class="mb-4">
                    <label class="text-muted small">Descripción Completa</label>
                    <div class="alert alert-light border">
                        <p class="mb-0">{{ $evento->descripcion ?? 'Sin descripción' }}</p>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.auditoria.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                    <div>
                        <a href="{{ route('admin.auditoria.por-usuario', $evento->usuario) }}" 
                           class="btn btn-info">
                            <i class="fas fa-user me-2"></i>Ver Historial del Usuario
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
