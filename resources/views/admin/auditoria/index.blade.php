@extends('layouts.admin')

@section('title', 'Auditoría del Sistema')

@push('styles')
    <link href="{{ asset('css/admin/auditoria-sistema.css') }}" rel="stylesheet">
@endpush

@section('header-content')
<div>
    <h1 class="h3 mb-0">Auditoría y Trazabilidad del Sistema</h1>
    <p class="text-muted mb-0">Registro completo de eventos y acciones del sistema</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item active">Auditoría</li>
@endsection

@section('content')

    <!-- Estadísticas -->
<div class="auditoria-sistema-container">
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fas fa-database fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Eventos</h6>
                            <h3 class="mb-0">{{ number_format($estadisticas['total']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="fas fa-calendar-day fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Eventos Hoy</h6>
                            <h3 class="mb-0">{{ number_format($estadisticas['hoy']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded p-3">
                                <i class="fas fa-users fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Usuarios Activos</h6>
                            <h3 class="mb-0">{{ number_format($estadisticas['usuarios_activos_hoy']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded p-3">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Acciones Críticas</h6>
                            <h3 class="mb-0">{{ number_format($estadisticas['acciones_criticas']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción Rápida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.auditoria.estadisticas') }}" class="btn btn-primary">
                            <i class="fas fa-chart-pie me-2"></i>Dashboard Estadísticas
                        </a>
                        <a href="{{ route('admin.auditoria.timeline') }}" class="btn btn-info">
                            <i class="fas fa-stream me-2"></i>Timeline de Eventos
                        </a>
                        <a href="{{ route('admin.auditoria.exportar', request()->all()) }}" class="btn btn-success">
                            <i class="fas fa-file-excel me-2"></i>Exportar a Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Avanzados -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Usuario</label>
                    <select name="usuario" class="form-select">
                        <option value="">Todos los usuarios</option>
                        @foreach($usuarios as $usr)
                        <option value="{{ $usr }}" {{ $filtros['usuario'] == $usr ? 'selected' : '' }}>
                            {{ $usr }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tipo de Acción</label>
                    <select name="accion" class="form-select">
                        <option value="">Todas las acciones</option>
                        <option value="LOGIN" {{ $filtros['accion'] == 'LOGIN' ? 'selected' : '' }}>Login</option>
                        <option value="LOGOUT" {{ $filtros['accion'] == 'LOGOUT' ? 'selected' : '' }}>Logout</option>
                        <option value="CREAR" {{ $filtros['accion'] == 'CREAR' ? 'selected' : '' }}>Crear</option>
                        <option value="MODIFICAR" {{ $filtros['accion'] == 'MODIFICAR' ? 'selected' : '' }}>Modificar</option>
                        <option value="ELIMINAR" {{ $filtros['accion'] == 'ELIMINAR' ? 'selected' : '' }}>Eliminar</option>
                        <option value="ANULAR" {{ $filtros['accion'] == 'ANULAR' ? 'selected' : '' }}>Anular</option>
                        <option value="ACCESO_DENEGADO" {{ $filtros['accion'] == 'ACCESO_DENEGADO' ? 'selected' : '' }}>Acceso Denegado</option>
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

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Buscar
                    </button>
                </div>

                <div class="col-md-10">
                    <label class="form-label">Búsqueda General</label>
                    <input type="text" name="buscar" class="form-control" 
                        placeholder="Buscar en descripción, usuario o acción..."
                        value="{{ $filtros['buscar'] }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('admin.auditoria.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-redo me-2"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Eventos -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Registro de Eventos ({{ $eventos->count() }})
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">ID</th>
                            <th width="120">Fecha/Hora</th>
                            <th width="120">Usuario</th>
                            <th width="150">Acción</th>
                            <th>Descripción</th>
                            <th width="120">IP</th>
                            <th width="80" class="text-center">Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($eventos as $evento)
                        <tr>
                            <td><small class="text-muted">#{{ $evento->id }}</small></td>
                            <td>
                                <div>{{ \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $evento->hora }}</small>
                            </td>
                            <td>
                                <a href="{{ route('admin.auditoria.por-usuario', $evento->usuario) }}" 
                                class="text-decoration-none">
                                    <i class="fas fa-user me-1"></i>{{ $evento->usuario }}
                                </a>
                            </td>
                            <td>
                                @if(in_array($evento->accion, ['LOGIN']))
                                    <span class="badge bg-success">
                                        <i class="fas fa-sign-in-alt me-1"></i>{{ $evento->accion }}
                                    </span>
                                @elseif(in_array($evento->accion, ['LOGOUT']))
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-sign-out-alt me-1"></i>{{ $evento->accion }}
                                    </span>
                                @elseif(in_array($evento->accion, ['CREAR']))
                                    <span class="badge bg-primary">
                                        <i class="fas fa-plus me-1"></i>{{ $evento->accion }}
                                    </span>
                                @elseif(in_array($evento->accion, ['MODIFICAR']))
                                    <span class="badge bg-warning">
                                        <i class="fas fa-edit me-1"></i>{{ $evento->accion }}
                                    </span>
                                @elseif(in_array($evento->accion, ['ELIMINAR', 'ANULAR']))
                                    <span class="badge bg-danger">
                                        <i class="fas fa-trash me-1"></i>{{ $evento->accion }}
                                    </span>
                                @elseif(in_array($evento->accion, ['ACCESO_DENEGADO']))
                                    <span class="badge bg-danger">
                                        <i class="fas fa-ban me-1"></i>{{ $evento->accion }}
                                    </span>
                                @else
                                    <span class="badge bg-info">{{ $evento->accion }}</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ Str::limit($evento->descripcion, 80) }}</small>
                            </td>
                            <td>
                                <code class="small">{{ $evento->ip ?? '-' }}</code>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.auditoria.detalle', $evento->id) }}" 
                                class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No se encontraron eventos con los filtros aplicados
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
