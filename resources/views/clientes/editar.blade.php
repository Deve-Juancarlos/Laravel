@extends('layouts.contador')

@section('title', 'Editar Cliente')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb y título -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('contabilidad.clientes.index') }}" class="text-decoration-none">
                            <i class="fas fa-arrow-left"></i> Clientes
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('contabilidad.clientes.show', $cliente->id) }}" class="text-decoration-none">
                            {{ $cliente->nombre_completo }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Información del cliente -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">{{ $cliente->nombre_completo }}</h4>
                            <p class="text-muted mb-0">
                                {{ $cliente->tipo_documento }}: {{ $cliente->numero_documento }}
                                <span class="badge bg-{{ $cliente->estado == 1 ? 'success' : 'danger' }} ms-2">
                                    {{ $cliente->estado == 1 ? 'Activo' : 'Inactivo' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="text-muted small">
                                <i class="fas fa-calendar me-1"></i>
                                Creado: {{ $cliente->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de edición -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit text-primary me-2"></i>
                        Editar Información del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formEditarCliente" novalidate>
                        @csrf
                        @method('PUT')

                        <!-- Tipo de documento -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="tipo_documento" class="form-label">Tipo de Documento *</label>
                                <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                    <option value="DNI" {{ $cliente->tipo_documento == 'DNI' ? 'selected' : '' }}>DNI</option>
                                    <option value="RUC" {{ $cliente->tipo_documento == 'RUC' ? 'selected' : '' }}>RUC</option>
                                </select>
                                <div class="invalid-feedback">Seleccione el tipo de documento.</div>
                            </div>
                            <div class="col-md-8">
                                <label for="numero_documento" class="form-label">Número de Documento *</label>
                                <input type="text" class="form-control" id="numero_documento" name="numero_documento" 
                                       value="{{ $cliente->numero_documento }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Campos para DNI (personas naturales) -->
                        <div id="campos-dni" style="{{ $cliente->tipo_documento == 'DNI' ? '' : 'display: none;' }}">
                            <h6 class="text-primary mb-3">Información Personal</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombres" class="form-label">Nombres</label>
                                    <input type="text" class="form-control text-uppercase" id="nombres" name="nombres" 
                                           value="{{ $cliente->nombres }}" placeholder="Ingrese los nombres">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="apellido_paterno" class="form-label">Apellido Paterno</label>
                                    <input type="text" class="form-control text-uppercase" id="apellido_paterno" name="apellido_paterno" 
                                           value="{{ $cliente->apellido_paterno }}" placeholder="Apellido paterno">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                    <input type="text" class="form-control text-uppercase" id="apellido_materno" name="apellido_materno" 
                                           value="{{ $cliente->apellido_materno }}" placeholder="Apellido materno">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                           value="{{ $cliente->fecha_nacimiento ? $cliente->fecha_nacimiento->format('Y-m-d') : '' }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Campo para RUC (personas jurídicas) -->
                        <div id="campos-ruc" style="{{ $cliente->tipo_documento == 'RUC' ? '' : 'display: none;' }}">
                            <h6 class="text-success mb-3">Información Empresarial</h6>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="razon_social" class="form-label">Razón Social *</label>
                                    <input type="text" class="form-control text-uppercase" id="razon_social" name="razon_social" 
                                           value="{{ $cliente->razon_social }}" placeholder="Razón social de la empresa">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Dirección -->
                        <h6 class="text-primary mb-3">Dirección</h6>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea class="form-control text-uppercase" id="direccion" name="direccion" rows="2" 
                                          placeholder="Dirección completa">{{ $cliente->direccion }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Ubicación -->
                        <h6 class="text-primary mb-3">Ubicación</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="departamento" class="form-label">Departamento</label>
                                <input type="text" class="form-control text-uppercase" id="departamento" name="departamento" 
                                       value="{{ $cliente->departamento }}" placeholder="Departamento">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="provincia" class="form-label">Provincia</label>
                                <input type="text" class="form-control text-uppercase" id="provincia" name="provincia" 
                                       value="{{ $cliente->provincia }}" placeholder="Provincia">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="distrito" class="form-label">Distrito</label>
                                <input type="text" class="form-control text-uppercase" id="distrito" name="distrito" 
                                       value="{{ $cliente->distrito }}" placeholder="Distrito">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary me-2" 
                                                onclick="window.history.back()">
                                            <i class="fas fa-times me-1"></i> Cancelar
                                        </button>
                                        <a href="{{ route('contabilidad.clientes.show', $cliente->id) }}" 
                                           class="btn btn-outline-info">
                                            <i class="fas fa-eye me-1"></i> Ver Detalles
                                        </a>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                                            <i class="fas fa-save me-1"></i> Guardar Cambios
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Información actual -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Información Actual
                    </h6>
                    <div class="mb-2">
                        <small class="text-muted">Creado:</small>
                        <div class="fw-bold">{{ $cliente->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Última actualización:</small>
                        <div class="fw-bold">{{ $cliente->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @if($cliente->usuario)
                    <div class="mb-2">
                        <small class="text-muted">Registrado por:</small>
                        <div class="fw-bold">{{ $cliente->usuario->name ?? 'Usuario del Sistema' }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Búsqueda RENIEC (solo para consulta) -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-search text-primary me-2"></i>
                        Consultar RENIEC
                    </h6>
                    <p class="text-muted small mb-3">
                        Verifique información actualizada en RENIEC para este documento.
                    </p>
                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-info btn-sm" 
                                onclick="consultarReniec()">
                            <i class="fas fa-search me-1"></i> Consultar en RENIEC
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de resultado RENIEC -->
<div class="modal fade" id="modalReniec" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-id-card text-primary me-2"></i>
                    Resultado de Consulta RENIEC
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="contenido-reniec">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Consultando...</span>
                        </div>
                        <p class="mt-2">Consultando datos en RENIEC...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnActualizarDesdeReniec" style="display: none;">
                    <i class="fas fa-sync-alt me-1"></i> Actualizar desde RENIEC
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let datosReniecActual = null;

// Formatear a mayúsculas automáticamente
document.addEventListener('DOMContentLoaded', function() {
    const camposTexto = document.querySelectorAll('#formEditarCliente input[type="text"], #formEditarCliente textarea');
    camposTexto.forEach(campo => {
        campo.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });

    // Configurar validaciones
    configurarValidaciones();
    configurarCambioTipoDocumento();
});

// Configurar cambio de tipo de documento
function configurarCambioTipoDocumento() {
    const tipoDocumento = document.getElementById('tipo_documento');
    const camposDni = document.getElementById('campos-dni');
    const camposRuc = document.getElementById('campos-ruc');
    const razonSocial = document.getElementById('razon_social');

    tipoDocumento.addEventListener('change', function() {
        if (this.value === 'DNI') {
            camposDni.style.display = '';
            camposRuc.style.display = 'none';
            razonSocial.value = '';
            razonSocial.removeAttribute('required');
        } else {
            camposDni.style.display = 'none';
            camposRuc.style.display = '';
            razonSocial.setAttribute('required', 'required');
        }
        limpiarValidacion();
    });
}

// Configurar validaciones
function configurarValidaciones() {
    const numeroDocumento = document.getElementById('numero_documento');
    const razonSocial = document.getElementById('razon_social');

    numeroDocumento.addEventListener('input', function() {
        const tipo = document.getElementById('tipo_documento').value;
        let maxLength = tipo === 'DNI' ? 8 : 11;
        let pattern = tipo === 'DNI' ? '\\d{8}' : '\\d{11}';
        
        this.maxLength = maxLength;
        this.pattern = pattern;
        
        // Validar solo números
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    razonSocial.addEventListener('input', function() {
        // Campo obligatorio para RUC
        if (document.getElementById('tipo_documento').value === 'RUC') {
            this.setAttribute('required', 'required');
        }
    });
}

// Limpiar validación
function limpiarValidacion() {
    const inputs = document.querySelectorAll('#formEditarCliente input, #formEditarCliente textarea, #formEditarCliente select');
    inputs.forEach(input => {
        input.classList.remove('is-invalid', 'is-valid');
        const feedback = input.parentNode.querySelector('.invalid-feedback');
        if (feedback) feedback.textContent = '';
    });
}

// Validar formulario
function validarFormulario() {
    const tipoDocumento = document.getElementById('tipo_documento').value;
    let esValido = true;

    // Limpiar validaciones anteriores
    limpiarValidacion();

    // Validar número de documento
    const numeroDoc = document.getElementById('numero_documento');
    if (!numeroDoc.value || numeroDoc.value.length === 0) {
        mostrarError(numeroDoc, 'El número de documento es obligatorio.');
        esValido = false;
    } else if (tipoDocumento === 'DNI' && numeroDoc.value.length !== 8) {
        mostrarError(numeroDoc, 'El DNI debe tener exactamente 8 dígitos.');
        esValido = false;
    } else if (tipoDocumento === 'RUC' && numeroDoc.value.length !== 11) {
        mostrarError(numeroDoc, 'El RUC debe tener exactamente 11 dígitos.');
        esValido = false;
    }

    // Validar campos específicos según tipo
    if (tipoDocumento === 'RUC') {
        const razonSocial = document.getElementById('razon_social');
        if (!razonSocial.value || razonSocial.value.trim().length === 0) {
            mostrarError(razonSocial, 'La razón social es obligatoria para RUC.');
            esValido = false;
        }
    }

    return esValido;
}

// Mostrar error de validación
function mostrarError(input, mensaje) {
    input.classList.add('is-invalid');
    const feedback = input.parentNode.querySelector('.invalid-feedback');
    if (feedback) feedback.textContent = mensaje;
}

// Manejar envío del formulario
document.getElementById('formEditarCliente').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validarFormulario()) {
        return;
    }

    const btnGuardar = document.getElementById('btnGuardar');
    const textoOriginal = btnGuardar.innerHTML;
    
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
    btnGuardar.disabled = true;

    const formData = new FormData(this);

    fetch(`/contabilidad/clientes/{{ $cliente->id }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-HTTP-Method-Override': 'PUT',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btnGuardar.innerHTML = textoOriginal;
        btnGuardar.disabled = false;

        if (data.success) {
            showAlert('success', data.message || 'Cliente actualizado exitosamente');
            setTimeout(() => {
                window.location.href = `/contabilidad/clientes/${data.cliente.id}`;
            }, 1500);
        } else {
            showAlert('error', data.message || 'Error al actualizar el cliente');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btnGuardar.innerHTML = textoOriginal;
        btnGuardar.disabled = false;
        showAlert('error', 'Error de conexión. Intente nuevamente.');
    });
});

// Consultar RENIEC
function consultarReniec() {
    const numeroDoc = document.getElementById('numero_documento').value;
    const tipoDoc = document.getElementById('tipo_documento').value;

    if (!numeroDoc) {
        showAlert('error', 'Ingrese el número de documento primero');
        return;
    }

    const modal = new bootstrap.Modal(document.getElementById('modalReniec'));
    modal.show();

    // Mostrar loading
    document.getElementById('contenido-reniec').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Consultando...</span>
            </div>
            <p class="mt-2">Consultando datos en RENIEC...</p>
        </div>
    `;

    fetch('/contabilidad/clientes/consultar-reniec', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            numero_documento: numeroDoc,
            tipo_documento: tipoDoc
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            datosReniecActual = data.datos;
            mostrarResultadoReniec(data.datos, data.existe);
        } else {
            mostrarErrorReniec(data.message || 'No se encontraron datos');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarErrorReniec('Error de conexión al consultar RENIEC');
    });
}

// Mostrar resultado de RENIEC
function mostrarResultadoReniec(datos, existe) {
    const contenido = document.getElementById('contenido-reniec');
    
    if (existe) {
        contenido.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Este cliente ya está registrado en el sistema.
            </div>
        `;
        document.getElementById('btnActualizarDesdeReniec').style.display = 'none';
        return;
    }

    let html = '';
    
    if (datos.tipo === 'DNI') {
        html = `
            <div class="row">
                <div class="col-12">
                    <h6 class="text-success">Datos encontrados para DNI: ${datos.numero}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr><td><strong>Nombres:</strong></td><td>${datos.nombres || '-'}</td></tr>
                            <tr><td><strong>Apellido Paterno:</strong></td><td>${datos.apellido_paterno || '-'}</td></tr>
                            <tr><td><strong>Apellido Materno:</strong></td><td>${datos.apellido_materno || '-'}</td></tr>
                            <tr><td><strong>Edad:</strong></td><td>${datos.edad || '-'}</td></tr>
                            <tr><td><strong>Ubicación:</strong></td><td>${datos.ubicacion || '-'}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        `;
    } else if (datos.tipo === 'RUC') {
        html = `
            <div class="row">
                <div class="col-12">
                    <h6 class="text-success">Datos encontrados para RUC: ${datos.numero}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr><td><strong>Razón Social:</strong></td><td>${datos.razon_social || '-'}</td></tr>
                            <tr><td><strong>Estado:</strong></td><td>${datos.estado || '-'}</td></tr>
                            <tr><td><strong>Condición:</strong></td><td>${datos.condicion || '-'}</td></tr>
                            <tr><td><strong>Dirección:</strong></td><td>${datos.direccion || '-'}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }

    contenido.innerHTML = html;
    document.getElementById('btnActualizarDesdeReniec').style.display = 'block';
}

// Mostrar error de RENIEC
function mostrarErrorReniec(mensaje) {
    document.getElementById('contenido-reniec').innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${mensaje}
        </div>
    `;
    document.getElementById('btnActualizarDesdeReniec').style.display = 'none';
}

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
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
@endpush