@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Registro de Compras SUNAT')

@push('styles')
    <link href="{{ asset('css/admin/sunat-compras.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="sunat-compras-container">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        Registro de Compras SUNAT - Formato 8.1
                    </h5>
                </div>
                <div class="card-body">
                    
                    <!-- Filtros -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Período (Mes/Año)</label>
                            <input type="month" name="periodo" class="form-control" 
                                   value="{{ $periodo }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                            <a href="{{ route('admin.reportes.sunat-compras.export', ['periodo' => $periodo]) }}" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </a>
                            <a href="{{ route('admin.reportes.sunat-compras.txt', ['periodo' => $periodo]) }}" 
                               class="btn btn-secondary ms-2">
                                <i class="fas fa-file me-2"></i>TXT
                            </a>
                        </div>
                    </form>

                    <!-- Resumen -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Total Documentos</p>
                                <h4 class="mb-0 text-primary">{{ number_format($registros->count()) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Base Afecta</p>
                                <h4 class="mb-0 text-success">S/ {{ number_format($registros->sum('BaseAfecta'), 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">IGV</p>
                                <h4 class="mb-0 text-warning">S/ {{ number_format($registros->sum('Igv'), 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Total</p>
                                <h4 class="mb-0 text-success">S/ {{ number_format($registros->sum('Total'), 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Fecha Emisión</th>
                                    <th>Tipo Doc</th>
                                    <th>Serie</th>
                                    <th>Número</th>
                                    <th>Proveedor</th>
                                    <th>RUC</th>
                                    <th class="text-end">Base Afecta</th>
                                    <th class="text-end">IGV</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($registros as $index => $registro)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $index + 1 }}</span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($registro->FechaEmision)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $registro->tipo_doc }}</span>
                                    </td>
                                    <td>{{ $registro->Serie }}</td>
                                    <td>{{ $registro->Numero }}</td>
                                    <td>
                                        <strong>{{ $registro->RazonSocial }}</strong>
                                    </td>
                                    <td>{{ $registro->Ruc }}</td>
                                    <td class="text-end">S/ {{ number_format($registro->BaseAfecta, 2) }}</td>
                                    <td class="text-end">S/ {{ number_format($registro->Igv, 2) }}</td>
                                    <td class="text-end fw-bold text-success">S/ {{ number_format($registro->Total, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                        No hay registros de compras para este período
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="7" class="text-end">TOTALES:</th>
                                    <th class="text-end">S/ {{ number_format($registros->sum('BaseAfecta'), 2) }}</th>
                                    <th class="text-end">S/ {{ number_format($registros->sum('Igv'), 2) }}</th>
                                    <th class="text-end">S/ {{ number_format($registros->sum('Total'), 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Información Adicional -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Nota:</strong> Este reporte muestra todas las compras del período en formato SUNAT 8.1. 
                                Puede exportar los datos a Excel o generar el archivo TXT para envío a SUNAT.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection
