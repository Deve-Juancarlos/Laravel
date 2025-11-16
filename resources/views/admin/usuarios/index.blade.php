@extends('layouts.admin')

@section('title', 'Gestión de Usuarios')

@push('styles')
    <link href="{{ asset('css/admin/gestion-usuarios.css') }}" rel="stylesheet">
@endpush

@section('header-content')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0">Gestión de Usuarios del Sistema</h1>
        <p class="text-muted mb-0">Control de accesos y permisos</p>
    </div>
    <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus me-2"></i>Crear Usuario
    </a>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item active">Usuarios</li>
@endsection

@section('content')
<div class="gestion-usuarios-container">
    <div class="row mb-4">
        <!-- Estadísticas -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Usuarios</h6>
                            <h3 class="mb-0">{{ $estadisticas['total'] }}</h3>
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
                                <i class="fas fa-user-check fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Activos</h6>
                            <h3 class="mb-0">{{ $estadisticas['activos'] }}</h3>
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
                                <i class="fas fa-user-times fa-2x text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Inactivos</h6>
                            <h3 class="mb-0">{{ $estadisticas['inactivos'] }}</h3>
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
                                <i class="fas fa-unlink fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Sin Vincular</h6>
                            <h3 class="mb-0">{{ $estadisticas['sin_vincular'] }}</h3>
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
                    <label class="form-label">Tipo de Usuario</label>
                    <select name="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="ADMIN" {{ $filtros['tipo'] == 'ADMIN' ? 'selected' : '' }}>Administrador</option>
                        <option value="CONTADOR" {{ $filtros['tipo'] == 'CONTADOR' ? 'selected' : '' }}>Contador</option>
                        <option value="VENDEDOR" {{ $filtros['tipo'] == 'VENDEDOR' ? 'selected' : '' }}>Vendedor</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" {{ $filtros['estado'] === '1' ? 'selected' : '' }}>Activos</option>
                        <option value="0" {{ $filtros['estado'] === '0' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="buscar" class="form-control" 
                        placeholder="Usuario, nombre o DNI..." 
                        value="{{ $filtros['buscar'] }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Listado de Usuarios ({{ $usuarios->count() }})
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Usuario</th>
                            <th>Empleado Vinculado</th>
                            <th>DNI</th>
                            <th>Cargo</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-center">Estado</th>
                            <th>Último Acceso</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                        <tr>
                            <td>
                                <strong>{{ $usuario->usuario }}</strong>
                            </td>
                            <td>
                                @if($usuario->empleado_nombre)
                                    <i class="fas fa-link text-success me-1"></i>
                                    {{ $usuario->empleado_nombre }}
                                @else
                                    <span class="text-danger">
                                        <i class="fas fa-unlink me-1"></i>Sin vincular
                                    </span>
                                @endif
                            </td>
                            <td>{{ $usuario->empleado_dni ?? '-' }}</td>
                            <td>
                                <small class="text-muted">{{ $usuario->empleado_cargo ?? '-' }}</small>
                            </td>
                            <td class="text-center">
                                @if($usuario->tipousuario == 'ADMIN')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-shield-alt me-1"></i>ADMIN
                                    </span>
                                @elseif($usuario->tipousuario == 'CONTADOR')
                                    <span class="badge bg-primary">
                                        <i class="fas fa-calculator me-1"></i>CONTADOR
                                    </span>
                                @else
                                    <span class="badge bg-info">
                                        <i class="fas fa-user-tie me-1"></i>VENDEDOR
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($usuario->idusuario)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Activo
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-times-circle me-1"></i>Inactivo
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(isset($usuario->creado)) 
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($usuario->creado)->format('d/m/Y H:i') }}
                                    </small>
                                @else
                                    <small class="text-muted">Nunca</small>
                                @endif

                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.usuarios.edit', $usuario->usuario) }}" 
                                    class="btn btn-outline-primary" 
                                    title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <a href="{{ route('admin.usuarios.roles', $usuario->usuario) }}" 
                                    class="btn btn-outline-info" 
                                    title="Cambiar Rol">
                                        <i class="fas fa-user-tag"></i>
                                    </a>
                                    
                                @if($usuario->idusuario)
                                        <form method="POST" action="{{ route('admin.usuarios.desactivar', $usuario->usuario) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger" title="Desactivar" onclick="return confirm('¿Desactivar este usuario?')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.usuarios.activar', $usuario->usuario) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Activar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif                                
                                    <a href="{{ route('admin.usuarios.historial', $usuario->usuario) }}" 
                                    class="btn btn-outline-secondary" 
                                    title="Historial">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                No se encontraron usuarios
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
