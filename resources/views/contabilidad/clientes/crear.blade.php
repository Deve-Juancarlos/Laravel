@extends('layouts.contador')

@section('title', 'Registrar Nuevo Cliente')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">➕ Registrar Cliente</h2>
                    <p class="text-muted mb-0">Agregar un nuevo cliente al sistema contable</p>
                </div>
                <div>
                    <a href="{{ route('contabilidad.clientes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver a Lista
                    </a>
                    <a href="{{ route('contabilidad.clientes.buscar') }}" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Buscar en RENIEC
                    </a>
                </div>
            </div>

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Errores en el formulario:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('contabilidad.clientes.store') }}" method="POST">
                @csrf
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>Información Personal/Empresarial
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Tipo de documento -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tipo_documento" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>Tipo de Documento <span class="text-danger">*</span>
                                </label>
                                <select name="tipo_documento" id="tipo_documento" class="form-select @error('tipo_documento') is-invalid @enderror" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="DNI" {{ old('tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                    <option value="RUC" {{ old('tipo_documento') == 'RUC' ? 'selected' : '' }}>RUC</option>
                                </select>
                                @error('tipo_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="numero_documento" class="form-label">
                                    <i class="fas fa-hashtag me-1"></i>Número de Documento <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="numero_documento" 
                                       id="numero_documento" 
                                       class="form-control @error('numero_documento') is-invalid @enderror" 
                                       value="{{ old('numero_documento') }}" 
                                       required 
                                       maxlength="11"
                                       oninput="validarDocumento(this)">
                                <div class="form-text">
                                    <span id="documento-info">DNI: 8 dígitos | RUC: 11 dígitos</span>
                                </div>
                                @error('numero_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Razón social -->
                        <div class="mb-3" id="razon-social-group" style="display: none;">
                            <label for="razon_social" class="form-label">
                                <i class="fas fa-building me-1"></i>Razón Social <span class="text-danger" id="razon-social-required">*</span>
                            </label>
                            <input type="text" 
                                   name="razon_social" 
                                   id="razon_social" 
                                   class="form-control @error('razon_social') is-invalid @enderror" 
                                   value="{{ old('razon_social') }}">
                            @error('razon_social')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nombres y apellidos -->
                        <div class="row mb-3" id="nombres-group">
                            <div class="col-md-4">
                                <label for="nombres" class="form-label">
                                    <i class="fas fa-user me-1"></i>Nombres
                                </label>
                                <input type="text" 
                                       name="nombres" 
                                       id="nombres" 
                                       class="form-control @error('nombres') is-invalid @enderror" 
                                       value="{{ old('nombres') }}"
                                       oninput="formatearMayusculas(this)">
                                @error('nombres')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="apellido_paterno" class="form-label">
                                    <i class="fas fa-user me-1"></i>Apellido Paterno
                                </label>
                                <input type="text" 
                                       name="apellido_paterno" 
                                       id="apellido_paterno" 
                                       class="form-control @error('apellido_paterno') is-invalid @enderror" 
                                       value="{{ old('apellido_paterno') }}"
                                       oninput="formatearMayusculas(this)">
                                @error('apellido_paterno')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="apellido_materno" class="form-label">
                                    <i class="fas fa-user me-1"></i>Apellido Materno
                                </label>
                                <input type="text" 
                                       name="apellido_materno" 
                                       id="apellido_materno" 
                                       class="form-control @error('apellido_materno') is-invalid @enderror" 
                                       value="{{ old('apellido_materno') }}"
                                       oninput="formatearMayusculas(this)">
                                @error('apellido_materno')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Fecha de nacimiento -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fecha_nacimiento" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Fecha de Nacimiento
                                </label>
                                <input type="date" 
                                       name="fecha_nacimiento" 
                                       id="fecha_nacimiento" 
                                       class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                       value="{{ old('fecha_nacimiento') }}"
                                       max="{{ date('Y-m-d') }}">
                                @error('fecha_nacimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="ubigeo" class="form-label">
                                    <i class="fas fa-map-pin me-1"></i>Código Ubigeo
                                </label>
                                <input type="text" 
                                       name="ubigeo" 
                                       id="ubigeo" 
                                       class="form-control @error('ubigeo') is-invalid @enderror" 
                                       value="{{ old('ubigeo') }}" 
                                       maxlength="6"
                                       placeholder="150101">
                                @error('ubigeo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de dirección -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>Información de Ubicación
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="direccion" class="form-label">
                                <i class="fas fa-home me-1"></i>Dirección Completa
                            </label>
                            <textarea name="direccion" 
                                      id="direccion" 
                                      class="form-control @error('direccion') is-invalid @enderror" 
                                      rows="3"
                                      placeholder="Av. Principal 123, Urbanización Los Olivos">{{ old('direccion') }}</textarea>
                            @error('direccion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label for="departamento" class="form-label">
                                    <i class="fas fa-flag me-1"></i>Departamento
                                </label>
                                <input type="text" 
                                       name="departamento" 
                                       id="departamento" 
                                       class="form-control @error('departamento') is-invalid @enderror" 
                                       value="{{ old('departamento') }}"
                                       oninput="formatearMayusculas(this)">
                                @error('departamento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="provincia" class="form-label">
                                    <i class="fas fa-map-signs me-1"></i>Provincia
                                </label>
                                <input type="text" 
                                       name="provincia" 
                                       id="provincia" 
                                       class="form-control @error('provincia') is-invalid @enderror" 
                                       value="{{ old('provincia') }}"
                                       oninput="formatearMayusculas(this)">
                                @error('provincia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="distrito" class="form-label">
                                    <i class="fas fa-map-pin me-1"></i>Distrito
                                </label>
                                <input type="text" 
                                       name="distrito" 
                                       id="distrito" 
                                       class="form-control @error('distrito') is-invalid @enderror" 
                                       value="{{ old('distrito') }}"
                                       oninput="formatearMayusculas(this)">
                                @error('distrito')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Los campos marcados con <span class="text-danger">*</span> son obligatorios
                                </small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="limpiarFormulario()">
                                    <i class="fas fa-eraser me-1"></i>Limpiar
                                </button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Cliente
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Consejos
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>DNI:</strong> Solo para personas naturales
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>RUC:</strong> Para empresas y personas jurídicas
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            <strong>Reniec:</strong> Busca primero en RENIEC para evitar errores
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-shield-alt text-primary me-2"></i>
                            <strong>Verificación:</strong> Todos los datos se validan automáticamente
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Búsqueda rápida RENIEC -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-search me-2"></i>Búsqueda Rápida
                    </h6>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" 
                               id="dni-busqueda" 
                               class="form-control" 
                               placeholder="DNI (8 dígitos)"
                               maxlength="8">
                        <button class="btn btn-outline-primary" type="button" onclick="buscarDniRapido()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted">
                        Busca en RENIEC y llena los campos automáticamente
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar/ocultar campos según tipo de documento
    const tipoDocumentoSelect = document.getElementById('tipo_documento');
    const razonSocialGroup = document.getElementById('razon-social-group');
    const nombresGroup = document.getElementById('nombres-group');
    const numeroDocumento = document.getElementById('numero_documento');

    tipoDocumentoSelect.addEventListener('change', function() {
        const tipo = this.value;
        
        if (tipo === 'RUC') {
            razonSocialGroup.style.display = 'block';
            nombresGroup.style.display = 'none';
            numeroDocumento.maxLength = 11;
            document.getElementById('documento-info').textContent = 'RUC: 11 dígitos requerido';
            document.getElementById('razon-social-required').style.display = 'inline';
        } else if (tipo === 'DNI') {
            razonSocialGroup.style.display = 'none';
            nombresGroup.style.display = 'flex';
            numeroDocumento.maxLength = 8;
            document.getElementById('documento-info').textContent = 'DNI: 8 dígitos requerido';
            document.getElementById('razon-social-required').style.display = 'none';
        } else {
            razonSocialGroup.style.display = 'none';
            nombresGroup.style.display = 'none';
            numeroDocumento.maxLength = 11;
            document.getElementById('documento-info').textContent = 'DNI: 8 dígitos | RUC: 11 dígitos';
        }
    });

    // Trigger initial state
    tipoDocumentoSelect.dispatchEvent(new Event('change'));
});

// Validar documento
function validarDocumento(input) {
    const tipo = document.getElementById('tipo_documento').value;
    const valor = input.value.replace(/\D/g, '');
    
    input.value = valor;
    
    if (tipo === 'DNI' && valor.length !== 8) {
        input.classList.add('is-invalid');
    } else if (tipo === 'RUC' && valor.length !== 11) {
        input.classList.add('is-invalid');
    } else {
        input.classList.remove('is-invalid');
    }
}

// Formatear a mayúsculas
function formatearMayusculas(input) {
    input.value = input.value.toUpperCase();
}

// Limpiar formulario
function limpiarFormulario() {
    if (confirm('¿Estás seguro de que quieres limpiar el formulario?')) {
        document.querySelector('form').reset();
        
        // Resetear validaciones
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        
        // Mostrar/ocultar campos según tipo de documento
        document.getElementById('tipo_documento').dispatchEvent(new Event('change'));
    }
}

// Búsqueda rápida RENIEC
function buscarDniRapido() {
    const dni = document.getElementById('dni-busqueda').value;
    
    if (dni.length !== 8) {
        alert('El DNI debe tener 8 dígitos');
        return;
    }

    // Redireccionar a la página de búsqueda con el DNI pre-cargado
    window.location.href = `/contabilidad/clientes/buscar?dni=${dni}`;
}
</script>
@endpush