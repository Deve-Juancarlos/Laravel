@extends('layouts.admin')

@section('title', 'Reporte de Cuentas por Cobrar')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/cuentas-cobrar-profesional.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid py-4">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-hand-holding-usd me-2"></i>
                        Análisis Aging - Cuentas por Cobrar
                    </h5>
                </div>
                <div class="card-body">
                    
                    <!-- Resumen Aging -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <p class="text-muted mb-1 small">Total</p>
                                <h5 class="mb-0 text-dark">S/ {{ number_format($resumen['total'], 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-success bg-opacity-10 p-3 rounded text-center border border-success">
                                <p class="text-muted mb-1 small">Vigente</p>
                                <h5 class="mb-0 text-success">S/ {{ number_format($resumen['vigente'], 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-warning bg-opacity-10 p-3 rounded text-center border border-warning">
                                <p class="text-muted mb-1 small">1-30 días</p>
                                <h5 class="mb-0 text-warning">S/ {{ number_format($resumen['vencido_1_30'], 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-warning bg-opacity-25 p-3 rounded text-center border border-warning">
                                <p class="text-muted mb-1 small">31-60 días</p>
                                <h5 class="mb-0 text-warning">S/ {{ number_format($resumen['vencido_31_60'], 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-danger bg-opacity-10 p-3 rounded text-center border border-danger">
                                <p class="text-muted mb-1 small">61-90 días</p>
                                <h5 class="mb-0 text-danger">S/ {{ number_format($resumen['vencido_61_90'], 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-danger bg-opacity-25 p-3 rounded text-center border border-danger">
                                <p class="text-muted mb-1 small">+90 días</p>
                                <h5 class="mb-0 text-danger">S/ {{ number_format($resumen['vencido_mas_90'], 2) }}</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="mb-3">
                        <a href="{{ route('admin.reportes.cuentas-cobrar.export') }}" class="btn btn-success">
                            <i class="fas fa-file-excel me-2"></i>Exportar Excel
                        </a>
                    </div>

                    <!-- Tabla Detallada -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Documento</th>
                                    <th>Fecha Emisión</th>
                                    <th>Fecha Venc.</th>
                                    <th class="text-end">Importe</th>
                                    <th class="text-end">Saldo</th>
                                    <th class="text-center">Días Venc.</th>
                                    <th class="text-center">Rango</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($aging as $cuenta)
                                <tr>
                                    <td>
                                        <strong>{{ $cuenta->Razon }}</strong><br>
                                        <small class="text-muted">{{ $cuenta->Codclie }}</small>
                                    </td>
                                    <td>{{ $cuenta->Documento }}</td>
                                    <td>{{ \Carbon\Carbon::parse($cuenta->FechaF)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($cuenta->FechaV)->format('d/m/Y') }}</td>
                                    <td class="text-end">S/ {{ number_format($cuenta->Importe, 2) }}</td>
                                    <td class="text-end fw-bold">S/ {{ number_format($cuenta->Saldo, 2) }}</td>
                                    <td class="text-center">{{ $cuenta->dias_vencidos }}</td>
                                    <td class="text-center">
                                        @if($cuenta->rango == 'VIGENTE')
                                            <span class="badge bg-success">Vigente</span>
                                        @elseif($cuenta->rango == '1-30')
                                            <span class="badge bg-warning">1-30 días</span>
                                        @elseif($cuenta->rango == '31-60')
                                            <span class="badge bg-warning">31-60 días</span>
                                        @elseif($cuenta->rango == '61-90')
                                            <span class="badge bg-danger">61-90 días</span>
                                        @else
                                            <span class="badge bg-danger">+90 días</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No hay cuentas por cobrar
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection
