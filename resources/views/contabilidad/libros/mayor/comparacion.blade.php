@extends('layouts.app')

@section('title', 'Comparación Períodos - Libro Mayor')

@section('styles')
<style>
    .period-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 25px;
        color: white;
        margin-bottom: 20px;
    }
    .comparison-table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .variation-positive {
        color: #28a745;
        font-weight: bold;
    }
    .variation-negative {
        color: #dc3545;
        font-weight: bold;
    }
    .variation-neutral {
        color: #6c757d;
        font-weight: bold;
    }
    .stat-box {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border-radius: 10px;
        padding: 20px;
        color: white;
        text-align: center;
        margin-bottom: 15px;
    }
    .stat-value {
        font-size: 2em;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .stat-label {
        opacity: 0.9;
        font-size: 0.9em;
    }
    .progress-bar-custom {
        height: 8px;
        border-radius: 10px;
        background-color: #e3e6f0;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.6s ease;
    }
</style>
@endsection

@section('sidebar-menu')
{{-- MENÚ PRINCIPAL --}}
<div class="nav-section">Dashboard</div>
<ul>
    <li><a href="{{ route('dashboard.contador') }}" class="nav-link">
        <i class="fas fa-chart-pie"></i> Panel Principal
    </a></li>
</ul>

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
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('contador.libro-mayor.index') }}">Libro Mayor</a></li>
                    <li class="breadcrumb-item active">Comparación Períodos</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header con Períodos -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="period-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h3 mb-2">
                            <i class="fas fa-balance-scale"></i>
                            Comparación entre Períodos
                        </h1>
                        <p class="mb-0">Análisis de variaciones entre períodos contables</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="badge bg-light text-dark fs-6">
                            Generado: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de Períodos -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-left-primary shadow">
                <div class="card-body">
                    <h5 class="text-primary">
                        <i class="fas fa-calendar-alt"></i> Período Actual
                    </h5>
                    <p class="mb-0">
                        <strong>{{ \Carbon\Carbon::parse($periodoActual['inicio'] ?? '')->format('d/m/Y') }}</strong>
                        al
                        <strong>{{ \Carbon\Carbon::parse($periodoActual['fin'] ?? '')->format('d/m/Y') }}</strong>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-left-warning shadow">
                <div class="card-body">
                    <h5 class="text-warning">
                        <i class="fas fa-calendar"></i> Período Anterior
                    </h5>
                    <p class="mb-0">
                        <strong>{{ \Carbon\Carbon::parse($periodoAnterior['inicio'] ?? '')->format('d/m/Y') }}</strong>
                        al
                        <strong>{{ \Carbon\Carbon::parse($periodoAnterior['fin'] ?? '')->format('d/m/Y') }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        @php
            $totalDebeActual = collect($comparacion ?? [])->sum('debe_actual');
            $totalDebeAnterior = collect($comparacion ?? [])->sum('debe_anterior');
            $totalHaberActual = collect($comparacion ?? [])->sum('haber_actual');
            $totalHaberAnterior = collect($comparacion ?? [])->sum('haber_anterior');
            
            $variacionDebe = $totalDebeActual - $totalDebeAnterior;
            $variacionHaber = $totalHaberActual - $totalHaberAnterior;
        @endphp
        
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-value">S/ {{ number_format($totalDebeActual, 2) }}</div>
                <div class="stat-label">Debe Período Actual</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-value">S/ {{ number_format($totalDebeAnterior, 2) }}</div>
                <div class="stat-label">Debe Período Anterior</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-value {{ $variacionDebe >= 0 ? 'variation-positive' : 'variation-negative' }}">
                    {{ $variacionDebe >= 0 ? '+' : '' }}S/ {{ number_format($variacionDebe, 2) }}
                </div>
                <div class="stat-label">Variación Debe</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-value">
                    {{ $totalDebeAnterior > 0 ? number_format(($variacionDebe / $totalDebeAnterior) * 100, 1) : 0 }}%
                </div>
                <div class="stat-label">% Variación</div>
            </div>
        </div>
    </div>

    <!-- Tabla de Comparación -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table"></i> Comparación Detallada por Cuenta
                    </h6>
                </div>
                <div class="card-body">
                    @if(($comparacion ?? collect())->count() > 0)
                    <div class="comparison-table">
                        <table class="table table-hover" id="comparisonTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Cuenta</th>
                                    <th class="text-center">Período Actual</th>
                                    <th class="text-center">Período Anterior</th>
                                    <th class="text-center">Variación</th>
                                    <th class="text-center">% Cambio</th>
                                    <th class="text-center">Tendencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($comparacion ?? [] as $comp)
                                @php
                                    $variacionTotal = ($comp['debe_actual'] ?? 0) - ($comp['debe_anterior'] ?? 0) +
                                                     ($comp['haber_actual'] ?? 0) - ($comp['haber_anterior'] ?? 0);
                                    $variacionAbs = abs($variacionTotal);
                                    $variacionPorcentaje = ($comp['debe_anterior'] ?? 0) > 0 ? 
                                        ($variacionTotal / ($comp['debe_anterior'] + $comp['haber_anterior'])) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $comp['cuenta'] }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $comp['nombre_cuenta'] ?? 'Sin nombre' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div>
                                            <small class="text-muted">Debe:</small>
                                            <strong class="text-success">S/ {{ number_format($comp['debe_actual'] ?? 0, 2) }}</strong>
                                        </div>
                                        <div>
                                            <small class="text-muted">Haber:</small>
                                            <strong class="text-danger">S/ {{ number_format($comp['haber_actual'] ?? 0, 2) }}</strong>
                                        </div>
                                        <div>
                                            <small class="text-muted">Saldo:</small>
                                            <strong>S/ {{ number_format(($comp['debe_actual'] ?? 0) - ($comp['haber_actual'] ?? 0), 2) }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div>
                                            <small class="text-muted">Debe:</small>
                                            <strong class="text-success">S/ {{ number_format($comp['debe_anterior'] ?? 0, 2) }}</strong>
                                        </div>
                                        <div>
                                            <small class="text-muted">Haber:</small>
                                            <strong class="text-danger">S/ {{ number_format($comp['haber_anterior'] ?? 0, 2) }}</strong>
                                        </div>
                                        <div>
                                            <small class="text-muted">Saldo:</small>
                                            <strong>S/ {{ number_format(($comp['debe_anterior'] ?? 0) - ($comp['haber_anterior'] ?? 0), 2) }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="{{ $variacionTotal >= 0 ? 'variation-positive' : 'variation-negative' }}">
                                            {{ $variacionTotal >= 0 ? '+' : '' }}S/ {{ number_format($variacionTotal, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if(abs($variacionPorcentaje) > 1)
                                            <span class="badge bg-{{ $variacionPorcentaje >= 0 ? 'success' : 'danger' }}">
                                                {{ number_format($variacionPorcentaje, 1) }}%
                                            </span>
                                        @else
                                            <span class="text-muted">Sin cambio</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($variacionTotal > 0)
                                            <i class="fas fa-arrow-up text-success" title="Aumento"></i>
                                        @elseif($variacionTotal < 0)
                                            <i class="fas fa-arrow-down text-danger" title="Disminución"></i>
                                        @else
                                            <i class="fas fa-minus text-muted" title="Sin cambio"></i>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td>TOTALES</td>
                                    <td class="text-center">
                                        <div>Debe: S/ {{ number_format($totalDebeActual, 2) }}</div>
                                        <div>Haber: S/ {{ number_format($totalHaberActual, 2) }}</div>
                                    </td>
                                    <td class="text-center">
                                        <div>Debe: S/ {{ number_format($totalDebeAnterior, 2) }}</div>
                                        <div>Haber: S/ {{ number_format($totalHaberAnterior, 2) }}</div>
                                    </td>
                                    <td class="text-center {{ $variacionDebe + $variacionHaber >= 0 ? 'variation-positive' : 'variation-negative' }}">
                                        S/ {{ number_format($variacionDebe + $variacionHaber, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if(($totalDebeAnterior + $totalHaberAnterior) > 0)
                                            {{ number_format((($variacionDebe + $variacionHaber) / ($totalDebeAnterior + $totalHaberAnterior)) * 100, 1) }}%
                                        @else
                                            0%
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(($variacionDebe + $variacionHaber) > 0)
                                            <i class="fas fa-arrow-up text-success"></i>
                                        @elseif(($variacionDebe + $variacionHaber) < 0)
                                            <i class="fas fa-arrow-down text-danger"></i>
                                        @else
                                            <i class="fas fa-minus text-muted"></i>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay datos para comparar</h5>
                        <p class="text-muted">No se encontraron movimientos en los períodos seleccionados</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Comparación -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Resumen Visual
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Gráfico de Barras Comparativo -->
                        <div class="col-md-6">
                            <h6>Comparación Debe vs Haber</h6>
                            <div class="progress-bar-custom mb-2">
                                <div class="progress-fill" style="width: {{ $totalDebeActual > 0 ? ($totalDebeActual / max($totalDebeActual, $totalDebeAnterior)) * 100 : 0 }}%; background: linear-gradient(90deg, #28a745, #20c997);"></div>
                            </div>
                            <small class="text-muted">Debe Actual: S/ {{ number_format($totalDebeActual, 2) }}</small>
                            
                            <div class="progress-bar-custom mb-2 mt-3">
                                <div class="progress-fill" style="width: {{ $totalDebeAnterior > 0 ? ($totalDebeAnterior / max($totalDebeActual, $totalDebeAnterior)) * 100 : 0 }}%; background: linear-gradient(90deg, #6c757d, #adb5bd);"></div>
                            </div>
                            <small class="text-muted">Debe Anterior: S/ {{ number_format($totalDebeAnterior, 2) }}</small>
                        </div>
                        
                        <!-- Indicadores de Variación -->
                        <div class="col-md-6">
                            <h6>Indicadores de Variación</h6>
                            @foreach($comparacion->take(3) as $comp)
                                @php
                                    $variacion = ($comp['debe_actual'] ?? 0) - ($comp['debe_anterior'] ?? 0);
                                @endphp
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small>{{ $comp['cuenta'] }}</small>
                                    <span class="badge bg-{{ $variacion >= 0 ? 'success' : 'danger' }}">
                                        {{ $variacion >= 0 ? '+' : '' }}S/ {{ number_format($variacion, 2) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animar barras de progreso
    setTimeout(() => {
        document.querySelectorAll('.progress-fill').forEach(bar => {
            bar.style.width = bar.style.width;
        });
    }, 100);
    
    // Destacar variaciones significativas
    document.querySelectorAll('tbody tr').forEach(row => {
        const variationCell = row.querySelector('td:nth-child(4)');
        if (variationCell) {
            const variationText = variationCell.textContent.trim();
            if (variationText.includes('-') && !variationText.includes('S/ -0')) {
                row.style.backgroundColor = 'rgba(220, 53, 69, 0.05)';
            } else if (!variationText.includes('S/ 0')) {
                row.style.backgroundColor = 'rgba(40, 167, 69, 0.05)';
            }
        }
    });
});
</script>
@endsection