@extends('layouts.app')
@section('title', 'Nueva Nota de Crédito')
@section('page-title', 'Nueva Nota de Crédito')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.notas-credito.index') }}">Notas de Crédito</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paso 1</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title m-0"><i class="fas fa-search me-2"></i>Paso 1: Buscar Documento de Venta</h5>
            </div>
            <form action="{{ route('contador.notas-credito.buscarFactura') }}" method="POST">
                @csrf
                <div class="card-body">
                    <p class="text-muted">Ingrese el número de la Factura o Boleta que desea anular o aplicar una devolución.</p>
                    <div class="mb-3">
                        <label for="numero_factura" class="form-label fw-bold">Número de Documento (Ej: F001-00001234)</label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="numero_factura" 
                               name="numero_factura" 
                               value="{{ old('numero_factura') }}" 
                               required>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        Siguiente <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection