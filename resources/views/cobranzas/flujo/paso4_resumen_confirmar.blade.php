@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Registrar Cobranza - Paso 4')

@section('page-title', 'Asistente de Registro de Cobranzas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.flujo.cobranzas.paso1') }}">Paso 1</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.flujo.cobranzas.paso2') }}">Paso 2</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.flujo.cobranzas.paso3') }}">Paso 3</a></li>
    <li class="breadcrumb-item active" aria-current="page"> Confirmar Pago</li>
@endsection

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        
        @include('cobranzas.flujo.partials._wizard_steps', ['paso_actual' => 4])

        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-check-circle me-2 text-primary"></i>
                    Paso 4: Resumen y Confirmación
                </h5>
            </div>
            
            {{-- Este formulario final apunta al método que PROCESA Y GUARDA TODO --}}
            <form action="{{ route('contador.flujo.cobranzas.procesar') }}" method="POST">
                @csrf
                {{-- Usamos un <input hidden> por si el usuario presiona "Enter" --}}
                <input type="hidden" name="confirmado" value="1">

                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle me-1"></i>
                        Por favor, revisa que toda la información sea correcta antes de guardar.
                    </div>

                    <h6 class="text-primary">1. Resumen del Pago</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Cliente:</strong>
                            <p class="text-muted">{{ $resumen['cliente']->Razon }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>RUC:</strong>
                            <p class="text-muted">{{ $resumen['cliente']->Documento }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Monto Total Pagado:</strong>
                            <h5 class="text-success">S/ {{ number_format($resumen['pago']['monto_pagado'], 2) }}</h5>
                        </div>
                        <div class="col-md-4">
                            <strong>Fecha de Pago:</strong>
                            <p class="text-muted">{{ Carbon\Carbon::parse($resumen['pago']['fecha_pago'])->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Método:</strong>
                            <p class="text-muted">{{ $resumen['pago']['metodo_pago'] }}</p>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-primary">2. Aplicación a Facturas</h6>
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Factura</th>
                                <th class="text-end">Saldo Anterior</th>
                                <th class="text-end">Monto Aplicado</th>
                                <th class="text-end">Nuevo Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resumen['aplicaciones'] as $app)
                                <tr>
                                    <td>{{ $app['factura']->Numero }}</td>
                                    <td class="text-end">S/ {{ number_format($app['factura']->Saldo, 2) }}</td>
                                    <td class="text-end text-success fw-bold">S/ {{ number_format($app['monto_aplicado'], 2) }}</td>
                                    <td class="text-end text-danger">S/ {{ number_format($app['factura']->Saldo - $app['monto_aplicado'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No se aplicó el pago a ninguna factura específica.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    @if($resumen['adelanto'] > 0)
                    <div class="alert alert-warning mt-3">
                        <strong>Pago a Cuenta (Adelanto):</strong>
                        <p class="mb-0">Se registrará un saldo a favor (adelanto) para el cliente por un monto de 
                           <strong class="h5">S/ {{ number_format($resumen['adelanto'], 2) }}</strong>.
                        </p>
                    </div>
                    @endif

                </div>
                
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('contador.flujo.cobranzas.paso3') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Atrás (Editar Aplicación)
                    </a>
                    <button 
                        type="submit" 
                        class="btn btn-success btn-lg"
                        onclick="this.disabled=true; this.innerText='Guardando...'; this.form.submit();" >
                        <i class="fas fa-save me-1"></i> Confirmar y Guardar Cobranza
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection