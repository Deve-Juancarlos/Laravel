@extends('layouts.contador')

@section('title', 'Registro de Ventas - SIFANO')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('registros.contabilidad') }}">Registros Contables</a></li>
    <li class="breadcrumb-item active">Ventas</li>
@endsection

@section('contador-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-shopping-cart text-success me-2"></i>
        Registro de Ventas
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success" onclick="exportarVentas()">
            <i class="fas fa-download me-2"></i>
            Exportar
        </button>
        <button class="btn btn-outline-primary" onclick="generarLibroVentas()">
            <i class="fas fa-book me-2"></i>
            Libro de Ventas
        </button>
        <button class="btn btn-outline-info" onclick="conciliarConFacturas()">
            <i class="fas fa-link me-2"></i>
            Conciliar
        </button>
    </div>
</div>

<!-- Filtros y Búsqueda -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            Filtros de Búsqueda
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('registros.ventas') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Fecha Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo de Documento</label>
                <select name="tipo_documento" class="form-select">
                    <option value="">Todos</option>
                    <option value="factura" {{ request('tipo_documento') === 'factura' ? 'selected' : '' }}>Factura</option>
                    <option value="boleta" {{ request('tipo_documento') === 'boleta' ? 'selected' : '' }}>Boleta</option>
                    <option value="nota_credito" {{ request('tipo_documento') === 'nota_credito' ? 'selected' : '' }}>Nota de Crédito</option>
                    <option value="nota_debito" {{ request('tipo_documento') === 'nota_debito' ? 'selected' : '' }}>Nota de Débito</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="emitida" {{ request('estado') === 'emitida' ? 'selected' : '' }}>Emitida</option>
                    <option value="anulada" {{ request('estado') === 'anulada' ? 'selected' : '' }}>Anulada</option>
                    <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cliente</label>
                <select name="cliente_id" class="form-select select2">
                    <option value="">Todos los clientes</option>
                    @foreach($clientes ?? [] as $cliente)
                        <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->documento }} - {{ $cliente->razon_social }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Monto Mínimo</label>
                <input type="number" name="monto_min" class="form-control" step="0.01" 
                       value="{{ request('monto_min') }}" placeholder="0.00">
            </div>
            <div class="col-md-2">
                <label class="form-label">Monto Máximo</label>
                <input type="number" name="monto_max" class="form-control" step="0.01" 
                       value="{{ request('monto_max') }}" placeholder="999999.99">
            </div>
            <div class="col-md-2">
                <label class="form-label">Vendedor</label>
                <select name="vendedor_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($vendedores ?? [] as $vendedor)
                        <option value="{{ $vendedor->id }}" {{ request('vendedor_id') == $vendedor->id ? 'selected' : '' }}>
                            {{ $vendedor->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">SUNAT</label>
                <select name="estado_sunat" class="form-select">
                    <option value="">Todos</option>
                    <option value="aceptado" {{ request('estado_sunat') === 'aceptado' ? 'selected' : '' }}>Aceptado</option>
                    <option value="observado" {{ request('estado_sunat') === 'observado' ? 'selected' : '' }}>Observado</option>
                    <option value="rechazado" {{ request('estado_sunat') === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                    <option value="pendiente" {{ request('estado_sunat') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>
                    Buscar
                </button>
                <a href="{{ route('registros.ventas') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-eraser me-2"></i>
                    Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Resumen de Ventas -->
<div class="row mb-4">
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-primary mb-2">
                    <i class="fas fa-receipt fa-2x"></i>
                </div>
                <h5 class="text-primary">{{ $totalDocumentos ?? 0 }}</h5>
                <p class="text-muted mb-0">Total Documentos</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-success mb-2">
                    <i class="fas fa-dollar-sign fa-2x"></i>
                </div>
                <h5 class="text-success">S/ {{ number_format($totalVentas ?? 0, 2) }}</h5>
                <p class="text-muted mb-0">Total Ventas</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-info mb-2">
                    <i class="fas fa-percentage fa-2x"></i>
                </div>
                <h5 class="text-info">S/ {{ number_format($totalIGV ?? 0, 2) }}</h5>
                <p class="text-muted mb-0">Total IGV</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-warning mb-2">
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
                <h5 class="text-warning">{{ number_format($ticketPromedio ?? 0, 2) }}</h5>
                <p class="text-muted mb-0">Ticket Promedio</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-success mb-2">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <h5 class="text-success">{{ $documentosAceptados ?? 0 }}</h5>
                <p class="text-muted mb-0">SUNAT Aceptado</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-danger mb-2">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <h5 class="text-danger">{{ $documentosObservados ?? 0 }}</h5>
                <p class="text-muted mb-0">SUNAT Observado</p>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Ventas -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Registro de Ventas
        </h5>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary active" onclick="cambiarVista('tabla')" id="btnTabla">
                <i class="fas fa-table me-1"></i> Tabla
            </button>
            <button class="btn btn-outline-secondary" onclick="cambiarVista('resumen')" id="btnResumen">
                <i class="fas fa-chart-bar me-1"></i> Resumen
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Vista Tabla -->
        <div id="vistaTabla">
            <div class="table-responsive">
                <table class="table table-striped data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Documento</th>
                            <th>Cliente</th>
                            <th>RUC/DNI</th>
                            <th>Base Imponible</th>
                            <th>IGV</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>SUNAT</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventas ?? [] as $venta)
                        <tr class="{{ $venta->estado === 'anulada' ? 'table-danger' : '' }}">
                            <td>{{ date('d/m/Y', strtotime($venta->fecha_emision)) }}</td>
                            <td>
                                <div>
                                    <strong>{{ $venta->tipo_documento }}</strong> #{{ $venta->numero }}
                                    @if($venta->estado === 'anulada')
                                        <span class="badge bg-danger ms-1">ANULADA</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div>{{ Str::limit($venta->cliente_razon_social, 30) }}</div>
                                @if($venta->cliente_direccion)
                                    <small class="text-muted">{{ Str::limit($venta->cliente_direccion, 25) }}</small>
                                @endif
                            </td>
                            <td>{{ $venta->cliente_documento }}</td>
                            <td class="text-end">S/ {{ number_format($venta->subtotal, 2) }}</td>
                            <td class="text-end">S/ {{ number_format($venta->igv, 2) }}</td>
                            <td class="text-end fw-bold">S/ {{ number_format($venta->total, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $venta->estado === 'emitida' ? 'success' : ($venta->estado === 'anulada' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($venta->estado) }}
                                </span>
                            </td>
                            <td>
                                @if($venta->estado_sunat === 'aceptado')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>Aceptado
                                    </span>
                                @elseif($venta->estado_sunat === 'observado')
                                    <span class="badge bg-warning">
                                        <i class="fas fa-exclamation me-1"></i>Observado
                                    </span>
                                @elseif($venta->estado_sunat === 'rechazado')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i>Rechazado
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info" onclick="verDetalleVenta({{ $venta->id }})" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="verPDF({{ $venta->id }})" title="Ver PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="enviarSUNAT({{ $venta->id }})" title="Enviar SUNAT">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No hay registros de ventas en el período seleccionado</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(($ventas ?? [])->count() > 0)
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Mostrando {{ ($ventas ?? [])->firstItem() ?? 0 }} a {{ ($ventas ?? [])->lastItem() ?? 0 }} 
                    de {{ ($ventas ?? [])->total() ?? 0 }} resultados
                </div>
                <div>
                    {{ ($ventas ?? [])->links() }}
                </div>
            </div>
            @endif
        </div>

        <!-- Vista Resumen -->
        <div id="vistaResumen" style="display: none;">
            <div class="row">
                <!-- Ventas por Tipo de Documento -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Ventas por Tipo de Documento</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="ventasTipoDocumentoChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Evolución Diaria -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Evolución de Ventas Diarias</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="evolucionVentasChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Clientes -->
            <div class="row mt-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Top 10 Clientes</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Documento</th>
                                            <th class="text-end">Cantidad</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topClientes ?? [] as $cliente)
                                        <tr>
                                            <td>{{ Str::limit($cliente->razon_social, 25) }}</td>
                                            <td>{{ $cliente->documento }}</td>
                                            <td class="text-end">{{ $cliente->cantidad_ventas }}</td>
                                            <td class="text-end fw-bold">S/ {{ number_format($cliente->total_ventas, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No hay datos</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Estado SUNAT</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="estadoSunatChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalle de Venta -->
<div class="modal fade" id="detalleVentaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleVentaContent">
                <!-- El contenido se carga dinámicamente -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function exportarVentas() {
        const params = new URLSearchParams(window.location.search);
        const url = `/registros/ventas/exportar?${params.toString()}`;
        
        showLoading();
        window.open(url, '_blank');
        hideLoading();
    }

    function generarLibroVentas() {
        const params = new URLSearchParams(window.location.search);
        const url = `/libro-ventas?${params.toString()}`;
        
        window.open(url, '_blank');
    }

    function conciliarConFacturas() {
        Swal.fire({
            title: 'Conciliación de Ventas',
            text: '¿Deseas verificar la conciliación entre ventas registradas y facturas emitidas?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, conciliar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                
                fetch('/api/registros/ventas/conciliar', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        Swal.fire('Conciliación Completada', data.message, 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message || 'Error en la conciliación', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión', 'error');
                });
            }
        });
    }

    function verDetalleVenta(ventaId) {
        showLoading();
        
        fetch(`/api/registros/ventas/${ventaId}/detalle`)
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    document.getElementById('detalleVentaContent').innerHTML = data.html;
                    const modal = new bootstrap.Modal(document.getElementById('detalleVentaModal'));
                    modal.show();
                } else {
                    Swal.fire('Error', data.message || 'Error cargando el detalle', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                Swal.fire('Error', 'Error de conexión', 'error');
            });
    }

    function verPDF(ventaId) {
        window.open(`/facturas/${ventaId}/pdf`, '_blank');
    }

    function enviarSUNAT(ventaId) {
        Swal.fire({
            title: 'Enviar a SUNAT',
            text: '¿Deseas enviar este documento a SUNAT?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, enviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                
                fetch(`/api/registros/ventas/${ventaId}/enviar-sunat`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        Swal.fire('Enviado', 'Documento enviado a SUNAT correctamente', 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message || 'Error enviando a SUNAT', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión', 'error');
                });
            }
        });
    }

    function cambiarVista(vista) {
        const tabla = document.getElementById('vistaTabla');
        const resumen = document.getElementById('vistaResumen');
        const btnTabla = document.getElementById('btnTabla');
        const btnResumen = document.getElementById('btnResumen');
        
        if (vista === 'tabla') {
            tabla.style.display = 'block';
            resumen.style.display = 'none';
            btnTabla.classList.add('active');
            btnResumen.classList.remove('active');
        } else {
            tabla.style.display = 'none';
            resumen.style.display = 'block';
            btnTabla.classList.remove('active');
            btnResumen.classList.add('active');
        }
    }

    // Gráficos para vista resumen
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de ventas por tipo de documento
        const ctx1 = document.getElementById('ventasTipoDocumentoChart');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($tiposDocumento ?? []) !!},
                    datasets: [{
                        data: {!! json_encode($cantidadesPorTipo ?? []) !!},
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Gráfico de evolución de ventas
        const ctx2 = document.getElementById('evolucionVentasChart');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: {!! json_encode($fechasEvolucion ?? []) !!},
                    datasets: [{
                        label: 'Ventas Diarias',
                        data: {!! json_encode($ventasDiarias ?? []) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'S/ ' + value.toFixed(0);
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de estado SUNAT
        const ctx3 = document.getElementById('estadoSunatChart');
        if (ctx3) {
            new Chart(ctx3, {
                type: 'pie',
                data: {
                    labels: ['Aceptados', 'Observados', 'Pendientes', 'Rechazados'],
                    datasets: [{
                        data: [
                            {{ $documentosAceptados ?? 0 }},
                            {{ $documentosObservados ?? 0 }},
                            {{ $documentosPendientes ?? 0 }},
                            {{ $documentosRechazados ?? 0 }}
                        ],
                        backgroundColor: ['#10b981', '#f59e0b', '#6b7280', '#ef4444']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    });
</script>
@endsection