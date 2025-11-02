@extends('layouts.app')

@section('title', 'Reporte de Vencimientos')
@section('page-title', 'Reporte de Productos por Vencer')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.inventario.index') }}">Inventario</a></li>
    <li class="breadcrumb-item active" aria-current="page">Vencimientos</li>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h5 class="card-title m-0 text-danger"><i class="fas fa-calendar-times me-2"></i>Reporte de Vencimientos</h5>
    </div>
    <div class="card-body">
        
        <div class="alert alert-warning">
            <i class="fas fa-info-circle me-1"></i>
            Este reporte muestra todos los productos en stock (tabla Saldos) que están vencidos o que vencerán en los próximos 6 meses.
        </div>

        <form method="GET" action="{{ route('contador.inventario.vencimientos') }}" class="mb-3">
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="q" class="form-label">Buscar (Producto, Código o Lote)</label>
                    <input type="text" class="form-control" name="q" id="q" value="{{ $filtros['q'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">Todos (hasta 6 meses)</option>
                        <option value="VENCIDO" @selected($filtros['estado'] ?? '' == 'VENCIDO')>VENCIDO</option>
                        <option value="CRÍTICO (30 días)" @selected($filtros['estado'] ?? '' == 'CRÍTICO (30 días)')>Crítico (30 días)</option>
                        <option value="ALERTA (90 días)" @selected($filtros['estado'] ?? '' == 'ALERTA (90 días)')>Alerta (90 días)</option>
                        <option value="PRECAUCIÓN (6 meses)" @selected($filtros['estado'] ?? '' == 'PRECAUCIÓN (6 meses)')>Precaución (6 meses)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 mt-4"><i class="fas fa-filter"></i> Filtrar</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('contador.inventario.vencimientos') }}" class="btn btn-secondary w-100 mt-4">Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th>Laboratorio</th>
                        <th>Lote</th>
                        <th>Vencimiento</th>
                        <th class="text-center">Días</th>
                        <th>Estado</th>
                        <th class="text-end">Stock</th>
                        <th class="text-end">Valorizado (Costo)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                        @php
                            $claseEstado = '';
                            if ($producto->EstadoVencimiento == 'VENCIDO') $claseEstado = 'table-danger';
                            if ($producto->EstadoVencimiento == 'CRÍTICO (30 días)') $claseEstado = 'table-warning';
                        @endphp
                        <tr class="{{ $claseEstado }}">
                            <td>
                                <strong>{{ $producto->Nombre }}</strong>
                                <br><small class="text-muted">{{ $producto->CodPro }}</small>
                            </td>
                            <td>{{ $producto->Laboratorio }}</td>
                            <td>{{ $producto->Lote }}</td>
                            <td><strong>{{ \Carbon\Carbon::parse($producto->Vencimiento)->format('d/m/Y') }}</strong></td>
                            <td class="text-center fw-bold">{{ $producto->DiasParaVencer }}</td>
                            <td>
                                <span class="badge 
                                    @if($producto->EstadoVencimiento == 'VENCIDO') bg-danger 
                                    @elseif($producto->EstadoVencimiento == 'CRÍTICO (30 días)') bg-warning text-dark
                                    @elseif($producto->EstadoVencimiento == 'ALERTA (90 días)') bg-info text-dark
                                    @else bg-secondary @endif">
                                    {{ $producto->EstadoVencimiento }}
                                </span>
                            </td>
                            <td class="text-end fw-bold">{{ number_format($producto->Stock, 2) }}</td>
                            <td class="text-end text-danger">S/ {{ number_format($producto->ValorInventario, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center p-4 text-muted">No se encontraron productos vencidos o por vencer.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            {{ $productos->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection