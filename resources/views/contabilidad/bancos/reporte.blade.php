@extends('layouts.app')

@section('title', 'Reportes de Bancos')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
    
    {{-- Estilos inline mejorados (SIN ANIMACIONES) --}}
    <style>
        /* === REPORTES DE BANCOS === */
        .reportes-bancos-view {
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
            /* SIN ANIMACIONES - ICONO ESTÁTICO */
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
            /* SIN ANIMACIONES - ICONO ESTÁTICO */
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

        .stat-card.warning .stat-icon { 
            background: linear-gradient(135deg, #ffc107, #fd7e14);
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

        /* === TABLA === */
        .table th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            color: #495057;
        }

        .table th i {
            margin-right: 0.5rem;
            color: #667eea;
        }

        .table td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
        }

        .table-hover tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        /* === BADGES === */
        .badge {
            border-radius: 20px;
            padding: 0.5rem 1rem;
            font-weight: 600;
        }

        .bg-success-soft {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }

        .bg-danger-soft {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
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

        /* === REPORTS GRID (COMO KPIs DEL DASHBOARD) === */
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .report-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border-left: 6px solid var(--accent-color);
        }

        .report-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-color), var(--accent-color-light));
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .report-card .report-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .report-card.general { --accent-color: #667eea; }
        .report-card.flujo { --accent-color: #28a745; }
        .report-card.comparativo { --accent-color: #17a2b8; }
        .report-card.conciliacion { --accent-color: #dc3545; }

        .report-card.general .report-icon { background: linear-gradient(135deg, #667eea, #764ba2); }
        .report-card.flujo .report-icon { background: linear-gradient(135deg, #28a745, #20c997); }
        .report-card.comparativo .report-icon { background: linear-gradient(135deg, #17a2b8, #6f42c1); }
        .report-card.conciliacion .report-icon { background: linear-gradient(135deg, #dc3545, #fd7e14); }

        .report-card h6 {
            font-weight: 700;
            margin-bottom: 1rem;
            color: #2c3e50;
            font-size: 1.3rem;
        }

        .report-card h6 i {
            margin-right: 0.5rem;
            color: var(--accent-color);
        }

        .report-card p {
            font-size: 1rem;
            color: #6c757d;
            margin: 0;
            line-height: 1.5;
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
            .reports-grid {
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
        }
    </style>
@endpush

@section('page-title')
    <div class="page-title-enhanced">
        <h1>
            <i class="fas fa-file-invoice me-3"></i>
            Reportes de Bancos
        </h1>
        <p>
            <i class="fas fa-chart-bar me-2"></i>
            Generación de reportes consolidados y de flujo
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
        <i class="fas fa-file-invoice me-1"></i>
        Reportes
    </li>
@endsection

@section('content')
<div class="container-fluid reportes-bancos-view">

    {{-- =========== NAVEGACIÓN DEL MÓDULO =========== --}}
    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link" href="{{ route('contador.bancos.index') }}">
            <i class="fas fa-tachometer-alt me-2"></i>
            Dashboard Bancos
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.flujo-diario') }}">
            <i class="fas fa-calendar-day me-2"></i>
            Flujo Diario
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.diario') }}">
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
        <a class="nav-link active" href="{{ route('contador.bancos.reporte') }}">
            <i class="fas fa-file-invoice me-2"></i>
            Reportes
        </a>
    </nav>
    {{-- =========== FIN NAVEGACIÓN =========== --}}

    {{-- =========== TIPOS DE REPORTES DISPONIBLES =========== --}}
    <div class="reports-grid mb-4">
        <div class="report-card general">
            <div class="report-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <h6>
                <i class="fas fa-list me-1"></i>
                Reporte General
            </h6>
            <p>Resumen completo de movimientos por cuenta bancaria con totales consolidados.</p>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Ideal para análisis generales
                </small>
            </div>
        </div>
        
        <div class="report-card flujo">
            <div class="report-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h6>
                <i class="fas fa-sort-amount-up me-1"></i>
                Reporte de Flujo
            </h6>
            <p>Análisis de los 10 principales movimientos y flujos más significativos del período.</p>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-trophy me-1"></i>
                    Top movimientos destacados
                </small>
            </div>
        </div>
        
        <div class="report-card comparativo">
            <div class="report-icon">
                <i class="fas fa-balance-scale"></i>
            </div>
            <h6>
                <i class="fas fa-exchange-alt me-1"></i>
                Reporte Comparativo
            </h6>
            <p>Comparación detallada de ingresos vs egresos con análisis de tendencias y variaciones.</p>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-chart-bar me-1"></i>
                    Análisis de rendimiento
                </small>
            </div>
        </div>
        
        <div class="report-card conciliacion">
            <div class="report-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <h6>
                <i class="fas fa-clipboard-check me-1"></i>
                Conciliación Bancaria
            </h6>
            <p>Reporte detallado para conciliación perfecta con estados de cuenta bancarios oficiales.</p>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-check-double me-1"></i>
                    Control y precisión
                </small>
            </div>
        </div>
    </div>

    {{-- =========== FILTROS =========== --}}
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.reporte') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_inicio">
                            <i class="fas fa-calendar-plus me-1"></i>
                            Fecha Inicio
                        </label>
                        <input 
                            type="date" 
                            name="fecha_inicio" 
                            id="fecha_inicio" 
                            class="form-control" 
                            value="{{ $fechaInicio }}"
                        >
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_fin">
                            <i class="fas fa-calendar-check me-1"></i>
                            Fecha Fin
                        </label>
                        <input 
                            type="date" 
                            name="fecha_fin" 
                            id="fecha_fin" 
                            class="form-control" 
                            value="{{ $fechaFin }}"
                        >
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="cuenta">
                            <i class="fas fa-university me-1"></i>
                            Cuenta (Opcional)
                        </label>
                        <select name="cuenta" id="cuenta" class="form-select">
                            <option value="">Todas las cuentas</option>
                            @foreach($listaBancos as $banco)
                                <option 
                                    value="{{ $banco->Cuenta }}" 
                                    {{ $cuentaSeleccionada == $banco->Cuenta ? 'selected' : '' }}
                                >
                                    {{ $banco->Banco }} - ({{ $banco->Cuenta }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="tipo_reporte">
                            <i class="fas fa-file-alt me-1"></i>
                            Tipo de Reporte
                        </label>
                        <select name="tipo_reporte" id="tipo_reporte" class="form-select">
                            <option value="general" {{ $tipoReporte == 'general' ? 'selected' : '' }}>
                                <i class="fas fa-file-alt me-1"></i>
                                General
                            </option>
                            <option value="flujo" {{ $tipoReporte == 'flujo' ? 'selected' : '' }}>
                                <i class="fas fa-chart-line me-1"></i>
                                Flujo (Top 10)
                            </option>
                            <option value="comparativo" {{ $tipoReporte == 'comparativo' ? 'selected' : '' }}>
                                <i class="fas fa-balance-scale me-1"></i>
                                Comparativo
                            </option>
                            <option value="conciliacion" {{ $tipoReporte == 'conciliacion' ? 'selected' : '' }}>
                                <i class="fas fa-tasks me-1"></i>
                                Conciliación
                            </option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>
                            Generar Reporte
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- =========== KPIs DEL REPORTE =========== --}}
    @if(isset($totalIngresos) && isset($totalEgresos))
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
                        S/ {{ number_format($totalIngresos, 2) }}
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
                        S/ {{ number_format($totalEgresos, 2) }}
                    </div>
                </div>
            </div>
            
            <div class="stat-card shadow-sm {{ ($totalIngresos - $totalEgresos) >= 0 ? 'info' : 'warning' }}">
                <div class="stat-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-label">
                        <i class="fas fa-chart-area me-1"></i>
                        Flujo Neto
                    </p>
                    <div class="stat-value">
                        S/ {{ number_format($totalIngresos - $totalEgresos, 2) }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- =========== CONTENEDOR DEL REPORTE =========== --}}
    <div class="card shadow-sm">
        <div class="card-header bg-primary">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar me-2"></i>
                Resultados del Reporte: {{ ucfirst($tipoReporte) }}
            </h5>
        </div>
        <div class="card-body">
            {{--
                Aquí usamos vistas parciales (sub-vistas) para mantener el código limpio.
                El service pasa los datos correctos para cada una.
            --}}
            @if($tipoReporte == 'general' || $tipoReporte == 'comparativo')
                @include('contabilidad.bancos.reportes.general', ['datos' => $porBanco, 'totales' => ['ingresos' => $totalIngresos, 'egresos' => $totalEgresos]])
            @elseif($tipoReporte == 'flujo')
                @include('contabilidad.bancos.reportes._flujo', ['datos' => $datosReporte])
            @elseif($tipoReporte == 'conciliacion')
                @include('contabilidad.bancos.reportes._conciliacion', ['datos' => $datosReporte])
            @else
                <div class="text-center p-5">
                    <i class="fas fa-file-alt fa-4x mb-3 d-block text-muted"></i>
                    <h4 class="text-muted">Seleccione un tipo de reporte válido</h4>
                    <p class="text-muted">Elija el tipo de reporte que desea generar desde el formulario de filtros.</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection