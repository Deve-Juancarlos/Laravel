@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Registro de Ventas')

@section('content')
<div class="container-fluid">
    {{-- Encabezado y filtros --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-invoice"></i> Registro de Ventas
                    </h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('contador.registros.ventas') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" 
                                   value="{{ $fechaInicio }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" 
                                   value="{{ $fechaFin }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cliente</label>
                            <input type="text" name="cliente" class="form-control" 
                                   value="{{ $cliente ?? '' }}" placeholder="Buscar cliente...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
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
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Documentos</h6>
                            <h3 class="mb-0">{{ number_format($totales->total_documentos ?? 0) }}</h3>
                        </div>
                        <i class="fas fa-file-alt fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Subtotal</h6>
                            <h3 class="mb-0">S/ {{ number_format($totales->total_subtotal ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-coins fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">IGV</h6>
                            <h3 class="mb-0">S/ {{ number_format($totales->total_igv ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-percentage fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total General</h6>
                            <h3 class="mb-0">S/ {{ number_format($totales->total_general ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de ventas --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Detalle de Ventas</h4>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-success" disabled title="Próximamente">
                            <i class="fas fa-file-excel"></i> Exportar SUNAT
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Número</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>RUC/DNI</th>
                                    <th>Vendedor</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">IGV</th>
                                    <th class="text-end">Total</th>
                                    <th>Moneda</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventas as $venta)
                                <tr>
                                    <td>
                                        <strong>{{ $venta->Numero }}</strong>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($venta->Fecha)->format('d/m/Y') }}</td>
                                    <td>{{ $venta->cliente }}</td>
                                    <td>{{ $venta->documento_cliente }}</td>
                                    <td>{{ $venta->vendedor }}</td>
                                    <td class="text-end">S/ {{ number_format($venta->Subtotal, 2) }}</td>
                                    <td class="text-end">S/ {{ number_format($venta->Igv, 2) }}</td>
                                    <td class="text-end">
                                        <strong>S/ {{ number_format($venta->Total, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge {{ $venta->Moneda == 1 ? 'bg-success' : 'bg-info' }}">
                                            {{ $venta->Moneda == 1 ? 'PEN' : 'USD' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>No hay ventas registradas en el período seleccionado</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Paginación --}}
                    <div class="mt-3">
                        {{ $ventas->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Clientes --}}
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users"></i> Top 10 Clientes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Documento</th>
                                    <th class="text-end">Total Ventas</th>
                                    <th class="text-center">Docs</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topClientes as $index => $cliente)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $cliente->cliente }}</td>
                                    <td>{{ $cliente->documento }}</td>
                                    <td class="text-end">
                                        <strong>S/ {{ number_format($cliente->total_ventas, 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $cliente->cantidad_documentos }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rendimiento por Vendedor --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-tie"></i> Rendimiento por Vendedor
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Vendedor</th>
                                    <th class="text-end">Total Ventas</th>
                                    <th class="text-center">Docs</th>
                                    <th class="text-end">Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rendimientoVendedores as $vendedor)
                                <tr>
                                    <td>{{ $vendedor->vendedor }}</td>
                                    <td class="text-end">
                                        <strong>S/ {{ number_format($vendedor->total_ventas, 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $vendedor->cantidad_documentos }}</span>
                                    </td>
                                    <td class="text-end">
                                        S/ {{ number_format($vendedor->promedio_venta, 2) }}
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

    {{-- Accesos rápidos a otros reportes --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Reportes Adicionales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                           {{-- <a href="{{ route('contador.registros.ventas.resumen-diario') }}" 
                               class="btn btn-outline-primary w-100">
                                <i class="fas fa-calendar-day"></i> Resumen Diario
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('contador.registros.ventas.resumen-mensual') }}" 
                               class="btn btn-outline-success w-100">
                                <i class="fas fa-calendar-alt"></i> Resumen Mensual
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('contador.registros.ventas.por-cliente') }}" 
                               class="btn btn-outline-info w-100">
                                <i class="fas fa-users"></i> Por Cliente
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('contador.registros.ventas.tendencias') }}" 
                               class="btn btn-outline-warning w-100">
                                <i class="fas fa-chart-line"></i> Tendencias
                            </a>--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
</style>
@endpush
