@extends('layouts.app')

@section('title', 'Balance por Clases - Balance de Comprobación')

@section('styles')
<style>
    .clase-header {
        background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .clase-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        transition: transform 0.2s ease;
    }
    
    .clase-card:hover {
        transform: translateY(-5px);
    }
    
    .clase-header-card {
        color: white;
        padding: 20px;
        text-align: center;
    }
    
    .activo { background: linear-gradient(135deg, #3b82f6, #1e40af); }
    .pasivo { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .patrimonio { background: linear-gradient(135deg, #06b6d4, #0891b2); }
    .ingresos { background: linear-gradient(135deg, #10b981, #059669); }
    .gastos { background: linear-gradient(135deg, #ef4444, #dc2626); }
    
    .clase-body {
        padding: 0;
    }
    
    .clase-table {
        margin: 0;
    }
    
    .clase-table th {
        background: #f8fafc;
        border: none;
        padding: 12px 15px;
        font-weight: 600;
        color: #374151;
    }
    
    .clase-table td {
        padding: 12px 15px;
        border-color: #e5e7eb;
    }
    
    .total-clase {
        background: #f3f4f6 !important;
        font-weight: 700;
        border-top: 2px solid #7c3aed !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="clase-header">
        <h1><i class="fas fa-layer-group me-3"></i>Balance por Clases de Cuentas</h1>
        <p class="mb-0">Organización según Plan Contable General Empresarial (PCGE)</p>
        <small class="opacity-75">
            Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
        </small>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.balance-comprobacion.clases') }}">
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
                            <i class="fas fa-search me-2"></i>Actualizar Clases
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Clase 1: ACTIVO -->
    @if(count($cuentasPorClase['ACTIVO']) > 0)
    <div class="clase-card">
        <div class="clase-header-card activo">
            <h3><i class="fas fa-building me-3"></i>ACTIVO (Clase 1)</h3>
            <p class="mb-0">Bienes, derechos y recursos controlados por la empresa</p>
        </div>
        <div class="clase-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0 clase-table">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Saldo</th>
                            <th>Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalActivo = 0; @endphp
                        @foreach($cuentasPorClase['ACTIVO'] as $cuenta)
                        @php $totalActivo += $cuenta->saldo; @endphp
                        <tr>
                            <td><strong>{{ $cuenta->cuenta }}</strong></td>
                            <td class="text-end">S/ {{ number_format($cuenta->saldo, 2) }}</td>
                            <td class="text-end">
                                {{ $totalActivo > 0 ? number_format(($cuenta->saldo / $totalActivo) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="total-clase">
                            <td><strong>TOTAL ACTIVO</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($totalActivo, 2) }}</strong></td>
                            <td class="text-end"><strong>100.0%</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Clase 2: PASIVO -->
    @if(count($cuentasPorClase['PASIVO']) > 0)
    <div class="clase-card">
        <div class="clase-header-card pasivo">
            <h3><i class="fas fa-credit-card me-3"></i>PASIVO (Clase 2)</h3>
            <p class="mb-0">Deudas y obligaciones de la empresa</p>
        </div>
        <div class="clase-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0 clase-table">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Saldo</th>
                            <th>Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalPasivo = 0; @endphp
                        @foreach($cuentasPorClase['PASIVO'] as $cuenta)
                        @php $totalPasivo += $cuenta->saldo; @endphp
                        <tr>
                            <td><strong>{{ $cuenta->cuenta }}</strong></td>
                            <td class="text-end">S/ {{ number_format($cuenta->saldo, 2) }}</td>
                            <td class="text-end">
                                {{ $totalPasivo > 0 ? number_format(($cuenta->saldo / $totalPasivo) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="total-clase">
                            <td><strong>TOTAL PASIVO</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($totalPasivo, 2) }}</strong></td>
                            <td class="text-end"><strong>100.0%</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Clase 3: PATRIMONIO -->
    @if(count($cuentasPorClase['PATRIMONIO']) > 0)
    <div class="clase-card">
        <div class="clase-header-card patrimonio">
            <h3><i class="fas fa-coins me-3"></i>PATRIMONIO (Clase 3)</h3>
            <p class="mb-0">Capital, reservas y resultados acumulados</p>
        </div>
        <div class="clase-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0 clase-table">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Saldo</th>
                            <th>Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalPatrimonio = 0; @endphp
                        @foreach($cuentasPorClase['PATRIMONIO'] as $cuenta)
                        @php $totalPatrimonio += $cuenta->saldo; @endphp
                        <tr>
                            <td><strong>{{ $cuenta->cuenta }}</strong></td>
                            <td class="text-end">S/ {{ number_format($cuenta->saldo, 2) }}</td>
                            <td class="text-end">
                                {{ $totalPatrimonio > 0 ? number_format(($cuenta->saldo / $totalPatrimonio) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="total-clase">
                            <td><strong>TOTAL PATRIMONIO</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($totalPatrimonio, 2) }}</strong></td>
                            <td class="text-end"><strong>100.0%</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Clase 4: INGRESOS -->
    @if(count($cuentasPorClase['INGRESOS']) > 0)
    <div class="clase-card">
        <div class="clase-header-card ingresos">
            <h3><i class="fas fa-chart-line me-3"></i>INGRESOS (Clase 4)</h3>
            <p class="mb-0">Ingresos por ventas y otros conceptos</p>
        </div>
        <div class="clase-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0 clase-table">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Saldo</th>
                            <th>Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalIngresos = 0; @endphp
                        @foreach($cuentasPorClase['INGRESOS'] as $cuenta)
                        @php $totalIngresos += $cuenta->saldo; @endphp
                        <tr>
                            <td><strong>{{ $cuenta->cuenta }}</strong></td>
                            <td class="text-end">S/ {{ number_format($cuenta->saldo, 2) }}</td>
                            <td class="text-end">
                                {{ $totalIngresos > 0 ? number_format(($cuenta->saldo / $totalIngresos) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="total-clase">
                            <td><strong>TOTAL INGRESOS</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($totalIngresos, 2) }}</strong></td>
                            <td class="text-end"><strong>100.0%</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Clase 5: GASTOS -->
    @if(count($cuentasPorClase['GASTOS']) > 0)
    <div class="clase-card">
        <div class="clase-header-card gastos">
            <h3><i class="fas fa-receipt me-3"></i>GASTOS (Clase 5)</h3>
            <p class="mb-0">Gastos operativos y administrativos</p>
        </div>
        <div class="clase-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0 clase-table">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Saldo</th>
                            <th>Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalGastos = 0; @endphp
                        @foreach($cuentasPorClase['GASTOS'] as $cuenta)
                        @php $totalGastos += $cuenta->saldo; @endphp
                        <tr>
                            <td><strong>{{ $cuenta->cuenta }}</strong></td>
                            <td class="text-end">S/ {{ number_format($cuenta->saldo, 2) }}</td>
                            <td class="text-end">
                                {{ $totalGastos > 0 ? number_format(($cuenta->saldo / $totalGastos) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="total-clase">
                            <td><strong>TOTAL GASTOS</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($totalGastos, 2) }}</strong></td>
                            <td class="text-end"><strong>100.0%</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Resumen General -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>
                        Resumen por Clases
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2">
                            <div class="p-3 border rounded">
                                <h6 class="text-primary">ACTIVO</h6>
                                <h5 class="text-primary">@isset($totalActivo) S/ {{ number_format($totalActivo, 2) }} @else S/ 0.00 @endisset</h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 border rounded">
                                <h6 class="text-warning">PASIVO</h6>
                                <h5 class="text-warning">@isset($totalPasivo) S/ {{ number_format($totalPasivo, 2) }} @else S/ 0.00 @endisset</h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 border rounded">
                                <h6 class="text-info">PATRIMONIO</h6>
                                <h5 class="text-info">@isset($totalPatrimonio) S/ {{ number_format($totalPatrimonio, 2) }} @else S/ 0.00 @endisset</h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 border rounded">
                                <h6 class="text-success">INGRESOS</h6>
                                <h5 class="text-success">@isset($totalIngresos) S/ {{ number_format($totalIngresos, 2) }} @else S/ 0.00 @endisset</h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 border rounded">
                                <h6 class="text-danger">GASTOS</h6>
                                <h5 class="text-danger">@isset($totalGastos) S/ {{ number_format($totalGastos, 2) }} @else S/ 0.00 @endisset</h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 border rounded">
                                <h6 class="text-secondary">ECUACIÓN</h6>
                                <small class="text-muted">ACTIVO =</small><br>
                                <small class="text-muted">PASIVO + PATRIMONIO</small>
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
            <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Volver al Balance
            </a>
            <a href="{{ route('contador.balance-comprobacion.verificar') }}" class="btn btn-warning me-2">
                <i class="fas fa-check-circle me-2"></i>Verificar Integridad
            </a>
            <button class="btn btn-success" onclick="exportarClases()">
                <i class="fas fa-download me-2"></i>Exportar Excel
            </button>
        </div>
    </div>
</div>

<script>
function exportarClases() {
    const params = new URLSearchParams({
        fecha_inicio: '{{ $fechaInicio }}',
        fecha_fin: '{{ $fechaFin }}',
        formato: 'clases'
    });
    window.location.href = `{{ route('contador.balance-comprobacion.exportar') }}?${params}`;
}
</script>
@endsection