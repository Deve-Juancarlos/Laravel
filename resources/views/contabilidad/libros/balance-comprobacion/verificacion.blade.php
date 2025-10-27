@extends('layouts.app')

@section('title', 'Verificación Balance - Balance de Comprobación')

@section('styles')
<style>
    .verification-header {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .status-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        border-left: 4px solid #dc2626;
    }
    
    .status-ok {
        border-left-color: #10b981;
    }
    
    .status-warning {
        border-left-color: #f59e0b;
    }
    
    .status-error {
        border-left-color: #dc2626;
    }
    
    .verification-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .verification-table th {
        background: #f8fafc;
        border: none;
        padding: 15px 12px;
        font-weight: 700;
        color: #374151;
    }
    
    .problem-row {
        background: #fef2f2 !important;
    }
    
    .ok-row {
        background: #f0fdf4 !important;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-box {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        text-align: center;
        transition: transform 0.2s ease;
    }
    
    .stat-box:hover {
        transform: translateY(-2px);
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #6b7280;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .alert-card {
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .alert-success {
        background: #d1fae5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }
    
    .alert-warning {
        background: #fef3cd;
        border: 1px solid #fde68a;
        color: #92400e;
    }
    
    .alert-danger {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="verification-header">
        <h1><i class="fas fa-shield-alt me-3"></i>Verificación de Integridad</h1>
        <p class="mb-0">Validación automática del Balance de Comprobación</p>
        <small class="opacity-75">
            Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
        </small>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.balance-comprobacion.verificar') }}">
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
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-search me-2"></i>Verificar Integridad
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas generales -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-number text-primary">{{ number_format($estadisticas['total_asientos']) }}</div>
            <div class="stat-label">Total Asientos</div>
        </div>
        <div class="stat-box">
            <div class="stat-number {{ $estadisticas['asientos_desequilibrados'] > 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format($estadisticas['asientos_desequilibrados']) }}
            </div>
            <div class="stat-label">Asientos Desequilibrados</div>
        </div>
        <div class="stat-box">
            <div class="stat-number text-info">{{ number_format($estadisticas['cuentas_con_movimientos']) }}</div>
            <div class="stat-label">Cuentas con Movimientos</div>
        </div>
        <div class="stat-box">
            <div class="stat-number {{ $estadisticas['cuadra'] ? 'text-success' : 'text-danger' }}">
                <i class="fas {{ $estadisticas['cuadra'] ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
            </div>
            <div class="stat-label">{{ $estadisticas['cuadra'] ? 'Balance Cuadra' : 'Balance No Cuadra' }}</div>
        </div>
    </div>

    <!-- Estado general -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="status-card {{ $estadisticas['cuadra'] ? 'status-ok' : 'status-error' }}">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="mb-1">
                            <i class="fas {{ $estadisticas['cuadra'] ? 'fa-check-circle text-success' : 'fa-exclamation-triangle text-danger' }} me-2"></i>
                            Estado General del Balance
                        </h4>
                        @if($estadisticas['cuadra'])
                            <p class="mb-0 text-success">✅ El balance de comprobación está correctamente cuadrado.</p>
                        @else
                            <p class="mb-0 text-danger">❌ El balance de comprobación presenta diferencias que requieren atención.</p>
                        @endif
                    </div>
                    <div class="text-end">
                        <h3 class="mb-0 text-{{ $estadisticas['cuadra'] ? 'success' : 'danger' }}">
                            S/ {{ number_format($estadisticas['diferencia'], 2) }}
                        </h3>
                        <small class="text-muted">Diferencia Total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalles de verificación -->
    <div class="row">
        <div class="col-md-6">
            <!-- Verificación de asientos -->
            <div class="verification-table">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Asientos Desequilibrados
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($asientosDesequilibrados->count() > 0)
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Asiento</th>
                                    <th>Total Debe</th>
                                    <th>Total Haber</th>
                                    <th>Diferencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asientosDesequilibrados as $asiento)
                                <tr class="problem-row">
                                    <td><strong>{{ $asiento->Numero }}</strong></td>
                                    <td class="text-end">S/ {{ number_format($asiento->total_debe, 2) }}</td>
                                    <td class="text-end">S/ {{ number_format($asiento->total_haber, 2) }}</td>
                                    <td class="text-end text-danger">
                                        <strong>S/ {{ number_format(abs($asiento->total_debe - $asiento->total_haber), 2) }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-4 text-center">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>No hay asientos desequilibrados</h5>
                            <p class="text-muted">Todos los asientos cumplen con la partida doble.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Cuentas sin movimientos recientes -->
            <div class="verification-table">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Cuentas Sin Movimientos Recientes
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($cuentasSinMovimientos->count() > 0)
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Último Movimiento</th>
                                    <th>Días Sin Movimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cuentasSinMovimientos as $cuenta)
                                @php
                                    $ultimoMovimiento = \Carbon\Carbon::parse($cuenta->FechaF);
                                    $diasSinMovimiento = \Carbon\Carbon::now()->diffInDays($ultimoMovimiento);
                                @endphp
                                <tr>
                                    <td><strong>{{ $cuenta->Tipo }}</strong></td>
                                    <td>{{ $ultimoMovimiento->format('d/m/Y') }}</td>
                                    <td class="text-end">
                                        <span class="badge {{ $diasSinMovimiento > 30 ? 'bg-danger' : ($diasSinMovimiento > 7 ? 'bg-warning' : 'bg-success') }}">
                                            {{ $diasSinMovimiento }} días
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-4 text-center">
                            <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                            <h5>Todas las cuentas tienen movimientos</h5>
                            <p class="text-muted">No se encontraron cuentas inactivas.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis detallado de totales -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="verification-table">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>
                        Análisis Detallado de Totales
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-primary">Total Debe</h6>
                                <h4 class="text-success">S/ {{ number_format($estadisticas['total_debe'], 2) }}</h4>
                                <small class="text-muted">Suma de débitos</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-primary">Total Haber</h6>
                                <h4 class="text-danger">S/ {{ number_format($estadisticas['total_haber'], 2) }}</h4>
                                <small class="text-muted">Suma de créditos</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-primary">Diferencia</h6>
                                <h4 class="{{ $estadisticas['diferencia'] > 0.01 ? 'text-danger' : 'text-success' }}">
                                    S/ {{ number_format($estadisticas['diferencia'], 2) }}
                                </h4>
                                <small class="text-muted">Debe - Haber</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-primary">Estado</h6>
                                <h4>
                                    @if($estadisticas['cuadra'])
                                        <span class="text-success">✅ CUADRA</span>
                                    @else
                                        <span class="text-danger">❌ NO CUADRA</span>
                                    @endif
                                </h4>
                                <small class="text-muted">Verificación final</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas y recomendaciones -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Recomendaciones y Alertas
                    </h6>
                </div>
                <div class="card-body">
                    @if($estadisticas['asientos_desequilibrados'] > 0)
                        <div class="alert-card alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Asientos Desequilibrados Detectados</h6>
                            <p>Se encontraron {{ $estadisticas['asientos_desequilibrados'] }} asientos que no cumplen con la partida doble. Revise y corrija estos asientos antes de continuar.</p>
                        </div>
                    @endif

                    @if($estadisticas['diferencia'] > 0.01)
                        <div class="alert-card alert-danger">
                            <h6><i class="fas fa-times-circle me-2"></i>Balance No Cuadra</h6>
                            <p>La diferencia de S/ {{ number_format($estadisticas['diferencia'], 2) }} indica que el balance no está cuadrado. Verifique los cálculos y registros contables.</p>
                        </div>
                    @endif

                    @if($estadisticas['cuadra'] && $estadisticas['asientos_desequilibrados'] == 0)
                        <div class="alert-card alert-success">
                            <h6><i class="fas fa-check-circle me-2"></i>Balance Verificado Correctamente</h6>
                            <p>✅ Todos los asientos están correctamente balanceados. ✅ El balance cuadra perfectamente. El sistema contable está funcionando correctamente.</p>
                        </div>
                    @endif

                    @if($cuentasSinMovimientos->count() > 0)
                        <div class="alert-card alert-warning">
                            <h6><i class="fas fa-info-circle me-2"></i>Cuentas con Baja Actividad</h6>
                            <p>Se detectaron {{ $cuentasSinMovimientos->count() }} cuentas sin movimientos recientes. Revise si deben mantenerse activas o si requieren ajustes.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="row mt-4">
        <div class="col-md-12 text-center">
            <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Volver al Balance
            </a>
            <a href="{{ route('contador.balance-comprobacion.comparacion') }}" class="btn btn-info me-2">
                <i class="fas fa-balance-scale me-2"></i>Comparar Períodos
            </a>
            <button class="btn btn-success" onclick="generarReporte()">
                <i class="fas fa-file-pdf me-2"></i>Generar Reporte PDF
            </button>
        </div>
    </div>
</div>

<script>
function generarReporte() {
    const params = new URLSearchParams({
        fecha_inicio: '{{ $fechaInicio }}',
        fecha_fin: '{{ $fechaFin }}',
        formato: 'verificacion'
    });
    window.location.href = `{{ route('contador.balance-comprobacion.exportar') }}?${params}`;
}
</script>
@endsection