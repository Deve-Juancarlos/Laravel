@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Reporte de Ventas vs Cobranzas')

@push('styles')
    {{-- Reutilizamos la misma hoja de estilos del dashboard si tiene estilos de gráficos --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard/contador.css') }}">
@endpush

{{-- Encabezado del Reporte --}}
@section('header-content')
    <div class="dashboard-header">
        <div class="header-content">
            <h1><i class="fas fa-file-invoice-dollar me-3"></i>Reporte: Flujo de Ventas vs Cobranzas</h1>
            <p class="subtitle">Análisis detallado de la facturación contra el ingreso real (últimos 12 meses)</p>
        </div>
    </div>
@endsection

@section('breadcrumbs')
    {{-- 🚀 CORRECCIÓN 1: La ruta al dashboard principal es 'contador.dashboard' --}}
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Reporte Ventas vs Cobranzas</li>
@endsection

@section('content')

<div class="container-fluid">

    {{-- Fila de Filtros y Acciones --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                    
                    {{-- Sección de Filtros (¡Importante para el futuro!) --}}
                    <div class="d-flex flex-wrap align-items-center me-3">
                        <label for="date-range" class="form-label me-2 fw-600 mb-0">Rango de Fechas:</label>
                        <input type="text" id="date-range" class="form-control" style="width: 250px;" placeholder="Seleccionar rango..." disabled>
                        <button class="btn btn-primary ms-2" disabled><i class="fas fa-filter me-1"></i> Aplicar</button>
                    </div>

                    {{-- Sección de Exportación --}}
                    <div>
                        {{-- 🚀 CORRECCIÓN 2: Usamos un nombre de ruta limpio que definiremos --}}
                        <a href="{{ route('contador.reportes.ventas.flujo-comparativo.excel') }}" class="btn btn-success-soft">
                            <i class="fas fa-file-excel me-1"></i> Exportar a Excel
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Fila del Gráfico --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h6><i class="fas fa-chart-line me-2"></i>Comparativo Anual (12 Meses)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 400px;">
                        <canvas id="reporteChart" 
                            data-labels='@json($labels)'
                            data-ventas='@json($ventasData)'
                            data-cobranzas='@json($cobranzasData)'>
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fila de la Tabla de Datos --}}
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h6><i class="fas fa-table me-2"></i>Datos Detallados</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover modern-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mes</th>
                                    <th class="text-end">Total Ventas (Facturado)</th>
                                    <th class="text-end">Total Cobranzas (Ingreso)</th>
                                    <th class="text-end">Brecha (Diferencia)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($datosTabla as $dato)
                                    @php
                                        $brecha = $dato['cobranzas'] - $dato['ventas'];
                                    @endphp
                                    <tr>
                                        <td class="fw-600">{{ $dato['mes'] }}</td>
                                        <td class="text-end">S/ {{ number_format($dato['ventas'], 2) }}</td>
                                        <td class="text-end">S/ {{ number_format($dato['cobranzas'], 2) }}</td>
                                        <td class="text-end {{ $brecha >= 0 ? 'text-success' : 'text-danger' }}">
                                            S/ {{ number_format($brecha, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center p-5">
                                            <div class="empty-state">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <h5>No se encontraron datos</h5>
                                                <p>No hay información para el rango de fechas seleccionado.</p>
                                            </div>
                                        </td>
                                    </tr>                                    
                                @endforelse 
                                
                                {{-- Fila de Totales --}}
                                @if(count($datosTabla) > 0)
                                    @php
                                        $totalVentas = array_sum(array_column($datosTabla, 'ventas'));
                                        $totalCobranzas = array_sum(array_column($datosTabla, 'cobranzas'));
                                        $totalBrecha = $totalCobranzas - $totalVentas;
                                    @endphp
                                    <tr class="table-light fw-bold" style="border-top: 2px solid #6c757d;">
                                        <td>TOTALES</td>
                                        <td class="text-end">S/ {{ number_format($totalVentas, 2) }}</td>
                                        <td class="text-end">S/ {{ number_format($totalCobranzas, 2) }}</td>
                                        <td class="text-end {{ $totalBrecha >= 0 ? 'text-success' : 'text-danger' }}">
                                            S/ {{ number_format($totalBrecha, 2) }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    
    {{-- 🚀 CORRECCIÓN 3: ¡El motor de gráficos! Esto es necesario para que 'new Chart' funcione. --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- 
      Script inline para esta página. 
      Dibuja el gráfico 'reporteChart' leyendo los datos del HTML.
    --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initReporteChart();
        });

        function initReporteChart() {
            const ctxReporte = document.getElementById('reporteChart');
            if (!ctxReporte) return;

            const labels = JSON.parse(ctxReporte.dataset.labels || '[]');
            const ventasData = JSON.parse(ctxReporte.dataset.ventas || '[]');
            const cobranzasData = JSON.parse(ctxReporte.dataset.cobranzas || '[]');

            const tooltipLabelFormatter = (context) => {
                let label = context.dataset.label || '';
                if (label) label += ': ';
                if (context.parsed.y !== null) {
                    label += context.parsed.y.toLocaleString('es-PE', { style: 'currency', currency: 'PEN' });
                }
                return label;
            };

            const yAxisTickFormatter = (value) => 'S/ ' + value.toLocaleString('es-PE');

            new Chart(ctxReporte, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Ventas',
                            data: ventasData,
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Cobranzas',
                            data: cobranzasData,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: yAxisTickFormatter, color: '#6c757d' } },
                        x: { ticks: { color: '#6c757d' } }
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: { label: tooltipLabelFormatter }
                        }
                    },
                    interaction: { mode: 'index', intersect: false }
                }
            });
        }
    </script>
@endpush