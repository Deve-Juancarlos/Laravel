@extends('layouts.app')

@section('title', 'Comparación Períodos - Libro Mayor')

@push('styles')
    {{-- CSS Específico para esta vista --}}
    <link href="{{ asset('css/contabilidad/libro-mayor-comparacion.css') }}" rel="stylesheet">
@endpush

{{-- 1. Título de la Página --}}
@section('page-title', 'Comparación de Períodos')

{{-- 2. Breadcrumbs --}}
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.libro-mayor.index') }}">Libro Mayor</a></li>
    <li class="breadcrumb-item active" aria-current="page">Comparación</li>
@endsection


{{-- 3. Contenido Principal --}}
@section('content')

{{-- Alertas --}}
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Filtros de Períodos --}}
<div class="card shadow-sm filters-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('contador.libro-mayor.comparacion') }}">
            <div class="row g-3 align-items-end">
                <!-- Período Actual -->
                <div class="col-md-3">
                    <label for="fecha_inicio_actual" class="form-label fw-bold text-primary">Período Actual (Inicio)</label>
                    <input type="date" id="fecha_inicio_actual" name="fecha_inicio_actual" value="{{ $periodoActual['inicio'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin_actual" class="form-label fw-bold text-primary">Período Actual (Fin)</label>
                    <input type="date" id="fecha_fin_actual" name="fecha_fin_actual" value="{{ $periodoActual['fin'] ?? '' }}" class="form-control">
                </div>
                
                <!-- Período Anterior -->
                <div class="col-md-3">
                    <label for="fecha_inicio_anterior" class="form-label fw-bold text-warning">Período Anterior (Inicio)</label>
                    <input type="date" id="fecha_inicio_anterior" name="fecha_inicio_anterior" value="{{ $periodoAnterior['inicio'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin_anterior" class="form-label fw-bold text-warning">Período Anterior (Fin)</label>
                    <input type="date" id="fecha_fin_anterior" name="fecha_fin_anterior" value="{{ $periodoAnterior['fin'] ?? '' }}" class="form-control">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-end">
                    <a href="{{ route('contador.libro-mayor.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver al Mayor
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Comparar Períodos
                    </button>
                </div>
            </div>
        </form>
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
        <div class="stat-box shadow-sm">
            <div class="stat-value">S/ {{ number_format($totalDebeActual, 2) }}</div>
            <div class="stat-label">Debe Período Actual</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box shadow-sm">
            <div class="stat-value">S/ {{ number_format($totalDebeAnterior, 2) }}</div>
            <div class="stat-label">Debe Período Anterior</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box shadow-sm">
            <div class="stat-value {{ $variacionDebe >= 0 ? 'variation-positive' : 'variation-negative' }}">
                {{ $variacionDebe >= 0 ? '+' : '' }}S/ {{ number_format($variacionDebe, 2) }}
            </div>
            <div class="stat-label">Variación Debe</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box shadow-sm">
            <div class="stat-value">
                {{ $totalDebeAnterior != 0 ? number_format(($variacionDebe / abs($totalDebeAnterior)) * 100, 1) : 'N/A' }}%
            </div>
            <div class="stat-label">% Variación</div>
        </div>
    </div>
</div>

<!-- Tabla de Comparación -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
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
                                $saldoActual = ($comp['debe_actual'] ?? 0) - ($comp['haber_actual'] ?? 0);
                                $saldoAnterior = ($comp['debe_anterior'] ?? 0) - ($comp['haber_anterior'] ?? 0);
                                $variacionTotal = $saldoActual - $saldoAnterior;
                                $variacionPorcentaje = ($saldoAnterior != 0) ? ($variacionTotal / abs($saldoAnterior)) * 100 : ($variacionTotal > 0 ? 100 : 0);
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
                                        <strong>S/ {{ number_format($saldoActual, 2) }}</strong>
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
                                        <strong>S/ {{ number_format($saldoAnterior, 2) }}</strong>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="{{ $variacionTotal >= 0 ? 'variation-positive' : 'variation-negative' }}">
                                        {{ $variacionTotal >= 0 ? '+' : '' }}S/ {{ number_format($variacionTotal, 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if(abs($variacionPorcentaje) > 0)
                                        <span class="badge bg-{{ $variacionPorcentaje >= 0 ? 'success' : 'danger' }}">
                                            {{ $variacionPorcentaje >= 0 ? '+' : '' }}{{ number_format($variacionPorcentaje, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($variacionTotal > 0.01)
                                        <i class="fas fa-arrow-up text-success" title="Aumento"></i>
                                    @elseif($variacionTotal < -0.01)
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
                                    @if(abs($totalDebeAnterior + $totalHaberAnterior) > 0)
                                        {{ number_format((($variacionDebe + $variacionHaber) / abs($totalDebeAnterior + $totalHaberAnterior)) * 100, 1) }}%
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
        <div class="card shadow-sm">
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
                        <h6>Cuentas con Mayor Variación (Top 3)</h6>
                        @foreach(collect($comparacion)->sortByDesc(function($item) { return abs(($item['debe_actual'] ?? 0) - ($item['debe_anterior'] ?? 0) + ($item['haber_actual'] ?? 0) - ($item['haber_anterior'] ?? 0)); })->take(3) as $comp)
                            @php
                                $variacion = ($comp['debe_actual'] ?? 0) - ($comp['debe_anterior'] ?? 0) + ($comp['haber_actual'] ?? 0) - ($comp['haber_anterior'] ?? 0);
                            @endphp
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small>{{ $comp['cuenta'] }} - {{ Str::limit($comp['nombre_cuenta'], 25) }}</small>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animar barras de progreso al cargar
    setTimeout(() => {
        document.querySelectorAll('.progress-fill').forEach(bar => {
            // Re-aplicar el width activa la transición CSS
            bar.style.width = bar.style.width; 
        });
    }, 100);
});
</script>
@endpush
