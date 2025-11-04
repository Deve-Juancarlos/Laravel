@extends('layouts.app')

@section('title', 'Flujo de Caja Diario')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
    
    {{-- Estilos inline mejorados --}}
    <style>
        /* === FLUJO DE CAJA DIARIO === */
        .flujo-diario-view {
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

        /* === PAGE TITLE MEGA ATRACTIVO === */
        .page-title-enhanced {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
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
            animation: float 3s ease-in-out infinite;
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

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(5deg); }
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
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
            --accent-color: #6c757d;
            --accent-color-light: #495057;
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
            background: linear-gradient(135deg, #6c757d, #495057);
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

        .card-header h6 {
            font-size: 1.1rem;
            margin: 0;
        }

        .card-header h6 i {
            margin-right: 0.5rem;
            color: #667eea;
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

        /* === RESPONSIVE === */
        @media (max-width: 768px) {
            .page-title-enhanced {
                padding: 1.5rem;
            }

            .page-title-enhanced h1 {
                font-size: 2rem;
            }

            .stats-grid {
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
            <i class="fas fa-calendar-day me-3"></i>
            Flujo de Caja Diario
        </h1>
        <p>
            <i class="fas fa-chart-line me-2"></i>
            Reporte de saldos diarios (Saldos Iniciales + Ingresos - Egresos = Saldos Finales)
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
        <i class="fas fa-calendar-day me-1"></i>
        Flujo Diario
    </li>
@endsection

@section('content')
<div class="container-fluid flujo-diario-view">

    {{-- =========== NAVEGACIÓN DEL MÓDULO =========== --}}
    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link" href="{{ route('contador.bancos.index') }}">
            <i class="fas fa-tachometer-alt me-2"></i>
            Dashboard Bancos
        </a>
        <a class="nav-link active" href="{{ route('contador.bancos.flujo-diario') }}">
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
        <a class="nav-link" href="{{ route('contador.bancos.reporte') }}">
            <i class="fas fa-file-invoice me-2"></i>
            Reportes
        </a>
    </nav>
    {{-- =========== FIN NAVEGACIÓN =========== --}}

    {{-- =========== FILTROS =========== --}}
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.flujo-diario') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="fecha">
                            <i class="fas fa-calendar-plus me-1"></i>
                            Fecha del Reporte
                        </label>
                        <input 
                            type="date" 
                            name="fecha" 
                            id="fecha" 
                            class="form-control" 
                            value="{{ $fecha }}"
                        >
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="banco_id">
                            <i class="fas fa-university me-1"></i>
                            Filtrar por Banco (Opcional)
                        </label>
                        <select name="banco_id" id="banco_id" class="form-select">
                            <option value="">Todos los bancos</option>
                            @foreach($listaBancos as $banco)
                                <option 
                                    value="{{ $banco->Cuenta }}" 
                                    {{ $bancoSeleccionado == $banco->Cuenta ? 'selected' : '' }}
                                >
                                    {{ $banco->Banco }} - ({{ $banco->Cuenta }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>
                            Generar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- =========== KPIs TOTALES =========== --}}
    <div class="stats-grid">
        <div class="stat-card shadow-sm primary">
            <div class="stat-icon">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="stat-info">
                <p class="stat-label">
                    <i class="fas fa-flag-checkered me-1"></i>
                    Saldo Inicial Total
                </p>
                <div class="stat-value">
                    S/ {{ number_format($totalesGenerales['saldo_inicial_total'], 2) }}
                </div>
            </div>
        </div>
        
        <div class="stat-card shadow-sm success">
            <div class="stat-icon">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stat-info">
                <p class="stat-label">
                    <i class="fas fa-coins me-1"></i>
                    Ingresos del Día
                </p>
                <div class="stat-value">
                    S/ {{ number_format($totalesGenerales['ingresos_total'], 2) }}
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
                    Egresos del Día
                </p>
                <div class="stat-value">
                    S/ {{ number_format($totalesGenerales['egresos_total'], 2) }}
                </div>
            </div>
        </div>
        
        <div class="stat-card shadow-sm info">
            <div class="stat-icon">
                <i class="fas fa-stop-circle"></i>
            </div>
            <div class="stat-info">
                <p class="stat-label">
                    <i class="fas fa-chart-area me-1"></i>
                    Saldo Final Total
                </p>
                <div class="stat-value">
                    S/ {{ number_format($totalesGenerales['saldo_final_total'], 2) }}
                </div>
            </div>
        </div>
    </div>

    {{-- =========== FLUJO POR BANCO =========== --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-chart-pie me-2"></i>
                Flujo por Banco (al {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }})
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <i class="fas fa-credit-card me-1"></i>
                                Cuenta
                            </th>
                            <th>
                                <i class="fas fa-university me-1"></i>
                                Banco
                            </th>
                            <th class="text-end">
                                <i class="fas fa-play-circle me-1 text-primary"></i>
                                Saldo Inicial
                            </th>
                            <th class="text-end">
                                <i class="fas fa-arrow-down me-1 text-success"></i>
                                Ingresos
                            </th>
                            <th class="text-end">
                                <i class="fas fa-arrow-up me-1 text-danger"></i>
                                Egresos
                            </th>
                            <th class="text-end">
                                <i class="fas fa-stop-circle me-1 text-info"></i>
                                Saldo Final
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($flujoCaja as $flujo)
                        <tr>
                            <td>
                                <strong>
                                    <i class="fas fa-hashtag me-1 text-muted"></i>
                                    {{ $flujo->Cuenta }}
                                </strong>
                            </td>
                            <td>
                                <div class="fw-medium">
                                    <i class="fas fa-building me-1 text-primary"></i>
                                    {{ $flujo->Banco }}
                                </div>
                            </td>
                            <td class="text-end fw-bold">
                                S/ {{ number_format($flujo->saldo_inicial, 2) }}
                            </td>
                            <td class="text-end text-success">
                                <strong>
                                    {{ $flujo->ingresos_dia > 0 ? 'S/ '.number_format($flujo->ingresos_dia, 2) : '-' }}
                                </strong>
                            </td>
                            <td class="text-end text-danger">
                                <strong>
                                    {{ $flujo->egresos_dia > 0 ? 'S/ '.number_format($flujo->egresos_dia, 2) : '-' }}
                                </strong>
                            </td>
                            <td class="text-end fw-bold text-info">
                                S/ {{ number_format($flujo->saldo_final, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center p-5 text-muted">
                                <i class="fas fa-chart-line fa-3x mb-3 d-block text-muted"></i>
                                <h5>No se encontraron datos de flujo</h5>
                                <p>No hay información disponible para la fecha seleccionada.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- =========== DETALLE DE MOVIMIENTOS DEL DÍA =========== --}}
    <div class="card shadow-sm">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-list-alt me-2"></i>
                Detalle de Movimientos del Día
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <i class="fas fa-university me-1"></i>
                                Banco
                            </th>
                            <th>
                                <i class="fas fa-tags me-1"></i>
                                Tipo
                            </th>
                            <th>
                                <i class="fas fa-layer-group me-1"></i>
                                Clase
                            </th>
                            <th>
                                <i class="fas fa-file-alt me-1"></i>
                                Documento
                            </th>
                            <th class="text-end">
                                <i class="fas fa-arrow-down me-1 text-success"></i>
                                Ingreso
                            </th>
                            <th class="text-end">
                                <i class="fas fa-arrow-up me-1 text-danger"></i>
                                Egreso
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                        <tr>
                            <td>
                                <div class="fw-medium">
                                    <i class="fas fa-building me-1 text-primary"></i>
                                    {{ $mov->Banco }}
                                </div>
                            </td>
                            <td>
                                @if($mov->Tipo == 1)
                                    <span class="badge bg-success-soft">
                                        <i class="fas fa-arrow-down me-1"></i>
                                        {{ $mov->tipo_descripcion }}
                                    </span>
                                @else
                                    <span class="badge bg-danger-soft">
                                        <i class="fas fa-arrow-up me-1"></i>
                                        {{ $mov->tipo_descripcion }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium">
                                    <i class="fas fa-layer-group me-1 text-muted"></i>
                                    {{ $mov->clase_descripcion }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-file-invoice me-1"></i>
                                    {{ $mov->Documento }}
                                </span>
                            </td>
                            <td class="text-end text-success">
                                <strong>
                                    {{ $mov->ingreso > 0 ? number_format($mov->ingreso, 2) : '-' }}
                                </strong>
                            </td>
                            <td class="text-end text-danger">
                                <strong>
                                    {{ $mov->egreso > 0 ? number_format($mov->egreso, 2) : '-' }}
                                </strong>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center p-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block text-muted"></i>
                                <h5>No se encontraron movimientos</h5>
                                <p>No hay movimientos registrados para esta fecha.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection