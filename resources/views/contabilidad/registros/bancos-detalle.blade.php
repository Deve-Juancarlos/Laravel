@extends('layouts.app')

@section('title', 'Detalle Cuenta Bancaria')

@section('content')
<div class="container-fluid py-4">
    <!-- Header con Info de Cuenta -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-1">
                                <i class="fas fa-university text-primary"></i>
                                {{ $infoCuenta->Banco }}
                            </h3>
                            <p class="text-muted mb-2">
                                <strong>Cuenta:</strong> <code class="fs-5">{{ $infoCuenta->Cuenta }}</code>
                            </p>
                            <p class="mb-0">
                                <span class="badge bg-{{ $infoCuenta->Moneda == 1 ? 'primary' : 'success' }} me-2">
                                    {{ $infoCuenta->Moneda == 1 ? 'Soles (PEN)' : 'Dólares (USD)' }}
                                </span>
                                <span class="badge bg-info">
                                    {{ $infoCuenta->total_movimientos }} movimientos totales
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <h6 class="text-muted mb-1">Saldo Actual</h6>
                                    <h2 class="mb-0 {{ $infoCuenta->saldo_actual >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $infoCuenta->Moneda == 1 ? 'S/.' : '$' }}
                                        {{ number_format($infoCuenta->saldo_actual, 2) }}
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Fecha -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('contador.bancos.detalle', $infoCuenta->Cuenta) }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" 
                                   value="{{ $fechaInicio }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" 
                                   value="{{ $fechaFin }}" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen del Período -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Saldo Inicial</h6>
                    <h4 class="text-info mb-0">
                        {{ number_format($saldoAnterior, 2) }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Ingresos</h6>
                    <h4 class="text-success mb-0">
                        {{ number_format($totalesPeriodo['ingresos'], 2) }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Egresos</h6>
                    <h4 class="text-danger mb-0">
                        {{ number_format($totalesPeriodo['egresos'], 2) }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Saldo Final</h6>
                    <h4 class="text-primary mb-0">
                        {{ number_format($totalesPeriodo['saldo_final'], 2) }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Movimientos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Movimientos del Período
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Número</th>
                                    <th>Tipo</th>
                                    <th>Clase</th>
                                    <th>Documento</th>
                                    <th>Referencia</th>
                                    <th class="text-end">Ingreso</th>
                                    <th class="text-end">Egreso</th>
                                    <th class="text-end">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $saldoAcumulado = $saldoAnterior; @endphp
                                @forelse($movimientos as $mov)
                                @php
                                    $saldoAcumulado += $mov->ingreso - $mov->egreso;
                                @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($mov->Fecha)->format('d/m/Y') }}</td>
                                    <td><code>{{ $mov->Numero }}</code></td>
                                    <td>
                                        <span class="badge {{ $mov->tipo_movimiento == 'INGRESO' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $mov->tipo_movimiento }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $mov->tipo_operacion }}</small>
                                    </td>
                                    <td>{{ $mov->Documento }}</td>
                                    <td>{{ $mov->Referencia }}</td>
                                    <td class="text-end text-success fw-bold">
                                        {{ $mov->ingreso > 0 ? number_format($mov->ingreso, 2) : '-' }}
                                    </td>
                                    <td class="text-end text-danger fw-bold">
                                        {{ $mov->egreso > 0 ? number_format($mov->egreso, 2) : '-' }}
                                    </td>
                                    <td class="text-end fw-bold {{ $saldoAcumulado >= 0 ? 'text-primary' : 'text-danger' }}">
                                        {{ number_format($saldoAcumulado, 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>No hay movimientos en este período</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $movimientos->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen Mensual -->
    @if($resumenMensual->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> Resumen Mensual
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Mes/Año</th>
                                    <th class="text-end">Ingresos</th>
                                    <th class="text-end">Egresos</th>
                                    <th class="text-end">Saldo Neto</th>
                                    <th class="text-center">Movimientos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resumenMensual as $mes)
                                <tr>
                                    <td>
                                        <strong>
                                            {{ DateTime::createFromFormat('!m', $mes->mes)->format('F') }} 
                                            {{ $mes->anio }}
                                        </strong>
                                    </td>
                                    <td class="text-end text-success">
                                        {{ number_format($mes->ingresos_mes, 2) }}
                                    </td>
                                    <td class="text-end text-danger">
                                        {{ number_format($mes->egresos_mes, 2) }}
                                    </td>
                                    <td class="text-end fw-bold {{ ($mes->ingresos_mes - $mes->egresos_mes) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($mes->ingresos_mes - $mes->egresos_mes, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $mes->total_movimientos }}</span>
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

    <!-- Botón Volver -->
    <div class="row mt-4">
        <div class="col-12">
            <a href="{{ route('contador.bancos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Libro de Bancos
            </a>
        </div>
    </div>
</div>
@endsection