{{-- 
  Esta es la nueva vista de reporte de vencimientos.
  Va en 'resources/views/reportes/inventario/'.
--}}

@extends('layouts.app') 

@section('title', 'Reporte de Productos por Vencer')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('contador.reportes.index') }}">Reportes</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Productos por vencer</li>
@endsection

@push('styles')
    <link href="{{ asset('css/contabilidad/reportes/inventario/productos_por_vencer.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="productosporvencer-container">

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

    <!-- Tarjeta de Totales -->
    <div class="card shadow-sm mb-3 bg-danger text-white">
        <div class="card-body text-center">
            <h5 class="card-title mb-0">Valor Total en Riesgo (hasta {{ $diasMaximos }} días)</h5>
            <p class="display-5 fw-bold mb-0">
                S/ {{ number_format($totalPerdida, 2) }}
            </p>
            <small>Este es el valor de inventario (al costo) que está por vencer.</small>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0">
                <i class="fas fa-calendar-times me-2 text-danger"></i>
                Reporte de Productos por Vencer
            </h4>
        </div>
        <div class="card-body">

            <!-- FILTROS -->
            <form method="GET" action="{{ route('contador.reportes.inventario.vencimientos') }}" class="mb-4 p-3 border rounded bg-light">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="dias_maximos" class="form-label fw-bold">Mostrar vencimientos en:</label>
                        <select name="dias_maximos" class="form-select">
                            <option value="30"  @if($diasMaximos == 30) selected @endif>30 días (Crítico)</option>
                            <option value="60"  @if($diasMaximos == 60) selected @endif>60 días</option>
                            <option value="90"  @if($diasMaximos == 90) selected @endif>90 días (Alerta)</option>
                            <option value="180" @if($diasMaximos == 180) selected @endif>180 días (Por defecto)</option>
                            <option value="365" @if($diasMaximos == 365) selected @endif>365 días</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>

            <!-- TABLA DE RESULTADOS -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Producto</th>
                            <th>Laboratorio</th>
                            <th>Lote</th>
                            <th class="text-center">Vencimiento</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Días Faltantes</th>
                            <th class="text-center">Valor (Costo)</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reporte as $producto)
                            @php
                                $claseFila = '';
                                if ($producto->DiasParaVencer < 0) $claseFila = 'table-dark text-white'; // Vencido
                                elseif ($producto->DiasParaVencer <= 30) $claseFila = 'table-danger';  // Crítico
                                elseif ($producto->DiasParaVencer <= 90) $claseFila = 'table-warning'; // Alerta
                            @endphp
                            <tr class="{{ $claseFila }}">
                                <td>
                                    <strong>{{ $producto->Nombre }}</strong>
                                    <br><small class="text-muted">{{ $producto->CodPro }}</small>
                                </td>
                                <td>{{ $producto->Laboratorio }}</td>
                                <td>{{ $producto->Lote }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($producto->Vencimiento)->format('d/m/Y') }}</td>
                                <td class="text-center fw-bold">{{ number_format($producto->Stock, 2) }}</td>
                                <td class="text-center fw-bold fs-5">
                                    {{ $producto->DiasParaVencer }}
                                </td>
                                <td class="text-end fw-bold">
                                    S/ {{ number_format($producto->ValorInventario, 2) }}
                                </td>
                                <td class="fw-bold">{{ $producto->EstadoVencimiento }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center p-4">
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    No hay productos por vencer en el rango de {{ $diasMaximos }} días.
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