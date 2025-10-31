@extends('layouts.app')

@section('title', "Detalle Cuenta - {$cuentaMostrar}")

@push('styles')
    <link href="{{ asset('css/contabilidad/estado-resultados.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-file-invoice-dollar me-2"></i>Detalle de Cuenta</h1>
        <p class="text-muted">Movimientos para la cuenta {{ $cuentaMostrar }}</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.estado-resultados.index') }}">Estado de Resultados</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detalle Cuenta</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filtros -->
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.estado-resultados.detalle', $cuentaMostrar) }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_fin">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="busqueda">Buscar en Concepto</label>
                        <input type="text" name="busqueda" id="busqueda" class="form-control" placeholder="Ej: Ventas, Sueldos..." value="{{ request('busqueda') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tarjeta de Cuenta -->
    <div class="card shadow-sm mb-4">
        <div class="card-header {{ $clasificacion == 'INGRESO' ? 'bg-success text-white' : 'bg-danger text-white' }}">
            <h5 class="mb-0">Cuenta: {{ $cuentaMostrar }} ({{ $clasificacion ?? 'N/A' }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Asiento</th>
                            <th>Cuenta</th>
                            <th>Concepto</th>
                            <th class="text-end">Débito (S/)</th>
                            <th class="text-end">Crédito (S/)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('contador.libro-diario.show', $mov->asiento_id) }}" target="_blank" class="badge bg-primary-soft text-primary text-decoration-none">
                                    {{ $mov->numero }}
                                </a>
                            </td>
                            <td><strong>{{ $mov->cuenta_contable }}</strong></td>
                            <td>{{ $mov->concepto }}</td>
                            <td class="text-end text-danger">{{ $mov->debito > 0 ? 'S/ ' . number_format($mov->debito, 2) : '-' }}</td>
                            <td class="text-end text-success">{{ $mov->credito > 0 ? 'S/ ' . number_format($mov->credito, 2) : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center p-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="mb-0">No se encontraron movimientos</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark fw-bold">
                        <tr>
                            <td colspan="4">TOTALES</td>
                            <td class="text-end">S/ {{ number_format($movimientos->sum('debito'), 2) }}</td>
                            <td class="text-end">S/ {{ number_format($movimientos->sum('credito'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
