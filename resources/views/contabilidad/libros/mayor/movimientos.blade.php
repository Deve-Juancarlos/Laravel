@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Movimientos - Libro Mayor')

@push('styles')
    <link href="{{ asset('css/contabilidad/libro-mayor-movimientos.css') }}" rel="stylesheet">
@endpush

{{-- 1. Título de la Página --}}
@section('page-title', 'Movimientos del Mayor')

{{-- 2. Breadcrumbs --}}
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.libro-mayor.index') }}">Libro Mayor</a></li>
    <li class="breadcrumb-item active" aria-current="page">Movimientos</li>
@endsection

{{-- 3. Contenido Principal --}}
@section('content')
<div class="container-fluid">

    {{-- Estadísticas --}}
    <div class="row mb-4 stats-grid">
        <div class="col-md-3">
            <div class="stat-card shadow-sm">
                <div class="stat-value">{{ number_format($totales['count'] ?? 0) }}</div>
                <div class="stat-label">Total Movimientos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm">
                <div class="stat-value">S/ {{ number_format($totales['debe'] ?? 0, 2) }}</div>
                <div class="stat-label">Total Debe</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm">
                <div class="stat-value">S/ {{ number_format($totales['haber'] ?? 0, 2) }}</div>
                <div class="stat-label">Total Haber</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm">
                <div class="stat-value">S/ {{ number_format(($totales['debe'] ?? 0) - ($totales['haber'] ?? 0), 2) }}</div>
                <div class="stat-label">Diferencia</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.libro-mayor.movimientos') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" 
                               value="{{ $fechaInicio ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_fin">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" 
                               value="{{ $fechaFin ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="cuenta">Cuenta Contable</label>
                        <input type="text" name="cuenta" id="cuenta" class="form-control" 
                               placeholder="Ej: 101, 201..."
                               value="{{ $cuenta ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="mes">Mes</label>
                        <select name="mes" id="mes" class="form-select">
                            <option value="">Todos los meses</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ ($mes ?? '') == $i ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($i)->locale('es')->isoFormat('MMMM') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <a href="{{ route('contador.libro-mayor.movimientos') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="card shadow-sm">
        <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Movimientos Contables
            </h6>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-info-soft">Página {{ $movimientos->currentPage() }} de {{ $movimientos->lastPage() }}</span>
                <a href="{{ route('contador.libro-mayor.exportar', array_merge(request()->query(), ['tipo' => 'detallado'])) }}" class="btn btn-export btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Exportar Detalle
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="dataTable">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Asiento</th>
                            <th>Cuenta</th>
                            <th>Concepto</th>
                            <th class="text-end">Debe</th>
                            <th class="text-end">Haber</th>
                            <th class="text-end">Saldo Mov.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos ?? [] as $movimiento)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('contador.libro-diario.show', $movimiento->asiento_id) }}" class="badge bg-primary-soft text-primary text-decoration-none">
                                    {{ $movimiento->numero }}
                                </a>
                            </td>
                            <td>
                                <strong>{{ $movimiento->cuenta_contable }}</strong>
                                <br>
                                <small class="text-muted">{{ $movimiento->nombre_cuenta ?? 'Sin nombre' }}</small>
                            </td>
                            <td>{{ Str::limit($movimiento->concepto, 50) }}</td>
                            <td class="text-end text-success">
                                {{ $movimiento->debe > 0 ? number_format($movimiento->debe, 2) : '-' }}
                            </td>
                            <td class="text-end text-danger">
                                {{ $movimiento->haber > 0 ? number_format($movimiento->haber, 2) : '-' }}
                            </td>
                            <td class="text-end">
                                <strong class="{{ ($movimiento->debe - $movimiento->haber) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($movimiento->debe - $movimiento->haber, 2) }}
                                </strong>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center p-5">
                                <div class="text-muted">
                                    <i class="fas fa-search fa-3x mb-3"></i>
                                    <p class="h5">No se encontraron movimientos</p>
                                    <p>Intenta ajustar los filtros de búsqueda.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(($movimientos ?? collect())->count() > 0)
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="4">TOTALES DE LA PÁGINA</td>
                            <td class="text-end text-success">S/ {{ number_format($movimientos->sum('debe'), 2) }}</td>
                            <td class="text-end text-danger">S/ {{ number_format($movimientos->sum('haber'), 2) }}</td>
                            <td class="text-end">S/ {{ number_format($movimientos->sum('debe') - $movimientos->sum('haber'), 2) }}</td>
                        </tr>
                        <tr class="fw-bold table-dark">
                            <td colspan="4">TOTALES FILTRADOS (TODAS LAS PÁGINAS)</td>
                            <td class="text-end">S/ {{ number_format($totales['debe'], 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totales['haber'], 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totales['debe'] - $totales['haber'], 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <!-- Paginación -->
            @if(($movimientos ?? collect())->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $movimientos->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Resumen Mensual -->
    @if(($resumenMensual ?? collect())->count() > 0)
    <div class="card shadow-sm mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-calendar-alt"></i> Resumen Mensual (Período Filtrado)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Mes/Año</th>
                            <th class="text-end">Total Debe</th>
                            <th class="text-end">Total Haber</th>
                            <th class="text-end">Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resumenMensual ?? [] as $resumen)
                        <tr>
                            <td>
                                <strong>
                                    {{ \Carbon\Carbon::createFromDate($resumen->anio, $resumen->mes_numero, 1)->locale('es')->isoFormat('MMMM YYYY') }}
                                </strong>
                            </td>
                            <td class="text-end text-success">
                                S/ {{ number_format($resumen->total_debe ?? 0, 2) }}
                            </td>
                            <td class="text-end text-danger">
                                S/ {{ number_format($resumen->total_haber ?? 0, 2) }}
                            </td>
                            <td class="text-end fw-bold">
                                S/ {{ number_format(($resumen->total_debe ?? 0) - ($resumen->total_haber ?? 0), 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
{{-- Scripts específicos si fueran necesarios --}}
@endpush
