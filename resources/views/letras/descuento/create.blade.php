@use('Illuminate\Support\Str')
@extends('layouts.app')
@section('title', 'Crear Planilla - Paso 1')
@section('page-title', 'Nueva Planilla de Descuento')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.letras_descuento.index') }}">Planillas</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paso 1: Crear</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title m-0"><i class="fas fa-file-invoice me-2"></i>Datos de la Planilla</h5>
            </div>
            <form action="{{ route('contador.letras_descuento.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="mb-3">
                        <label for="Serie" class="form-label fw-bold">Serie <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="Serie" name="Serie" value="PL01" required>
                    </div>
                    <div class="mb-3">
                        <label for="CodBanco" class="form-label fw-bold">Banco de Descuento <span class="text-danger">*</span></label>
                        <select class="form-select" name="CodBanco" required>
                            <option value="">Seleccione un banco...</option>
                            @foreach($bancos as $banco)
                                <option value="{{ $banco->Cuenta }}">{{ $banco->Banco }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="Fecha" class="form-label fw-bold">Fecha Planilla <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="Fecha" name="Fecha" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        Siguiente (Añadir Letras) <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection