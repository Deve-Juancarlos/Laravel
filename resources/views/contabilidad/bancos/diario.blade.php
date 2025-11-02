@extends('layouts.app')

@section('title', 'Reporte Diario de Bancos')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-calendar-alt me-2"></i>Reporte Diario de Bancos</h1>
        <p class="text-muted">Movimientos consolidados para la fecha: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Reporte Diario</li>
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
        <a class="nav-link active" href="{{ route('contador.bancos.diario') }}">
            <i class="fas fa-calendar-alt me-1"></i> Reporte Diario
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.mensual') }}">
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
            <form method="GET" action="{{ route('contador.bancos.diario') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-10">
                        <label class="form-label" for="fecha">Seleccionar Fecha</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" value="{{ $fecha }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Ver Día
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Resumen por Banco -->
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h6 class="mb-0">Resumen del Día por Banco</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Banco</th>
                            <th class="text-end">Ingresos</th>
                            <th class="text-end">Egresos</th>
                            <th class="text-end">Neto</th>
                            <th class="text-center">Movs.</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- CORRECCIÓN (Error 1): De $resumen['Banco'] a $resumen->Banco --}}
                        @forelse($resumenPorBanco as $resumen)
                        <tr>
                            <td>
                                <strong>{{ $resumen->Banco }}</strong>
                                <small class="text-muted d-block">Moneda: {{ $resumen->Moneda == 1 ? 'SOLES' : 'DÓLARES' }}</small>
                            </td>
                            <td class="text-end text-success">S/ {{ number_format($resumen->total_ingresos, 2) }}</td>
                            <td class="text-end text-danger">S/ {{ number_format($resumen->total_egresos, 2) }}</td>
                            <td class="text-end fw-bold">S/ {{ number_format($resumen->total_ingresos - $resumen->total_egresos, 2) }}</td>
                            <td class="text-center">{{ $resumen->total_movimientos }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center p-3 text-muted">No hay datos.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark fw-bold">
                        <tr>
                            <td>TOTALES DEL DÍA</td>
                            <td class="text-end">S/ {{ number_format($totalesDiarios['total_ingresos'], 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totalesDiarios['total_egresos'], 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totalesDiarios['total_ingresos'] - $totalesDiarios['total_egresos'], 2) }}</td>
                            <td class="text-center">{{ $totalesDiarios['total_movimientos'] }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

