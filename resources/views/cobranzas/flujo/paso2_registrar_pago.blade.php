@extends('layouts.app')

@section('title', 'Registrar Cobranza - Paso 2')
@section('page-title', 'Asistente de Registro de Cobranzas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.flujo.cobranzas.paso1') }}">Paso 1</a></li>
    <li class="breadcrumb-item active" aria-current="page">Registrar Pago</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        
        {{-- Aquí puedes incluir el partial de pasos si lo creaste --}}
        {{-- @include('cobranzas.flujo.partials._wizard_steps', ['paso_actual' => 2]) --}}

        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-dollar-sign me-2 text-primary"></i>
                    Paso 2: Registrar el Ingreso del Pago
                </h5>
            </div>
            
            <form action="{{ route('contador.flujo.cobranzas.paso2') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Cliente:</strong> {{ $cliente->Razon }} (RUC: {{ $cliente->Documento }})
                    </div>
                    
                    <h6 class="text-primary">Datos del Pago</h6>
                    <hr class="mt-0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="monto_pagado" class="form-label fw-bold">Monto Total Recibido (S/)</label>
                            <input type="number" class="form-control form-control-lg" id="monto_pagado" name="monto_pagado" step="0.01" min="0.01" value="{{ old('monto_pagado', $pago['monto_pagado'] ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_pago" class="form-label fw-bold">Fecha del Pago</label>
                            <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', $pago['fecha_pago'] ?? now()->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="metodo_pago" class="form-label">Método de Pago</label>
                            <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                <option value="">Seleccione...</option>
                                <option value="transferencia" @selected(old('metodo_pago', $pago['metodo_pago'] ?? '') == 'transferencia')>Transferencia Bancaria</option>
                                <option value="deposito" @selected(old('metodo_pago', $pago['metodo_pago'] ?? '') == 'deposito')>Depósito en Cuenta</option>
                                <option value="efectivo" @selected(old('metodo_pago', $pago['metodo_pago'] ?? '') == 'efectivo')>Efectivo</option>
                                <option value="cheque" @selected(old('metodo_pago', $pago['metodo_pago'] ?? '') == 'cheque')>Cheque</option>
                                <option value="yape_plin" @selected(old('metodo_pago', $pago['metodo_pago'] ?? '') == 'yape_plin')>Yape/Plin</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cuenta_destino" class="form-label">Cuenta/Caja Destino</label>
                            <select class="form-select" id="cuenta_destino" name="cuenta_destino" required>
                                <option value="">Seleccione...</option>
                                {{-- ¡CORREGIDO! Usa 'Bancos.Cuenta' como el value --}}
                                @foreach($cuentasBancarias as $cuenta)
                                    <option value="{{ $cuenta->Cuenta }}" @selected(old('cuenta_destino', $pago['cuenta_destino'] ?? '') == $cuenta->Cuenta)>
                                        {{ $cuenta->Banco }} ({{ $cuenta->Cuenta }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="referencia" class="form-label">N° de Referencia / N° Cheque</label>
                        <input type="text" class="form-control" id="referencia" name="referencia" value="{{ old('referencia', $pago['referencia'] ?? '') }}" placeholder="Ej: N° Op. 123456, Cheque 001, etc.">
                    </div>

                    <h6 class="text-primary mt-4">Datos de la Planilla de Cobranza</h6>
                    <hr class="mt-0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                             <label for="serie_planilla" class="form-label">Serie de Planilla</label>
                             <select class="form-select" id="serie_planilla" name="serie_planilla" required>
                                {{-- El controlador pasa $seriesPlanilla --}}
                                @foreach($seriesPlanilla as $serie)
                                     <option value="{{ $serie }}" @selected(old('serie_planilla', $pago['serie_planilla'] ?? '') == $serie)>{{ $serie }}</option>
                                @endforeach
                             </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="vendedor_id" class="form-label">Cobrador / Vendedor</label>
                            <select class="form-select" id="vendedor_id" name="vendedor_id" required>
                                <option value="">Seleccione...</option>
                                {{-- El controlador pasa $vendedores --}}
                                @foreach($vendedores as $vendedor)
                                    <option value="{{ $vendedor->Codemp }}" @selected(old('vendedor_id', $pago['vendedor_id'] ?? '') == $vendedor->Codemp)>
                                        {{ $vendedor->Nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
                
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('contador.flujo.cobranzas.paso1') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Atrás
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Siguiente (Aplicar Pago) <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection