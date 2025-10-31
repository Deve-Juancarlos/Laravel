@extends('layouts.app')

@section('title', 'Balance de Comprobación - SIFANO')

@push('styles')
<link href="{{ asset('css/contabilidad/balance-comprobacion.css') }}" rel="stylesheet">
@endpush

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
<div class="balance-comprobacion-view">
    <div class="container-fluid">
        <!-- Header -->
        <div class="balance-header">
            <h1><i class="fas fa-balance-scale me-2"></i>Balance de Comprobación</h1>
            <p>Verificación de saldos contables - Sistema SIFANO</p>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('contador.balance-comprobacion.index') }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Generar Balance
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Estado del Balance -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="stat-card">
                    <div class="stat-value">
                        @if($cuadra)
                            <span class="cuadra-badge cuadra-true">
                                <i class="fas fa-check-circle me-2"></i>BALANCE CUADRADO
                            </span>
                        @else
                            <span class="cuadra-badge cuadra-false">
                                <i class="fas fa-exclamation-triangle me-2"></i>BALANCE DESCUADRADO
                            </span>
                        @endif
                    </div>
                    <div class="stat-label mt-3">
                        Diferencia: S/ {{ number_format($diferencia, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($estadisticas['total_asientos']) }}</div>
                    <div class="stat-label">Total Asientos</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($estadisticas['total_movimientos']) }}</div>
                    <div class="stat-label">Total Movimientos</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($estadisticas['cuentas_utilizadas']) }}</div>
                    <div class="stat-label">Cuentas Utilizadas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value">S/ {{ number_format($totalDeudor, 2) }}</div>
                    <div class="stat-label">Total Deudor</div>
                </div>
            </div>
        </div>

        <!-- Balance Principal -->
        <div class="balance-table">
            <div class="card-header">
                <h5>
                    <i class="fas fa-table me-2"></i>
                    Balance de Comprobación al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                </h5>
            </div>
            
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th class="text-end">Debe</th>
                            <th class="text-end">Haber</th>
                            <th class="text-end">Saldos Deudores</th>
                            <th class="text-end">Saldos Acreedores</th>
                            <th class="text-center">Movimientos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Cuentas Deudoras -->
                        @foreach($cuentasDeudoras as $cuenta)
                        <tr>
                            <td><strong>{{ $cuenta['cuenta'] }}</strong></td>
                            <td class="text-end">{{ number_format($cuenta['saldo'], 2) }}</td>
                            <td class="text-end text-muted">0.00</td>
                            <td class="text-end fw-semibold text-success">{{ number_format($cuenta['saldo'], 2) }}</td>
                            <td class="text-end text-muted">0.00</td>
                            <td class="text-center">{{ number_format($cuenta['movimientos']) }}</td>
                        </tr>
                        @endforeach

                        <!-- Cuentas Acreedoras -->
                        @foreach($cuentasAcreedoras as $cuenta)
                        <tr>
                            <td><strong>{{ $cuenta['cuenta'] }}</strong></td>
                            <td class="text-end text-muted">0.00</td>
                            <td class="text-end">{{ number_format($cuenta['saldo'], 2) }}</td>
                            <td class="text-end text-muted">0.00</td>
                            <td class="text-end fw-semibold text-danger">{{ number_format($cuenta['saldo'], 2) }}</td>
                            <td class="text-center">{{ number_format($cuenta['movimientos']) }}</td>
                        </tr>
                        @endforeach

                        <!-- Totales -->
                        <tr class="total-row">
                            <td><strong>TOTALES</strong></td>
                            <td class="text-end"><strong>{{ number_format($totalDeudor, 2) }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($totalAcreedor, 2) }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($totalDeudor, 2) }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($totalAcreedor, 2) }}</strong></td>
                            <td class="text-center"><strong>{{ number_format($estadisticas['total_movimientos']) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Resumen por Clases -->
        <div class="resumen-clases mb-4">
            <h6><i class="fas fa-chart-pie me-2"></i>Resumen por Clases de Cuentas</h6>
            <div class="row g-3">
                <div class="col-md-2">
                    <div class="clase-box">
                        <h6 class="text-primary">ACTIVO</h6>
                        <div class="fw-bold">S/ {{ number_format($resumenClases['ACTIVO']['total_debe'], 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="clase-box">
                        <h6 class="text-warning">PASIVO</h6>
                        <div class="fw-bold">S/ {{ number_format($resumenClases['PASIVO']['total_haber'], 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="clase-box">
                        <h6 class="text-info">PATRIMONIO</h6>
                        <div class="fw-bold">S/ {{ number_format($resumenClases['PATRIMONIO']['total_haber'], 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="clase-box">
                        <h6 class="text-success">INGRESOS</h6>
                        <div class="fw-bold">S/ {{ number_format($resumenClases['INGRESOS']['total_haber'], 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="clase-box">
                        <h6 class="text-danger">GASTOS</h6>
                        <div class="fw-bold">S/ {{ number_format($resumenClases['GASTOS']['total_debe'], 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="clase-box">
                        <h6>PERIODO</h6>
                        <div class="small">{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}</div>
                        <div class="small">{{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de navegación -->
        <div class="row mt-4">
            <div class="col-md-12 text-center">
                <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-outline-primary me-2 mb-2">
                    <i class="fas fa-home me-2"></i>Inicio
                </a>

                <a href="{{ route('contador.balance-comprobacion.detalle', [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin
                    ]) }}" class="btn btn-outline-success me-2 mb-2">
                    <i class="fas fa-eye me-2"></i>Detalle
                </a>

                <a href="{{ route('contador.balance-comprobacion.clases', [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin
                    ]) }}" class="btn btn-outline-info me-2 mb-2">
                    <i class="fas fa-layer-group me-2"></i>Clases
                </a>

                <a href="{{ route('contador.balance-comprobacion.comparacion', [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin
                    ]) }}" class="btn btn-outline-secondary me-2 mb-2">
                    <i class="fas fa-exchange-alt me-2"></i>Comparación
                </a>

                <a href="{{ route('contador.balance-comprobacion.verificar', [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin
                    ]) }}" class="btn btn-outline-warning mb-2">
                    <i class="fas fa-check-circle me-2"></i>Verificar
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function exportarBalance() {
    const params = new URLSearchParams({
        fecha_inicio: '{{ $fechaInicio }}',
        fecha_fin: '{{ $fechaFin }}',
        formato: 'excel'
    });
    window.location.href = `{{ route('contador.balance-comprobacion.exportar') }}?${params}`;
}
</script>
@endpush