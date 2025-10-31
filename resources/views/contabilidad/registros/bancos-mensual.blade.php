@extends('layouts.app')

@section('title', 'Resumen Mensual')
@section('page-title', 'Resumen Bancario: ' . $meses[$mes] . ' de ' . $anio)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Mensual</li>
@endsection

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-success">Consolidado por Banco</h6>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            Total Ingresos Consolidados: **S/ {{ number_format($totalesMes['ingresos'], 2) }}** |
            Total Egresos Consolidados: **S/ {{ number_format($totalesMes['egresos'], 2) }}**
        </div>
        
        <p class="text-muted text-center p-3">Tabla resumen con saldos iniciales, movimientos y saldos finales por cada banco.</p>
    </div>
</div>
@endsection