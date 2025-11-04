@extends('layouts.app')

@section('title', 'Reporte Diario de Bancos')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
    
    {{-- Estilos inline mejorados (SIN ANIMACIONES) --}}
    <style>
        /* === REPORTE DIARIO DE BANCOS === */
        .reporte-diario-view {
            padding: 0;
        }

        /* === NAVEGACIÓN TABS MEJORADA === */
        .eerr-subnav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 1rem;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
            backdrop-filter: blur(10px);
        }

        .eerr-subnav .nav-link {
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 15px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.75rem 1.25rem;
            margin: 0 0.25rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(15px);
            position: relative;
            overflow: hidden;
        }

        .eerr-subnav .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .eerr-subnav .nav-link:hover::before {
            left: 100%;
        }

        .eerr-subnav .nav-link:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            border-color: rgba(255,255,255,0.5);
        }

        .eerr-subnav .nav-link.active {
            background: white;
            color: #667eea;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            transform: translateY(-2px);
            border-color: rgba(255,255,255,0.8);
        }

        .eerr-subnav .nav-link i {
            font-size: 1.3rem;
            margin-right: 0.5rem;
        }

        /* === PAGE TITLE MEGA ATRACTIVO (ESTÁTICO) === */
        .page-title-enhanced {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 12px 40px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
        }

        .page-title-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M20 20c0-11.046-8.954-20-20-20v20h20zm20 0c0 11.046-8.954 20-20 20v-20h20z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .page-title-enhanced h1 {
            font-size: 3.2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 4px 8px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }

        .page-title-enhanced h1 i {
            font-size: 3.8rem;
            margin-right: 1.5rem;
            text-shadow: 0 6px 12px rgba(0,0,0,0.4);
        }

        .page-title-enhanced p {
            font-size: 1.4rem;
            opacity: 0.95;
            margin: 0;
            position: relative;
            z-index: 2;
        }

        .page-title-enhanced p i {
            font-size: 1.5rem;
            margin-right: 0.75rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        /* === FILTERS CARD === */
        .filters-card {
            border: none;
            border-radius: 15px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .filters-card .form-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .filters-card .form-label i {
            margin-right: 0.5rem;
            color: #667eea;
        }

        .filters-card .form-control,
        .filters-card .form-select {
            border-radius: 10px;
            border: 2px solid transparent;
            background: white;
            transition: all 0.3s ease;
        }

        .filters-card .form-control:focus,
        .filters-card .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* === STATS GRID === */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card { 
            display: flex; 
            align-items: center; 
            background: #fff; 
            border-radius: 15px; 
            padding: 2rem; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-color), var(--accent-color-light));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .stat-card .stat-icon { 
            width: 70px; 
            height: 70px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin-right: 1.5rem; 
            font-size: 2rem; 
            color: #fff; 
            position: relative;
            z-index: 2;
        }

        .stat-card.primary {
            --accent-color: #667eea;
            --accent-color-light: #764ba2;
        }

        .stat-card.success {
            --accent-color: #28a745;
            --accent-color-light: #20c997;
        }

        .stat-card.danger {
            --accent-color: #dc3545;
            --accent-color-light: #fd7e14;
        }

        .stat-card.info {
            --accent-color: #17a2b8;
            --accent-color-light: #6f42c1;
        }

        .stat-card.primary .stat-icon { 
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .stat-card.success .stat-icon { 
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .stat-card.danger .stat-icon { 
            background: linear-gradient(135deg, #dc3545, #fd7e14);
        }

        .stat-card.info .stat-icon { 
            background: linear-gradient(135deg, #17a2b8, #6f42c1);
        }

        .stat-info .stat-label { 
            font-size: 0.95rem; 
            font-weight: 600; 
            color: #6c757d; 
            text-transform: uppercase; 
            margin-bottom: 0.5rem; 
            letter-spacing: 0.5px;
        }

        .stat-info .stat-label i {
            margin-right: 0.5rem;
            color: var(--accent-color);
        }

        .stat-info .stat-value { 
            font-size: 1.8rem; 
            font-weight: 800; 
            color: #2c3e50;
        }

        /* === BANCOS CARDS GRID === */
        .bancos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .banco-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border-left: 6px solid var(--banco-color, #667eea);
        }

        .banco-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--banco-color, #667eea), var(--banco-color-light, #764ba2));
        }

        .banco-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .banco-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .banco-logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--banco-color, #667eea), var(--banco-color-light, #764ba2));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
            margin-right: 1rem;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        .banco-info h5 {
            margin: 0;
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.3rem;
        }

        .banco-info .banco-currency {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .banco-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .banco-stat {
            text-align: center;
            padding: 1rem;
            border-radius: 10px;
            background: #f8f9fa;
        }

        .banco-stat-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .banco-stat-value {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .banco-stat.ingresos .banco-stat-value {
            color: #28a745;
        }

        .banco-stat.egresos .banco-stat-value {
            color: #dc3545;
        }

        .banco-stat.neto .banco-stat-value {
            color: #667eea;
        }

        .banco-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }

        .movimientos-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .banco-acciones {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            border-radius: 8px;
        }

        /* === CARDS GENERALES === */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: none;
            border-radius: 15px 15px 0 0;
            font-weight: 600;
            color: #2c3e50;
        }

        .card-header h5,
        .card-header h6 {
            font-size: 1.1rem;
            margin: 0;
        }

        .card-header h5 i,
        .card-header h6 i {
            margin-right: 0.5rem;
            color: #667eea;
        }

        .card-header.bg-primary h5,
        .card-header.bg-primary h6 {
            color: white;
        }

        .card-header.bg-primary i {
            color: white;
        }

        /* === BUTTONS === */
        .btn {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-primary i {
            margin-right: 0.5rem;
        }

        .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-color: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        /* === TOTALES CARD === */
        .totales-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }

        .totales-card .card-body {
            padding: 2rem;
        }

        .totales-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .total-item {
            text-align: center;
            padding: 1.5rem;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .total-item h6 {
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }

        .total-item .value {
            font-size: 2rem;
            font-weight: 800;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        /* === RESPONSIVE === */
        @media (max-width: 768px) {
            .page-title-enhanced {
                padding: 1.5rem;
            }

            .page-title-enhanced h1 {
                font-size: 2rem;
            }

            .stats-grid,
            .bancos-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .stat-card .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
                margin-right: 1rem;
            }

            .stat-info .stat-value {
                font-size: 1.5rem;
            }

            .eerr-subnav {
                flex-direction: column;
            }

            .eerr-subnav .nav-link {
                margin: 0.25rem 0;
                text-align: center;
            }

            .banco-stats {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .banco-footer {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
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
<div class="container-fluid reporte-diario-view">

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