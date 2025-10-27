@extends('layouts.app')

@section('title', 'Balance de Comprobación - SIFANO')

@section('styles')
<style>
    .balance-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .balance-table {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .balance-table table {
        margin-bottom: 0;
    }
    
    .balance-table th {
        background: #f8fafc;
        border: none;
        padding: 15px 12px;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    
    .balance-table td {
        padding: 12px;
        border-color: #e5e7eb;
        vertical-align: middle;
    }
    
    .total-row {
        background: #f3f4f6 !important;
        font-weight: 700;
        border-top: 3px solid #1e3a8a !important;
    }
    
    .cuadra-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .cuadra-true {
        background: #d1fae5;
        color: #065f46;
    }
    
    .cuadra-false {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-left: 4px solid #1e3a8a;
        margin-bottom: 20px;
        transition: transform 0.2s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1e3a8a;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #6b7280;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="balance-header">
        <h1><i class="fas fa-balance-scale me-3"></i>Balance de Comprobación</h1>
        <p class="mb-0">Verificación de saldos contables - Sistema SIFANO</p>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.balance-comprobacion.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Generar Balance
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estado del Balance -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="stat-card text-center">
                <div class="stat-value">
                    @if($cuadra)
                        <span class="cuadra-badge cuadra-true">
                            <i class="fas fa-check-circle me-2"></i>BALANCE CUADRA
                        </span>
                    @else
                        <span class="cuadra-badge cuadra-false">
                            <i class="fas fa-exclamation-triangle me-2"></i>BALANCE NO CUADRA
                        </span>
                    @endif
                </div>
                <div class="stat-label">
                    Diferencia: S/ {{ number_format($diferencia, 2) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($estadisticas['total_asientos']) }}</div>
                <div class="stat-label">Total Asientos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($estadisticas['total_movimientos']) }}</div>
                <div class="stat-label">Total Movimientos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($estadisticas['cuentas_utilizadas']) }}</div>
                <div class="stat-label">Cuentas Utilizadas</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">S/ {{ number_format($totalDeudor, 2) }}</div>
                <div class="stat-label">Total Deudor</div>
            </div>
        </div>
    </div>

    <!-- Balance Principal -->
    <div class="balance-table">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>
                Balance de Comprobación al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Cuenta</th>
                        <th>Debe</th>
                        <th>Haber</th>
                        <th>Saldos Deudores</th>
                        <th>Saldos Acreedores</th>
                        <th>Movimientos</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Cuentas Deudoras -->
                    @foreach($cuentasDeudoras as $cuenta)
                    <tr>
                        <td><strong>{{ $cuenta['cuenta'] }}</strong></td>
                        <td class="text-end">{{ number_format($cuenta['saldo'], 2) }}</td>
                        <td class="text-end">0.00</td>
                        <td class="text-end fw-bold text-success">{{ number_format($cuenta['saldo'], 2) }}</td>
                        <td class="text-end">0.00</td>
                        <td class="text-center">{{ number_format($cuenta['movimientos']) }}</td>
                    </tr>
                    @endforeach

                    <!-- Cuentas Acreedoras -->
                    @foreach($cuentasAcreedoras as $cuenta)
                    <tr>
                        <td><strong>{{ $cuenta['cuenta'] }}</strong></td>
                        <td class="text-end">0.00</td>
                        <td class="text-end">{{ number_format($cuenta['saldo'], 2) }}</td>
                        <td class="text-end">0.00</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($cuenta['saldo'], 2) }}</td>
                        <td class="text-center">{{ number_format($cuenta['movimientos']) }}</td>
                    </tr>
                    @endforeach

                    <!-- Totales -->
                    <tr class="total-row">
                        <td><strong>TOTALES</strong></td>
                        <td class="text-end"><strong>{{ number_format($totalDeudor, 2) }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($totalAcreedor, 2) }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($totalDeudor, 2) }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($totalAcreedor, 2) }}</strong></td>
                        <td class="text-center"><strong>{{ number_format($estadisticas['total_movimientos']) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Resumen por Clases -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Resumen por Clases de Cuentas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-primary">ACTIVO</h6>
                                <div class="fw-bold">S/ {{ number_format($resumenClases['ACTIVO']['total_debe'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-warning">PASIVO</h6>
                                <div class="fw-bold">S/ {{ number_format($resumenClases['PASIVO']['total_haber'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-info">PATRIMONIO</h6>
                                <div class="fw-bold">S/ {{ number_format($resumenClases['PATRIMONIO']['total_haber'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-success">INGRESOS</h6>
                                <div class="fw-bold">S/ {{ number_format($resumenClases['INGRESOS']['total_haber'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-danger">GASTOS</h6>
                                <div class="fw-bold">S/ {{ number_format($resumenClases['GASTOS']['total_debe'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-secondary">PERIODO</h6>
                                <div class="small">{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="row mt-4">
        <div class="col-md-12 text-center">
            <a href="{{ route('contador.balance-comprobacion.detalle', ['cuenta' => 'all']) }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-eye me-2"></i>Ver Detalle Cuentas
            </a>
            <a href="{{ route('contador.balance-comprobacion.verificar') }}" class="btn btn-outline-warning me-2">
                <i class="fas fa-check-circle me-2"></i>Verificar Integridad
            </a>
            <button class="btn btn-success" onclick="exportarBalance()">
                <i class="fas fa-download me-2"></i>Exportar Excel
            </button>
        </div>
    </div>
</div>

<script>
function exportarBalance() {
    const params = new URLSearchParams({
        fecha_inicio: '{{ $fechaInicio }}',
        fecha_fin: '{{ $fechaFin }}',
        formato: 'excel'
    });
    window.location.href = `{{ route('contador.balance-comprobacion.exportar') }}?${params}`;
}
</script>
@endsection