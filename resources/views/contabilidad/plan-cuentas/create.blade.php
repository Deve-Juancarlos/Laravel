@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Nueva Cuenta Contable')

@push('styles')
    <link href="{{ asset('css/contabilidad/plan-cuentas/create.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-plus me-2"></i>Nueva Cuenta Contable</h1>
        <p class="text-muted">Crear una nueva cuenta en el Plan Contable</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.plan-cuentas.index') }}">Plan de Cuentas</a></li>
    <li class="breadcrumb-item active" aria-current="page">Nueva Cuenta</li>
@endsection

@section('content')
<div class="plan-cuentas-view">
    <div class="card shadow-sm form-card">
        <div class="card-body">
            
            {{-- Formulario --}}
            <form action="{{ route('contador.plan-cuentas.store') }}" method="POST">
                
                {{-- Incluimos el formulario parcial --}}
                @include('contabilidad.plan-cuentas.Formulario', [
                    'cuenta' => null,
                    'cuentasPadre' => $cuentasPadre,
                    'tipos' => $tipos
                ])

            </form>

        </div>
    </div>
</div>
@endsection
