@extends('layouts.admin')

@section('title', 'Historial de Accesos')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Historial de Accesos: {{ $usuarioData->usuario }}</h1>
    <p class="text-muted mb-0">Registro de actividad y sesiones del usuario</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.usuarios.index') }}">Usuarios</a></li>
<li class="breadcrumb-item active">Historial</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Información del Usuario -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-2">{{ $usuarioData->usuario }}</h5>
                        @if($usuarioData->empleado_nombre)
                            <p class="text-muted mb-1">
                                <i class="fas fa-user-tie me-2"></i>{{ $usuarioData->empleado_nombre }}
                            </p>
                        @endif
                        <p class="text-muted mb-0">
                            <i class="fas fa-clock me-2"></i>
                            Último acceso: 
                            @if($usuarioData->ultimoacceso)
                                {{ \Carbon\Carbon::parse($usuarioData->ultimoacceso)->format('d/m/Y H:i:s') }}
                            @else
                                <span class="text-warning">Nunca</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Historial -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Registro de Actividad ({{ $historial->count() }} eventos)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="150">Fecha</th>
                                <th width="100">Hora</th>
                                <th width="150">Acción</th>
                                <th>Descripción</th>
                                <th width="150">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historial as $evento)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y') }}</td>
                                <td>{{ $evento->hora }}</td>
                                <td>
                                    @if($evento->accion == 'LOGIN')
                                        <span class="badge bg-success">
                                            <i class="fas fa-sign-in-alt me-1"></i>Login
                                        </span>
                                    @elseif($evento->accion == 'LOGOUT')
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                                        </span>
                                    @elseif($evento->accion == 'ACCESO_DENEGADO')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-ban me-1"></i>Acceso Denegado
                                        </span>
                                    @else
                                        <span class="badge bg-info">{{ $evento->accion }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $evento->descripcion ?? '-' }}</small>
                                </td>
                                <td>
                                    <code class="small">{{ $evento->ip ?? '-' }}</code>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No hay registros de actividad para este usuario
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
@endsection
