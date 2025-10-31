@extends('layouts.app')

@section('title', 'Generador de Reportes')
@section('page-title', 'Generador de Reportes Bancarios')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Reportes</li>
@endsection

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-file-pdf me-2"></i>Configuración de Reporte</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('contabilidad.bancos.generar') }}" method="GET">
            <p class="text-muted">Formulario para seleccionar el tipo de reporte (Ej: Analítico, Resumen, Flujo).</p>
            <button type="submit" class="btn btn-danger"><i class="fas fa-print me-2"></i>Generar</button>
        </form>
    </div>
</div>

@isset($datosReporte)
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Resultado del Reporte: {{ $tipoReporte }} ({{ $fechaInicio }} a {{ $fechaFin }})</h6>
    </div>
    <div class="card-body">
        <p class="text-muted">Contenido del reporte...</p>
    </div>
</div>
@endisset
@endsection