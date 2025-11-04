@extends('layouts.app')
@section('title', 'Anular Planilla de Cobranza')
@section('page-title', 'Anular Planilla')

@section('breadcrumbs')
    <li class="breadcrumb-item">Contabilidad</li>
    <li class="breadcrumb-item">Planillas</li>
    <li class="breadcrumb-item active" aria-current="page">Anular</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <form action="{{ route('contador.anulacion.store') }}" method="POST">
            @csrf
            <input type="hidden" name="serie" value="{{ $planilla->Serie }}">
            <input type="hidden" name="numero" value="{{ $planilla->Numero }}">

            <div class="card shadow border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title m-0"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar Anulación de Planilla</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <strong>¡Advertencia!</strong> Está a punto de anular esta planilla. Esta acción es irreversible y generará un asiento contable de extorno (reversión).
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted mb-0">Planilla N°:</p>
                            <p class="fw-bold fs-5">{{ $planilla->Serie }}-{{ $planilla->Numero }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-0">Vendedor:</p>
                            <p class="fw-bold fs-5">{{ $planilla->VendedorNombre }}</p>
                        </div>
                         <div class="col-md-6">
                            <p class="text-muted mb-0">Fecha de Ingreso:</p>
                            <p class="fw-bold fs-5">{{ \Carbon\Carbon::parse($planilla->FechaIng)->format('d/m/Y') }}</p>
                        </div>
                         <div class="col-md-6">
                            <p class="text-muted mb-0">Monto Total a Revertir:</p>
                            <p class="fw-bold fs-5 text-danger">S/ {{ number_format($total, 2) }}</p>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="mb-3">
                        <label for="motivo" class="form-label fw-bold">Motivo de la Anulación (Obligatorio)</label>
                        <textarea class="form-control @error('motivo') is-invalid @enderror" 
                                  id="motivo" name="motivo" rows="3" 
                                  placeholder="Ej: Depósito duplicado, error de digitación, etc.">{{ old('motivo') }}</textarea>
                        @error('motivo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('dashboard.contador') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="fas fa-trash-alt me-1"></i> Sí, Confirmar Anulación
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection