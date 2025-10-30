@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active"><i class="fas fa-chart-line"></i> Estado de Resultados</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary">
                <i class="fas fa-chart-line me-2"></i>
                Estado de Resultados SUNAT
            </h2>
            <p class="text-muted mb-0">Análisis de ingresos, costos y gastos - Distribuidora de Fármacos</p>
        </div>
        <div class="text-end">
            <small class="text-muted">Período: 
                <strong>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</strong>
            </small>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.estado-resultados.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i> Aplicar Filtros
                        </button>
                        <a href="{{ route('contador.estado-resultados.periodos') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-calendar-alt"></i> Ver Períodos
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas Principales -->
    <div class="row mb-4">
        @php
            $totalVentas = $ventasNetas ?? 0;
            $totalCostoVentas = $costoVentas ?? 0;
            $totalGastos = $resultados['gastos_operativos'] ?? 0;
            $utilidadBruta = $resultados['utilidad_bruta'] ?? 0;
            $utilidadOperativa = $resultados['utilidad_operativa'] ?? 0;
            $utilidadNeta = $resultados['utilidad_neta'] ?? 0;
            $margenBruto = $resultados['margen_bruto'] ?? 0;
            $margenOperativo = $resultados['margen_operativo'] ?? 0;
            $margenNeto = $resultados['margen_neto'] ?? 0;
        @endphp

        @foreach([
            ['title'=>'Ventas Netas','value'=>$totalVentas,'icon'=>'shopping-cart','bg'=>'primary'],
            ['title'=>'Costo de Ventas','value'=>$totalCostoVentas,'icon'=>'truck','bg'=>'success'],
            ['title'=>'Utilidad Bruta','value'=>$utilidadBruta,'icon'=>'chart-bar','bg'=>'info','margen'=>$margenBruto],
            ['title'=>'Utilidad Neta','value'=>$utilidadNeta,'icon'=>'dollar-sign','bg'=>$utilidadNeta>=0?'warning':'danger','margen'=>$margenNeto]
        ] as $card)
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-{{ $card['bg'] }} text-white">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0">{{ $card['title'] }}</h5>
                        <h3>{{ number_format($card['value'], 2) }}</h3>
                        @if(isset($card['margen']))
                        <small>S/. ({{ number_format($card['margen'],1) }}%)</small>
                        @endif
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-{{ $card['icon'] }} fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Tabla Detallada -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i> Estado de Resultados Detallado</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Concepto</th>
                                <th class="text-end">Importe (S/.)</th>
                                <th class="text-end">% Ventas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- INGRESOS -->
                            <tr class="table-success"><td colspan="3"><strong>INGRESOS</strong></td></tr>
                            <tr>
                                <td>Ventas Netas</td>
                                <td class="text-end">{{ number_format($totalVentas,2) }}</td>
                                <td class="text-end">100%</td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>TOTAL INGRESOS</strong></td>
                                <td class="text-end"><strong>{{ number_format($totalVentas,2) }}</strong></td>
                                <td class="text-end"><strong>100%</strong></td>
                            </tr>

                            <!-- COSTOS Y GASTOS -->
                            <tr class="table-warning"><td colspan="3"><strong>COSTOS Y GASTOS</strong></td></tr>
                            <tr>
                                <td>Costo de Ventas</td>
                                <td class="text-end">{{ number_format($totalCostoVentas,2) }}</td>
                                <td class="text-end">{{ $totalVentas>0 ? number_format(($totalCostoVentas/$totalVentas)*100,1) : 0 }}%</td>
                            </tr>
                            <tr>
                                <td>Gastos Operativos</td>
                                <td class="text-end">{{ number_format($totalGastos,2) }}</td>
                                <td class="text-end">{{ $totalVentas>0 ? number_format(($totalGastos/$totalVentas)*100,1) : 0 }}%</td>
                            </tr>
                            <tr class="table-warning">
                                <td><strong>TOTAL COSTOS Y GASTOS</strong></td>
                                <td class="text-end"><strong>{{ number_format($totalCostoVentas+$totalGastos,2) }}</strong></td>
                                <td class="text-end"><strong>{{ $totalVentas>0 ? number_format((($totalCostoVentas+$totalGastos)/$totalVentas)*100,1) : 0 }}%</strong></td>
                            </tr>

                            <!-- UTILIDAD NETA -->
                            <tr class="{{ $utilidadNeta>=0?'table-success':'table-danger' }}">
                                <td><strong>UTILIDAD NETA</strong></td>
                                <td class="text-end"><strong>{{ number_format($utilidadNeta,2) }}</strong></td>
                                <td class="text-end"><strong>{{ number_format($margenNeto,1) }}%</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas de Ingresos y Gastos por Cuenta -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Cuentas de Ingresos (7xxx)</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Cuenta</th><th>Descripción</th><th class="text-end">Total (S/.)</th></tr>
                        </thead>
                        <tbody>
                            @foreach($ingresos as $ingreso)
                            <tr>
                                <td><code>{{ $ingreso->cuenta_contable ?? '---' }}</code></td>
                                <td>{{ $ingreso->descripcion ?? '---' }}</td>
                                <td class="text-end">{{ number_format($ingreso->total,2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-minus-circle me-2"></i> Cuentas de Gastos (5xxx)</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Cuenta</th><th>Descripción</th><th class="text-end">Total (S/.)</th></tr>
                        </thead>
                        <tbody>
                            @foreach($gastos as $gasto)
                            <tr>
                                <td><code>{{ $gasto->cuenta_contable ?? '---' }}</code></td>
                                <td>{{ $gasto->descripcion ?? '---' }}</td>
                                <td class="text-end">{{ number_format($gasto->total,2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparación con período anterior -->
    @if(isset($comparacion))
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> Comparación con Período Anterior</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartComparacion" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Botones de acción -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex justify-content-between flex-wrap gap-2">
                    <div>
                        <a href="{{ route('contador.estado-resultados.periodos') }}" class="btn btn-info">
                            <i class="fas fa-calendar-alt"></i> Ver Períodos
                        </a>
                        <a href="{{ route('contador.estado-resultados.comparativo') }}" class="btn btn-warning">
                            <i class="fas fa-comparison"></i> Comparativo
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('contador.estado-resultados.detalle',['cuenta'=>'all']) }}" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i> Ver Detalles
                        </a>
                        <a href="{{ route('contador.estado-resultados.exportar') }}" class="btn btn-primary">
                            <i class="fas fa-download"></i> Exportar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($comparacion))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartComparacion').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Ventas','Costo Ventas','Utilidad Bruta','Gastos','Utilidad Neta'],
            datasets: [
                {
                    label: 'Actual',
                    data: [
                        {{ $comparacion['actual']['ventas_netas'] ?? 0 }},
                        {{ $comparacion['actual']['costo_ventas'] ?? 0 }},
                        {{ $comparacion['actual']['utilidad_bruta'] ?? 0 }},
                        {{ $comparacion['actual']['gastos_operativos'] ?? 0 }},
                        {{ $comparacion['actual']['utilidad_operativa'] ?? 0 }}
                    ],
                    backgroundColor: 'rgba(54,162,235,0.8)'
                },
                {
                    label: 'Anterior',
                    data: [
                        {{ $comparacion['anterior']['ventas_netas'] ?? 0 }},
                        {{ $comparacion['anterior']['costo_ventas'] ?? 0 }},
                        {{ $comparacion['anterior']['utilidad_bruta'] ?? 0 }},
                        {{ $comparacion['anterior']['gastos_operativos'] ?? 0 }},
                        {{ $comparacion['anterior']['utilidad_operativa'] ?? 0 }}
                    ],
                    backgroundColor: 'rgba(153,102,255,0.8)'
                }
            ]
        },
        options: {
            responsive:true,
            plugins: {
                title: { display:true, text:'Comparación de Períodos' }
            },
            scales: {
                y: {
                    beginAtZero:true,
                    ticks: {
                        callback: function(val){ return 'S/. '+val.toLocaleString(); }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endif

@endsection
