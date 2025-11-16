@extends('layouts.admin')

@section('title', 'Gestión de Empleados')


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/empleados/index.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('header-content')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0">Gestión de Empleados</h1>
        <p class="text-muted mb-0">Administración de personal de la empresa</p>
    </div>
    <a href="{{ route('admin.empleados.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus me-2"></i>Nuevo Empleado
    </a>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item active">Empleados</li>
@endsection

@section('content')

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Total Empleados</h6>
                        <h3 class="mb-0">{{ $estadisticas['total'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded p-3">
                            <i class="fas fa-user-check fa-2x text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Con Usuario</h6>
                        <h3 class="mb-0">{{ $estadisticas['con_usuario'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 rounded p-3">
                            <i class="fas fa-user-times fa-2x text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Sin Usuario</h6>
                        <h3 class="mb-0">{{ $estadisticas['sin_usuario'] }}</h3>
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
            <div class="col-md-5">
                <label class="form-label">Buscar</label>
                <input type="text" name="buscar" class="form-control" 
                       placeholder="Nombre o documento..." 
                       value="{{ $filtros['buscar'] }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" {{ $filtros['tipo'] == '1' ? 'selected' : '' }}>Tipo 1</option>
                    <option value="2" {{ $filtros['tipo'] == '2' ? 'selected' : '' }}>Tipo 2</option>
                    <option value="3" {{ $filtros['tipo'] == '3' ? 'selected' : '' }}>Tipo 3</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-search me-2"></i>Buscar
                </button>
                <a href="{{ route('admin.empleados.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Empleados -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Listado de Empleados ({{ $empleados->count() }})
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="80">Código</th>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Teléfono</th>
                        <th>Celular</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Usuario</th>
                        <th class="text-center" width="200">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($empleados as $empleado)
                    <tr>
                        <td><span class="badge bg-secondary">#{{ $empleado->Codemp }}</span></td>
                        <td>
                            <strong>{{ $empleado->Nombre }}</strong>
                        </td>
                        <td>
                            <code>{{ $empleado->Documento }}</code>
                        </td>
                        <td>{{ $empleado->Telefono1 ?? '-' }}</td>
                        <td>{{ $empleado->Celular ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-primary">Tipo {{ $empleado->Tipo }}</span>
                        </td>
                        <td class="text-center">
                            @if($empleado->tiene_usuario)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-times-circle"></i>
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.empleados.show', $empleado->Codemp) }}" 
                                   class="btn btn-outline-info" 
                                   title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.empleados.edit', $empleado->Codemp) }}" 
                                   class="btn btn-outline-primary" 
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" 
                                      action="{{ route('admin.empleados.destroy', $empleado->Codemp) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-outline-danger btn-sm" 
                                            title="Eliminar"
                                            onclick="return confirm('¿Está seguro de eliminar este empleado?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-users fa-3x mb-3 d-block"></i>
                            No se encontraron empleados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
