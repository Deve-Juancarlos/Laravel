@extends('layouts.admin')

@section('title', 'Detalle del Empleado')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/empleados/detalle.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('header-content')
<div>
    <h1 class="h3 mb-0">{{ $empleado->Nombre }}</h1>
    <p class="text-muted mb-0">Información completa del empleado</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.empleados.index') }}">Empleados</a></li>
<li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Información Personal -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>
                    Información Personal
                </h5>
                <a href="{{ route('admin.empleados.edit', $empleado->Codemp) }}" class="btn btn-sm btn-light">
                    <i class="fas fa-edit me-1"></i>Editar
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="text-muted small">Código</label>
                        <p class="mb-0 fw-bold">#{{ $empleado->Codemp }}</p>
                    </div>
                    <div class="col-md-8">
                        <label class="text-muted small">Nombre Completo</label>
                        <p class="mb-0 fw-bold">{{ $empleado->Nombre }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="text-muted small">Documento</label>
                        <p class="mb-0">
                            <code class="fs-6">{{ $empleado->Documento }}</code>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Tipo</label>
                        <p class="mb-0">
                            <span class="badge bg-primary">Tipo {{ $empleado->Tipo }}</span>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Cumpleaños</label>
                        <p class="mb-0">{{ $empleado->Cumpleaños ?? '-' }}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Dirección</label>
                    <p class="mb-0">{{ $empleado->Direccion ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Información de Contacto -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-phone me-2"></i>
                    Información de Contacto
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Teléfono 1</label>
                        <p class="mb-0">
                            @if($empleado->Telefono1)
                                <i class="fas fa-phone text-primary me-2"></i>
                                {{ $empleado->Telefono1 }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Teléfono 2</label>
                        <p class="mb-0">
                            @if($empleado->Telefono2)
                                <i class="fas fa-phone text-primary me-2"></i>
                                {{ $empleado->Telefono2 }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Celular</label>
                        <p class="mb-0">
                            @if($empleado->Celular)
                                <i class="fas fa-mobile-alt text-success me-2"></i>
                                {{ $empleado->Celular }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Nextel</label>
                        <p class="mb-0">
                            @if($empleado->Nextel)
                                <i class="fas fa-radio text-info me-2"></i>
                                {{ $empleado->Nextel }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usuario Vinculado -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-lock me-2"></i>
                    Usuario del Sistema
                </h5>
            </div>
            <div class="card-body">
                @if($usuario)
                    <div class="alert alert-success">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-1">Tiene Usuario Asignado</h6>
                                <p class="mb-0">
                                    <strong>Usuario:</strong> {{ $usuario->usuario }} | 
                                    <strong>Tipo:</strong> <span class="badge bg-primary">{{ $usuario->tipousuario }}</span> | 
                                    <strong>Estado:</strong> 
                                    @if($usuario->estado == 1)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('admin.usuarios.edit', $usuario->usuario) }}" class="btn btn-primary">
                        <i class="fas fa-user-edit me-2"></i>Gestionar Usuario
                    </a>
                @else
                    <div class="alert alert-warning">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-1">Sin Usuario Asignado</h6>
                                <p class="mb-0">Este empleado no tiene un usuario de acceso al sistema</p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('admin.usuarios.create') }}?empleado={{ $empleado->Codemp }}" class="btn btn-success">
                        <i class="fas fa-user-plus me-2"></i>Crear Usuario
                    </a>
                @endif
            </div>
        </div>

        <!-- Botones -->
        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.empleados.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al Listado
            </a>
            <div>
                <a href="{{ route('admin.empleados.edit', $empleado->Codemp) }}" class="btn btn-primary me-2">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
                <form method="POST" action="{{ route('admin.empleados.destroy', $empleado->Codemp) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" 
                            onclick="return confirm('¿Está seguro de eliminar este empleado?')">
                        <i class="fas fa-trash me-2"></i>Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
