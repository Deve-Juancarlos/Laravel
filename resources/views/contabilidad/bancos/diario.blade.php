@extends('layouts.app')

@section('title', 'Reporte Diario de Bancos')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos/diario.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div class="page-title-enhanced">
        <h1>
            <i class="fas fa-calendar-alt me-3"></i>
            Reporte Diario de Bancos
        </h1>
        <p>
            <i class="fas fa-chart-bar me-2"></i>
            Movimientos consolidados para la fecha: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
        </p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.contador') }}">
            <i class="fas fa-calculator me-1"></i>
            Contabilidad
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('contador.bancos.index') }}">
            <i class="fas fa-university me-1"></i>
            Bancos
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-calendar-alt me-1"></i>
        Reporte Diario
    </li>
@endsection

@section('content')
<div class="diario-view">

    {{-- =========== NAVEGACIÓN DEL MÓDULO (CORREGIDA) =========== --}}
    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link" href="{{ route('contador.bancos.index') }}">
            <i class="fas fa-tachometer-alt me-2"></i>
            Dashboard Bancos
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.flujo-diario') }}">
            <i class="fas fa-calendar-day me-2"></i>
            Flujo Diario
        </a>
        <a class="nav-link active" href="{{ route('contador.bancos.diario') }}">
            <i class="fas fa-calendar-alt me-2"></i>
            Reporte Diario
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.mensual') }}">
            <i class="fas fa-calendar-week me-2"></i>
            Resumen Mensual
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.conciliacion') }}">
            <i class="fas fa-tasks me-2"></i>
            Conciliación
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.transferencias') }}">
            <i class="fas fa-exchange-alt me-2"></i>
            Transferencias
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.reporte') }}">
            <i class="fas fa-file-invoice me-2"></i>
            Reportes
        </a>
    </nav>
    {{-- =========== FIN NAVEGACIÓN =========== --}}

    {{-- =========== FILTROS =========== --}}
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.diario') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-10">
                        <label class="form-label" for="fecha">
                            <i class="fas fa-calendar-plus me-1"></i>
                            Seleccionar Fecha
                        </label>
                        <input type="date" name="fecha" id="fecha" class="form-control" value="{{ $fecha }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Ver Día
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- =========== KPIs DEL DÍA =========== --}}
    <div class="stats-grid mb-4">
        <div class="stat-card shadow-sm success">
            <div class="stat-icon">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stat-info">
                <p class="stat-label">
                    <i class="fas fa-coins me-1"></i>
                    Total Ingresos
                </p>
                <div class="stat-value">
                    S/ {{ number_format($totalesDiarios['total_ingresos'], 2) }}
                </div>
            </div>
        </div>
        
        <div class="stat-card shadow-sm danger">
            <div class="stat-icon">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="stat-info">
                <p class="stat-label">
                    <i class="fas fa-money-bill-wave me-1"></i>
                    Total Egresos
                </p>
                <div class="stat-value">
                    S/ {{ number_format($totalesDiarios['total_egresos'], 2) }}
                </div>
            </div>
        </div>
        
        <div class="stat-card shadow-sm {{ ($totalesDiarios['total_ingresos'] - $totalesDiarios['total_egresos']) >= 0 ? 'info' : 'warning' }}">
            <div class="stat-icon">
                <i class="fas fa-balance-scale"></i>
            </div>
            <div class="stat-info">
                <p class="stat-label">
                    <i class="fas fa-chart-area me-1"></i>
                    Flujo Neto
                </p>
                <div class="stat-value">
                    S/ {{ number_format($totalesDiarios['total_ingresos'] - $totalesDiarios['total_egresos'], 2) }}
                </div>
            </div>
        </div>
        
        <div class="stat-card shadow-sm primary">
            <div class="stat-icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="stat-info">
                <p class="stat-label">
                    <i class="fas fa-list me-1"></i>
                    Total Movimientos
                </p>
                <div class="stat-value">
                    {{ $totalesDiarios['total_movimientos'] }}
                </div>
            </div>
        </div>
    </div>
    
    {{-- =========== BANCOS EN CARDS (MEJORADO) =========== --}}
    <div class="bancos-grid mb-4">
        @forelse($resumenPorBanco as $index => $resumen)
        @php
            // Colores únicos para cada banco
            $colores = [
                ['#667eea', '#764ba2'], // Azul-púrpura
                ['#28a745', '#20c997'], // Verde
                ['#17a2b8', '#6f42c1'], // Cyan-púrpura
                ['#fd7e14', '#dc3545'], // Naranja-rojo
                ['#6f42c1', '#667eea'], // Púrpura-azul
                ['#20c997', '#28a745']  // Verde claro
            ];
            $coloresBanco = $colores[$index % count($colores)];
        @endphp
        <div class="banco-card" style="--banco-color: {{ $coloresBanco[0] }}; --banco-color-light: {{ $coloresBanco[1] }};">
            <div class="banco-header">
                <div class="banco-logo">
                    {{ substr($resumen->Banco, 0, 2) }}
                </div>
                <div class="banco-info">
                    <h5>{{ $resumen->Banco }}</h5>
                    <div class="banco-currency">
                        <i class="fas fa-coins me-1"></i>
                        Moneda: {{ $resumen->Moneda == 1 ? 'SOLES' : 'DÓLARES' }}
                    </div>
                </div>
            </div>
            
            <div class="banco-stats">
                <div class="banco-stat ingresos">
                    <div class="banco-stat-label">
                        <i class="fas fa-arrow-down me-1"></i>
                        Ingresos
                    </div>
                    <div class="banco-stat-value">
                        S/ {{ number_format($resumen->total_ingresos, 2) }}
                    </div>
                </div>
                
                <div class="banco-stat egresos">
                    <div class="banco-stat-label">
                        <i class="fas fa-arrow-up me-1"></i>
                        Egresos
                    </div>
                    <div class="banco-stat-value">
                        S/ {{ number_format($resumen->total_egresos, 2) }}
                    </div>
                </div>
                
                <div class="banco-stat neto">
                    <div class="banco-stat-label">
                        <i class="fas fa-balance-scale me-1"></i>
                        Neto
                    </div>
                    <div class="banco-stat-value">
                        S/ {{ number_format($resumen->total_ingresos - $resumen->total_egresos, 2) }}
                    </div>
                </div>
                
                <div class="banco-stat movimientos">
                    <div class="banco-stat-label">
                        <i class="fas fa-list me-1"></i>
                        Movimientos
                    </div>
                    <div class="banco-stat-value">
                        {{ $resumen->total_movimientos }}
                    </div>
                </div>
            </div>
            
            <div class="banco-footer">
                <div class="movimientos-badge">
                    <i class="fas fa-exchange-alt me-1"></i>
                    {{ $resumen->total_movimientos }} movimientos
                </div>
                <div class="banco-acciones">
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i>
                        Ver Detalles
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    <i class="fas fa-inbox fa-4x mb-3 d-block text-muted"></i>
                    <h4 class="text-muted">No hay datos</h4>
                    <p class="text-muted">No se encontraron movimientos para la fecha seleccionada.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- =========== TOTALES DEL DÍA =========== --}}
    <div class="card shadow-sm totales-card">
        <div class="card-body">
            <h4 class="text-center mb-4">
                <i class="fas fa-calculator me-2"></i>
                TOTALES CONSOLIDADOS DEL DÍA
            </h4>
            <div class="totales-row">
                <div class="total-item">
                    <h6>
                        <i class="fas fa-arrow-down me-1"></i>
                        Total Ingresos
                    </h6>
                    <div class="value">
                        S/ {{ number_format($totalesDiarios['total_ingresos'], 2) }}
                    </div>
                </div>
                
                <div class="total-item">
                    <h6>
                        <i class="fas fa-arrow-up me-1"></i>
                        Total Egresos
                    </h6>
                    <div class="value">
                        S/ {{ number_format($totalesDiarios['total_egresos'], 2) }}
                    </div>
                </div>
                
                <div class="total-item">
                    <h6>
                        <i class="fas fa-balance-scale me-1"></i>
                        Flujo Neto
                    </h6>
                    <div class="value">
                        S/ {{ number_format($totalesDiarios['total_ingresos'] - $totalesDiarios['total_egresos'], 2) }}
                    </div>
                </div>
                
                <div class="total-item">
                    <h6>
                        <i class="fas fa-list me-1"></i>
                        Total Movimientos
                    </h6>
                    <div class="value">
                        {{ $totalesDiarios['total_movimientos'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection