@extends('layouts.app')

@section('title', 'Registrar Pago a Proveedor - Paso 1')
@section('page-title', 'Flujo de Egreso: Registrar Pago')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.cxp.index') }}">Cuentas por Pagar</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paso 1: Registrar Pago</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <form action="{{ route('contador.flujo.egresos.handlePaso1') }}" method="POST">
            @csrf
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title m-0">Paso 1: Registrar Datos del Egreso</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Pagando a:</strong>
                        <h4 class="m-0">{{ $proveedor->RazonSocial }} ({{ $proveedor->Ruc }})</h4>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="monto_pagado" class="form-label fw-bold">Monto Total a Pagar</label>
                            <input type="number" class="form-control form-control-lg" id="monto_pagado" name="monto_pagado" 
                                   step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_pago" class="form-label fw-bold">Fecha del Pago</label>
                            <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" 
                                   value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="metodo_pago" class="form-label fw-bold">Método de Pago</label>
                            <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                <option value="transferencia">Transferencia</option>
                                <option value="cheque">Cheque</option>
                                <option value="efectivo">Efectivo (Caja)</option>
                            </select>
                        </div>
                         <div class="col-md-6">
                            <label for="cuenta_origen" class="form-label fw-bold">Cuenta de Origen (Banco)</label>
                            <select class="form-select" id="cuenta_origen" name="cuenta_origen" required>
                                @foreach ($cuentasBancarias as $cuenta)
                                    <option value="{{ $cuenta->Cuenta }}">{{ $cuenta->Banco }} - {{ $cuenta->Cuenta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="referencia" class="form-label fw-bold">N° de Operación o Cheque (Referencia)</label>
                            <input type="text" class="form-control" id="referencia" name="referencia" 
                                   placeholder="Ej: OP-123456 o Cheque 00123" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Siguiente: Aplicar Pago <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection