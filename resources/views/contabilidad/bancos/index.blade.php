@extends('layouts.app')

@section('title', 'Gestión de Bancos')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
    
    {{-- Estilos inline mejorados --}}
    <style>
        /* === BANCOS DASHBOARD === */
        .bancos-dashboard {
            padding: 0;
        }

        /* === NAVEGACIÓN TABS === */
        .eerr-subnav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 0.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .eerr-subnav .nav-link {
            background: rgba(255,255,255,0.1);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 0.25rem;
            backdrop-filter: blur(10px);
        }

        .eerr-subnav .nav-link:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .eerr-subnav .nav-link.active {
            background: white;
            color: #667eea;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* === PAGE TITLE === */
        .page-title-enhanced {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .page-title-enhanced h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-title-enhanced p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
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

        /* === SALDO CARD === */
        .saldo-card {
            border: none;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
        }

        .saldo-card:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .saldo-card a {
            color: inherit;
            text-decoration: none;
        }

        .saldo-value {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .saldo-card:hover .saldo-value {
            transform: scale(1.05);
        }

        /* === TABLA === */
        .table th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            color: #495057;
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

        .bg-primary-soft {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }

        /* === PAGINATION === */
        .pagination-wrapper {
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            border-radius: 0 0 15px 15px;
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
            <i class="fas fa-university me-3"></i>
            Gestión de Bancos
        </h1>
        <p>
            <i class="fas fa-chart-line me-2"></i>
            Dashboard principal de movimientos y saldos bancarios
        </p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.contador') }}">Contabilidad</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Bancos</li>
@endsection

@section('content')
    <div class="container-fluid bancos-dashboard">
        {{-- =========== NAVEGACIÓN DEL MÓDULO =========== --}}
        <nav class="nav nav-tabs eerr-subnav mb-4">
            <a class="nav-link active" href="{{ route('contador.bancos.index') }}">
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
            <a class="nav-link" href="{{ route('contador.bancos.reporte') }}">
                <i class="fas fa-file-invoice me-2"></i>
                Reportes
            </a>
        </nav>
        {{-- =========== FIN NAVEGACIÓN =========== --}}

        {{-- =========== FILTROS =========== --}}
        <div class="card shadow-sm filters-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('contador.bancos.index') }}">
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
                        
                        <div class="col-md-4">
                            <label class="form-label" for="cuenta">
                                <i class="fas fa-university me-1"></i>
                                Cuenta Bancaria
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
                        
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i>
                                Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- =========== KPIs DEL PERÍODO =========== --}}
        <div class="stats-grid">
            <div class="stat-card shadow-sm success">
                <div class="stat-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-label">
                        <i class="fas fa-coins me-1"></i>
                        Ingresos del Período
                    </p>
                    <div class="stat-value">
                        S/ {{ number_format($totalesPeriodo->total_ingresos, 2) }}
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
                        Egresos del Período
                    </p>
                    <div class="stat-value">
                        S/ {{ number_format($totalesPeriodo->total_egresos, 2) }}
                    </div>
                </div>
            </div>
            
            <div class="stat-card shadow-sm info">
                <div class="stat-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-label">
                        <i class="fas fa-chart-area me-1"></i>
                        Flujo Neto del Período
                    </p>
                    <div class="stat-value">
                        S/ {{ number_format($totalesPeriodo->total_ingresos - $totalesPeriodo->total_egresos, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- =========== COLUMNA IZQUIERDA =========== --}}
            <div class="col-lg-4">
                {{-- Saldos Actuales --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-wallet me-2"></i>
                            Saldos Actuales (Total)
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($saldosActuales as $saldo)
                                <li class="list-group-item saldo-card">
                                    <a 
                                        href="{{ route('contador.bancos.detalle', $saldo->Cuenta) }}" 
                                        class="text-decoration-none"
                                    >
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold text-primary">
                                                    <i class="fas fa-university me-1"></i>
                                                    {{ $saldo->Banco }}
                                                </div>
                                            </div>
                                            <div class="saldo-value {{ $saldo->saldo_actual >= 0 ? 'text-success' : 'text-danger' }}">
                                                S/ {{ number_format($saldo->saldo_actual, 2) }}
                                            </div>
                                        </div>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <i class="fas fa-credit-card me-1"></i>
                                                {{ $saldo->Cuenta }} 
                                                <i class="fas fa-circle mx-2" style="font-size: 0.3rem;"></i>
                                                <i class="fas fa-sort-numeric-up me-1"></i>
                                                {{ $saldo->total_movimientos }} movs.
                                            </small>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center p-4">
                                    <i class="fas fa-university fa-2x mb-2 d-block text-muted"></i>
                                    No hay cuentas bancarias registradas.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="card-footer text-center fw-bold bg-success text-white">
                        <i class="fas fa-coins me-2"></i>
                        Total Disponible: S/ {{ number_format($saldosActuales->sum('saldo_actual'), 2) }}
                    </div>
                </div>

                {{-- Resumen por Cuenta (Período) --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>
                            Resumen del Período (Filtrado)
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <i class="fas fa-university me-1"></i>
                                            Banco
                                        </th>
                                        <th class="text-end">
                                            <i class="fas fa-arrow-down me-1 text-success"></i>
                                            Ingresos
                                        </th>
                                        <th class="text-end">
                                            <i class="fas fa-arrow-up me-1 text-danger"></i>
                                            Egresos
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($resumenCuentas as $resumen)
                                        <tr>
                                            <td>
                                                <a 
                                                    href="{{ route('contador.bancos.detalle', $resumen->Cuenta) }}" 
                                                    class="text-decoration-none fw-500"
                                                >
                                                    <i class="fas fa-building me-1"></i>
                                                    {{ $resumen->Banco }}
                                                </a>
                                            </td>
                                            <td class="text-end text-success">
                                                <strong>
                                                    S/ {{ number_format($resumen->total_ingresos, 2) }}
                                                </strong>
                                            </td>
                                            <td class="text-end text-danger">
                                                <strong>
                                                    S/ {{ number_format($resumen->total_egresos, 2) }}
                                                </strong>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center p-4 text-muted">
                                                <i class="fas fa-chart-line fa-2x mb-2 d-block text-muted"></i>
                                                No hay datos en el período.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========== COLUMNA DERECHA =========== --}}
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Últimos Movimientos (Período)
                        </h6>
                        @if($movimientosBancarios->hasPages())
                            <small class="text-muted">
                                <i class="fas fa-file-alt me-1"></i>
                                Pág. {{ $movimientosBancarios->currentPage() }} de {{ $movimientosBancarios->lastPage() }}
                            </small>
                        @endif
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <i class="fas fa-calendar me-1"></i>
                                            Fecha
                                        </th>
                                        <th>
                                            <i class="fas fa-university me-1"></i>
                                            Banco
                                        </th>
                                        <th>
                                            <i class="fas fa-file-text me-1"></i>
                                            Concepto
                                        </th>
                                        <th>
                                            <i class="fas fa-id-card me-1"></i>
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
                                    @forelse($movimientosBancarios as $mov)
                                        <tr>
                                            <td>
                                                <div class="fw-medium">
                                                    {{ \Carbon\Carbon::parse($mov->Fecha)->format('d/m/Y') }}
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <a 
                                                    href="{{ route('contador.bancos.detalle', $mov->Cuenta) }}" 
                                                    class="badge bg-primary-soft text-decoration-none"
                                                >
                                                    <i class="fas fa-building me-1"></i>
                                                    {{ $mov->Banco }}
                                                </a>
                                            </td>
                                            
                                            <td>
                                                <div class="fw-medium">
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
                                            <td colspan="6" class="text-center p-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3 d-block text-muted"></i>
                                                    <h5>No se encontraron movimientos</h5>
                                                    <p>No hay registros que coincidan con los filtros seleccionados.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($movimientosBancarios->hasPages())
                            <div class="card-footer pagination-wrapper">
                                {{ $movimientosBancarios->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection