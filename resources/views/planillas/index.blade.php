<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planillas de Cobranza - Sistema Farmacéutico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .filtros-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .estado-pendiente { background-color: #ffc107; }
        .estado-proceso { background-color: #17a2b8; }
        .estado-completada { background-color: #28a745; }
        .estado-cancelada { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-file-invoice-dollar text-primary"></i> Planillas de Cobranza</h2>
                        <p class="text-muted mb-0">Gestión y seguimiento de cobranzas</p>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('planillas-cobranza.nueva') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nueva Planilla
                        </a>
                        <a href="{{ route('planillas-cobranza.reporte-efectividad') }}" class="btn btn-outline-primary">
                            <i class="fas fa-chart-bar"></i> Reportes
                        </a>
                    </div>
                </div>
                <hr>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x mb-3"></i>
                        <h3>{{ $planillas->where('Estado', 1)->count() }}</h3>
                        <p class="mb-0">Pendientes</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card stat-card h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <div class="card-body text-center">
                        <i class="fas fa-spinner fa-2x mb-3"></i>
                        <h3>{{ $planillas->where('Estado', 2)->count() }}</h3>
                        <p class="mb-0">En Proceso</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card stat-card h-100" style="background: linear-gradient(135deg, #11998e 0%, #28a745 100%);">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x mb-3"></i>
                        <h3>{{ $planillas->where('Estado', 3)->count() }}</h3>
                        <p class="mb-0">Completadas</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card stat-card h-100" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <div class="card-body text-center">
                        <i class="fas fa-percentage fa-2x mb-3"></i>
                        <h3>{{ $estadisticas['efectividad_mes'] ?? 0 }}%</h3>
                        <p class="mb-0">Efectividad Mes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row">
            <div class="col-12">
                <div class="filtros-section">
                    <form method="GET" action="{{ route('planillas-cobranza.index') }}">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-filter text-primary"></i> Estado
                                </label>
                                <select name="estado" class="form-select">
                                    <option value="todas" {{ $estado == 'todas' ? 'selected' : '' }}>Todas</option>
                                    <option value="1" {{ $estado == '1' ? 'selected' : '' }}>Pendientes</option>
                                    <option value="2" {{ $estado == '2' ? 'selected' : '' }}>En Proceso</option>
                                    <option value="3" {{ $estado == '3' ? 'selected' : '' }}>Completadas</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-calendar-alt text-success"></i> Período
                                </label>
                                <input type="month" 
                                       name="fecha" 
                                       class="form-control" 
                                       value="{{ $fecha }}"
                                       id="fechaInput">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                                        <i class="fas fa-eraser"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista de Planillas -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-list text-primary"></i> Planillas</h5>
                            <span class="badge bg-primary">{{ $planillas->total() }} total</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($planillas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaPlanillas">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fas fa-hashtag"></i> Código</th>
                                        <th><i class="fas fa-user"></i> Cliente</th>
                                        <th><i class="fas fa-user-tie"></i> Vendedor</th>
                                        <th><i class="fas fa-calendar"></i> Fecha</th>
                                        <th><i class="fas fa-calendar-check"></i> Vence</th>
                                        <th><i class="fas fa-info-circle"></i> Estado</th>
                                        <th><i class="fas fa-cogs"></i> Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($planillas as $planilla)
                                    <tr>
                                        <td>
                                            <strong>{{ $planilla->CodPlanilla }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ Str::limit($planilla->cliente_nombre, 25) }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $planilla->RucDni ?? 'Sin documento' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            {{ Str::limit($planilla->vendedor_nombre, 20) }}
                                        </td>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($planilla->Fecha)->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($planilla->FechaVence)->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            @if($planilla->Estado == 1)
                                                <span class="badge estado-pendiente">Pendiente</span>
                                            @elseif($planilla->Estado == 2)
                                                <span class="badge estado-proceso">En Proceso</span>
                                            @elseif($planilla->Estado == 3)
                                                <span class="badge estado-completada">Completada</span>
                                            @else
                                                <span class="badge estado-cancelada">Cancelada</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('planillas-cobranza.ver', [$planilla->CodPlanilla[0:3], $planilla->CodPlanilla]) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if($planilla->Estado == 1)
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="procesarPlanilla('{{ $planilla->CodPlanilla }}')" title="Procesar">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                                @endif
                                                
                                                @if($planilla->Estado == 1)
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="eliminarPlanilla('{{ $planilla->CodPlanilla }}')" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Mostrando {{ $planillas->firstItem() ?? 0 }} a {{ $planillas->lastItem() ?? 0 }} de {{ $planillas->total() }} registros
                            </div>
                            <div>
                                {{ $planillas->appends(request()->query())->links() }}
                            </div>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay planillas</h5>
                            <p class="text-muted">Comienza creando tu primera planilla de cobranza</p>
                            <a href="{{ route('planillas-cobranza.nueva') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Crear Planilla
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tablaPlanillas').DataTable({
                pageLength: 25,
                order: [[3, 'desc']], // Ordenar por fecha descendente
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                responsive: true
            });
        });

        function limpiarFiltros() {
            window.location.href = '{{ route("planillas-cobranza.index") }}';
        }

        function procesarPlanilla(codigoPlanilla) {
            if (confirm('¿Desea procesar esta planilla?')) {
                window.location.href = `/contabilidad/planillas-cobranza/planilla/${codigoPlanilla}/procesar`;
            }
        }

        function eliminarPlanilla(codigoPlanilla) {
            if (confirm('¿Está seguro de eliminar esta planilla? Esta acción no se puede deshacer.')) {
                $.ajax({
                    url: `/contabilidad/planillas-cobranza/planilla/${codigoPlanilla}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error al eliminar la planilla');
                    }
                });
            }
        }
    </script>
</body>
</html>