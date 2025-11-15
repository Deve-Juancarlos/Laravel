@extends('layouts.admin')

@section('title', 'Resumen Ejecutivo')

@section('content')
<div class="container-fluid py-4">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-file-pdf me-2"></i>
                            Resumen Ejecutivo - {{ ucfirst(str_replace('_', ' ', $periodo)) }}
                        </h4>
                        <button onclick="window.print()" class="btn btn-light btn-sm">
                            <i class="fas fa-print me-2"></i>Imprimir
                        </button>
                    </div>
                </div>
                <div class="card-body p-5">
                    
                    <!-- Encabezado del Reporte -->
                    <div class="text-center mb-5">
                        <h2 class="fw-bold mb-2">RESUMEN EJECUTIVO</h2>
                        <p class="text-muted mb-0">
                            Periodo: {{ $resumen['fecha_inicio']->format('d/m/Y') }} al {{ $resumen['fecha_fin']->format('d/m/Y') }}
                        </p>
                        <p class="text-muted">
                            Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    <!-- Resumen Financiero -->
                    <div class="row mb-5">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-chart-line me-2 text-primary"></i>
                                Resumen Financiero
                            </h5>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <p class="text-muted mb-1 small">Ventas Totales</p>
                                <h3 class="mb-0 text-success fw-bold">
                                    S/ {{ number_format($resumen['ventas_totales'], 2) }}
                                </h3>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <p class="text-muted mb-1 small">Utilidad</p>
                                <h3 class="mb-0 text-primary fw-bold">
                                    S/ {{ number_format($resumen['utilidad'], 2) }}
                                </h3>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <p class="text-muted mb-1 small">Clientes Activos</p>
                                <h3 class="mb-0 text-info fw-bold">
                                    {{ number_format($resumen['clientes_activos'], 0) }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Análisis de Margen -->
                    <div class="row mb-5">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-percent me-2 text-success"></i>
                                Análisis de Rentabilidad
                            </h5>
                        </div>
                        
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Concepto</th>
                                            <th class="text-end">Monto</th>
                                            <th class="text-end">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Ventas Totales</strong></td>
                                            <td class="text-end">S/ {{ number_format($resumen['ventas_totales'], 2) }}</td>
                                            <td class="text-end">100.00%</td>
                                        </tr>
                                        <tr>
                                            <td>Utilidad Bruta</td>
                                            <td class="text-end text-success">S/ {{ number_format($resumen['utilidad'], 2) }}</td>
                                            <td class="text-end">
                                                {{ $resumen['ventas_totales'] > 0 ? number_format(($resumen['utilidad'] / $resumen['ventas_totales']) * 100, 2) : 0 }}%
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Firma -->
                    <div class="row mt-5 pt-5">
                        <div class="col-6 text-center">
                            <div class="border-top pt-3 mt-5">
                                <p class="mb-0"><strong>Administrador General</strong></p>
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="border-top pt-3 mt-5">
                                <p class="mb-0"><strong>Contador</strong></p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<style>
@media print {
    .sidebar, .navbar, .card-header button {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: none !important;
    }
}
</style>
@endsection
