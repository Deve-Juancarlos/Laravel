@extends('layouts.app')

@section('title', 'Registrar Pago a Proveedor - Paso 3')
@section('page-title', 'Flujo de Egreso: Resumen y Confirmación')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.cxp.index') }}">Cuentas por Pagar</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paso 3: Confirmar</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <form action="{{ route('contador.flujo.egresos.procesar') }}" method="POST">
            @csrf
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title m-0">Paso 3: Resumen y Confirmación</h5>
                </div>
                <div class="card-body">
                    
                    <div class="alert alert-warning">
                        <h4 class="alert-heading">¡Atención!</h4>
                        <p>Está a punto de registrar un egreso. Esta acción es definitiva y generará los asientos contables correspondientes.</p>
                        <hr>
                        <p class="mb-0">Revise que todos los datos sean correctos antes de confirmar.</p>
                    </div>

                    <div class="row">
                        {{-- Resumen del Proveedor --}}
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">PROVEEDOR</h6>
                                    <h5>{{ $resumen['proveedor']->RazonSocial }}</h5>
                                    <small>{{ $resumen['proveedor']->Ruc }}</small>
                                </div>
                            </div>
                        </div>

                        {{-- Resumen del Pago --}}
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">DATOS DEL PAGO</h6>
                                    <h5>S/ {{ number_format($resumen['pago']['monto_pagado'], 2) }}</h5>
                                    <small>
                                        Fecha: {{ \Carbon\Carbon::parse($resumen['pago']['fecha_pago'])->format('d/m/Y') }}<br>
                                        Referencia: {{ $resumen['pago']['referencia'] }}<br>
                                        Cuenta: {{ $resumen['pago']['cuenta_origen'] }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Facturas Aplicadas --}}
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="m-0">Facturas que se van a pagar</h6>
                        </div>
                        <ul class="list-group list-group-flush">
                            @foreach($resumen['aplicaciones'] as $app)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Factura: {{ $app['factura']->Documento }}</strong><br>
                                    <small>Saldo Total: S/ {{ number_format($app['factura']->Saldo, 2) }}</small>
                                </div>
                                <span class="badge bg-primary rounded-pill fs-6">
                                    Se aplica: S/ {{ number_format($app['monto_aplicado'], 2) }}
                                </span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    <div class="alert alert-danger text-center fs-5 fw-bold mt-3">
                        Total Aplicado: S/ {{ number_format($resumen['pago']['monto_aplicado'], 2) }}
                        @if(abs($resumen['pago']['diferencia']) > 0.01)
                            <br><small>(Diferencia: S/ {{ number_format($resumen['pago']['diferencia'], 2) }})</small>
                        @endif
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('contador.flujo.egresos.paso2') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver (Paso 2)
                    </a>
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="fas fa-check-circle me-2"></i>
                        Confirmar y Registrar Egreso
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection