@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Detalle del Producto')

@section('header-content')
<div>
    <h1 class="h3 mb-0">{{ $producto->Nombre }}</h1>
    <p class="text-muted mb-0">Código: {{ $producto->CodPro }}</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.inventario.index') }}">Inventario</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.inventario.productos') }}">Productos</a></li>
<li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')

<div class="row">
    <div class="col-lg-4 mb-4">
        <!-- Información del Producto -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Información del Producto
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Código</label>
                    <p class="mb-0">ode classss="fs-6">{{ $producto->CodPro }}</code></p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Nombre</label>
                    <p class="mb-0 fw-bold">{{ $producto->Nombre }}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Laboratorio</label>
                    <p class="mb-0">{{ $producto->Laboratorio }}</p>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="text-muted small">Costo</label>
                        <p class="mb-0"><strong class="text-danger">S/ {{ number_format($producto->Costo, 2) }}</strong></p>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small">Precio</label>
                        <p class="mb-0"><strong class="text-success">S/ {{ number_format($producto->Precio, 2) }}</strong></p>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Valorización Total</label>
                    <h4 class="mb-0 text-success">S/ {{ number_format($valorizacion, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Saldos por Almacén/Lote -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-warehouse me-2"></i>
                    Stock por Almacén y Lote
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Almacén</th>
                                <th>Lote</th>
                                <th>Vencimiento</th>
                                <th class="text-end">Saldo</th>
                                <th class="text-end">Valorización</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $stockTotal = 0; @endphp
                            @forelse($saldos as $saldo)
                            @php $stockTotal += $saldo->saldo; @endphp
                            <tr>
                                <td><strong>{{ $saldo->almacen }}</strong></td>
                                <td>odede>{{ $saldo->lote }}</code></td>
                                <td>
                                    @if($saldo->vencimiento)
                                        {{ \Carbon\Carbon::parse($saldo->vencimiento)->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-{{ $saldo->saldo > 10 ? 'success' : 'warning' }}">
                                        {{ number_format($saldo->saldo) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <strong>S/ {{ number_format($saldo->saldo * $producto->Costo, 2) }}</strong>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-box-open fa-3x mb-3 d-block"></i>
                                    Este producto no tiene stock disponible
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($saldos->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">TOTAL:</th>
                                <th class="text-end">
                                    <span class="badge bg-primary fs-6">{{ number_format($stockTotal) }}</span>
                                </th>
                                <th class="text-end">
                                    <strong class="fs-5 text-success">S/ {{ number_format($valorizacion, 2) }}</strong>
                                </th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Botón Volver -->
        <div class="mt-3">
            <a href="{{ route('admin.inventario.productos') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al Listado
            </a>
        </div>
    </div>
</div>

@endsection
