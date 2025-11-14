@extends('layouts.app')

@section('title', 'Enviar Notificación')
@section('page-title', 'Enviar Notificación a Administrador')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">Nueva Notificación</div>
                <div class="card-body">
                    <form action="{{ route('contador.notificaciones.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="usuario_id" class="form-label">Enviar a (Admin):</label>
                            <select name="usuario_id" id="usuario_id" class="form-select" required>
                                @foreach($admins as $admin)
                                <option value="{{ $admin->idusuario }}">{{ $admin->usuario }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título:</label>
                            <input type="text" class="form-control" name="titulo" id="titulo" value="{{ old('titulo') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="mensaje" class="form-label">Mensaje:</label>
                            <textarea name="mensaje" id="mensaje" class="form-control" rows="3" required>{{ old('mensaje') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="url" class="form-label">URL (Opcional):</label>
                            <input type="text" class="form-control" name="url" id="url" value="{{ old('url') }}" placeholder="Ej: {{ route('contador.compras.index') }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="icono" class="form-label">Icono:</label>
                                <input type="text" class="form-control" name="icono" id="icono" value="fa-warning" required>
                                <small class="text-muted">Ej: fa-warning, fa-check, fa-exclamation-triangle</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="color" class="form-label">Color:</label>
                                <input type="color" class="form-control form-control-color" name="color" id="color" value="#ffc107" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Enviar Notificación
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection