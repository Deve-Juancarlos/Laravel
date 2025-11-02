@extends('layouts.app')
@section('title', 'Stock y Lotes')
@section('page-title', 'Reporte de Stock y Lotes')
@section('breadcrumbs')
    <li class="breadcrumb-item">Inventario</li>
    <li class="breadcrumb-item active" aria-current="page">Stock y Lotes</li>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h5 class="card-title m-0">Stock Detallado por Lote (Tabla Saldos)</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('contador.inventario.stock') }}" class="mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="q" placeholder="Buscar por Producto, Código o Lote..." value="{{ $filtros['q'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filtrar</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('contador.inventario.stock') }}" class="btn btn-secondary w-100">Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Vencimiento</th>
                        <th class="text-center">Almacén</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lotes as $lote)
                        @php
                            $diasVencer = $lote->vencimiento ? \Carbon\Carbon::parse($lote->vencimiento)->diffInDays(now(), false) : null;
                            $claseVencido = '';
                            if ($diasVencer > 0) $claseVencido = 'table-danger'; // Vencido
                            elseif ($diasVencer > -90) $claseVencido = 'table-warning'; // Por vencer
                        @endphp
                        <tr class="{{ $claseVencido }}">
                            <td><code>{{ $lote->codpro }}</code></td>
                            <td><strong>{{ $lote->Nombre }}</strong></td>
                            <td>{{ $lote->lote }}</td>
                            <td>
                                {{ $lote->vencimiento ? \Carbon\Carbon::parse($lote->vencimiento)->format('d/m/Y') : 'N/A' }}
                                @if($diasVencer > 0)
                                    <span class="badge bg-danger ms-1">Vencido</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $lote->almacen }}</td>
                            <td class="text-end fw-bold">{{ number_format($lote->saldo, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-4 text-muted">No se encontró stock detallado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end">
            {{ $lotes->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection