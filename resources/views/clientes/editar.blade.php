@extends('layouts.app')

@section('title', 'Editar Cliente')
@section('page-title', 'Editar Información del Cliente')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('contador.clientes.index') }}" class="text-decoration-none">
            <i class="fas fa-users"></i> Clientes
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('contador.clientes.show', $cliente->Codclie) }}" class="text-decoration-none">
            {{ $cliente->Razon }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Editar</li>
@endsection

@push('styles')
<style>
    .form-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }
    .form-section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #667eea;
    }
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    .form-control, .form-select {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }
    .form-control.is-invalid {
        border-color: #dc3545;
    }
    .form-control.is-valid {
        border-color: #28a745;
    }
    .invalid-feedback, .valid-feedback {
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    .info-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    .info-box h5 {
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .info-box p {
        margin-bottom: 0;
        opacity: 0.9;
    }
    .btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        color: white;
    }
    .help-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    .char-counter {
        font-size: 0.75rem;
        color: #6c757d;
        text-align: right;
        margin-top: 0.25rem;
    }
</style>
@endpush

@section('content')

<!-- Información del Cliente Actual -->
<div class="info-box">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5><i class="fas fa-user-edit me-2"></i>{{ $cliente->Razon }}</h5>
            <p class="mb-0">
                <i class="fas fa-id-card me-2"></i>{{ $cliente->Documento }} | 
                <i class="fas fa-calendar me-2"></i>Cliente desde {{ \Carbon\Carbon::parse($cliente->Fecha)->format('d/m/Y') }}
            </p>
        </div>
        <div>
            <a href="{{ route('contador.clientes.show', $cliente->Codclie) }}" class="btn btn-light">
                <i class="fas fa-eye me-1"></i>Ver Detalle
            </a>
        </div>
    </div>
</div>

<form id="formEditarCliente" method="POST" action="{{ route('contador.clientes.update', $cliente->Codclie) }}" novalidate>
    @csrf
    @method('PUT')

    <!-- Sección: Información Básica -->
    <div class="form-section">
        <h6 class="form-section-title">
            <i class="fas fa-info-circle me-2"></i>Información Básica
        </h6>
        
        <div class="row">
            <div class="col-md-8 mb-3">
                <label for="Razon" class="form-label">
                    Razón Social / Nombre Completo <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control text-uppercase @error('Razon') is-invalid @enderror" 
                       id="Razon" name="Razon" value="{{ old('Razon', $cliente->Razon) }}" 
                       required maxlength="80" placeholder="Ingrese el nombre o razón social">
                <div class="char-counter">
                    <span id="razonCounter">{{ strlen($cliente->Razon) }}</span>/80 caracteres
                </div>
                @error('Razon')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label for="Documento" class="form-label">
                    RUC / DNI <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control @error('Documento') is-invalid @enderror" 
                       id="Documento" name="Documento" value="{{ old('Documento', $cliente->Documento) }}" 
                       required maxlength="12" placeholder="11 dígitos para RUC">
                <small class="help-text">
                    <i class="fas fa-info-circle"></i> RUC (11) o DNI (8) dígitos
                </small>
                @error('Documento')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Sección: Ubicación y Contacto -->
    <div class="form-section">
        <h6 class="form-section-title">
            <i class="fas fa-map-marker-alt me-2"></i>Ubicación y Contacto
        </h6>
        
        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="Direccion" class="form-label">Dirección Completa</label>
                <textarea class="form-control text-uppercase @error('Direccion') is-invalid @enderror" 
                          id="Direccion" name="Direccion" rows="2" 
                          maxlength="60" placeholder="Av. / Jr. / Calle, Número, Referencia">{{ old('Direccion', $cliente->Direccion) }}</textarea>
                <div class="char-counter">
                    <span id="direccionCounter">{{ strlen($cliente->Direccion ?? '') }}</span>/60 caracteres
                </div>
                @error('Direccion')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label for="Telefono1" class="form-label">
                    <i class="fas fa-phone me-1"></i>Teléfono Principal
                </label>
                <input type="text" class="form-control @error('Telefono1') is-invalid @enderror" 
                       id="Telefono1" name="Telefono1" value="{{ old('Telefono1', $cliente->Telefono1) }}" 
                       maxlength="10" placeholder="999 888 777">
                @error('Telefono1')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label for="Celular" class="form-label">
                    <i class="fas fa-mobile-alt me-1"></i>Celular
                </label>
                <input type="text" class="form-control @error('Celular') is-invalid @enderror" 
                       id="Celular" name="Celular" value="{{ old('Celular', $cliente->Celular) }}" 
                       maxlength="10" placeholder="987 654 321">
                @error('Celular')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label for="Email" class="form-label">
                    <i class="fas fa-envelope me-1"></i>Correo Electrónico
                </label>
                <input type="email" class="form-control @error('Email') is-invalid @enderror" 
                       id="Email" name="Email" value="{{ old('Email', $cliente->Email) }}" 
                       maxlength="100" placeholder="correo@empresa.com">
                @error('Email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Sección: Información Comercial -->
    <div class="form-section">
        <h6 class="form-section-title">
            <i class="fas fa-handshake me-2"></i>Información Comercial
        </h6>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="TipoClie" class="form-label">Tipo de Cliente</label>
                <select class="form-select @error('TipoClie') is-invalid @enderror" 
                        id="TipoClie" name="TipoClie">
                    <option value="1" {{ old('TipoClie', $cliente->TipoClie) == 1 ? 'selected' : '' }}>Regular</option>
                    <option value="2" {{ old('TipoClie', $cliente->TipoClie) == 2 ? 'selected' : '' }}>Premium</option>
                    <option value="3" {{ old('TipoClie', $cliente->TipoClie) == 3 ? 'selected' : '' }}>VIP</option>
                    <option value="4" {{ old('TipoClie', $cliente->TipoClie) == 4 ? 'selected' : '' }}>Corporativo</option>
                </select>
                @error('TipoClie')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label for="Vendedor" class="form-label">Vendedor Asignado</label>
                <select class="form-select @error('Vendedor') is-invalid @enderror" 
                        id="Vendedor" name="Vendedor">
                    <option value="">Seleccione un vendedor</option>
                    @foreach($vendedores as $vendedor)
                    <option value="{{ $vendedor->Codemp }}" 
                            {{ old('Vendedor', $cliente->Vendedor) == $vendedor->Codemp ? 'selected' : '' }}>
                        {{ $vendedor->Nombre }}
                    </option>
                    @endforeach
                </select>
                @error('Vendedor')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label for="Limite" class="form-label">
                    <i class="fas fa-credit-card me-1"></i>Límite de Crédito (S/)
                </label>
                <input type="number" class="form-control @error('Limite') is-invalid @enderror" 
                       id="Limite" name="Limite" value="{{ old('Limite', $cliente->Limite) }}" 
                       min="0" step="0.01" placeholder="0.00">
                <small class="help-text">
                    <i class="fas fa-info-circle"></i> Monto máximo de crédito disponible
                </small>
                @error('Limite')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('contador.clientes.show', $cliente->Codclie) }}" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i>Cancelar
            </a>
        </div>
        <div>
            <button type="button" class="btn btn-outline-info me-2" onclick="consultarReniec()">
                <i class="fas fa-search me-1"></i>Verificar RENIEC
            </button>
            <button type="submit" class="btn btn-save" id="btnGuardar">
                <i class="fas fa-save me-1"></i>Guardar Cambios
            </button>
        </div>
    </div>
