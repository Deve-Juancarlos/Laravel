@extends('layouts.contador')

@section('title', 'Libro Diario - SIFANO')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('libros-diario') }}">Libro Diario</a></li>
    <li class="breadcrumb-item active">Lista</li>
@endsection

@section('contador-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-book text-success me-2"></i>
        Libro Diario
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success" onclick="exportBook()">
            <i class="fas fa-download me-2"></i>
            Exportar
        </button>
        @hasrole('Administrador|Contador')
        <a href="{{ route('libros-diario.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>
            Nuevo Asiento
        </a>
        @endhasrole
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('libros-diario') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Fecha Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cuenta</label>
                <select name="cuenta_id" class="form-select">
                    <option value="">Todas las cuentas</option>
                    @foreach($cuentas ?? [] as $cuenta)
                        <option value="{{ $cuenta->id }}" {{ request('cuenta_id') == $cuenta->id ? 'selected' : '' }}>
                            {{ $cuenta->codigo }} - {{ $cuenta->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Usuario</label>
                <select name="usuario_id" class="form-select">
                    <option value="">Todos los usuarios</option>
                    @foreach($usuarios ?? [] as $usuario)
                        <option value="{{ $usuario->id }}" {{ request('usuario_id') == $usuario->id ? 'selected' : '' }}>
                            {{ $usuario->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>
                    Filtrar
                </button>
                <a href="{{ route('libros-diario') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-eraser me-2"></i>
                    Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-muted">Total Debe</h5>
                <h3 class="text-success">S/ {{ number_format($totalDebe ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-muted">Total Haber</h5>
                <h3 class="text-primary">S/ {{ number_format($totalHaber ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-muted">Diferencia</h5>
                <h3 class="{{ ($totalDebe ?? 0) - ($totalHaber ?? 0) == 0 ? 'text-success' : 'text-danger' }}">
                    S/ {{ number_format(($totalDebe ?? 0) - ($totalHaber ?? 0), 2) }}
                </h3>
                <small class="{{ ($totalDebe ?? 0) - ($totalHaber ?? 0) == 0 ? 'text-success' : 'text-danger' }}">
                    {{ ($totalDebe ?? 0) - ($totalHaber ?? 0) == 0 ? 'Balance correcto' : 'Desbalanceado' }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Asientos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Asientos Contables
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Número</th>
                        <th>Descripción</th>
                        <th>Usuario</th>
                        <th>Debe</th>
                        <th>Haber</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asientos ?? [] as $asiento)
                    <tr>
                        <td>{{ date('d/m/Y', strtotime($asiento->fecha)) }}</td>
                        <td>
                            <strong>{{ $asiento->numero_asiento }}</strong>
                        </td>
                        <td>{{ Str::limit($asiento->descripcion, 50) }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-2">
                                    {{ strtoupper(substr($asiento->usuario_nombre, 0, 1)) }}
                                </div>
                                {{ $asiento->usuario_nombre }}
                            </div>
                        </td>
                        <td class="text-success fw-bold">
                            S/ {{ number_format($asiento->total_debe, 2) }}
                        </td>
                        <td class="text-primary fw-bold">
                            S/ {{ number_format($asiento->total_haber, 2) }}
                        </td>
                        <td>
                            @if($asiento->estado === 'balanceado')
                                <span class="badge bg-success">Balanceado</span>
                            @else
                                <span class="badge bg-danger">Desbalanceado</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('libros-diario.show', $asiento->id) }}" class="btn btn-outline-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('update', $asiento)
                                <a href="{{ route('libros-diario.edit', $asiento->id) }}" class="btn btn-outline-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('delete', $asiento)
                                <button class="btn btn-outline-danger" onclick="deleteAsiento({{ $asiento->id }})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No hay asientos contables registrados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(($asientos ?? [])->count() > 0)
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Mostrando {{ ($asientos ?? [])->firstItem() ?? 0 }} a {{ ($asientos ?? [])->lastItem() ?? 0 }} 
                de {{ ($asientos ?? [])->total() ?? 0 }} resultados
            </div>
            <div>
                {{ ($asientos ?? [])->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este asiento contable?</p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let deleteId = null;

    function deleteAsiento(id) {
        deleteId = id;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (deleteId) {
            showLoading();
            
            fetch(`/libros-diario/${deleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    Swal.fire('Éxito', 'Asiento eliminado correctamente', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Error eliminando el asiento', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                Swal.fire('Error', 'Error de conexión', 'error');
            });
        }
    });

    function exportBook() {
        const params = new URLSearchParams(window.location.search);
        const url = `/libros-diario/exportar?${params.toString()}`;
        
        showLoading();
        window.open(url, '_blank');
        hideLoading();
    }

    // Validación automática del balance
    document.addEventListener('DOMContentLoaded', function() {
        // Marcar filas con desbalance
        document.querySelectorAll('tbody tr').forEach(row => {
            const debe = parseFloat(row.children[4].textContent.replace(/[^0-9.-]/g, ''));
            const haber = parseFloat(row.children[5].textContent.replace(/[^0-9.-]/g, ''));
            
            if (Math.abs(debe - haber) > 0.01) {
                row.classList.add('table-danger');
            }
        });
    });
</script>
@endsection