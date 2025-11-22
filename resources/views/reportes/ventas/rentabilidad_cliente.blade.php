@use('Illuminate\Support\Str')
@extends('layouts.app') 

@section('title', 'Reporte de Rentabilidad por Cliente')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('contador.reportes.index') }}">Reportes</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Rentabilidad(Ventas)</li>
@endsection

@push('styles')
    <link href="{{ asset('css/contabilidad/reportes/ventas/rentabilidad_cliente.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="rentabilidad-container">

    {{-- =========== NAVEGACIÓN =========== --}}
    <nav class="nav nav-tabs reportes-nav-wrapper mb-4">
        <a href="{{ route('contador.reportes.index') }}" 
            class="nav-item {{ request()->routeIs('contador.reportes.index') ? 'active' : '' }}">
            <i class="fas fa-hand-holding-usd me-2"></i>
            Cuentas por Cobrar
        </a>
        
        <a href="{{ route('contador.reportes.ventas.rentabilidad') }}" 
            class="nav-item {{ request()->routeIs('contador.reportes.ventas.rentabilidad') ? 'active' : '' }}">
            <i class="fas fa-chart-line me-2"></i>
            Rentabilidad (Ventas)
        </a>
        
        <a href="{{ route('contador.reportes.inventario.sugerencias') }}" 
            class="nav-item {{ request()->routeIs('contador.reportes.inventario.sugerencias') ? 'active' : '' }}">
            <i class="fas fa-dolly-flatbed me-2"></i>
            Sugerencias (Compra)
        </a>
        
        <a href="{{ route('contador.reportes.inventario.vencimientos') }}" 
            class="nav-item {{ request()->routeIs('contador.reportes.inventario.vencimientos') ? 'active' : '' }}">
            <i class="fas fa-calendar-times me-2"></i>
            Productos por Vencer
        </a>
    </nav>
    {{-- =========== FIN NAVEGACIÓN =========== --}}


    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>
                Análisis de Rentabilidad por Cliente
            </h4>
        </div>
        <div class="card-body">

            <!-- FILTROS DE FECHA -->
            <form method="GET" action="{{ route('contador.reportes.ventas.rentabilidad') }}" class="mb-4 p-3 border rounded bg-light">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="fecha_inicio" class="form-label fw-bold">Fecha Inicio:</label>
                        <input type="date" class="form-control" name="fecha_inicio" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-3">
                        <label for="fecha_fin" class="form-label fw-bold">Fecha Fin:</label>
                        <input type="date" class="form-control" name="fecha_fin" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('contador.reportes.ventas.rentabilidad') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <!-- TABLA DE RESULTADOS -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Cliente</th>
                            <th>Total Facturado</th>
                            <th>Total Descuentos</th>
                            <th>Total Devoluciones (N/C)</th>
                            <th class="bg-primary text-white">VENTA NETA</th>
                            <th class="text-center"># Facturas</th>
                            <th>Teléfono</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reporte as $cliente)
                            <tr>
                                <td>
                                    <strong>{{ $cliente->Razon }}</strong>
                                    <br><small class="text-muted">Cód: {{ $cliente->Codclie }}</small>
                                </td>
                                <td class="text-end text-success">
                                    + {{ number_format($cliente->TotalFacturado, 2) }}
                                </td>
                                <td class="text-end text-warning">
                                    - {{ number_format($cliente->TotalDescuentos, 2) }}
                                </td>
                                <td class="text-end text-danger">
                                    - {{ number_format($cliente->TotalDevoluciones, 2) }}
                                </td>
                                <td class="text-end fw-bold fs-5 bg-light">
                                    S/ {{ number_format($cliente->VentaNeta, 2) }}
                                </td>
                                <td class="text-center">{{ $cliente->CantidadFacturas }}</td>
                                <td>{{ $cliente->Telefono1 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center p-4">
                                    <i class="fas fa-info-circle me-1"></i>
                                    No se encontraron resultados para este rango de fechas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <!-- Esto es lo que SÍ DEBERÍA ejecutarse -->
            @if ($reporte instanceof \Illuminate\Pagination\LengthAwarePaginator)
                {{ $reporte->links() }}
            @endif

        </div>
    </div>
</div>
@endsection