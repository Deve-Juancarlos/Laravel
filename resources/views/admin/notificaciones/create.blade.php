@extends('layouts.admin')

@section('title', 'Nueva Notificación')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Crear Nueva Notificación</h1>
    <p class="text-muted mb-0">Enviar notificación manual a usuarios</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.notificaciones.index') }}">Notificaciones</a></li>
<li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-bell me-2"></i>
                    Formulario de Notificación
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.notificaciones.store') }}">
                    @csrf

                    <!-- Tipo de Notificación -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Tipo de Notificación *</label>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="radio" name="tipo" value="INFO" id="tipoInfo" 
                                       class="btn-check" {{ old('tipo') == 'INFO' ? 'checked' : '' }} required>
                                <label class="btn btn-outline-primary w-100" for="tipoInfo">
                                    <i class="fas fa-info-circle fa-2x d-block mb-2"></i>
                                    Información
                                </label>
                            </div>
                            <div class="col-md-3">
                                <input type="radio" name="tipo" value="ALERTA" id="tipoAlerta" 
                                       class="btn-check" {{ old('tipo') == 'ALERTA' ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning w-100" for="tipoAlerta">
                                    <i class="fas fa-exclamation-triangle fa-2x d-block mb-2"></i>
                                    Alerta
                                </label>
                            </div>
                            <div class="col-md-3">
                                <input type="radio" name="tipo" value="CRITICO" id="tipoCritico" 
                                       class="btn-check" {{ old('tipo') == 'CRITICO' ? 'checked' : '' }}>
                                <label class="btn btn-outline-danger w-100" for="tipoCritico">
                                    <i class="fas fa-exclamation-circle fa-2x d-block mb-2"></i>
                                    Crítico
                                </label>
                            </div>
                            <div class="col-md-3">
                                <input type="radio" name="tipo" value="EXITO" id="tipoExito" 
                                       class="btn-check" {{ old('tipo') == 'EXITO' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success w-100" for="tipoExito">
                                    <i class="fas fa-check-circle fa-2x d-block mb-2"></i>
                                    Éxito
                                </label>
                            </div>
                        </div>
                        @error('tipo')
                        <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Título -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Título *</label>
                        <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" 
                               placeholder="Título de la notificación" 
                               value="{{ old('titulo') }}" required>
                        @error('titulo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Mensaje -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mensaje *</label>
                        <textarea name="mensaje" rows="4" 
                                  class="form-control @error('mensaje') is-invalid @enderror" 
                                  placeholder="Contenido de la notificación..." required>{{ old('mensaje') }}</textarea>
                        @error('mensaje')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Destinatario -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Destinatario</label>
                        <select name="usuario_id" class="form-select">
                            <option value="">Todos los usuarios</option>
                            @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" {{ old('usuario_id') == $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->usuario }} ({{ $usuario->tipousuario }})
                            </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Dejar en blanco para enviar a todos los usuarios
                        </small>
                    </div>

                    <!-- URL de Acción -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">URL de Acción (Opcional)</label>
                        <input type="url" name="url" class="form-control @error('url') is-invalid @enderror" 
                               placeholder="https://..." 
                               value="{{ old('url') }}">
                        @error('url')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            URL a donde redirigir al hacer clic en la notificación
                        </small>
                    </div>

                    <!-- Icono -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Icono (Opcional)</label>
                        <select name="icono" class="form-select">
                            <option value="fa-bell">Campana (por defecto)</option>
                            <option value="fa-info-circle">Información</option>
                            <option value="fa-exclamation-triangle">Advertencia</option>
                            <option value="fa-exclamation-circle">Error</option>
                            <option value="fa-check-circle">Éxito</option>
                            <option value="fa-user">Usuario</option>
                            <option value="fa-money-bill-wave">Dinero</option>
                            <option value="fa-box">Producto</option>
                            <option value="fa-university">Banco</option>
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.notificaciones.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Notificación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
