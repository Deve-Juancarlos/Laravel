@extends('layouts.app')

@section('title', 'Movimientos Diarios')
@section('page-title', 'Movimientos Bancarios Diarios')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Diario</li>
@endsection

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info">Resumen para la Fecha: {{ $fecha }}</h6>
    </div>
    <div class="card-body">
        <p class="h5">Total Ingresos del Día: <span class="text-success">S/ {{ number_format($totalesDiarios['ingresos'], 2) }}</span></p>
        <p class="h5">Total Egresos del Día: <span class="text-danger">S/ {{ number_format($totalesDiarios['egresos'], 2) }}</span></p>
        
        <p class="text-muted text-center p-3">Tabla detallada de todos los movimientos del día {{ $fecha }}.</p>
    </div>
</div>
@endsection