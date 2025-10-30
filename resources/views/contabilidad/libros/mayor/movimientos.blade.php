@extends('layouts.app')

@section('title', 'Movimientos - Libro Mayor')
@php use Carbon\Carbon; @endphp
@section('styles')
<style>
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 20px;
        color: white;
        margin-bottom: 20px;
    }
    .stat-value {
        font-size: 2.2em;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .stat-label {
        opacity: 0.9;
        font-size: 0.9em;
    }
    .table-responsive {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .btn-export {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        transition: transform 0.3s ease;
    }
    .btn-export:hover {
        transform: translateY(-2px);
        color: white;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('contador.libro-mayor.index') }}">Libro Mayor</a></li>
                    <li class="breadcrumb-item active">Movimientos</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 text-gray-800">
                <i class="fas fa-exchange-alt text-primary"></i>
                Movimientos por Cuenta
            </h1>
            <p class="text-muted">Listado completo de movimientos contables con filtros avanzados</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('contador.libro-mayor.exportar') }}" class="btn btn-export">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($movimientos->total() ?? 0) }}</div>
                <div class="stat-label">Total Movimientos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">S/ {{ number_format($movimientos->sum('debe') ?? 0, 2) }}</div>
                <div class="stat-label">Total Debe</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">S/ {{ number_format($movimientos->sum('haber') ?? 0, 2) }}</div>
                <div class="stat-label">Total Haber</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">S/ {{ number_format(($movimientos->sum('debe') ?? 0) - ($movimientos->sum('haber') ?? 0), 2) }}</div>
                <div class="stat-label">Diferencia</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter"></i> Filtros de Búsqueda
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('contador.libro-mayor.movimientos') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" 
                                       value="{{ $fechaInicio ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control" 
                                       value="{{ $fechaFin ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cuenta Contable</label>
                                <input type="text" name="cuenta" class="form-control" 
                                       placeholder="Ej: 101.11, 201.11"
                                       value="{{ $cuenta ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mes</label>
                                <select name="mes" class="form-select">
                                    <option value="">Todos los meses</option>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ ($mes ?? '') == $i ? 'selected' : '' }}>
                                            {{ Carbon::create()->month($i)->locale('es')->isoFormat('MMMM') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="{{ route('contador.libro-mayor.movimientos') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table"></i> Movimientos Contables
                    </h6>
                    <span class="badge bg-info">Página {{ $movimientos->currentPage() }} de {{ $movimientos->lastPage() }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="dataTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Asiento</th>
                                    <th>Cuenta</th>
                                    <th>Concepto</th>
                                    <th class="text-end">Debe</th>
                                    <th class="text-end">Haber</th>
                                    <th class="text-end">Diferencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movimientos ?? [] as $movimiento)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $movimiento->numero }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $movimiento->cuenta_contable }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $movimiento->nombre_cuenta ?? 'Sin nombre' }}</small>
                                    </td>
                                    <td>{{ Str::limit($movimiento->concepto, 50) }}</td>
                                    <td class="text-end text-success">
                                        <strong>{{ number_format($movimiento->debe ?? 0, 2) }}</strong>
                                    </td>
                                    <td class="text-end text-danger">
                                        <strong>{{ number_format($movimiento->haber ?? 0, 2) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <strong class="{{ (($movimiento->debe ?? 0) - ($movimiento->haber ?? 0)) >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format(($movimiento->debe ?? 0) - ($movimiento->haber ?? 0), 2) }}
                                        </strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-search fa-3x mb-3"></i>
                                            <p>No se encontraron movimientos en el período seleccionado</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if(($movimientos ?? collect())->count() > 0)
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td colspan="4">TOTALES</td>
                                    <td class="text-end text-success">S/ {{ number_format($movimientos->sum('debe') ?? 0, 2) }}</td>
                                    <td class="text-end text-danger">S/ {{ number_format($movimientos->sum('haber') ?? 0, 2) }}</td>
                                    <td class="text-end">S/ {{ number_format(($movimientos->sum('debe') ?? 0) - ($movimientos->sum('haber') ?? 0), 2) }}</td>
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
        </div>
    </div>

    <!-- Resumen Mensual -->
    @if(($resumenMensual ?? collect())->count() > 0)
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt"></i> Resumen Mensual
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
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
                                        {{ \Carbon\Carbon::createFromDate($resumen->anio, $resumen->mes_numero, 1)
                                            ->locale('es_ES')  {{-- Español --}}
                                            ->isoFormat('MMMM') 
                                        }} {{ $resumen->anio }}
                                        </strong>

                                    </td>
                                    <td class="text-end text-success">
                                        S/ {{ number_format($resumen->total_debe ?? 0, 2) }}
                                    </td>
                                    <td class="text-end text-danger">
                                        S/ {{ number_format($resumen->total_haber ?? 0, 2) }}
                                    </td>
                                    <td class="text-end">
                                        S/ {{ number_format(($resumen->total_debe ?? 0) - ($resumen->total_haber ?? 0), 2) }}
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
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-submit del formulario cuando cambien los filtros
    $('select[name="mes"]').change(function() {
        $(this).closest('form').submit();
    });
    
    // Formato de números
    $('.number-format').each(function() {
        var value = parseFloat($(this).text());
        if (!isNaN(value)) {
            $(this).text('S/ ' + value.toLocaleString('es-PE', {minimumFractionDigits: 2}));
        }
    });
});
</script>
@endsection