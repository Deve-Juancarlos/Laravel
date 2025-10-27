{{-- Vista index.blade.php CORREGIDA para contador.libro-diario.index --}}
@extends('layouts.app') {{-- Usar tu layout --}}

@section('title', 'Libro Diario - SIFANO')

@section('content')
<div class="container-fluid p-0">
    {{-- Header con gradiente --}}
    <div class="d-flex justify-content-between align-items-center p-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white;">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-book"></i> Libro Diario
            </h1>
            <p class="mb-0 opacity-75">Registro completo de asientos contables - SIFANO</p>
        </div>
        <div>
            <a href="{{ route('contador.libro-diario.create') }}" class="btn btn-light btn-lg">
                <i class="fas fa-plus"></i> Nuevo Asiento
            </a>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Alertas contables --}}
    @if(!empty($alertas))
        @foreach($alertas as $alerta)
            <div class="alert alert-{{ $alerta['tipo'] === 'warning' ? 'warning' : 'info' }} alert-dismissible fade show" role="alert">
                <i class="fas fa-{{ $alerta['icono'] }}"></i> 
                <strong>{{ $alerta['titulo'] }}:</strong> {{ $alerta['mensaje'] }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endforeach
    @endif

    {{-- Estadísticas en cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background-color: rgba(59, 130, 246, 0.1);">
                                <i class="fas fa-clipboard-list text-primary" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold text-dark" style="font-size: 1.8rem;">{{ number_format($totales['total_asientos']) }}</div>
                            <div class="text-muted">Total Asientos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background-color: rgba(16, 185, 129, 0.1);">
                                <i class="fas fa-arrow-up text-success" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold text-success" style="font-size: 1.8rem;">S/ {{ number_format($totales['total_debe'], 2) }}</div>
                            <div class="text-muted">Total Debe</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background-color: rgba(239, 68, 68, 0.1);">
                                <i class="fas fa-arrow-down text-danger" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold text-danger" style="font-size: 1.8rem;">S/ {{ number_format($totales['total_haber'], 2) }}</div>
                            <div class="text-muted">Total Haber</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background-color: rgba(139, 92, 246, 0.1);">
                                <i class="fas fa-chart-line text-info" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold text-info" style="font-size: 1.8rem;">S/ {{ number_format($totales['promedio_asiento'], 2) }}</div>
                            <div class="text-muted">Promedio Asiento</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter"></i> Filtros de Búsqueda
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('contador.libro-diario.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" name="fecha_inicio" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" name="fecha_fin" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Número Asiento</label>
                        <input type="text" class="form-control" name="numero_asiento" value="{{ request('numero_asiento') }}" placeholder="Ej: 2025001">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cuenta Contable</label>
                        <select class="form-select" name="cuenta_contable">
                            <option value="">Todas las cuentas</option>
                            @foreach($cuentasContables as $cuenta)
                                <option value="{{ $cuenta->codigo }}" {{ request('cuenta_contable') == $cuenta->codigo ? 'selected' : '' }}>
                                    {{ $cuenta->codigo }} - {{ $cuenta->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="{{ route('contador.libro-diario.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de Asientos --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-table"></i> Asientos Contables
            </h5>
            <div>
                <button class="btn btn-success btn-sm me-2" onclick="exportarExcel()">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn btn-danger btn-sm" onclick="exportarPDF()">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($asientos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th>Glosa</th>
                                <th class="text-end">Total Debe</th>
                                <th class="text-end">Total Haber</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asientos as $asiento)
                            <tr>
                                <td>
                                    <strong class="text-primary">{{ $asiento->numero }}</strong>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($asiento->fecha)->format('d/m/Y') }}</td>
                                <td>{{ Str::limit($asiento->glosa, 50) }}</td>
                                <td class="text-end">
                                    <span class="text-success fw-bold">S/ {{ number_format($asiento->total_debe, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-danger fw-bold">S/ {{ number_format($asiento->total_haber, 2) }}</span>
                                </td>
                                <td>
                                    @if($asiento->balanceado)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Balanceado
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('contador.libro-diario.show', $asiento->id) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('contador.libro-diario.edit', $asiento->id) }}" class="btn btn-sm btn-outline-secondary ms-1" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list" style="font-size: 4rem; color: #6c757d;"></i>
                    <h5 class="mt-3 text-muted">No se encontraron asientos contables</h5>
                    <p class="text-muted">Los asientos aparecerán aquí según los filtros seleccionados.</p>
                    <a href="{{ route('contador.libro-diario.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Primer Asiento
                    </a>
                </div>
            @endif
        </div>
        
        {{-- Paginación --}}
        @if($asientos instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="card-footer">
                {{ $asientos->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de asientos por mes
    const ctx = document.getElementById('asientosChart').getContext('2d');
    
    @if(isset($graficoAsientosPorMes))
    const asientosChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($graficoAsientosPorMes['labels']),
            datasets: [{
                label: 'Cantidad de Asientos',
                data: @json($graficoAsientosPorMes['data']),
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    @endif

    function exportarExcel() {
        const params = new URLSearchParams(window.location.search);
        params.set('formato', 'excel');
        window.location.href = `{{ route('contador.libro-diario.exportar') }}?${params}`;
    }

    function exportarPDF() {
        const params = new URLSearchParams(window.location.search);
        params.set('formato', 'pdf');
        window.location.href = `{{ route('contador.libro-diario.exportar') }}?${params}`;
    }
</script>
@endpush