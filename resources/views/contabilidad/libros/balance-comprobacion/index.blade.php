@extends('layouts.app')

@section('title', 'Balance de Comprobación - SEIMCORP')

@push('styles')
    <link href="{{ asset('css/contabilidad/balance-comparacion/index.css') }}" rel="stylesheet">
@endpush

{{-- 1. Título de la Página --}}
@section('page-title')
    <div>
        <h1><i class="fas fa-balance-scale me-2"></i>Balance de Comprobación</h1>
        <p class="text-muted">Verificación de sumas y saldos contables</p>
    </div>
@endsection

{{-- 2. Breadcrumbs --}}
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item active" aria-current="page">Balance de Comprobación</li>
@endsection

{{-- 3. Contenido Principal --}}
@section('content')
<div class="balance-comprobacion-view">

    {{-- Alertas --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Filtros --}}
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.balance-comprobacion.index') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_fin">Fecha Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-6 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Generar Balance
                        </button>
                        {{-- Botón de exportar añadido --}}
                        <button type="button" class="btn btn-success w-100" onclick="exportarBalance()">
                            <i class="fas fa-file-excel me-1"></i>Exportar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Estado del Balance --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="stat-card shadow-sm">
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

    {{-- Estadísticas --}}
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="stat-card-small shadow-sm">
                <div class="stat-value-small">{{ number_format($estadisticas['total_asientos']) }}</div>
                <div class="stat-label-small">Total Asientos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-small shadow-sm">
                <div class="stat-value-small">{{ number_format($estadisticas['total_movimientos']) }}</div>
                <div class="stat-label-small">Total Movimientos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-small shadow-sm">
                <div class="stat-value-small">{{ number_format($estadisticas['cuentas_utilizadas']) }}</div>
                <div class="stat-label-small">Cuentas Utilizadas</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-small shadow-sm">
                <div class="stat-value-small">S/ {{ number_format($totalDeudor, 2) }}</div>
                <div class="stat-label-small">Total Sumas</div>
            </div>
        </div>
    </div>

    {{-- Balance Principal --}}
    <div class="card shadow-sm balance-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 card-title">
                <i class="fas fa-table me-2"></i>
                Balance al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
            </h5>
            <div>
                {{-- Botones de navegación a otros reportes --}}
                <a href="{{ route('contador.balance-comprobacion.detalle', request()->query()) }}" class="btn btn-info-soft btn-sm">
                    <i class="fas fa-eye me-1"></i>Detalle
                </a>
                <a href="{{ route('contador.balance-comprobacion.clases', request()->query()) }}" class="btn btn-info-soft btn-sm">
                    <i class="fas fa-layer-group me-1"></i>Clases
                </a>
                <a href="{{ route('contador.balance-comprobacion.comparacion', request()->query()) }}" class="btn btn-info-soft btn-sm">
                    <i class="fas fa-exchange-alt me-1"></i>Comparar
                </a>
                <a href="{{ route('contador.balance-comprobacion.verificar', request()->query()) }}" class="btn btn-warning-soft btn-sm">
                    <i class="fas fa-check-circle me-1"></i>Verificar
                </a>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cuenta</th>
                        <th>Nombre</th>
                        <th class="text-end">Mov. Debe</th>
                        <th class="text-end">Mov. Haber</th>
                        <th class="text-end">Saldo Deudor</th>
                        <th class="text-end">Saldo Acreedor</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Cuentas Deudoras --}}
                    @foreach($cuentasDeudoras as $cuenta)
                    <tr>
                        <td><strong>{{ $cuenta['cuenta'] }}</strong></td>
                        <td>{{ $cuenta['nombre_cuenta'] ?? 'N/A' }}</td>
                        <td class="text-end text-muted">{{ number_format($cuenta['saldo_deudor_raw'], 2) }}</td>
                        <td class="text-end text-muted">{{ number_format($cuenta['saldo_acredor_raw'], 2) }}</td>
                        <td class="text-end fw-semibold text-success">S/ {{ number_format($cuenta['saldo'], 2) }}</td>
                        <td class="text-end text-muted">-</td>
                    </tr>
                    @endforeach

                    {{-- Cuentas Acreedoras --}}
                    @foreach($cuentasAcreedoras as $cuenta)
                    <tr>
                        <td><strong>{{ $cuenta['cuenta'] }}</strong></td>
                        <td>{{ $cuenta['nombre_cuenta'] ?? 'N/A' }}</td>
                        <td class="text-end text-muted">{{ number_format($cuenta['saldo_deudor_raw'], 2) }}</td>
                        <td class="text-end text-muted">{{ number_format($cuenta['saldo_acredor_raw'], 2) }}</td>
                        <td class="text-end text-muted">-</td>
                        <td class="text-end fw-semibold text-danger">S/ {{ number_format($cuenta['saldo'], 2) }}</td>
                    </tr>
                    @endforeach

                    {{-- Totales --}}
                    <tr class="total-row table-dark">
                        <td colspan="2"><strong>SUMAS IGUALES</strong></td>
                        <td class="text-end"><strong>S/ {{ number_format($cuentasDeudoras->sum('saldo_deudor_raw') + $cuentasAcreedoras->sum('saldo_deudor_raw'), 2) }}</strong></td>
                        <td class="text-end"><strong>S/ {{ number_format($cuentasDeudoras->sum('saldo_acredor_raw') + $cuentasAcreedoras->sum('saldo_acredor_raw'), 2) }}</strong></td>
                        <td class="text-end"><strong>S/ {{ number_format($totalDeudor, 2) }}</strong></td>
                        <td class="text-end"><strong>S/ {{ number_format($totalAcreedor, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Resumen por Clases --}}
    <div class="resumen-clases mb-4">
        <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Resumen por Clases de Cuentas</h5>
        <div class="row g-3">
            <div class="col">
                <div class="clase-box shadow-sm">
                    <h6 class="text-primary">ACTIVO (1)</h6>
                    <div class="fw-bold">S/ {{ number_format($resumenClases['ACTIVO']['saldo'], 2) }}</div>
                </div>
            </div>
            <div class="col">
                <div class="clase-box shadow-sm">
                    <h6 class="text-warning">PASIVO (2)</h6>
                    <div class="fw-bold">S/ {{ number_format($resumenClases['PASIVO']['saldo'], 2) }}</div>
                </div>
            </div>
            <div class="col">
                <div class="clase-box shadow-sm">
                    <h6 class="text-info">PATRIMONIO (3)</h6>
                    <div class="fw-bold">S/ {{ number_format($resumenClases['PATRIMONIO']['saldo'], 2) }}</div>
                </div>
            </div>
            <div class="col">
                <div class="clase-box shadow-sm">
                    <h6 class="text-success">INGRESOS (4)</h6>
                    <div class="fw-bold">S/ {{ number_format($resumenClases['INGRESOS']['saldo'], 2) }}</div>
                </div>
            </div>
            <div class="col">
                <div class="clase-box shadow-sm">
                    <h6 class="text-danger">GASTOS (5,6,9)</h6>
                    <div class="fw-bold">S/ {{ number_format($resumenClases['GASTOS']['saldo'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportarBalance() {
    // Tomar los valores actuales de los filtros
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;

    // Construir la URL con los parámetros
    const params = new URLSearchParams({
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
    });
    
    // Redirigir a la ruta de exportación
    window.location.href = `{{ route('contador.balance-comprobacion.exportar') }}?${params}`;
}
</script>
@endpush
