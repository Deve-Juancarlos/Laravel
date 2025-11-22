@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Crear Asiento Manual - Caja')

@push('styles')
    {{-- Reutilizamos los mismos estilos del formulario --}}
    <link href="{{ asset('css/contabilidad/caja.css') }}" rel="stylesheet">
    <link href="{{ asset('css/contabilidad/asiento-form.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-pencil-alt me-2"></i>Crear Asiento Manual (Avanzado)</h1>
        <p class="text-muted">Registro de un movimiento de caja con contrapartida contable manual.</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.caja.index') }}">Caja</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.caja.create') }}">Nuevo</a></li>
    <li class="breadcrumb-item active" aria-current="page">Avanzado</li>
@endsection

@section('content')

    {{-- Alertas --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Error al validar los datos:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            {{--
                Aquí reutilizamos el mismo formulario que usa 'edit',
                pasándole todas las variables que espera (cuentasCaja, cuentasContrapartida, etc.)
                que ya nos pasa el controlador 'createAvanzado'.
            --}}
            <form action="{{ route('contador.caja.store') }}" method="POST">
                @include('contabilidad.caja._form')
            </form>
        </div>
    </div>
@endsection

