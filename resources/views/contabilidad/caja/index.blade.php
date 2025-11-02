@extends('layouts.app')

@section('title', 'Caja Chica')

@push('styles')
    <link href="{{ asset('css/contabilidad/caja.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-cash-register me-2"></i>Caja Chica</h1>
        <p class="text-muted">Movimientos de efectivo (ingresos y egresos).</p>
    </div>
    <a href="{{ route('contador.caja.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nuevo Movimiento
    </a>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item active" aria-current="page">Caja</li>
@endsection

@section('content')
<div class="container-fluid">

    <!-- Filtros -->
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.caja.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_fin">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="tipo_movimiento">Tipo</label>
                        <select name="tipo_movimiento" id="tipo_movimiento" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ $tipoMovimiento == '1' ? 'selected' : '' }}>Ingreso</option>
                            <option value="2" {{ $tipoMovimiento == '2' ? 'selected' : '' }}>Egreso</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row mb-4 stats-grid">
        <div class="col-md-3">
            <div class="stat-card shadow-sm primary">
                <i class="fas fa-calendar-alt"></i>
                <div class="stat-info">
                    <p class="stat-label">Saldo Inicial</p>
                    <div class="stat-value">S/ {{ number_format($saldoInicial, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm success">
                <i class="fas fa-arrow-down"></i>
                <div class="stat-info">
                    <p class="stat-label">Ingresos Período</p>
                    <div class="stat-value">S/ {{ number_format($totalesPeriodo->ingresos, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm danger">
                <i class="fas fa-arrow-up"></i>
                <div class="stat-info">
                    <p class="stat-label">Egresos Período</p>
                    <div class="stat-value">S/ {{ number_format($totalesPeriodo->egresos, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm info">
                <i class="fas fa-wallet"></i>
                <div class="stat-info">
                    <p class="stat-label">Saldo Final</p>
                    <div class="stat-value">S/ {{ number_format($saldoFinal, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="card shadow-sm table-container">
        <div class="card-header table-header">
            <h5 class="table-title mb-0">Movimientos de Caja</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Documento</th>
                            <th>Razón / Contrapartida</th>
                            <th class="text-end">Monto</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                        <tr class="{{ $mov->Eliminado ? 'table-danger opacity-50' : '' }}">
                            <td>{{ \Carbon\Carbon::parse($mov->Fecha)->format('d/m/Y') }}</td>
                            <td>
                                @if($mov->Tipo == 1)
                                    <span class="badge bg-success-soft text-success">Ingreso</span>
                                @else
                                    <span class="badge bg-danger-soft text-danger">Egreso</span>
                                @endif
                            </td>
                            <td>{{ $mov->Documento }}</td>
                            <td>{{ $mov->razon_descripcion ?? 'N/A' }}</td>
                            <td class="text-end fw-bold">{{ number_format($mov->Monto, 2) }}</td>
                            <td class="text-center">
                                @if($mov->Eliminado)
                                    <span class="badge bg-danger">Anulado</span>
                                @else
                                    <span class="badge bg-success">Activo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('contador.caja.show', $mov->Numero) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('contador.caja.edit', $mov->Numero) }}" class="btn btn-sm btn-outline-secondary {{ $mov->Eliminado ? 'disabled' : '' }}" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center p-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3"></i>
                                <h5 class="mb-0">No se encontraron movimientos.</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($movimientos->hasPages())
            <div class="card-footer pagination-wrapper">
                {{ $movimientos->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

