@extends('layouts.app')

@section('title', 'Conciliación Bancaria')
@section('page-title', 'Herramienta de Conciliación Bancaria')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Conciliación</li>
@endsection

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-balance-scale me-2"></i>Realizar Nueva Conciliación</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('contabilidad.bancos.conciliar') }}" method="POST">
            @csrf
            <p class="text-muted">Formulario para inicio de conciliación...</p>
            <button type="submit" class="btn btn-warning"><i class="fas fa-sync me-2"></i>Iniciar Conciliación</button>
        </form>
        
        @isset($ultimaConciliacion)
        <hr>
        <h6>Última Conciliación ({{ $ultimaConciliacion->fecha_corte }}):</h6>
        <div class="alert alert-{{ abs($diferencias) < 0.01 ? 'success' : 'danger' }}">
            Diferencia: **S/ {{ number_format($diferencias, 2) }}**
        </div>
        @endisset
    </div>
</div>
@endsection