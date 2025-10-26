@extends('layouts.contador')

@section('title', 'Detalles del Cliente')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb y título -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('clientes.index') }}" class="text-decoration-none">
                            <i class="fas fa-arrow-left"></i> Clientes
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $clientes->CodClie}}
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Información principal del cliente -->
        <div class="col-lg-8 col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user text-primary me-2"></i>
                            Información Personal
                        </h5>
                        <div class="btn-group">
                            <a href="{{ route('clientes.edit', $cliente->id) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                    onclick="confirmarEliminacion({{ $cliente->id }})"
                                    {{ $cliente->estado == 0 ? 'disabled' : '' }}>
                                <i class="fas fa-trash"></i> Desactivar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Datos básicos -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Tipo de Documento</label>
                                <div class="fw-bold">
                                    <span class="badge bg-{{ $cliente->tipo_documento == 'DNI' ? 'info' : 'success' }} fs-6">
                                        {{ $cliente->tipo_documento }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Número de Documento</label>
                                <div class="fw-bold fs-6">{{ $cliente->numero_documento }}</div>
                            </div>
                        </div>
                    </div>

                    @if($cliente->tipo_documento == 'DNI')
                    <!-- Información para personas naturales -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Nombres</label>
                                <div class="fw-bold">{{ $cliente->nombres ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Apellido Paterno</label>
                                <div class="fw-bold">{{ $cliente->apellido_paterno ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Apellido Materno</label>
                                <div class="fw-bold">{{ $cliente->apellido_materno ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Fecha de Nacimiento</label>
                                <div class="fw-bold">
                                    {{ $cliente->fecha_nacimiento ? \Carbon\Carbon::parse($cliente->fecha_nacimiento)->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Edad</label>
                                <div class="fw-bold">
                                    @if($estadisticas['edad'])
                                        {{ $estadisticas['edad'] }} años
                                        @if($estadisticas['es_mayor_edad'])
                                            <span class="badge bg-success ms-1">Mayor de edad</span>
                                        @else
                                            <span class="badge bg-warning ms-1">Menor de edad</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- Información para personas jurídicas -->
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Razón Social</label>
                                <div class="fw-bold">{{ $cliente->razon_social ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Información de dirección -->
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Dirección</label>
                                <div class="fw-bold">{{ $cliente->direccion ?? 'No especificada' }}</div>
                            </div>
                        </div>
                    </div>

                    @if($cliente->departamento || $cliente->provincia || $cliente->distrito)
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Departamento</label>
                                <div class="fw-bold">{{ $cliente->departamento ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Provincia</label>
                                <div class="fw-bold">{{ $cliente->provincia ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Distrito</label>
                                <div class="fw-bold">{{ $cliente->distrito ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Información de cuentas asociadas -->
            @if($estadisticas['total_cuentas'] > 0)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-university text-primary me-2"></i>
                        Cuentas Asociadas ({{ $estadisticas['total_cuentas'] }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Saldo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->cuentas as $cuenta)
                                <tr>
                                    <td>{{ $cuenta->cta }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $cuenta->tipo }}</span>
                                    </td>
                                    <td>
                                        @if($cuenta->estado == 1)
                                            <span class="badge bg-success">Activa</span>
                                        @else
                                            <span class="badge bg-danger">Inactiva</span>
                                        @endif
                                    </td>
                                    <td>S/ {{ number_format($cuenta->saldo, 2) }}</td>
                                    <td>
                                        <a href="#" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Panel lateral con información adicional -->
        <div class="col-lg-4 col-md-5">
            <!-- Estado del cliente -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Estado del Cliente
                    </h6>
                    <div class="text-center">
                        @if($cliente->estado == 1)
                            <div class="badge bg-success fs-6 px-3 py-2 mb-3">
                                <i class="fas fa-check-circle me-1"></i> Activo
                            </div>
                            <p class="text-muted small mb-3">
                                Cliente disponible para operaciones
                            </p>
                            <button type="button" class="btn btn-outline-warning btn-sm w-100" 
                                    onclick="confirmarDesactivacion({{ $cliente->id }})">
                                <i class="fas fa-pause"></i> Desactivar Cliente
                            </button>
                        @else
                            <div class="badge bg-danger fs-6 px-3 py-2 mb-3">
                                <i class="fas fa-times-circle me-1"></i> Inactivo
                            </div>
                            <p class="text-muted small mb-3">
                                Cliente no disponible para nuevas operaciones
                            </p>
                            <button type="button" class="btn btn-outline-success btn-sm w-100" 
                                    onclick="reactivarCliente({{ $cliente->id }})">
                                <i class="fas fa-play"></i> Reactivar Cliente
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        Estadísticas
                    </h6>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h4 mb-1 text-primary">{{ $estadisticas['total_cuentas'] }}</div>
                                <div class="small text-muted">Cuentas</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-1 text-{{ $cliente->estado == 1 ? 'success' : 'danger' }}">
                                {{ $cliente->estado == 1 ? 'Activo' : 'Inactivo' }}
                            </div>
                            <div class="small text-muted">Estado</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de registro -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        Información de Registro
                    </h6>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Creado</label>
                        <div class="fw-bold small">{{ $estadisticas['created_at_formatted'] }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Última Actualización</label>
                        <div class="fw-bold small">{{ $estadisticas['updated_at_formatted'] }}</div>
                    </div>
                    @if($cliente->usuario)
                    <div class="mb-3">
                        <label class="form-label text-muted small">Registrado por</label>
                        <div class="fw-bold small">{{ $cliente->usuario->name ?? 'Usuario del Sistema' }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para desactivar cliente -->
<div class="modal fade" id="modalDesactivar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Desactivación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea desactivar este cliente?</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    El cliente será desactivado pero mantendrá sus registros históricos.
                    Podrá reactivarlo más adelante si es necesario.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarDesactivar">
                    <i class="fas fa-pause me-1"></i> Desactivar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para reactivar cliente -->
<div class="modal fade" id="modalReactivar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Reactivación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea reactivar este cliente?</p>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    El cliente volverá a estar disponible para nuevas operaciones.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarReactivar">
                    <i class="fas fa-play me-1"></i> Reactivar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let clienteIdActual = null;

// Confirmar desactivación
function confirmarDesactivacion(id) {
    clienteIdActual = id;
    const modal = new bootstrap.Modal(document.getElementById('modalDesactivar'));
    modal.show();
}

// Confirmar reactivación
function reactivarCliente(id) {
    clienteIdActual = id;
    const modal = new bootstrap.Modal(document.getElementById('modalReactivar'));
    modal.show();
}

// Manejar confirmación de desactivación
document.getElementById('btnConfirmarDesactivar').addEventListener('click', function() {
    if (!clienteIdActual) return;

    const btn = this;
    const textoOriginal = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Desactivando...';
    btn.disabled = true;

    fetch(`/contabilidad/clientes/${clienteIdActual}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            bootstrap.Modal.getInstance(document.getElementById('modalDesactivar')).hide();
            
            // Mostrar mensaje y recargar página
            showAlert('success', data.message);
            
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Error al desactivar el cliente');
            btn.innerHTML = textoOriginal;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error de conexión. Intente nuevamente.');
        btn.innerHTML = textoOriginal;
        btn.disabled = false;
    });
});

// Manejar confirmación de reactivación
document.getElementById('btnConfirmarReactivar').addEventListener('click', function() {
    if (!clienteIdActual) return;

    const btn = this;
    const textoOriginal = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Reactivando...';
    btn.disabled = true;

    fetch(`/contabilidad/clientes/${clienteIdActual}/reactivar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            bootstrap.Modal.getInstance(document.getElementById('modalReactivar')).hide();
            
            // Mostrar mensaje y recargar página
            showAlert('success', data.message);
            
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Error al reactivar el cliente');
            btn.innerHTML = textoOriginal;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error de conexión. Intente nuevamente.');
        btn.innerHTML = textoOriginal;
        btn.disabled = false;
    });
});

// Función para mostrar alertas
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-eliminar después de 5 segundos
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
@endpush