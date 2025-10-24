@extends('layouts.contador')

@section('title', 'Auxiliar de Clientes - SIFANO')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('auxiliares.contabilidad') }}">Auxiliares Contables</a></li>
    <li class="breadcrumb-item active">Clientes</li>
@endsection

@section('contador-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-users text-success me-2"></i>
        Auxiliar de Clientes
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success" onclick="exportarAuxiliar()">
            <i class="fas fa-download me-2"></i>
            Exportar
        </button>
        <button class="btn btn-outline-primary" onclick="generarEstadoCuenta()">
            <i class="fas fa-file-invoice me-2"></i>
            Estados de Cuenta
        </button>
        <button class="btn btn-outline-info" onclick="conciliarSaldos()">
            <i class="fas fa-balance-scale me-2"></i>
            Conciliar
        </button>
    </div>
</div>

<!-- Filtros de Búsqueda -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            Filtros de Búsqueda
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('auxiliares.clientes') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Fecha Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-md-3">
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
                <label class="form-label">Tipo de Documento</label>
                <select name="tipo_documento" class="form-select">
                    <option value="">Todos</option>
                    <option value="factura" {{ request('tipo_documento') === 'factura' ? 'selected' : '' }}>Factura</option>
                    <option value="boleta" {{ request('tipo_documento') === 'boleta' ? 'selected' : '' }}>Boleta</option>
                    <option value="nota_credito" {{ request('tipo_documento') === 'nota_credito' ? 'selected' : '' }}>Nota de Crédito</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Saldo</label>
                <select name="tipo_saldo" class="form-select">
                    <option value="">Todos</option>
                    <option value="con_saldo" {{ request('tipo_saldo') === 'con_saldo' ? 'selected' : '' }}>Con Saldo</option>
                    <option value="sin_saldo" {{ request('tipo_saldo') === 'sin_saldo' ? 'selected' : '' }}>Sin Saldo</option>
                    <option value="saldo_favor" {{ request('tipo_saldo') === 'saldo_favor' ? 'selected' : '' }}>Saldo a Favor</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>
                    Buscar
                </button>
                <a href="{{ route('auxiliares.clientes') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-eraser me-2"></i>
                    Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Resumen de Cuentas por Cobrar -->
<div class="row mb-4">
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-primary mb-2">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <h5 class="text-primary">{{ $totalClientes ?? 0 }}</h5>
                <p class="text-muted mb-0">Total Clientes</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-success mb-2">
                    <i class="fas fa-dollar-sign fa-2x"></i>
                </div>
                <h5 class="text-success">S/ {{ number_format($totalCuentasPorCobrar ?? 0, 2) }}</h5>
                <p class="text-muted mb-0">Cuentas por Cobrar</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-info mb-2">
                    <i class="fas fa-calendar-check fa-2x"></i>
                </div>
                <h5 class="text-info">S/ {{ number_format($vencidas ?? 0, 2) }}</h5>
                <p class="text-muted mb-0">Vencidas</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-warning mb-2">
                    <i class="fas fa-clock fa-2x"></i>
                </div>
                <h5 class="text-warning">S/ {{ number_format($porVencer ?? 0, 2) }}</h5>
                <p class="text-muted mb-0">Por Vencer</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-danger mb-2">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <h5 class="text-danger">{{ $clientesConSaldo ?? 0 }}</h5>
                <p class="text-muted mb-0">Clientes con Saldo</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-secondary mb-2">
                    <i class="fas fa-percentage fa-2x"></i>
                </div>
                <h5 class="text-secondary">{{ number_format($diasPromedioCobro ?? 0, 1) }}</h5>
                <p class="text-muted mb-0">Días Promedio</p>
            </div>
        </div>
    </div>
</div>

<!-- Alertas de Cartera Vencida -->
@if(($alertasVencimiento ?? [])->count() > 0)
<div class="alert alert-warning">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle me-3"></i>
        <div>
            <strong>Alertas de Cartera Vencida:</strong>
            <span>{{ ($alertasVencimiento ?? [])->count() }} facturas vencidas requieren atención inmediata</span>
            <button class="btn btn-sm btn-outline-warning ms-3" onclick="verAlertasVencimiento()">
                Ver Detalles
            </button>
        </div>
    </div>
</div>
@endif

<!-- Tabla de Auxiliar de Clientes -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Auxiliar de Clientes
        </h5>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary active" onclick="cambiarVista('resumen')" id="btnResumen">
                <i class="fas fa-table me-1"></i> Resumen
            </button>
            <button class="btn btn-outline-secondary" onclick="cambiarVista('detalle')" id="btnDetalle">
                <i class="fas fa-list-alt me-1"></i> Detalle
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Vista Resumen -->
        <div id="vistaResumen">
            <div class="table-responsive">
                <table class="table table-striped data-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>RUC/DNI</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Límite Crédito</th>
                            <th>Saldo Actual</th>
                            <th>Último Movimiento</th>
                            <th>Días Vencimiento</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientesAuxiliar ?? [] as $cliente)
                        <tr class="{{ ($cliente->saldo_actual ?? 0) > ($cliente->limite_credito ?? 0) ? 'table-warning' : '' }}">
                            <td>
                                <div>
                                    <strong>{{ Str::limit($cliente->razon_social, 30) }}</strong>
                                    @if($cliente->tipo_cliente === 'juridico')
                                        <span class="badge bg-primary ms-1">Jurídico</span>
                                    @else
                                        <span class="badge bg-success ms-1">Natural</span>
                                    @endif
                                </div>
                                <small class="text-muted">{{ Str::limit($cliente->direccion, 35) }}</small>
                            </td>
                            <td>{{ $cliente->documento }}</td>
                            <td>{{ $cliente->telefono ?? 'N/A' }}</td>
                            <td>{{ $cliente->email ?? 'N/A' }}</td>
                            <td class="text-end">S/ {{ number_format($cliente->limite_credito ?? 0, 2) }}</td>
                            <td class="text-end fw-bold {{ ($cliente->saldo_actual ?? 0) > 0 ? 'text-danger' : (($cliente->saldo_actual ?? 0) < 0 ? 'text-success' : 'text-muted') }}">
                                S/ {{ number_format($cliente->saldo_actual ?? 0, 2) }}
                            </td>
                            <td>
                                @if($cliente->ultimo_movimiento)
                                    {{ date('d/m/Y', strtotime($cliente->ultimo_movimiento)) }}
                                @else
                                    <span class="text-muted">Sin movimientos</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($cliente->dias_vencimiento > 0)
                                    <span class="badge bg-{{ $cliente->dias_vencimiento > 30 ? 'danger' : ($cliente->dias_vencimiento > 15 ? 'warning' : 'success') }}">
                                        {{ $cliente->dias_vencimiento }} días
                                    </span>
                                @else
                                    <span class="badge bg-success">Al día</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $cliente->estado === 'activo' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($cliente->estado) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info" onclick="verEstadoCuenta({{ $cliente->id }})" title="Estado de Cuenta">
                                        <i class="fas fa-file-invoice"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="verDetalleCliente({{ $cliente->id }})" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="editarCliente({{ $cliente->id }})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <p>No hay clientes registrados</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(($clientesAuxiliar ?? [])->count() > 0)
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Mostrando {{ ($clientesAuxiliar ?? [])->firstItem() ?? 0 }} a {{ ($clientesAuxiliar ?? [])->lastItem() ?? 0 }} 
                    de {{ ($clientesAuxiliar ?? [])->total() ?? 0 }} resultados
                </div>
                <div>
                    {{ ($clientesAuxiliar ?? [])->links() }}
                </div>
            </div>
            @endif
        </div>

        <!-- Vista Detalle -->
        <div id="vistaDetalle" style="display: none;">
            @forelse($clientesAuxiliar ?? [] as $cliente)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">
                            {{ $cliente->razon_social }}
                            <span class="badge bg-{{ $cliente->estado === 'activo' ? 'success' : 'secondary' }} ms-2">
                                {{ ucfirst($cliente->estado) }}
                            </span>
                        </h6>
                        <small class="text-muted">{{ $cliente->documento }} - {{ $cliente->telefono ?? 'Sin teléfono' }}</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold {{ ($cliente->saldo_actual ?? 0) > 0 ? 'text-danger' : (($cliente->saldo_actual ?? 0) < 0 ? 'text-success' : 'text-muted') }}">
                            Saldo: S/ {{ number_format($cliente->saldo_actual ?? 0, 2) }}
                        </div>
                        <small class="text-muted">
                            Límite: S/ {{ number_format($cliente->limite_credito ?? 0, 2) }}
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    @if(($cliente->movimientos ?? [])->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Documento</th>
                                    <th>Concepto</th>
                                    <th class="text-end">Debe</th>
                                    <th class="text-end">Haber</th>
                                    <th class="text-end">Saldo</th>
                                    <th>Vencimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->movimientos ?? [] as $movimiento)
                                <tr class="{{ $movimiento->dias_vencimiento > 0 && $movimiento->dias_vencimiento <= 0 ? 'table-danger' : '' }}">
                                    <td>{{ date('d/m/Y', strtotime($movimiento->fecha)) }}</td>
                                    <td>{{ $movimiento->numero_documento }}</td>
                                    <td>{{ Str::limit($movimiento->concepto, 30) }}</td>
                                    <td class="text-end text-danger">
                                        @if($movimiento->debe > 0)
                                            S/ {{ number_format($movimiento->debe, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end text-success">
                                        @if($movimiento->haber > 0)
                                            S/ {{ number_format($movimiento->haber, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold {{ $movimiento->saldo >= 0 ? 'text-success' : 'text-danger' }}">
                                        S/ {{ number_format($movimiento->saldo, 2) }}
                                    </td>
                                    <td>
                                        @if($movimiento->fecha_vencimiento)
                                            {{ date('d/m/Y', strtotime($movimiento->fecha_vencimiento)) }}
                                            @if($movimiento->dias_vencimiento > 0)
                                                <span class="badge bg-{{ $movimiento->dias_vencimiento > 30 ? 'danger' : ($movimiento->dias_vencimiento > 15 ? 'warning' : 'success') }} ms-1">
                                                    {{ $movimiento->dias_vencimiento }} días
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">Sin vencimiento</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-inbox mb-2"></i>
                        <p>No hay movimientos para este cliente</p>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="fas fa-users fa-2x mb-2"></i>
                <p>No hay clientes para mostrar</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal de Alertas de Vencimiento -->
<div class="modal fade" id="alertasVencimientoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alertas de Cartera Vencida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Documento</th>
                                <th>Fecha Vencimiento</th>
                                <th>Monto</th>
                                <th>Días Vencidos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($alertasVencimiento ?? [] as $alerta)
                            <tr class="table-warning">
                                <td>{{ $alerta->cliente_nombre }}</td>
                                <td>{{ $alerta->numero_documento }}</td>
                                <td>{{ date('d/m/Y', strtotime($alerta->fecha_vencimiento)) }}</td>
                                <td class="fw-bold">S/ {{ number_format($alerta->monto, 2) }}</td>
                                <td>
                                    <span class="badge bg-danger">{{ $alerta->dias_vencidos }} días</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="contactarCliente({{ $alerta->cliente_id }})">
                                        Contactar
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay alertas de vencimiento</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function exportarAuxiliar() {
        const params = new URLSearchParams(window.location.search);
        const url = `/auxiliares/clientes/exportar?${params.toString()}`;
        
        showLoading();
        window.open(url, '_blank');
        hideLoading();
    }

    function generarEstadoCuenta() {
        const params = new URLSearchParams(window.location.search);
        const url = `/estados-cuenta/clientes?${params.toString()}`;
        
        window.open(url, '_blank');
    }

    function conciliarSaldos() {
        Swal.fire({
            title: 'Conciliación de Saldos',
            text: '¿Deseas verificar la conciliación de saldos con el libro mayor?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, conciliar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                
                fetch('/api/auxiliares/clientes/conciliar', {
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

    function verEstadoCuenta(clienteId) {
        const url = `/estados-cuenta/clientes/${clienteId}`;
        window.open(url, '_blank');
    }

    function verDetalleCliente(clienteId) {
        showLoading();
        
        fetch(`/api/auxiliares/clientes/${clienteId}/detalle`)
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    Swal.fire({
                        title: data.cliente.razon_social,
                        html: data.html,
                        width: '800px',
                        showConfirmButton: false
                    });
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

    function editarCliente(clienteId) {
        const url = `/clientes/${clienteId}/edit`;
        window.open(url, '_blank');
    }

    function verAlertasVencimiento() {
        const modal = new bootstrap.Modal(document.getElementById('alertasVencimientoModal'));
        modal.show();
    }

    function contactarCliente(clienteId) {
        // Implementar funcionalidad de contacto
        Swal.fire('Función disponible próximamente', 'La funcionalidad de contacto estará disponible en una actualización futura.', 'info');
    }

    function cambiarVista(vista) {
        const resumen = document.getElementById('vistaResumen');
        const detalle = document.getElementById('vistaDetalle');
        const btnResumen = document.getElementById('btnResumen');
        const btnDetalle = document.getElementById('btnDetalle');
        
        if (vista === 'resumen') {
            resumen.style.display = 'block';
            detalle.style.display = 'none';
            btnResumen.classList.add('active');
            btnDetalle.classList.remove('active');
        } else {
            resumen.style.display = 'none';
            detalle.style.display = 'block';
            btnResumen.classList.remove('active');
            btnDetalle.classList.add('active');
        }
    }

    // Auto-actualizar alertas cada 5 minutos
    setInterval(function() {
        fetch('/api/auxiliares/clientes/alertas')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.nuevas_alertas > 0) {
                    // Mostrar notificación de nuevas alertas
                    Swal.fire({
                        title: 'Nuevas Alertas',
                        text: `${data.nuevas_alertas} nueva(s) alerta(s) de vencimiento`,
                        icon: 'warning',
                        timer: 5000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                }
            })
            .catch(error => {
                console.error('Error checking alerts:', error);
            });
    }, 300000); // 5 minutos
</script>
@endsection