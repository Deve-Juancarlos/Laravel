@extends('layouts.app')

@section('title', 'Editar Movimiento de Caja')

@push('styles')
    <link href="{{ asset('css/contabilidad/caja.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-edit me-2"></i>Editar Movimiento de Caja</h1>
        <p class="text-muted">Documento: {{ $movimiento->Documento ?? 'N/A' }} | N° {{ $movimiento->Numero }}</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.caja.index') }}">Caja</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.caja.show', $movimiento->Numero) }}">{{ $movimiento->Numero }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Editar</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">

            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Atención:</strong> Por integridad contable, solo se permite la modificación de la fecha, glosa y documento de referencia. Para cambiar montos o cuentas, debe anular este movimiento y crear uno nuevo.
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Detalles del Movimiento (Solo Edición)</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('contador.caja.update', $movimiento->Numero) }}" method="POST">
                        @method('PUT')
                        @include('contabilidad.caja._form', [
                            'movimiento' => $movimiento,
                            'asiento' => $asiento,
                            'detalles' => $detalles,
                            'cuentasCaja' => $cuentasCaja,
                            'cuentasContrapartida' => $cuentasContrapartida,
                            'tiposMovimiento' => $tiposMovimiento,
                            'clasesOperacion' => $clasesOperacion
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

