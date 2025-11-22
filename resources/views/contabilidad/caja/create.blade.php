@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Nuevo Movimiento de Caja')
@section('page-title')
    <div>
        <h1><i class="fas fa-plus-circle me-2"></i>Nuevo Movimiento de Caja</h1>
        <p class="text-muted">Registrar un nuevo ingreso o egreso de caja chica.</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.caja.index') }}">Caja</a></li>
    <li class="breadcrumb-item active" aria-current="page">Crear</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Detalles del Movimiento</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('contador.caja.store') }}" method="POST">
                        @csrf
                        
                        {{-- Aquí incluimos tu formulario --}}
                        @include('contabilidad.caja._form')
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection