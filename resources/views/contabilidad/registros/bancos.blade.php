@extends('layouts.app')

@section('title', 'Libro de Bancos')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                         <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('contador.bancos.reporte') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-list"></i> Reporte General
                            </a>
                            <a href="{{ route('contador.bancos.diario') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-calendar-day"></i> Diario
                            </a>
                            <a href="{{ route('contador.bancos.conciliacion') }}" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-check-double"></i> Conciliación
                            </a>
                            <a href="{{ route('contador.bancos.transferencias') }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-exchange-alt"></i> Transferencias
                            </a>
                            <a href="#" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel"></i> Exportar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-filter"></i> Filtros de Búsqueda
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('contador.bancos.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" 
                                   value="{{ $fechaInicio }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" 
                                   value="{{ $fechaFin }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Cuenta Bancaria</label>
                            <select name="cuenta" class="form-select">
                                <option value="">Todas las cuentas</option>
                                @foreach($listaBancos as $banco)
                                    <option value="{{ $banco->Cuenta }}" 
                                            {{ $cuenta == $banco->Cuenta ? 'selected' : '' }}>
                                        {{ $banco->Banco }} - {{ $banco->Cuenta }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Saldos Actuales -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-wallet"></i> Saldos Actuales por Cuenta
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Banco</th>
                                    <th>Moneda</th>
                                    <th class="text-end">Total Ingresos</th>
                                    <th class="text-end">Total Egresos</th>
                                    <th class="text-end">Saldo Actual</th>
                                    <th class="text-center">Movimientos</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($saldosActuales as $saldo)
                                <tr>
                                    <td><code>{{ $saldo->Cuenta }}</code></td>
                                    <td>{{ $saldo->Banco }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $saldo->Moneda == 1 ? 'S/.' : '$' }}
                                        </span>
                                    </td>
                                    <td class="text-end text-success fw-bold">
                                        {{ number_format($saldo->total_ingresos, 2) }}
                                    </td>
                                    <td class="text-end text-danger fw-bold">
                                        {{ number_format($saldo->total_egresos, 2) }}
                                    </td>
                                    <td class="text-end fw-bold {{ $saldo->saldo_actual >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($saldo->saldo_actual, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $saldo->total_movimientos }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('contador.bancos.detalle', $saldo->Cuenta) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>No hay cuentas bancarias registradas</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen del Período -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Ingresos</h6>
                            <h4 class="text-success mb-0">
                                S/. {{ number_format($totalesPeriodo->total_ingresos ?? 0, 2) }}
                            </h4>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-arrow-up fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Egresos</h6>
                            <h4 class="text-danger mb-0">
                                S/. {{ number_format($totalesPeriodo->total_egresos ?? 0, 2) }}
                            </h4>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-arrow-down fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Saldo Neto</h6>
                            <h4 class="text-info mb-0">
                                S/. {{ number_format(($totalesPeriodo->total_ingresos ?? 0) - ($totalesPeriodo->total_egresos ?? 0), 2) }}
                            </h4>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-balance-scale fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Movimientos</h6>
                            <h4 class="text-dark mb-0">
                                {{ number_format($totalesPeriodo->total_movimientos ?? 0) }}
                            </h4>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-exchange-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Movimientos Bancarios -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                        <i class="fas fa-list"></i> Movimientos Bancarios
                        <span class="badge bg-secondary ms-2">{{ $movimientosBancarios->total() }}</span>
                    </h5>
                    <div>
                        <button class="btn btn-sm btn-outline-success" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Número</th>
                                    <th>Cuenta</th>
                                    <th>Banco</th>
                                    <th>Tipo</th>
                                    <th>Clase</th>
                                    <th>Documento</th>
                                    <th>Referencia</th>
                                    <th class="text-end">Ingreso</th>
                                    <th class="text-end">Egreso</th>
                                    <th class="text-center">Moneda</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movimientosBancarios as $movimiento)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($movimiento->Fecha)->format('d/m/Y') }}</td>
                                    <td><code>{{ $movimiento->Numero }}</code></td>
                                    <td>{{ $movimiento->Cuenta }}</td>
                                    <td>{{ $movimiento->Banco }}</td>
                                    <td>
                                        <span class="badge {{ $movimiento->tipo_movimiento == 'INGRESO' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $movimiento->tipo_movimiento }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $movimiento->tipo_operacion }}
                                        </span>
                                    </td>
                                    <td>{{ $movimiento->Documento }}</td>
                                    <td>{{ $movimiento->Referencia }}</td>
                                    <td class="text-end text-success fw-bold">
                                        {{ $movimiento->ingreso > 0 ? number_format($movimiento->ingreso, 2) : '-' }}
                                    </td>
                                    <td class="text-end text-danger fw-bold">
                                        {{ $movimiento->egreso > 0 ? number_format($movimiento->egreso, 2) : '-' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $movimiento->Moneda == 1 ? 'bg-primary' : 'bg-success' }}">
                                            {{ $movimiento->Moneda == 1 ? 'PEN' : 'USD' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">
                                        <i class="fas fa-search fa-2x mb-2"></i>
                                        <p>No se encontraron movimientos en el período seleccionado</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Mostrando {{ $movimientosBancarios->firstItem() ?? 0 }} a 
                            {{ $movimientosBancarios->lastItem() ?? 0 }} de 
                            {{ $movimientosBancarios->total() }} registros
                        </div>
                        <div>
                            {{ $movimientosBancarios->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen por Cuenta del Período -->
    @if($resumenCuentas->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Resumen por Cuenta (Período Seleccionado)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Banco</th>
                                    <th>Moneda</th>
                                    <th class="text-end">Total Ingresos</th>
                                    <th class="text-end">Total Egresos</th>
                                    <th class="text-end">Saldo Período</th>
                                    <th class="text-center">Movimientos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resumenCuentas as $resumen)
                                <tr>
                                    <td><code>{{ $resumen->Cuenta }}</code></td>
                                    <td>{{ $resumen->Banco }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info">
                                            {{ $resumen->Moneda == 1 ? 'S/.' : '$' }}
                                        </span>
                                    </td>
                                    <td class="text-end text-success">
                                        {{ number_format($resumen->total_ingresos, 2) }}
                                    </td>
                                    <td class="text-end text-danger">
                                        {{ number_format($resumen->total_egresos, 2) }}
                                    </td>
                                    <td class="text-end fw-bold {{ ($resumen->total_ingresos - $resumen->total_egresos) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($resumen->total_ingresos - $resumen->total_egresos, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $resumen->total_movimientos }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }
    
    @media print {
        .btn, .card-header, .pagination {
            display: none !important;
        }
        .card {
            border: 1px solid #000 !important;
        }
    }
</style>
@endpush
@endsection