</form>

<!-- Modal de Verificación RENIEC -->
<div class="modal fade" id="modalReniec" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-id-card text-primary me-2"></i>Verificación en RENIEC
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="contenido-reniec">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Consultando...</span>
                        </div>
                        <p class="mt-3 text-muted">Consultando datos en RENIEC...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btnActualizarDesdeReniec" style="display: none;">
                    <i class="fas fa-sync-alt me-1"></i>Actualizar datos desde RENIEC
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let datosReniecActual = null;

document.addEventListener('DOMContentLoaded', function() {
    // Auto-uppercase
    const camposTexto = document.querySelectorAll('input[type="text"]:not(#Email), textarea');
    camposTexto.forEach(campo => {
        campo.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });

    // Contadores de caracteres
    setupCharCounter('Razon', 'razonCounter', 80);
    setupCharCounter('Direccion', 'direccionCounter', 60);

    // Validación en tiempo real
    setupRealtimeValidation();
});

function setupCharCounter(inputId, counterId, maxLength) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if (input && counter) {
        input.addEventListener('input', function() {
            counter.textContent = this.value.length;
            if (this.value.length >= maxLength) {
                counter.classList.add('text-danger');
            } else {
                counter.classList.remove('text-danger');
            }
        });
    }
}

function setupRealtimeValidation() {
    const documento = document.getElementById('Documento');
    documento.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        
        const length = this.value.length;
        if (length === 8 || length === 11) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else if (length > 0) {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
            const feedback = this.parentNode.querySelector('.invalid-feedback') || document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.style.display = 'block';
            feedback.textContent = 'El documento debe tener 8 (DNI) u 11 (RUC) dígitos';
            this.parentNode.appendChild(feedback);
        }
    });

    // Validación de email
    const email = document.getElementById('Email');
    email.addEventListener('input', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value && !emailRegex.test(this.value)) {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else if (this.value) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
    });
}

