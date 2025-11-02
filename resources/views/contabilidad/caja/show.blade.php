@extends('layouts.app')

@section('title', 'Detalle Movimiento #' . $movimiento->Numero)

@push('styles')
    <link href="{{ asset('css/contabilidad/caja.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-receipt me-2"></i>Detalle de Movimiento</h1>
        <p class="text-muted">Movimiento de Caja N° {{ $movimiento->Numero }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('contador.caja.edit', $movimiento->Numero) }}" class="btn btn-secondary {{ $movimiento->Eliminado ? 'disabled' : '' }}">
            <i class="fas fa-edit me-1"></i> Editar
        </a>
        <form action="{{ route('contador.caja.destroy', $movimiento->Numero) }}" method="POST" onsubmit="return confirm('¿Está seguro de ANULAR este movimiento y su asiento contable asociado? Esta acción no se puede deshacer.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger {{ $movimiento->Eliminado ? 'disabled' : '' }}">
                <i class="fas fa-trash me-1"></i> Anular
            </button>
        </form>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.caja.index') }}">Caja</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $movimiento->Numero }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Movimiento de Caja #{{ $movimiento->Numero }}</h5>
                    @if($movimiento->Tipo == 1)
                        <span class="badge bg-success fs-6">Ingreso</span>
                    @else
                        <span class="badge bg-danger fs-6">Egreso</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-5 text-muted">Monto:</div>
                        <div class="col-7 fs-5 fw-bold">S/ {{ number_format($movimiento->Monto, 2) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted">Fecha:</div>
                        <div class="col-7">{{ \Carbon\Carbon::parse($movimiento->Fecha)->format('d/m/Y') }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted">Documento Ref:</div>
                        <div class="col-7">{{ $movimiento->Documento ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted">Contrapartida:</div>
                        <div class="col-7">{{ $movimiento->contrapartida_nombre ?? $movimiento->Razon }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted">Moneda:</div>
                        <div class="col-7">{{ $movimiento->Moneda == 1 ? 'SOLES' : 'DÓLARES' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted">Estado:</div>
                        <div class="col-7">
                             @if($movimiento->Eliminado)
                                <span class="badge bg-danger">Anulado</span>
                            @else
                                <span class="badge bg-success">Activo</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm asiento-vinculado">
                @if($asiento)
                <div class="card-header asiento-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Asiento Contable Vinculado</h5>
                    <a href="{{ route('contador.libro-diario.show', $asiento->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        Ver Asiento #{{ $asiento->numero }}
                    </a>
                </div>
                <div class="card-body">
                    <p><strong>Glosa:</strong> {{ $asiento->glosa }}</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Nombre</th>
                                    <th class="text-end">Debe</th>
                                    <th class="text-end">Haber</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($detalles)
                                    @foreach($detalles as $detalle)
                                    <tr>
                                        <td>{{ $detalle->cuenta_contable }}</td>
                                        <td>{{ $detalle->cuenta_nombre }}</td>
                                        <td class="text-end">{{ $detalle->debe > 0 ? number_format($detalle->debe, 2) : '-' }}</td>
                                        <td class="text-end">{{ $detalle->haber > 0 ? number_format($detalle->haber, 2) : '-' }}</td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="2">TOTALES</td>
                                    <td class="text-end">S/ {{ number_format($asiento->total_debe, 2) }}</td>
                                    <td class="text-end">S/ {{ number_format($asiento->total_haber, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @else
                <div class="card-body text-center p-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5 class="mb-0">Sin Asiento Vinculado</h5>
                    <p class="text-muted">Este movimiento de caja no tiene un asiento contable asociado.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

