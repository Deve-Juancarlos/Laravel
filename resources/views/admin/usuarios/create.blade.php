@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Crear Usuario')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Crear Nuevo Usuario</h1>
    <p class="text-muted mb-0">Vincular usuario con empleado del sistema</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.usuarios.index') }}">Usuarios</a></li>
<li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        {{-- Mostrar mensajes de error --}}
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Por favor corrija los siguientes errores:
            </h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Formulario de Nuevo Usuario
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.usuarios.store') }}">
                    @csrf

                    <!-- Seleccionar Empleado -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user-tie text-primary me-2"></i>
                            Seleccionar Empleado <span class="text-danger">*</span>
                        </label>
                        <select name="idusuario" 
                                class="form-select @error('idusuario') is-invalid @enderror" 
                                id="empleadoSelect" 
                                required>
                            <option value="">-- Seleccione un empleado --</option>
                            @foreach($empleadosDisponibles as $empleado)
                            <option value="{{ $empleado->Codemp }}" 
                                    data-nombre="{{ $empleado->Nombre }}"
                                    data-dni="{{ $empleado->Documento }}"
                                    data-telefono="{{ $empleado->Telefono1 ?? $empleado->Celular ?? 'N/A' }}"
                                    {{ old('idusuario') == $empleado->Codemp ? 'selected' : '' }}>
                                {{ $empleado->Nombre }} - DNI: {{ $empleado->Documento }}
                            </option>
                            @endforeach
                        </select>
                        @error('idusuario')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Solo se muestran empleados que aún no tienen usuario asignado
                        </small>
                    </div>

                    <!-- Información del Empleado (Preview) -->
                    <div id="empleadoInfo" class="alert alert-info d-none mb-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Información del Empleado Seleccionado
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Nombre:</strong> <span id="infoNombre">-</span>
                            </div>
                            <div class="col-md-6">
                                <strong>DNI:</strong> <span id="infoDNI">-</span>
                            </div>
                            <div class="col-md-6 mt-2">
                                <strong>Teléfono:</strong> <span id="infoTelefono">-</span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Nombre de Usuario -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user text-primary me-2"></i>
                            Nombre de Usuario <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="usuario" 
                               class="form-control @error('usuario') is-invalid @enderror" 
                               placeholder="Ej: jperez"
                               value="{{ old('usuario') }}" 
                               required>
                        @error('usuario')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Usuario único para iniciar sesión (solo letras, números y guiones)
                        </small>
                    </div>

                    <!-- Contraseña -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-lock text-primary me-2"></i>
                                Contraseña <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   name="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Mínimo 6 caracteres"
                                   required>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-lock text-primary me-2"></i>
                                Confirmar Contraseña <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   class="form-control" 
                                   placeholder="Repita la contraseña"
                                   required>
                        </div>
                    </div>

                    <!-- Tipo de Usuario -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user-shield text-primary me-2"></i>
                            Tipo de Usuario / Rol <span class="text-danger">*</span>
                        </label>
                        <select name="tipousuario" 
                                class="form-select @error('tipousuario') is-invalid @enderror" 
                                required>
                            <option value="">-- Seleccione un rol --</option>
                            <option value="administrador" {{ old('tipousuario') == 'administrador' ? 'selected' : '' }}>
                                Administrador (Acceso total)
                            </option>
                            <option value="CONTADOR" {{ old('tipousuario') == 'CONTADOR' ? 'selected' : '' }}>
                                Contador (Contabilidad y reportes)
                            </option>
                            <option value="VENDEDOR" {{ old('tipousuario') == 'VENDEDOR' ? 'selected' : '' }}>
                                Vendedor (Ventas y clientes)
                            </option>
                        </select>
                        @error('tipousuario')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('empleadoSelect').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const infoDiv = document.getElementById('empleadoInfo');
    
    if (this.value) {
        document.getElementById('infoNombre').textContent = option.dataset.nombre || '-';
        document.getElementById('infoDNI').textContent = option.dataset.dni || '-';
        document.getElementById('infoTelefono').textContent = option.dataset.telefono || '-';
        infoDiv.classList.remove('d-none');
    } else {
        infoDiv.classList.add('d-none');
    }
});

// Si hay un empleado pre-seleccionado (old input), mostrar su info
window.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('empleadoSelect');
    if (select.value) {
        select.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
