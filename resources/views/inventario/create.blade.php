@extends('layouts.app')

@section('title', 'Crear Producto')
@section('page-title', 'Crear Nuevo Producto y Lote Inicial')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.inventario.index') }}">Inventario</a></li>
    <li class="breadcrumb-item active" aria-current="page">Crear Producto</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <form action="{{ route('contador.inventario.store') }}" method="POST">
            @csrf
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title m-0"><i class="fas fa-box me-2"></i>Datos del Producto (Catálogo)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="CodPro" class="form-label">Código Producto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="CodPro" name="CodPro" value="{{ old('CodPro') }}" maxlength="10" required>
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="Nombre" class="form-label">Nombre Comercial <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="Nombre" name="Nombre" value="{{ old('Nombre') }}" maxlength="70" required>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-9 mb-3">
                            <label for="Principio" class="form-label">Principio Activo</label>
                            <input type="text" class="form-control" id="Principio" name="Principio" value="{{ old('Principio') }}" maxlength="200">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="CodProv" class="form-label">Cód. Proveedor</label>
                            <input type="text" class="form-control" id="CodProv" name="CodProv" value="{{ old('CodProv') }}" maxlength="4">
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="Costo" class="form-label">Costo (Compra) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="Costo" name="Costo" value="{{ old('Costo', 0) }}" min="0" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="PventaMa" class="form-label">Precio Venta Mayor <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="PventaMa" name="PventaMa" value="{{ old('PventaMa', 0) }}" min="0" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="PventaMi" class="form-label">Precio Venta Minorista <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="PventaMi" name="PventaMi" value="{{ old('PventaMi', 0) }}" min="0" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="Clinea" class="form-label">Línea <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="Clinea" name="Clinea" value="{{ old('Clinea', 29) }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="Clase" class="form-label">Clase <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="Clase" name="Clase" value="{{ old('Clase', 5) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ¡AQUÍ ESTÁ LO NUEVO! --}}
            <div class="card shadow mt-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title m-0"><i class="fas fa-boxes me-2"></i>Lote Inicial (Inventario en Saldos)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Este será el primer lote de este producto que ingresará al inventario (tabla Saldos).</p>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="stock_inicial" class="form-label">Stock Inicial <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="stock_inicial" name="stock_inicial" value="{{ old('stock_inicial', 1) }}" min="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="lote" class="form-label">Número de Lote <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lote" name="lote" value="{{ old('lote') }}" maxlength="15" required>
                        </div>
                         <div class="col-md-4 mb-3">
                            <label for="vencimiento" class="form-label">Fecha de Vencimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="vencimiento" name="vencimiento" value="{{ old('vencimiento') }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <a href="{{ route('contador.inventario.index') }}" class="btn btn-secondary btn-lg">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-1"></i> Crear Producto y Lote
                </button>
            </div>
        </form>
    </div>
</div>
@endsection