@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Detalle de Producto')
@section('page-title')
    Detalle: {{ $producto->Nombre }}
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.inventario.index') }}">Inventario</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $producto->CodPro }}</li>
@endsection

@push('styles')
{{-- Estilos para los KPIs (los mismos de tu dashboard) --}}
<style>
.kpi-card { display: flex; align-items: center; background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
.kpi-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1.25rem; font-size: 1.5rem; color: #fff; }
.kpi-content .kpi-label { font-size: 0.875rem; font-weight: 600; color: #6c757d; text-transform: uppercase; margin-bottom: 0.25rem; }
.kpi-content .kpi-value { font-size: 1.5rem; font-weight: 700; color: #343a40; }
</style>
@endpush

@section('content')

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="card-title m-0"><i class="fas fa-box me-2"></i>Información del Producto</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong class="text-muted d-block">Código:</strong>
                    <code>{{ $producto->CodPro }}</code>
                </p>
                <p class="mb-2">
                    <strong class="text-muted d-block">Nombre:</strong>
                    <h4>{{ $producto->Nombre }}</h4>
                </p>
                <p class="mb-2">
                    <strong class="text-muted d-block">Laboratorio:</strong>
                    <span>{{ $producto->Laboratorio ?? 'N/A' }}</span>
                </p>
                <p class="mb-2">
                    <strong class="text-muted d-block">Principio Activo:</strong>
                    <span>{{ $producto->Principio ?? 'No especificado' }}</span>
                </p>
                <p class="mb-2">
                    <strong class="text-muted d-block">Registro Sanitario:</strong>
                    <span>{{ $producto->RegSanit ?? 'N/A' }}</span>
                </p>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="card-title m-0"><i class="fas fa-dollar-sign me-2"></i>Precios y Costos</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <div class="kpi-label">Costo</div>
                        <div class="kpi-value text-danger">S/ {{ number_format($producto->Costo, 2) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="kpi-label">Precio Venta</div>
                        <div class="kpi-value text-success">S/ {{ number_format($producto->PventaMa, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0"><i class="fas fa-boxes me-2"></i>Stock Detallado por Lote</h5>
                <div class="kpi-card bg-primary text-white p-2">
                    <div class="kpi-content">
                        <div class="kpi-label text-white">Stock Total (Saldos)</div>
                        <div class="kpi-value text-white">{{ number_format($stockDetallado->sum('saldo'), 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">Almacén</th>
                                <th>Lote</th>
                                <th>Vencimiento</th>
                                <th class="text-end">Stock Lote</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockDetallado as $lote)
                                @php
                                    $diasVencer = $lote->vencimiento ? \Carbon\Carbon::parse($lote->vencimiento)->diffInDays(now(), false) : null;
                                    $claseVencido = '';
                                    if ($diasVencer !== null && $diasVencer > 0) $claseVencido = 'table-danger'; // Vencido
                                    elseif ($diasVencer !== null && $diasVencer > -90) $claseVencido = 'table-warning'; // Por vencer
                                @endphp
                                <tr class="{{ $claseVencido }}">
                                    <td class="text-center">{{ $lote->almacen }}</td>
                                    <td><strong>{{ $lote->lote }}</strong></td>
                                    <td>
                                        {{ $lote->vencimiento ? \Carbon\Carbon::parse($lote->vencimiento)->format('d/m/Y') : 'N/A' }}
                                        @if($diasVencer > 0)
                                            <span class="badge bg-danger ms-1">Vencido</span>
                                        @elseif($diasVencer !== null && $diasVencer > -90)
                                            <span class="badge bg-warning text-dark ms-1">Vence Pronto</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold fs-6">{{ number_format($lote->saldo, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center p-4 text-muted">Este producto no tiene stock registrado en la tabla Saldos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="3" class="text-end fw-bold">Stock Total (Suma de lotes):</td>
                                <td class="text-end fw-bolder fs-5">
                                    {{ number_format($stockDetallado->sum('saldo'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection