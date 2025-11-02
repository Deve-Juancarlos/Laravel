@extends('layouts.app')

@section('title', 'Crear Nuevo Proveedor')
@section('page-title', 'Crear Nuevo Proveedor')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.proveedores.index') }}">Proveedores</a></li>
    <li class="breadcrumb-item active" aria-current="page">Crear Proveedor</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <form action="{{ route('contador.proveedores.store') }}" method="POST">
            @csrf
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title m-0">
                        <i class="fas fa-truck me-2"></i>Información del Proveedor
                    </h5>
                </div>
                <div class="card-body">
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="Ruc" class="form-label fw-bold">RUC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="Ruc" name="Ruc" value="{{ old('Ruc') }}" required maxlength="11">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="RazonSocial" class="form-label fw-bold">Razón Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="RazonSocial" name="RazonSocial" value="{{ old('RazonSocial') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="Direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="Direccion" name="Direccion" value="{{ old('Direccion') }}">
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="Contacto" class="form-label">Nombre de Contacto</label>
                            <input type="text" class="form-control" id="Contacto" name="Contacto" value="{{ old('Contacto') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="Telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="Telefono" name="Telefono" value="{{ old('Telefono') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="Email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="Email" name="Email" value="{{ old('Email') }}">
                        </div>
                    </div>
                </div>
                
                <div class="card-footer text-end">
                    <a href="{{ route('contador.proveedores.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar Proveedor
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection