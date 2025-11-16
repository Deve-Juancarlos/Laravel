@extends('layouts.admin')

@section('title', 'Editar Usuario')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Editar Usuario: {{ $usuarioData->usuario }}</h1>
    <p class="text-muted mb-0">Modificar vinculación y rol del usuario</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.usuarios.index') }}">Usuarios</a></li>
<li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        {{-- Mostrar mensajes de éxito/error --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

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

        <!-- Información Actual -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Información Actual del Usuario
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Usuario:</strong>
                        <p class="mb-0">{{ $usuarioData->usuario }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Rol Actual:</strong>
                        <p class="mb-0">
                            @if($usuarioData->tipousuario == 'administrador')
                                <span class="badge bg-danger">ADMINISTRADOR</span>
                            @elseif($usuarioData->tipousuario == 'CONTADOR')
                                <span class="badge bg-primary">CONTADOR</span>
                            @else
                                <span class="badge bg-info">VENDEDOR</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Empleado Vinculado:</strong>
                        <p class="mb-0">
                            @if($usuarioData->empleado_nombre)
                                <i class="fas fa-link text-success me-1"></i>
                                {{ $usuarioData->empleado_nombre }}
                            @else
                                <span class="text-danger">
                                    <i class="fas fa-unlink me-1"></i>Sin vincular
                                </span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Estado:</strong>
                        <p class="mb-0">
                            @if($usuarioData->estado == 'ACTIVO')
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de Edición -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Modificar Usuario
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.usuarios.update', $usuarioData->usuario) }}">
                    @csrf
                    @method('PUT')

                    <!-- Cambiar Empleado Vinculado -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user-tie text-primary me-2"></i>
                            Empleado Vinculado <span class="text-danger">*</span>
                        </label>
                        <select name="idusuario" 
                                class="form-select @error('idusuario') is-invalid @enderror" 
                                id="empleadoSelect" 
                                required>
                            @if($usuarioData->idusuario)
                                <option value="{{ $usuarioData->idusuario }}" 
                                        {{ old('idusuario', $usuarioData->idusuario) == $usuarioData->idusuario ? 'selected' : '' }}>
                                    {{ $usuarioData->empleado_nombre }} - DNI: {{ $usuarioData->empleado_dni }}
                                </option>
                            @endif
                            
                            @if($empleadosDisponibles->count() > 0)
                                <optgroup label="Otros empleados disponibles">
                                    @foreach($empleadosDisponibles as $empleado)
                                    <option value="{{ $empleado->Codemp }}"
                                            {{ old('idusuario') == $empleado->Codemp ? 'selected' : '' }}>
                                        {{ $empleado->Nombre }} - DNI: {{ $empleado->Documento }}
                                    </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        @error('idusuario')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Puede cambiar el empleado vinculado a este usuario
                        </small>
                    </div>

                    <!-- Cambiar Rol -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user-shield text-primary me-2"></i>
                            Tipo de Usuario / Rol <span class="text-danger">*</span>
                        </label>
                        <select name="tipousuario" 
                                class="form-select @error('tipousuario') is-invalid @enderror" 
                                required>
                            <option value="administrador" 
                                    {{ old('tipousuario', $usuarioData->tipousuario) == 'administrador' ? 'selected' : '' }}>
                                Administrador (Acceso total)
                            </option>
                            <option value="CONTADOR" 
                                    {{ old('tipousuario', $usuarioData->tipousuario) == 'CONTADOR' ? 'selected' : '' }}>
                                Contador (Contabilidad y reportes)
                            </option>
                            <option value="VENDEDOR" 
                                    {{ old('tipousuario', $usuarioData->tipousuario) == 'VENDEDOR' ? 'selected' : '' }}>
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
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-key me-2"></i>
                    Restablecer Contraseña
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.usuarios.reset-password', $usuarioData->usuario) }}">
                    @csrf
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Atención:</strong> Esta acción cambiará la contraseña del usuario inmediatamente.
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nueva Contraseña</label>
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
                            <label class="form-label fw-bold">Confirmar Contraseña</label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   class="form-control" 
                                   placeholder="Repita la contraseña"
                                   required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning" onclick="return confirm('¿Confirma el cambio de contraseña?')">
                        <i class="fas fa-key me-2"></i>Cambiar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
