@extends('layouts.app')

@section('title', "Cuenta {$cuenta} - Libro Mayor")

@push('styles')
    <link href="{{ asset('css/contabilidad/libro-mayor-cuenta.css') }}" rel="stylesheet">
@endpush

{{-- 1. Título de la Página --}}
@section('page-title')
    <div>
        <h1><i class="fas fa-calculator me-2"></i>Cuenta: {{ $cuenta }}</h1>
        <p class="text-muted">{{ $infoCuenta->nombre ?? 'Detalle de Movimientos de Cuenta' }}</p>
    </div>
@endsection

{{-- 2. Breadcrumbs --}}
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.libro-mayor.index') }}">Libro Mayor</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $cuenta }}</li>
@endsection

{{-- 3. Contenido Principal --}}
@section('content')
<div class="container-fluid">
    <div class="main-content-wrapper">

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

        {{-- Filtros de Fecha --}}
        <div class="card shadow-sm filters-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('contador.libro-mayor.cuenta', $cuenta) }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label" for="fecha_inicio">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" 
                                   value="{{ $fechaInicio ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="fecha_fin">Fecha Fin</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" 
                                   value="{{ $fechaFin ?? '' }}">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i> Filtrar Período
                            </button>
                            <a href="{{ route('contador.libro-mayor.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        {{-- Información de la cuenta --}}
        <div class="account-info">
            <div class="account-field">
                <span class="account-label">Cuenta Contable</span>
                <span class="account-value">{{ $cuenta }}</span>
            </div>
            <div class="account-field">
                <span class="account-label">Nombre de la Cuenta</span>
                {{-- CORRECCIÓN: Era 'cuenta_nombre', se cambió a 'nombre' --}}
                <span class="account-value">{{ $infoCuenta->nombre ?? 'Sin nombre' }}</span>
            </div>
            <div class="account-field">
                <span class="account-label">Período</span>
                <span class="account-value">{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</span>
            </div>
            <div class="account-field">
                <span class="account-label">Saldo Anterior</span>
                <span class="account-value saldo {{ $saldoAnterior >= 0 ? 'saldo-acreedor' : 'saldo-deudor' }}">
                    S/ {{ number_format(abs($saldoAnterior),2) }} <small>({{ $saldoAnterior >=0 ? 'Acreedor':'Deudor' }})</small>
                </span>
            </div>
            <div class="account-field">
                <span class="account-label">Saldo Final</span>
                <span class="account-value saldo {{ $totalesPeriodo['saldo_final'] >= 0 ? 'saldo-acreedor' : 'saldo-deudor' }}">
                    S/ {{ number_format(abs($totalesPeriodo['saldo_final']),2) }} <small>({{ $totalesPeriodo['saldo_final'] >=0 ? 'Acreedor':'Deudor' }})</small>
                </span>
            </div>
        </div>

        {{-- Resumen del período --}}
        <div class="summary-cards">
            <div class="summary-card debe shadow-sm">
                <div class="summary-label">Total Debe (Período)</div>
                <div class="summary-value">S/ {{ number_format($totalesPeriodo['debe'],2) }}</div>
            </div>
            <div class="summary-card haber shadow-sm">
                <div class="summary-label">Total Haber (Período)</div>
                <div class="summary-value">S/ {{ number_format($totalesPeriodo['haber'],2) }}</div>
            </div>
            <div class="summary-card total shadow-sm">
                <div class="summary-label">Saldo del Período</div>
                <div class="summary-value {{ ($totalesPeriodo['debe'] - $totalesPeriodo['haber']) >= 0 ? 'text-success' : 'text-danger' }}">
                    S/ {{ number_format($totalesPeriodo['debe'] - $totalesPeriodo['haber'],2) }}
                </div>
            </div>
        </div>

        {{-- Tabla de movimientos --}}
        <div class="table-container card shadow-sm">
            <div class="table-header card-header d-flex justify-content-between align-items-center">
                <h5 class="table-title card-title mb-0"><i class="fas fa-list me-2"></i>Movimientos del Período</h5>
                <a href="{{ route('contador.libro-mayor.exportar', array_merge(request()->query(), ['cuenta' => $cuenta])) }}" class="btn btn-success-soft btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Exportar Cuenta
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>N° Asiento</th>
                            <th>Descripción</th>
                            <th class="text-end">Debe (S/)</th>
                            <th class="text-end">Haber (S/)</th>
                            <th class="text-end">Saldo Acumulado (S/)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="saldo-acumulado {{ $saldoAnterior >= 0 ? 'saldo-acreedor' : 'saldo-deudor' }}">
                            <td colspan="5" class="text-end"><strong>Saldo Anterior al {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}</strong></td>
                            <td class="text-end">{{ number_format($saldoAnterior,2) }}</td>
                        </tr>

                        @forelse($movimientos as $mov)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
                            <td>
                                {{-- CORRECCIÓN: Enlace al asiento --}}
                                <a href="{{ route('contador.libro-diario.show', $mov->asiento_id) }}" target="_blank" class="documento-link" title="Ver Asiento {{ $mov->numero }}">
                                    {{ $mov->numero }}
                                </a>
                            </td>
                            <td>{{ Str::limit($mov->concepto ?? '-',50) }}</td>
                            <td class="text-end debe-column">{{ $mov->debe>0?number_format($mov->debe,2):'-' }}</td>
                            <td class="text-end haber-column">{{ $mov->haber>0?number_format($mov->haber,2):'-' }}</td>
                            <td class="text-end saldo-column {{ $mov->saldo_acumulado>=0?'saldo-acreedor':'saldo-deudor' }}">
                                {{ number_format($mov->saldo_acumulado,2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted p-5">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                <h5 class="mb-0">No hay movimientos para este período</h5>
                            </td>
                        </tr>
                        @endforelse

                        @if($movimientos->count()>0)
                        <tr class="table-light fw-bold">
                            <td colspan="3" class="text-end"><strong>Totales del Período</strong></td>
                            <td class="text-end debe-column">{{ number_format($totalesPeriodo['debe'],2) }}</td>
                            <td class="text-end haber-column">{{ number_format($totalesPeriodo['haber'],2) }}</td>
                            <td class="text-end">---</td>
                        </tr>
                        <tr class="table-dark fw-bold">
                            <td colspan="5" class="text-end"><strong>Saldo Final al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</strong></td>
                            <td class="text-end saldo-column {{ $totalesPeriodo['saldo_final']>=0?'saldo-acreedor':'saldo-deudor' }}">
                                {{ number_format($totalesPeriodo['saldo_final'],2) }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- No se necesita JS específico para esta vista, pero dejamos el push por si acaso --}}
@endpush
