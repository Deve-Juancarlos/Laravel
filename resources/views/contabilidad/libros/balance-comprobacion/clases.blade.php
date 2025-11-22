@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Balance por Clases - Balance de Comprobación')

@push('styles')
    <link href="{{ asset('css/contabilidad/balance-comparacion/clases.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-layer-group me-2"></i>Balance por Clases</h1>
        <p class="text-muted">Organización según Plan Contable General Empresarial (PCGE)</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.balance-comprobacion.index') }}">Balance de Comprobación</a></li>
    <li class="breadcrumb-item active" aria-current="page">Balance por Clases</li>
@endsection


@section('content')
<div class="container-fluid">

    <!-- Filtros -->
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.balance-comprobacion.clases') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="fecha_fin">Fecha Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Actualizar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="row mb-4">
        @php
            $clases = [
                'ACTIVO' => ['color' => 'bg-activo', 'icon' => 'fa-wallet'],
                'PASIVO' => ['color' => 'bg-pasivo', 'icon' => 'fa-credit-card'],
                'PATRIMONIO' => ['color' => 'bg-patrimonio', 'icon' => 'fa-building-columns'],
                'INGRESOS' => ['color' => 'bg-ingresos', 'icon' => 'fa-hand-holding-dollar'],
                'GASTOS' => ['color' => 'bg-gastos', 'icon' => 'fa-receipt']
            ];
            $utilidad = (isset($cuentasPorClase['INGRESOS']) ? collect($cuentasPorClase['INGRESOS'])->sum('saldo') : 0) - (isset($cuentasPorClase['GASTOS']) ? collect($cuentasPorClase['GASTOS'])->sum('saldo') : 0);
        @endphp
        
        @foreach($clases as $nombre => $data)
            @php
                $total = (isset($cuentasPorClase[$nombre]) ? collect($cuentasPorClase[$nombre])->sum('saldo') : 0);
            @endphp
            <div class="col-lg col-md-4 col-6 mb-3">
                <div class="summary-card {{ $data['color'] }} shadow-sm text-center">
                    <div class="summary-icon"><i class="fas {{ $data['icon'] }}"></i></div>
                    <h6 class="mb-1">{{ $nombre }}</h6>
                    <h4 class="fw-bold mb-0">S/ {{ number_format($total, 2) }}</h4>
                </div>
            </div>
        @endforeach
        
        <div class="col-lg col-md-4 col-6 mb-3">
            <div class="summary-card shadow-sm text-center border">
                <div class="summary-icon text-primary"><i class="fas fa-calculator"></i></div>
                <h6 class="mb-1">UTILIDAD (I-G)</h6>
                <h4 class="fw-bold mb-0 {{ $utilidad >= 0 ? 'text-success' : 'text-danger' }}">S/ {{ number_format($utilidad, 2) }}</h4>
            </div>
        </div>
    </div>

    <!-- Tablas por clase -->
    <div class="row">
        @foreach($clases as $nombre => $data)
            @if(isset($cuentasPorClase[strtoupper($nombre)]) && count($cuentasPorClase[strtoupper($nombre)]) > 0)
                @php
                    $cuentas = $cuentasPorClase[strtoupper($nombre)];
                    $total = collect($cuentas)->sum('saldo');
                @endphp
                <div class="col-lg-6">
                    <div class="class-card shadow-sm">
                        <div class="class-card-header {{ $data['color'] }}">
                            <i class="fas {{ $data['icon'] }} me-2"></i>
                            {{ $nombre }}
                        </div>
                        <div class="class-card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cuenta</th>
                                            <th class="text-end">Saldo</th>
                                            <th class="text-end" style="width: 120px;">% del Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cuentas as $cuenta)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('contador.libro-mayor.cuenta', $cuenta->cuenta) }}" class="text-decoration-none">
                                                        {{ $cuenta->cuenta }}
                                                    </a>
                                                </td>
                                                <td class="text-end">S/ {{ number_format($cuenta->saldo, 2) }}</td>
                                                <td class="text-end">
                                                    @php
                                                        $porcentaje = $total > 0 ? ($cuenta->saldo / $total) * 100 : 0;
                                                    @endphp
                                                    <small class="text-muted">{{ number_format($porcentaje, 1) }}%</small>
                                                    <div class="progress" style="height: 5px;">
                                                        <div class="progress-bar {{ $data['color'] }}" role="progressbar" style="width: {{ $porcentaje }}%;" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-dark">
                                        <tr class="total-row">
                                            <td>TOTAL {{ $nombre }}</td>
                                            <td class="text-end">S/ {{ number_format($total, 2) }}</td>
                                            <td class="text-end">100.0%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Botones de acción -->
    <div class="d-flex justify-content-center mt-4 gap-2 flex-wrap">
        <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver al Balance
        </a>
        <a href="{{ route('contador.balance-comprobacion.verificar', request()->query()) }}" class="btn btn-warning">
            <i class="fas fa-check-circle me-2"></i>Verificar Integridad
        </a>
        <button class="btn btn-success" onclick="exportarClases()">
            <i class="fas fa-download me-2"></i>Exportar Excel
        </button>
    </div>

</div>
@endsection

@push('scripts')
<script>
function exportarClases() {
    // Tomar los valores actuales de los filtros
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]').value;
    const fechaFin = document.querySelector('input[name="fecha_fin"]').value;

    const params = new URLSearchParams({
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        formato: 'clases' // Especificar el formato
    });
    window.location.href = `{{ route('contador.balance-comprobacion.exportar') }}?${params}`;
}
</script>
@endpush
