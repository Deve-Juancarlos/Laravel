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
                        <h5 class="mb-2">
                            <i class="fas fa-user-circle text-primary me-2"></i>
                            {{ $usuarioData->usuario }}
                        </h5>
                        @if($usuarioData->empleado_nombre)
                            <p class="text-muted mb-1">
                                <i class="fas fa-user-tie me-2"></i>{{ $usuarioData->empleado_nombre }}
                            </p>
                        @endif
                        <p class="text-muted mb-1">
                            <i class="fas fa-id-badge me-2"></i>
                            DNI: {{ $usuarioData->empleado_dni ?? 'N/A' }}
                        </p>
                        <p class="text-muted mb-1">
                            <i class="fas fa-shield-alt me-2"></i>
                            Rol: 
                            @if($usuarioData->tipousuario == 'administrador')
                                <span class="badge bg-danger">ADMINISTRADOR</span>
                            @elseif($usuarioData->tipousuario == 'CONTADOR')
                                <span class="badge bg-primary">CONTADOR</span>
                            @else
                                <span class="badge bg-info">VENDEDOR</span>
                            @endif
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-toggle-on me-2"></i>
                            Estado: 
                            @if($usuarioData->estado == 'ACTIVO')
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
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
                    Registro de Accesos ({{ $historial->count() }} registros)
                </h5>
            </div>
            <div class="card-body p-0">
                @if($historial->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th width="150">Fecha</th>
                                <th width="100">Hora</th>
                                <th width="150">Dirección IP</th>
                                <th>Navegador / Dispositivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historial as $index => $evento)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <i class="fas fa-calendar-alt text-muted me-1"></i>
                                    {{ \Carbon\Carbon::parse($evento->fecha_acceso)->format('d/m/Y') }}
                                </td>
                                <td>
                                    <i class="fas fa-clock text-muted me-1"></i>
                                    {{ \Carbon\Carbon::parse($evento->fecha_acceso)->format('H:i:s') }}
                                </td>
                                <td>
                                    @if($evento->ip_address)
                                        <code class="small">{{ $evento->ip_address }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($evento->user_agent)
                                        <small class="text-muted">
                                            <i class="fas fa-desktop me-1"></i>
                                            {{ \Illuminate\Support\Str::limit($evento->user_agent, 100) }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay registros de actividad</h5>
                    <p class="text-muted mb-0">
                        Este usuario aún no ha iniciado sesión o la tabla de historial no está configurada.
                    </p>
                </div>
                @endif
            </div>
            
            @if($historial->count() > 0)
            <div class="card-footer bg-light text-muted">
                <small>
                    <i class="fas fa-info-circle me-1"></i>
                    Se muestran los últimos 50 accesos registrados
                </small>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
