@extends('layouts.app')

@section('title', 'Resumen Mensual de Bancos')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-calendar-week me-2"></i>Resumen Mensual de Bancos</h1>
        <p class="text-muted">Consolidado de ingresos y egresos por mes.</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Resumen Mensual</li>
@endsection

@section('content')
<div class="container-fluid">

    <!-- Navegación del Módulo de Bancos -->
    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link" href="{{ route('contador.bancos.index') }}">
            <i class="fas fa-home me-1"></i> Dashboard Bancos
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.flujo-diario') }}">
            <i class="fas fa-calendar-day me-1"></i> Flujo Diario
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.diario') }}">
            <i class="fas fa-calendar-alt me-1"></i> Reporte Diario
        </a>
        <a class="nav-link active" href="{{ route('contador.bancos.mensual') }}">
            <i class="fas fa-calendar-week me-1"></i> Resumen Mensual
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.conciliacion') }}">
            <i class="fas fa-tasks me-1"></i> Conciliación
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.transferencias') }}">
            <i class="fas fa-exchange-alt me-1"></i> Transferencias
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.reporte') }}">
            <i class="fas fa-file-invoice me-1"></i> Reportes
        </a>
    </nav>

    <!-- Filtros -->
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.mensual') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="mes">Mes</label>
                        <select name="mes" id="mes" class="form-select">
                            @for ($m = 1; $m <= 12; $m++)
                                {{-- CORRECCIÓN (Error 2): $mesSeleccionado existe ahora --}}
                                <option value="{{ $m }}" {{ $mesSeleccionado == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m)->locale('es')->isoFormat('MMMM') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="anio">Año</label>
                        <select name="anio" id="anio" class="form-select">
                            @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ $anioSeleccionado == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Generar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Resumen Mensual por Banco -->
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h6 class="mb-0">Resumen Mensual por Banco</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Banco</th>
                            <th>Cuenta</th>
                            <th class="text-end">Ingresos</th>
                            <th class="text-end">Egresos</th>
                            <th class="text-end">Flujo Neto</th>
                            <th class="text-center">Movs.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resumenMensual as $resumen)
                        <tr>
                            <td>
                                <a href="{{ route('contador.bancos.detalle', $resumen->Cuenta) }}" class="text-decoration-none fw-500">
                                    {{ $resumen->Banco }}
                                </a>
                            </td>
                            <td>{{ $resumen->Cuenta }}</td>
                            <td class="text-end text-success">S/ {{ number_format($resumen->ingresos_mes, 2) }}</td>
                            <td class="text-end text-danger">S/ {{ number_format($resumen->egresos_mes, 2) }}</td>
                            <td class="text-end fw-bold">S/ {{ number_format($resumen->saldo_mes, 2) }}</td>
                            <td class="text-center">{{ $resumen->total_movimientos }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center p-3 text-muted">No hay datos para este mes.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark fw-bold">
                        <tr>
                            <td colspan="2">TOTALES DEL MES</td>
                            <td class="text-end">S/ {{ number_format($totalesMes['total_ingresos'], 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totalesMes['total_egresos'], 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totalesMes['total_ingresos'] - $totalesMes['total_egresos'], 2) }}</td>
                            <td class="text-center">{{ $totalesMes['total_movimientos'] }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

