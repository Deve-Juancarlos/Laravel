@extends('layouts.app')

@section('title', 'Estado de Resultados')

@push('styles')
    <link href="{{ asset('css/contabilidad/estado-resultados/index.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-chart-line me-2"></i>Estado de Resultados</h1>
        <p class="text-muted">Análisis de Ganancias y Pérdidas (Utilidad Neta)</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item active" aria-current="page">Estado de Resultados</li>
@endsection

@section('content')
<div class="estadosfinancieros-container">

    
    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link active" href="{{ route('contador.estado-resultados.index') }}">
            <i class="fas fa-chart-line me-1"></i> Estado de Resultados
        </a>
        <a class="nav-link" href="{{ route('contador.estado-resultados.periodos') }}">
            <i class="fas fa-chart-bar me-1"></i> Resultados por Períodos
        </a>
        <a class="nav-link" href="{{ route('contador.estado-resultados.comparativo') }}">
            <i class="fas fa-exchange-alt me-1"></i> Comparativo EERR
        </a>
        <a class="nav-link" href="{{ route('contador.estado-resultados.balance-general') }}">
            <i class="fas fa-balance-scale-right me-1"></i> Balance General
        </a>
        
    </nav>
  

   
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.estado-resultados.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="fecha_fin">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Generar Reporte
                        </button>
                        <a href="{{ route('contador.estado-resultados.exportar', request()->query()) }}" class="btn btn-success" title="Exportar">
                            <i class="fas fa-file-excel"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reporte Principal -->
    <div class="row">
        <!-- Columna Izquierda: Reporte Estructurado -->
        <div class="col-lg-8">
            <div class="card shadow-sm er-card">
                <div class="card-header">
                    <h5 class="mb-0">Reporte del Período</h5>
                    <span class="text-muted">{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <!-- Ventas Netas -->
                        <li class="list-group-item er-line-item">
                            <span>Ventas Netas (Facturación)</span>
                            <span class="fw-bold">S/ {{ number_format($resultados['ventas_netas'], 2) }}</span>
                        </li>
                        <!-- Costo de Ventas -->
                        <li class="list-group-item er-line-item">
                            <span>(-) Costo de Ventas (Facturación)</span>
                            <span class="fw-bold text-danger">(S/ {{ number_format($resultados['costo_ventas'], 2) }})</span>
                        </li>
                        <!-- Utilidad Bruta -->
                        <li class="list-group-item er-total">
                            <span>UTILIDAD BRUTA</span>
                            <span class="fw-bold">S/ {{ number_format($resultados['utilidad_bruta'], 2) }}</span>
                        </li>
                        
                        <!-- Gastos Operativos -->
                        <li class="list-group-item er-line-item">
                            <span>(-) Gastos Operativos (Clase 6 y 9)</span>
                            <span class="fw-bold text-danger">(S/ {{ number_format($resultados['gastos_operativos'], 2) }})</span>
                        </li>
                        <!-- Utilidad Operativa -->
                        <li class="list-group-item er-total">
                            <span>UTILIDAD OPERATIVA</span>
                            <span class="fw-bold">S/ {{ number_format($resultados['utilidad_operativa'], 2) }}</span>
                        </li>
                        
                        <!-- (Espacio para Otros Ingresos/Gastos e Impuestos) -->
                        
                        <!-- Utilidad Neta -->
                        <li class="list-group-item er-grand-total">
                            <span>UTILIDAD NETA DEL EJERCICIO</span>
                            <span class="fw-bold">S/ {{ number_format($resultados['utilidad_neta'], 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Detalle de Cuentas -->
            <div class="row mt-4">
                <!-- Ingresos -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success-soft">
                            <h6 class="mb-0 text-success"><i class="fas fa-plus-circle me-2"></i>Detalle de Ingresos (Clase 7)</h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($ingresos as $cuenta)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <small>{{ $cuenta->descripcion }}</small>
                                    <span class="badge bg-success-soft text-success">S/ {{ number_format($cuenta->total, 2) }}</span>
                                </li>
                                @empty
                                <li class="list-group-item text-muted text-center"><small>No hay ingresos en Clase 7</small></li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Gastos -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-danger-soft">
                            <h6 class="mb-0 text-danger"><i class="fas fa-minus-circle me-2"></i>Detalle de Gastos (Clase 6 y 9)</h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                                @forelse($gastos as $cuenta)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <small>{{ $cuenta->descripcion }} ({{ $cuenta->cuenta_contable }})</small>
                                    <span class="badge bg-danger-soft text-danger">S/ {{ number_format($cuenta->total, 2) }}</span>
                                </li>
                                @empty
                                <li class="list-group-item text-muted text-center"><small>No hay gastos en Clase 6/9</small></li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: KPIs y Comparativa -->
        <div class="col-lg-4">
            <!-- Márgenes -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Análisis de Márgenes</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Margen Bruto</span>
                            <span class="fw-bold">{{ $resultados['margen_bruto'] }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $resultados['margen_bruto'] }}%" aria-valuenow="{{ $resultados['margen_bruto'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Margen Operativo</span>
                            <span class="fw-bold">{{ $resultados['margen_operativo'] }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $resultados['margen_operativo'] }}%" aria-valuenow="{{ $resultados['margen_operativo'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Margen Neto</span>
                            <span class="fw-bold">{{ $resultados['margen_neto'] }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $resultados['margen_neto'] }}%" aria-valuenow="{{ $resultados['margen_neto'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparativa -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Comparación (vs Mes Anterior)</h6>
                </div>
                <div class="card-body">
                    <div class="comp-item">
                        <small class="text-muted">Ventas Netas</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="comp-value">S/ {{ number_format($comparacion['ventas_actual'], 2) }}</span>
                            @if($comparacion['ventas_variacion'] >= 0)
                                <span class="badge bg-success-soft text-success"><i class="fas fa-arrow-up me-1"></i>{{ number_format($comparacion['ventas_variacion'], 2) }}%</span>
                            @else
                                <span class="badge bg-danger-soft text-danger"><i class="fas fa-arrow-down me-1"></i>{{ number_format($comparacion['ventas_variacion'], 2) }}%</span>
                            @endif
                        </div>
                        <small class="text-muted">Anterior: S/ {{ number_format($comparacion['ventas_anterior'], 2) }}</small>
                    </div>
                    <hr>
                    <div class="comp-item">
                        <small class="text-muted">Costo de Ventas</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="comp-value">S/ {{ number_format($comparacion['costos_actual'], 2) }}</span>
                            @if($comparacion['costos_variacion'] >= 0)
                                <span class="badge bg-danger-soft text-danger"><i class="fas fa-arrow-up me-1"></i>{{ number_format($comparacion['costos_variacion'], 2) }}%</span>
                            @else
                                <span class="badge bg-success-soft text-success"><i class="fas fa-arrow-down me-1"></i>{{ number_format($comparacion['costos_variacion'], 2) }}%</span>
                            @endif
                        </div>
                        <small class="text-muted">Anterior: S/ {{ number_format($comparacion['costos_anterior'], 2) }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

