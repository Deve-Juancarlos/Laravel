@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Editar Empleado')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/empleados/edit.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('header-content')
<div>
    <h1 class="h3 mb-0">Editar Empleado: {{ $empleado->Nombre }}</h1>
    <p class="text-muted mb-0">Actualizar información del empleado</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.empleados.index') }}">Empleados</a></li>
<li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>
                    Actualizar Datos del Empleado
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.empleados.update', $empleado->Codemp) }}">
                    @csrf
                    @method('PUT')

                    <!-- Código (readonly) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Código</label>
                        <input type="text" class="form-control bg-light" 
                               value="{{ $empleado->Codemp }}" readonly>
                    </div>

                    <!-- Nombre Completo -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre Completo *</label>
                        <input type="text" name="Nombre" 
                               class="form-control @error('Nombre') is-invalid @enderror" 
                               value="{{ old('Nombre', $empleado->Nombre) }}" required>
                        @error('Nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Documento -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">DNI / Documento *</label>
                            <input type="text" name="Documento" 
                                   class="form-control @error('Documento') is-invalid @enderror" 
                                   value="{{ old('Documento', $empleado->Documento) }}" required>
                            @error('Documento')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tipo *</label>
                            <select name="Tipo" class="form-select @error('Tipo') is-invalid @enderror" required>
                                <option value="1" {{ old('Tipo', $empleado->Tipo) == '1' ? 'selected' : '' }}>Tipo 1</option>
                                <option value="2" {{ old('Tipo', $empleado->Tipo) == '2' ? 'selected' : '' }}>Tipo 2</option>
                                <option value="3" {{ old('Tipo', $empleado->Tipo) == '3' ? 'selected' : '' }}>Tipo 3</option>
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
                               value="{{ old('Direccion', $empleado->Direccion) }}">
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
                                   value="{{ old('Telefono1', $empleado->Telefono1) }}">
                            @error('Telefono1')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teléfono 2</label>
                            <input type="text" name="Telefono2" 
                                   class="form-control @error('Telefono2') is-invalid @enderror" 
                                   value="{{ old('Telefono2', $empleado->Telefono2) }}">
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
                                   value="{{ old('Celular', $empleado->Celular) }}">
                            @error('Celular')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nextel</label>
                            <input type="text" name="Nextel" 
                                   class="form-control @error('Nextel') is-invalid @enderror" 
                                   value="{{ old('Nextel', $empleado->Nextel) }}">
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
                               value="{{ old('Cumpleaños', $empleado->Cumpleaños) }}">
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
                            <i class="fas fa-save me-2"></i>Actualizar Empleado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
