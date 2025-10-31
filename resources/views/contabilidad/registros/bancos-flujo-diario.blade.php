@extends('layouts.app')

@section('title', 'Flujo de Caja Diario')
@section('page-title', 'Flujo de Caja Diario Estimado')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Flujo</li>
@endsection

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-chart-line me-2"></i>Proyección de Flujo ({{ $fecha }})</h6>
    </div>
    <div class="card-body">
        <p class="text-muted">Total de Saldos Finales del Día: **S/ {{ number_format($totalesGenerales['saldo_final'], 2) }}**</p>
        
        <p class="text-muted text-center p-3">Tabla detallada de saldos iniciales, proyecciones de cobros/pagos y saldos finales por cada cuenta.</p>
    </div>
</div>
@endsection