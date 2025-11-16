@extends('layouts.admin')

@section('title', 'Gestionar Roles')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Gestionar Rol: {{ $usuarioData->usuario }}</h1>
    <p class="text-muted mb-0">Cambiar permisos y nivel de acceso</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.usuarios.index') }}">Usuarios</a></li>
<li class="breadcrumb-item active">Gestionar Roles</li>
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

        <!-- Información del Usuario -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-4 d-inline-block">
                            <i class="fas fa-user fa-3x text-primary"></i>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <h4 class="mb-1">{{ $usuarioData->usuario }}</h4>
                        @if($usuarioData->empleado_nombre)
                            <p class="text-muted mb-2">
                                <i class="fas fa-user-tie me-2"></i>{{ $usuarioData->empleado_nombre }}
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-id-card me-2"></i>DNI: {{ $usuarioData->empleado_dni ?? 'N/A' }}
                                @if($usuarioData->empleado_telefono || $usuarioData->empleado_celular)
                                    | <i class="fas fa-phone ms-2 me-2"></i>{{ $usuarioData->empleado_telefono ?? $usuarioData->empleado_celular }}
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Selector de Roles -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-tag me-2"></i>
                    Seleccionar Rol del Usuario
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.usuarios.updateRol', $usuarioData->usuario) }}">
                    @csrf
                    @method('PUT')

                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Rol Actual:</strong> 
                        @if($usuarioData->tipousuario == 'administrador')
                            <span class="badge bg-danger">ADMINISTRADOR</span>
                        @elseif($usuarioData->tipousuario == 'CONTADOR')
                            <span class="badge bg-primary">CONTADOR</span>
                        @else
                            <span class="badge bg-info">VENDEDOR</span>
                        @endif
                    </div>

                    <!-- Opciones de Rol -->
                    <div class="mb-4">
                        <div class="row g-3">
                            <!-- Administrador -->
                            <div class="col-md-4">
                                <div class="card {{ $usuarioData->tipousuario == 'administrador' ? 'border-danger shadow' : 'border' }} h-100">
                                    <div class="card-body text-center">
                                        <input type="radio" 
                                               name="tipousuario" 
                                               value="administrador" 
                                               id="rolAdmin"
                                               class="form-check-input d-none"
                                               {{ $usuarioData->tipousuario == 'administrador' ? 'checked' : '' }}>
                                        <label for="rolAdmin" class="w-100 cursor-pointer">
                                            <div class="bg-danger bg-opacity-10 rounded p-3 mb-3">
                                                <i class="fas fa-shield-alt fa-3x text-danger"></i>
                                            </div>
                                            <h5 class="mb-2">Administrador</h5>
                                            <p class="text-muted small mb-0">
                                                Acceso completo al sistema. Gestión de usuarios, configuración y reportes ejecutivos.
                                            </p>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Contador -->
                            <div class="col-md-4">
                                <div class="card {{ $usuarioData->tipousuario == 'CONTADOR' ? 'border-primary shadow' : 'border' }} h-100">
                                    <div class="card-body text-center">
                                        <input type="radio" 
                                               name="tipousuario" 
                                               value="CONTADOR" 
                                               id="rolContador"
                                               class="form-check-input d-none"
                                               {{ $usuarioData->tipousuario == 'CONTADOR' ? 'checked' : '' }}>
                                        <label for="rolContador" class="w-100 cursor-pointer">
                                            <div class="bg-primary bg-opacity-10 rounded p-3 mb-3">
                                                <i class="fas fa-calculator fa-3x text-primary"></i>
                                            </div>
                                            <h5 class="mb-2">Contador</h5>
                                            <p class="text-muted small mb-0">
                                                Acceso a contabilidad, tesorería, libro diario, mayor, estados financieros y reportes.
                                            </p>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Vendedor -->
                            <div class="col-md-4">
                                <div class="card {{ $usuarioData->tipousuario == 'VENDEDOR' ? 'border-info shadow' : 'border' }} h-100">
                                    <div class="card-body text-center">
                                        <input type="radio" 
                                               name="tipousuario" 
                                               value="VENDEDOR" 
                                               id="rolVendedor"
                                               class="form-check-input d-none"
                                               {{ $usuarioData->tipousuario == 'VENDEDOR' ? 'checked' : '' }}>
                                        <label for="rolVendedor" class="w-100 cursor-pointer">
                                            <div class="bg-info bg-opacity-10 rounded p-3 mb-3">
                                                <i class="fas fa-user-tie fa-3x text-info"></i>
                                            </div>
                                            <h5 class="mb-2">Vendedor</h5>
                                            <p class="text-muted small mb-0">
                                                Acceso a ventas, clientes, productos y consulta de inventario disponible.
                                            </p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @error('tipousuario')
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                    </div>
                    @enderror

                    <!-- Botones -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('¿Confirma el cambio de rol?')">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.cursor-pointer {
    cursor: pointer;
}
.card.border-danger,
.card.border-primary,
.card.border-info {
    transition: all 0.3s ease;
}
label:hover .card {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
@endpush

@push('scripts')
<script>
// Mejorar la interactividad de los radio buttons
document.querySelectorAll('input[type="radio"][name="tipousuario"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remover clases de todos los cards
        document.querySelectorAll('.card').forEach(card => {
            card.classList.remove('border-danger', 'border-primary', 'border-info', 'shadow');
            card.classList.add('border');
        });
        
        // Agregar clase al card seleccionado
        const label = this.nextElementSibling;
        const card = label.closest('.card');
        
        if (this.value === 'administrador') {
            card.classList.add('border-danger', 'shadow');
        } else if (this.value === 'CONTADOR') {
            card.classList.add('border-primary', 'shadow');
        } else if (this.value === 'VENDEDOR') {
            card.classList.add('border-info', 'shadow');
        }
    });
});
</script>
@endpush
