code
preview
@extends('layouts.contador')

@section('title', 'Gestión de Clientes')

@section('content')
<div class="container mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">👥 Gestión de Clientes</h2>
            <p class="text-muted mb-0">Administra la base de datos de clientes</p>
        </div>
        <div>
            <a href="{{ route('contabilidad.clientes.buscar') }}" class="btn btn-primary me-2">
                <i class="fas fa-search me-1"></i>Buscar/RENIEC
            </a>
            <a href="{{ route('contabilidad.clientes.create') }}" class="btn btn-success">
                <i class="fas fa-user-plus me-1"></i>Nuevo Cliente
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h4>{{ $contadores['total'] }}</h4>
                    <p class="mb-0">Total Clientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-user-check fa-2x mb-2"></i>
                    <h4>{{ $contadores['activos'] }}</h4>
                    <p class="mb-0">Clientes Activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-id-card fa-2x mb-2"></i>
                    <h4>{{ $contadores['dni'] }}</h4>
                    <p class="mb-0">DNI</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-building fa-2x mb-2"></i>
                    <h4>{{ $contadores['ruc'] }}</h4>
                    <p class="mb-0">RUC</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="buscar" class="form-control" 
                           placeholder="Nombre, DNI, RUC..." 
                           value="{{ request('buscar') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo_documento" class="form-select">
                        <option value="">Todos</option>
                        <option value="DNI" {{ request('tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                        <option value="RUC" {{ request('tipo_documento') == 'RUC' ? 'selected' : '' }}>RUC</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" {{ request('estado') == '1' ? 'selected' : '' }}>Activos</option>
                        <option value="0" {{ request('estado') == '0' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de clientes -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>Lista de Clientes
                <span class="badge bg-secondary ms-2">{{ $clientes->total() }}</span>
            </h5>
        </div>
        <div class="card-body">
            @if($clientes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Documento</th>
                                <th>Nombre/Razón Social</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                                <tr>
                                    <td>{{ $cliente->id }}</td>
                                    <td>
                                        <span class="badge bg-{{ $cliente->tipo_documento == 'DNI' ? 'info' : 'warning' }}">
                                            {{ $cliente->tipo_documento }}
                                        </span><br>
                                        <small>{{ $cliente->numero_documento }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $cliente->getNombreCompleto() }}</strong><br>
                                        @if($cliente->edad)
                                            <small class="text-muted">{{ $cliente->edad }} años</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($cliente->ubicacion_completa)
                                            <small>{{ $cliente->ubicacion_completa }}</small>
                                        @else
                                            <small class="text-muted">No especificado</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $cliente->estado ? 'success' : 'secondary' }}">
                                            {{ $cliente->estado ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $cliente->created_at->format('d/m/Y') }}</small><br>
                                        <small class="text-muted">{{ $cliente->usuario->usuario ?? 'Sistema' }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('contabilidad.clientes.show', $cliente) }}" 
                                               class="btn btn-outline-primary" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('contabilidad.clientes.edit', $cliente) }}" 
                                               class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="eliminarCliente({{ $cliente->id }})" 
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-4">
                    {{ $clientes->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay clientes registrados</h5>
                    <p class="text-muted">Comienza agregando tu primer cliente</p>
                    <a href="{{ route('contabilidad.clientes.create') }}" class="btn btn-success">
                        <i class="fas fa-user-plus me-1"></i>Agregar Cliente
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function eliminarCliente(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este cliente?')) {
        fetch(`/contabilidad/clientes/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el cliente');
        });
    }
}
</script>
@endpush