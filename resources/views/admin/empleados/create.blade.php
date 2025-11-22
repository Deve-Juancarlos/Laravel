@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Nuevo Empleado')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/empleados/create.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('header-content')
<div>
    <h1 class="h3 mb-0">Crear Nuevo Empleado</h1>
    <p class="text-muted mb-0">Registrar empleado en el sistema</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.empleados.index') }}">Empleados</a></li>
<li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Datos del Empleado
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.empleados.store') }}">
                    @csrf

                    <!-- Código de Empleado -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Código de Empleado *</label>
                        <input type="number" name="Codemp" 
                               class="form-control @error('Codemp') is-invalid @enderror" 
                               placeholder="Ej: 1001"
                               value="{{ old('Codemp') }}" 
                               min="1" 
                               required>
                        @error('Codemp')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Código único interno del empleado (debe ser numérico)</small>
                    </div>

                    <!-- Nombre Completo -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre Completo *</label>
                        <input type="text" name="Nombre" 
                               class="form-control @error('Nombre') is-invalid @enderror" 
                               placeholder="Nombres y apellidos completos"
                               value="{{ old('Nombre') }}" required>
                        @error('Nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Documento -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">DNI / Documento</label>
                            <input type="text" name="Documento" 
                                   class="form-control @error('Documento') is-invalid @enderror" 
                                   placeholder="Número de documento"
                                   value="{{ old('Documento') }}">
                            @error('Documento')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Opcional (hasta 12 caracteres)</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tipo *</label>
                            <select name="Tipo" class="form-select @error('Tipo') is-invalid @enderror" required>
                                <option value="">Seleccione el rol...</option>
                                <option value="1" {{ old('Tipo') == '1' ? 'selected' : '' }}>Administrador</option>
                                <option value="2" {{ old('Tipo') == '2' ? 'selected' : '' }}>Contador</option>
                                <option value="3" {{ old('Tipo') == '3' ? 'selected' : '' }}>Vendedor</option>
                            </select>
                            @error('Tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Dirección</label>
                        <input type="text" name="Direccion" 
                               class="form-control @error('Direccion') is-invalid @enderror" 
                               placeholder="Dirección completa"
                               value="{{ old('Direccion') }}">
                        @error('Direccion')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Teléfonos -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teléfono 1</label>
                            <input type="text" name="Telefono1" 
                                   class="form-control @error('Telefono1') is-invalid @enderror" 
                                   placeholder="Teléfono fijo"
                                   value="{{ old('Telefono1') }}">
                            @error('Telefono1')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teléfono 2</label>
                            <input type="text" name="Telefono2" 
                                   class="form-control @error('Telefono2') is-invalid @enderror" 
                                   placeholder="Teléfono alternativo"
                                   value="{{ old('Telefono2') }}">
                            @error('Telefono2')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Celular y Nextel -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Celular</label>
                            <input type="text" name="Celular" 
                                   class="form-control @error('Celular') is-invalid @enderror" 
                                   placeholder="Número de celular"
                                   value="{{ old('Celular') }}">
                            @error('Celular')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nextel</label>
                            <input type="text" name="Nextel" 
                                   class="form-control @error('Nextel') is-invalid @enderror" 
                                   placeholder="Número Nextel"
                                   value="{{ old('Nextel') }}">
                            @error('Nextel')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Cumpleaños -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Cumpleaños</label>
                        <input type="text" name="Cumpleaños" 
                               class="form-control @error('Cumpleaños') is-invalid @enderror" 
                               placeholder="Ej: 15/03"
                               value="{{ old('Cumpleaños') }}">
                        @error('Cumpleaños')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Formato: DD/MM</small>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.empleados.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar Empleado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection