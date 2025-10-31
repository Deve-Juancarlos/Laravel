@extends('layouts.app')

@section('title', 'Detalle de Cuenta')
@section('page-title', 'Detalle de Cuenta: ' . $infoCuenta['Banco'] . ' (' . $infoCuenta['Cuenta'] . ')')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detalle</li>
@endsection

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Movimientos de la Cuenta en el Periodo</h6>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            Saldo Anterior: **S/ {{ number_format($saldoAnterior, 2) }}** |
            Total Ingresos: **S/ {{ number_format($totalesPeriodo['ingresos'], 2) }}** |
            Total Egresos: **S/ {{ number_format($totalesPeriodo['egresos'], 2) }}** |
            Saldo Final Estimado: **S/ {{ number_format($totalesPeriodo['saldo_final'], 2) }}**
        </div>
        
        <p class="text-muted text-center p-3">Aquí se mostraría la tabla detallada de movimientos de la cuenta {{ $infoCuenta['Cuenta'] }}.</p>
        
        {{ $movimientos->links() }}
    </div>
</div>
@endsection