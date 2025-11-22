@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Editar Movimiento de Caja')
@section('page-title')
    <div>
        <h1><i class="fas fa-edit me-2"></i>Editar Movimiento de Caja</h1>
        <p class="text-muted">Movimiento #{{ $movimiento->Numero }}</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.caja.index') }}">Caja</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.caja.show', $movimiento->Numero) }}">#{{ $movimiento->Numero }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Editar</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Editando Movimiento #{{ $movimiento->Numero }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('contador.caja.update', $movimiento->Numero) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        {{-- Aquí incluimos tu formulario --}}
                        @include('contabilidad.caja._form')
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection