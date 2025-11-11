@extends('layouts.app')

@section('title', 'Editar Cuenta - ' . $cuenta->codigo)

@push('styles')
    <link href="{{ asset('css/contabilidad/plan-cuentas/edit.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-edit me-2"></i>Editar Cuenta Contable</h1>
        <p class="text-muted">Modificando la cuenta: {{ $cuenta->codigo }} - {{ $cuenta->nombre }}</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item active" aria-current="page">Editar cuenta</li>
@endsection


@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.plan-cuentas.index') }}">Plan de Cuentas</a></li>
    <li class="breadcrumb-item active" aria-current="page">Editar</li>
@endsection

@section('content')
<div class="plan-cuentas-view">
    <div class="card shadow-sm form-card">
        <div class="card-body">
            
            {{-- Formulario --}}
            <form action="{{ route('contador.plan-cuentas.update', $cuenta->codigo) }}" method="POST">
                @method('PUT')
                
                {{-- Incluimos el formulario parcial --}}
                @include('contabilidad.plan-cuentas.Formulario', [
                    'cuenta' => $cuenta,
                    'cuentasPadre' => $cuentasPadre,
                    'tipos' => $tipos
                ])

            </form>

        </div>
    </div>
</div>
@endsection
