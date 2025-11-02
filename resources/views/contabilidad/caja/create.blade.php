@extends('layouts.app')

@section('title', 'Nuevo Movimiento de Caja')

@push('styles')
    <link href="{{ asset('css/contabilidad/caja.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-plus-circle me-2"></i>Nuevo Movimiento de Caja</h1>
        <p class="text-muted">Registrar un nuevo ingreso o egreso de efectivo.</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.caja.index') }}">Caja</a></li>
    <li class="breadcrumb-item active" aria-current="page">Nuevo</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Detalles del Movimiento</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('contador.caja.store') }}" method="POST">
                        @include('contabilidad.caja._form', [
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

