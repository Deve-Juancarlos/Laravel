@extends('layouts.app')

@section('title', 'Transferencias Bancarias')
@section('page-title', 'Listado de Transferencias Realizadas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Transferencias</li>
@endsection

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Transferencias del Periodo</h6>
        <button class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i> Registrar Transferencia</button>
    </div>
    <div class="card-body">
        <p class="text-muted">Total Transferido: **S/ {{ number_format($resumenTransferencias['total'], 2) }}**</p>
        
        <p class="text-muted text-center p-3">Tabla detallada de todas las transferencias bancarias.</p>
        
        {{ $transferencias->links() }}
    </div>
</div>
@endsection