@extends('layouts.app') 

@section('title', 'Reporte de Sugerencias de Compra')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('contador.reportes.index') }}">Reportes</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Sugerencias(Compra)</li>
@endsection

@push('styles')
    <link href="{{ asset('css/contabilidad/reportes/inventario/sugerencias_compras.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="sugerenciascompra-container">

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
                <i class="fas fa-dolly-flatbed me-2"></i>
                Sugerencias de Compra (Reposición de Stock)
            </h4>
            <small class="text-muted">
                Este reporte muestra todos los productos cuyo Stock Actual está por debajo del Stock Mínimo definido.
            </small>
        </div>
        <div class="card-body">

            <!-- TABLA DE RESULTADOS -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Laboratorio</th>
                            <th class="text-center">Stock Mínimo</th>
                            <th class="text-center">Stock Actual</th>
                            <th class="text-center bg-primary text-white">Cantidad Sugerida</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reporte as $producto)
                            <tr>
                                <td>{{ $producto->CodPro }}</td>
                                <td><strong>{{ $producto->Nombre }}</strong></td>
                                <td>{{ $producto->Laboratorio }}</td>
                                <td class="text-center fw-bold">{{ number_format($producto->Minimo, 2) }}</td>
                                <td class="text-center fw-bold text-danger">
                                    {{-- El stock actual está en rojo porque está bajo --}}
                                    {{ number_format($producto->stock_actual, 2) }}
                                </td>
                                <td class="text-center fw-bold fs-5 bg-light">
                                    {{-- La cantidad a comprar --}}
                                    {{ number_format($producto->CantidadSugerida, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center p-4">
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    ¡Felicidades! No hay productos por debajo del stock mínimo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <div class="mt-3 d-flex justify-content-center">
                {{ $reporte->links() }}
            </div>

        </div>
    </div>
</div>
@endsection