@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Registro de Caja')

@section('content')
<div class="container-fluid">
    {{-- Encabezado y filtros --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-cash-register"></i> Registro de Caja
                    </h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('contador.registros.caja') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" 
                                   value="{{ $fechaInicio }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" 
                                   value="{{ $fechaFin }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Movimientos</h6>
                            <h3 class="mb-0">{{ number_format($totales->total_movimientos ?? 0) }}</h3>
                        </div>
                        <i class="fas fa-exchange-alt fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Ingresos</h6>
                            <h3 class="mb-0">S/ {{ number_format($totales->total_ingresos ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-arrow-down fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Egresos</h6>
                            <h3 class="mb-0">S/ {{ number_format($totales->total_egresos ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-arrow-up fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Saldo</h6>
                            <h3 class="mb-0">S/ {{ number_format($totales->saldo ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-wallet fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de movimientos --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Movimientos de Caja</h4>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-success" disabled>
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </button>
                        <button class="btn btn-sm btn-danger" disabled>
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Número</th>
                                    <th>Documento</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th class="text-end">Monto</th>
                                    <th>Moneda</th>
                                    <th class="text-center">Asiento</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movimientosCaja as $movimiento)
                                <tr>
                                    <td>
                                        <strong>{{ $movimiento->Numero }}</strong>
                                    </td>
                                    <td>{{ $movimiento->Documento ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($movimiento->Fecha)->format('d/m/Y') }}</td>
                                    <td>
                                        @if(isset($movimiento->tipo_descripcion))
                                            <span class="badge bg-secondary">
                                                {{ $movimiento->tipo_descripcion }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Tipo {{ $movimiento->Tipo }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong>S/ {{ number_format($movimiento->Monto, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge {{ $movimiento->Moneda == 1 ? 'bg-success' : 'bg-info' }}">
                                            {{ $movimiento->Moneda == 1 ? 'PEN' : 'USD' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($movimiento->asiento_id)
                                            <span class="badge bg-primary" title="Asiento N° {{ $movimiento->asiento_id }}">
                                                <i class="fas fa-check"></i> {{ $movimiento->asiento_id }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-times"></i> Sin asiento
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>No hay movimientos de caja en el período seleccionado</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end"><strong>TOTALES:</strong></td>
                                    <td class="text-end">
                                        <strong>S/ {{ number_format($totales->saldo ?? 0, 2) }}</strong>
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    {{-- Paginación --}}
                    <div class="mt-3">
                        {{ $movimientosCaja->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Resúmenes adicionales --}}
    @if(isset($resumenPorMoneda) && $resumenPorMoneda->count() > 0)
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-coins"></i> Resumen por Moneda
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Moneda</th>
                                    <th class="text-center">Movimientos</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resumenPorMoneda as $resumen)
                                <tr>
                                    <td>
                                        <span class="badge {{ $resumen->Moneda == 1 ? 'bg-success' : 'bg-info' }}">
                                            {{ $resumen->Moneda == 1 ? 'SOLES (PEN)' : 'DÓLARES (USD)' }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $resumen->cantidad }}</td>
                                    <td class="text-end">
                                        <strong>{{ number_format($resumen->total, 2) }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($resumenPorTipo) && $resumenPorTipo->count() > 0)
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Resumen por Tipo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resumenPorTipo as $tipo)
                                <tr>
                                    <td>{{ $tipo->tipo_descripcion ?? 'Tipo ' . $tipo->Tipo }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $tipo->cantidad }}</span>
                                    </td>
                                    <td class="text-end">
                                        <strong>S/ {{ number_format($tipo->total, 2) }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .opacity-50 {
        opacity: 0.5;
    }
    .card {
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }
    .card-tools {
        float: right;
    }
</style>
@endpush