// Envío del formulario
document.getElementById('formEditarCliente').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btnGuardar = document.getElementById('btnGuardar');
    const textoOriginal = btnGuardar.innerHTML;
    
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';
    btnGuardar.disabled = true;

    const formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btnGuardar.innerHTML = textoOriginal;
        btnGuardar.disabled = false;

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.mensaje || 'Cliente actualizado correctamente',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = "{{ route('contador.clientes.show', $cliente->Codclie) }}";
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.mensaje || 'Error al actualizar el cliente'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btnGuardar.innerHTML = textoOriginal;
        btnGuardar.disabled = false;
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión. Intente nuevamente.'
        });
    });
});

// Consultar RENIEC
function consultarReniec() {
    const documento = document.getElementById('Documento').value;
    
    if (!documento) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Ingrese el número de documento primero'
        });
        return;
    }

    const modal = new bootstrap.Modal(document.getElementById('modalReniec'));
    modal.show();

    fetch(`{{ url('contador/clientes/api/consulta-documento') }}/${documento}`)

        .then(response => response.json())
        .then(data => {
            if (data.success) {
                datosReniecActual = data.data;
                mostrarResultadoReniec(data.data);
            } else {
                mostrarErrorReniec(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarErrorReniec('Error de conexión al consultar RENIEC');
        });
}

function mostrarResultadoReniec(datos) {
    const contenido = document.getElementById('contenido-reniec');
    contenido.innerHTML = `
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Datos encontrados en RENIEC</strong>
        </div>
        <table class="table table-bordered">
            <tr>
                <th width="30%">Documento:</th>
                <td>${datos.numero || '-'}</td>
            </tr>
            <tr>
                <th>Razón Social / Nombre:</th>
                <td>${datos.razon_social || datos.nombres || '-'}</td>
            </tr>
            <tr>
                <th>Dirección:</th>
                <td>${datos.direccion || datos.address || '-'}</td>
            </tr>
        </table>
    `;
    document.getElementById('btnActualizarDesdeReniec').style.display = 'block';
}

function mostrarErrorReniec(mensaje) {
    document.getElementById('contenido-reniec').innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${mensaje}
        </div>
    `;
    document.getElementById('btnActualizarDesdeReniec').style.display = 'none';
}
</script>
@endpush