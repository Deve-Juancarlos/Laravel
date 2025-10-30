@extends('layouts.app')

@section('title', 'Comparación Balance - Balance de Comprobación')

@section('styles')
    <link href="{{ asset('css/contabilidad/comparacion-balance.css') }}" rel="stylesheet">
    <style>
        .comparacion-balance-view {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .comparison-header {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-left: 4px solid #2563eb;
        }
        
        .comparison-header h1 {
            font-size: 1.875rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .comparison-header p {
            color: #64748b;
            font-size: 0.95rem;
            margin: 0;
        }
        
        .card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
        }
        
        .card-header h6 {
            margin: 0;
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9375rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .period-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .period-header-actual {
            background: #2563eb;
            color: white;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .period-header-actual h4 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 0.25rem 0;
        }
        
        .period-header-actual small {
            font-size: 0.8125rem;
            opacity: 0.9;
        }
        
        .period-header-anterior {
            background: #64748b;
            color: white;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .period-header-anterior h4 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 0.25rem 0;
        }
        
        .period-header-anterior small {
            font-size: 0.8125rem;
            opacity: 0.9;
        }
        
        .period-body {
            padding: 1.5rem;
        }
        
        .period-body h6 {
            font-size: 0.8125rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        
        .period-body h4 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        .comparison-table {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .comparison-table .card-header {
            background: #1e293b;
            color: white;
            padding: 1rem 1.5rem;
            border: none;
        }
        
        .comparison-table .card-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .table {
            margin: 0;
        }
        
        .table thead th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 0.875rem 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .table tbody tr {
            border-bottom: 1px solid #f1f5f9;
        }
        
        .table tbody tr:hover {
            background: #f8fafc;
        }
        
        .table tbody td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
            color: #334155;
        }
        
        .total-actual {
            background: #eff6ff;
        }
        
        .total-anterior {
            color: #64748b;
        }
        
        .chart-container {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .chart-container h6 {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            font-size: 0.9375rem;
        }
        
        .form-control {
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 0.625rem 0.875rem;
            font-size: 0.9375rem;
        }
        
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .form-label {
            font-weight: 500;
            color: #475569;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        
        .btn {
            border-radius: 6px;
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            font-size: 0.9375rem;
            border: 1px solid transparent;
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
        }
        
        .btn-secondary {
            background: #64748b;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #475569;
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .badge {
            padding: 0.375rem 0.75rem;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.8125rem;
        }
        
        .text-success {
            color: #10b981 !important;
        }
        
        .text-danger {
            color: #ef4444 !important;
        }
        
        .text-primary {
            color: #2563eb !important;
        }
        
        .text-secondary {
            color: #64748b !important;
        }
    </style>
@endsection

@section('sidebar-menu')
{{-- MENÚ PRINCIPAL --}}
<div class="nav-section">Dashboard</div>
<ul>
    <li><a href="{{ route('dashboard.contador') }}" class="nav-link active">
        <i class="fas fa-chart-pie"></i> Panel Principal
    </a></li>
</ul>

{{-- CONTABILIDAD --}}
<div class="nav-section">Contabilidad</div>
<ul>
    <li>
        <a href="{{ route('contador.libro-diario.index') }}" class="nav-link has-submenu">
            <i class="fas fa-book"></i> Libros Contables
        </a>
        <div class="nav-submenu">
            <a href="{{ route('contador.libro-diario.index') }}" class="nav-link"><i class="fas fa-file-alt"></i> Libro Diario</a>
            <a href="{{ route('contador.libro-mayor.index') }}" class="nav-link"><i class="fas fa-book-open"></i> Libro Mayor</a>
            <a href="{{route('contador.balance-comprobacion.index')}}" class="nav-link"><i class="fas fa-balance-scale"></i> Balance Comprobación</a>    
            <a href="{{ route('contador.estado-resultados.index') }}" class="nav-link"><i class="fas fa-chart-bar"></i> Estados Financieros</a>
        </div>
    </li>
    <li>
        <a href="#" class="nav-link has-submenu">
            <i class="fas fa-file-invoice"></i> Registros
        </a>
        <div class="nav-submenu">
            <a href="#" class="nav-link"><i class="fas fa-shopping-cart"></i> Compras</a>
            <a href="#" class="nav-link"><i class="fas fa-cash-register"></i> Ventas</a>
            <a href="#" class="nav-link"><i class="fas fa-university"></i> Bancos</a>
            <a href="#" class="nav-link"><i class="fas fa-money-bill-wave"></i> Caja</a>
        </div>
    </li>
</ul>

{{-- VENTAS Y COBRANZAS --}}
<div class="nav-section">Ventas & Cobranzas</div>
<ul>
    <li><a href="{{ route('contador.reportes.ventas') }}" class="nav-link">
        <i class="fas fa-chart-line"></i> Análisis Ventas
    </a></li>
    <li><a href="{{ route('contador.reportes.compras') }}" class="nav-link">
        <i class="fas fa-wallet"></i> Cartera
    </a></li>
    <li><a href="{{ route('contador.facturas.create') }}" class="nav-link">
        <i class="fas fa-clock"></i> Fact. Pendientes
    </a></li>
    <li><a href="{{ route('contador.facturas.index') }}" class="nav-link">
        <i class="fas fa-exclamation-triangle"></i> Fact. Vencidas
    </a></li>
</ul>

{{-- GESTIÓN --}}
<div class="nav-section">Gestión</div>
<ul>
    <li><a href="{{ route('contador.clientes') }}" class="nav-link">
        <i class="fas fa-users"></i> Clientes
    </a></li>
    <li><a href="{{ route('contador.reportes.medicamentos-controlados') }}" class="nav-link">
        <i class="fas fa-percentage"></i> Márgenes
    </a></li>
    <li><a href="{{ route('contador.reportes.inventario') }}" class="nav-link">
        <i class="fas fa-boxes"></i> Inventario
    </a></li>
</ul>

{{-- REPORTES SUNAT --}}
<div class="nav-section">SUNAT</div>
<ul>
    <li><a href="#" class="nav-link">
        <i class="fas fa-file-invoice-dollar"></i> PLE
    </a></li>
    <li><a href="#" class="nav-link">
        <i class="fas fa-percent"></i> IGV Mensual
    </a></li>
</ul>
@endsection

@section('content')
<div class="comparacion-balance-view">
    <div class="container-fluid">
        <!-- Header -->
        <div class="comparison-header">
            <h1><i class="fas fa-exchange-alt me-2"></i>Comparación de Balance</h1>
            <p>Análisis comparativo entre períodos contables</p>
        </div>

        <!-- Filtros de períodos -->
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-calendar-alt me-2"></i>Configuración de Períodos</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('contador.balance-comprobacion.comparacion') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Período Actual - Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" value="{{ $periodoActual['inicio'] }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Período Actual - Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" value="{{ $periodoActual['fin'] }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Período Anterior - Inicio</label>
                            <input type="date" class="form-control" value="{{ $periodoAnterior['inicio'] }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Período Anterior - Fin</label>
                            <input type="date" class="form-control" value="{{ $periodoAnterior['fin'] }}" readonly>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sync-alt me-2"></i>Generar Comparación
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tarjetas de resumen por período -->
        <div class="row mb-4 g-3">
            <div class="col-md-6">
                <div class="period-card">
                    <div class="period-header-actual">
                        <h4>PERÍODO ACTUAL</h4>
                        <small>{{ \Carbon\Carbon::parse($periodoActual['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($periodoActual['fin'])->format('d/m/Y') }}</small>
                    </div>
                    <div class="period-body">
                        <div class="row text-center g-3">
                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <h6 class="text-primary">TOTAL DEUDOR</h6>
                                    <h4 class="text-success">S/ {{ number_format($balanceActual['total_deudor'], 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <h6 class="text-primary">TOTAL ACREEDOR</h6>
                                    <h4 class="text-danger">S/ {{ number_format($balanceActual['total_acreedor'], 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                @if($balanceActual['cuadra'])
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>CUADRA
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>NO CUADRA
                                    </span>
                                @endif
                                <small class="d-block text-muted mt-2">
                                    Diferencia: S/ {{ number_format($balanceActual['diferencia'], 2) }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="period-card">
                    <div class="period-header-anterior">
                        <h4>PERÍODO ANTERIOR</h4>
                        <small>{{ \Carbon\Carbon::parse($periodoAnterior['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($periodoAnterior['fin'])->format('d/m/Y') }}</small>
                    </div>
                    <div class="period-body">
                        <div class="row text-center g-3">
                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <h6 class="text-secondary">TOTAL DEUDOR</h6>
                                    <h4 class="text-success">S/ {{ number_format($balanceAnterior['total_deudor'], 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <h6 class="text-secondary">TOTAL ACREEDOR</h6>
                                    <h4 class="text-danger">S/ {{ number_format($balanceAnterior['total_acreedor'], 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                @if($balanceAnterior['cuadra'])
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>CUADRA
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>NO CUADRA
                                    </span>
                                @endif
                                <small class="d-block text-muted mt-2">
                                    Diferencia: S/ {{ number_format($balanceAnterior['diferencia'], 2) }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de comparación detallada -->
        <div class="comparison-table">
            <div class="card-header">
                <h5><i class="fas fa-table me-2"></i>Comparación Detallada de Balances</h5>
            </div>
            
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th class="text-center">Período Actual</th>
                            <th class="text-center">Período Anterior</th>
                            <th class="text-center">Variación</th>
                            <th class="text-center">% Cambio</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="total-actual">
                            <td><strong>Total Deudor</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($balanceActual['total_deudor'], 2) }}</strong></td>
                            <td class="text-end total-anterior">S/ {{ number_format($balanceAnterior['total_deudor'], 2) }}</td>
                            <td class="text-end">
                                @php
                                    $variacionDeudor = $balanceActual['total_deudor'] - $balanceAnterior['total_deudor'];
                                    $porcentajeDeudor = $balanceAnterior['total_deudor'] > 0 ? ($variacionDeudor / $balanceAnterior['total_deudor']) * 100 : 0;
                                @endphp
                                <strong>{{ $variacionDeudor >= 0 ? '+' : '' }}S/ {{ number_format($variacionDeudor, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <strong>{{ $porcentajeDeudor >= 0 ? '+' : '' }}{{ number_format($porcentajeDeudor, 1) }}%</strong>
                            </td>
                            <td class="text-center">
                                @if($balanceActual['cuadra'] && $balanceAnterior['cuadra'])
                                    <span class="badge bg-success">Cuadran Ambos</span>
                                @else
                                    <span class="badge bg-warning text-dark">Revisar</span>
                                @endif
                            </td>
                        </tr>
                        
                        <tr>
                            <td><strong>Total Acreedor</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($balanceActual['total_acreedor'], 2) }}</strong></td>
                            <td class="text-end total-anterior">S/ {{ number_format($balanceAnterior['total_acreedor'], 2) }}</td>
                            <td class="text-end">
                                @php
                                    $variacionAcreedor = $balanceActual['total_acreedor'] - $balanceAnterior['total_acreedor'];
                                    $porcentajeAcreedor = $balanceAnterior['total_acreedor'] > 0 ? ($variacionAcreedor / $balanceAnterior['total_acreedor']) * 100 : 0;
                                @endphp
                                <strong>{{ $variacionAcreedor >= 0 ? '+' : '' }}S/ {{ number_format($variacionAcreedor, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <strong>{{ $porcentajeAcreedor >= 0 ? '+' : '' }}{{ number_format($porcentajeAcreedor, 1) }}%</strong>
                            </td>
                            <td class="text-center">
                                @if($balanceActual['cuadra'] && $balanceAnterior['cuadra'])
                                    <span class="badge bg-success">Cuadran Ambos</span>
                                @else
                                    <span class="badge bg-warning text-dark">Revisar</span>
                                @endif
                            </td>
                        </tr>
                        
                        <tr class="total-actual">
                            <td><strong>Diferencia</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($balanceActual['diferencia'], 2) }}</strong></td>
                            <td class="text-end total-anterior">S/ {{ number_format($balanceAnterior['diferencia'], 2) }}</td>
                            <td class="text-end">
                                @php
                                    $variacionDiff = $balanceActual['diferencia'] - $balanceAnterior['diferencia'];
                                    $porcentajeDiff = $balanceAnterior['diferencia'] > 0 ? ($variacionDiff / $balanceAnterior['diferencia']) * 100 : 0;
                                @endphp
                                <strong>{{ $variacionDiff >= 0 ? '+' : '' }}S/ {{ number_format($variacionDiff, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <strong>{{ $porcentajeDiff >= 0 ? '+' : '' }}{{ number_format($porcentajeDiff, 1) }}%</strong>
                            </td>
                            <td class="text-center">
                                @if($balanceActual['cuadra'] && $balanceAnterior['cuadra'])
                                    <span class="badge bg-success">Perfecto</span>
                                @else
                                    <span class="badge bg-danger">Error</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Gráfico comparativo -->
        <div class="chart-container">
            <h6><i class="fas fa-chart-bar me-2"></i>Evolución Comparativa de Balances</h6>
            <canvas id="comparisonChart" height="100"></canvas>
        </div>

        <!-- Análisis de variaciones -->
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-chart-line me-2"></i>Análisis de Variaciones Significativas</h6>
            </div>
            <div class="card-body">
                @php
                    $variaciones = [
                        'deudor' => [
                            'variacion' => $balanceActual['total_deudor'] - $balanceAnterior['total_deudor'],
                            'porcentaje' => $balanceAnterior['total_deudor'] > 0 ? (($balanceActual['total_deudor'] - $balanceAnterior['total_deudor']) / $balanceAnterior['total_deudor']) * 100 : 0,
                            'significado' => $balanceActual['total_deudor'] - $balanceAnterior['total_deudor'] > 0 ? 'Incremento en activos y gastos' : 'Reducción en activos y gastos'
                        ],
                        'acreedor' => [
                            'variacion' => $balanceActual['total_acreedor'] - $balanceAnterior['total_acreedor'],
                            'porcentaje' => $balanceAnterior['total_acreedor'] > 0 ? (($balanceActual['total_acreedor'] - $balanceAnterior['total_acreedor']) / $balanceAnterior['total_acreedor']) * 100 : 0,
                            'significado' => $balanceActual['total_acreedor'] - $balanceAnterior['total_acreedor'] > 0 ? 'Incremento en pasivos, patrimonio e ingresos' : 'Reducción en pasivos, patrimonio e ingresos'
                        ]
                    ];
                @endphp
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded">
                            <h6>Variación Total Deudor</h6>
                            <p class="mb-1">
                                <strong>{{ $variaciones['deudor']['variacion'] >= 0 ? '+' : '' }}S/ {{ number_format($variaciones['deudor']['variacion'], 2) }}</strong>
                                ({{ $variaciones['deudor']['porcentaje'] >= 0 ? '+' : '' }}{{ number_format($variaciones['deudor']['porcentaje'], 1) }}%)
                            </p>
                            <small class="text-muted">{{ $variaciones['deudor']['significado'] }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded">
                            <h6>Variación Total Acreedor</h6>
                            <p class="mb-1">
                                <strong>{{ $variaciones['acreedor']['variacion'] >= 0 ? '+' : '' }}S/ {{ number_format($variaciones['acreedor']['variacion'], 2) }}</strong>
                                ({{ $variaciones['acreedor']['porcentaje'] >= 0 ? '+' : '' }}{{ number_format($variaciones['acreedor']['porcentaje'], 1) }}%)
                            </p>
                            <small class="text-muted">{{ $variaciones['acreedor']['significado'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div class="row mt-4">
            <div class="col-md-12 text-center">
                <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Balance
                </a>
                <a href="{{ route('contador.balance-comprobacion.verificar') }}" class="btn btn-warning me-2">
                    <i class="fas fa-check-circle me-2"></i>Verificar Integridad
                </a>
                <button class="btn btn-success" onclick="exportarComparacion()">
                    <i class="fas fa-download me-2"></i>Exportar Comparación
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('comparisonChart').getContext('2d');
const comparisonChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Total Deudor', 'Total Acreedor', 'Diferencia'],
        datasets: [
            {
                label: 'Período Actual',
                data: [{{ $balanceActual['total_deudor'] }}, {{ $balanceActual['total_acreedor'] }}, {{ $balanceActual['diferencia'] }}],
                backgroundColor: '#2563eb',
                borderColor: '#2563eb',
                borderWidth: 1
            },
            {
                label: 'Período Anterior',
                data: [{{ $balanceAnterior['total_deudor'] }}, {{ $balanceAnterior['total_acreedor'] }}, {{ $balanceAnterior['diferencia'] }}],
                backgroundColor: '#64748b',
                borderColor: '#64748b',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                },
                ticks: {
                    callback: function(value) {
                        return 'S/ ' + value.toLocaleString('es-PE');
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 10,
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': S/ ' + context.parsed.y.toLocaleString('es-PE', {minimumFractionDigits: 2});
                    }
                }
            }
        }
    }
});

function exportarComparacion() {
    const params = new URLSearchParams({
        fecha_inicio_actual: '{{ $periodoActual['inicio'] }}',
        fecha_fin_actual: '{{ $periodoActual['fin'] }}',
        fecha_inicio_anterior: '{{ $periodoAnterior['inicio'] }}',
        fecha_fin_anterior: '{{ $periodoAnterior['fin'] }}',
        formato: 'comparacion'
    });
    window.location.href = `{{ route('contador.balance-comprobacion.exportar') }}?${params}`;
}
</script>
@endsection