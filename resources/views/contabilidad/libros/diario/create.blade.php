{{-- Vista create.blade.php CORREGIDA para contador.libro-diario.create --}}
@extends('layouts.app') 

@section('title', 'Nuevo Asiento - Libro Diario')

@push('styles')
    <link href="{{ asset('css/contabilidad/libro-diario-create.css') }}" rel="stylesheet">
@endpush

@section('sidebar-menu')
{{-- MENÚ PRINCIPAL --}}
<div class="nav-section">Dashboard</div>
<ul>
    <li><a href="{{ route('dashboard.contador') }}" class="nav-link">
        <i class="fas fa-chart-pie"></i> Panel Principal
    </a></li>
</ul>

<div class="nav-section">Contabilidad</div>
<ul>
    <li>
        <a href="{{ route('contador.libro-diario.index') }}" class="nav-link has-submenu">
            <i class="fas fa-book"></i> Libros Contables
        </a>
        <div class="nav-submenu">
            <a href="{{ route('contador.libro-diario.index') }}" class="nav-link"><i class="fas fa-file-alt"></i> Libro Diario</a>
            <a href="{{ route('contador.libro-mayor.index') }}" class="nav-link"><i class="fas fa-book-open"></i> Libro Mayor</a>
            <a href="{{route('contador.balance-comprobacion.index')}}" class="nav-link"><i class="fas fa-balance-scale"></i> Balance Comprobación</a>    
            <a href="{{ route('contador.estado-resultados.index') }}" class="nav-link"><i class="fas fa-chart-bar"></i> Estados Financieros</a>
        </div>
    </li>
    <li>
        <a href="#" class="nav-link has-submenu">
            <i class="fas fa-file-invoice"></i> Registros
        </a>
        <div class="nav-submenu">
            <a href="#" class="nav-link"><i class="fas fa-shopping-cart"></i> Compras</a>
            <a href="#" class="nav-link"><i class="fas fa-cash-register"></i> Ventas</a>
            <a href="#" class="nav-link"><i class="fas fa-university"></i> Bancos</a>
            <a href="#" class="nav-link"><i class="fas fa-money-bill-wave"></i> Caja</a>
        </div>
    </li>
</ul>

{{-- VENTAS Y COBRANZAS --}}
<div class="nav-section">Ventas & Cobranzas</div>
<ul>
    <li><a href="{{ route('contador.reportes.ventas') }}" class="nav-link">
        <i class="fas fa-chart-line"></i> Análisis Ventas
    </a></li>
    <li><a href="{{ route('contador.reportes.compras') }}" class="nav-link">
        <i class="fas fa-wallet"></i> Cartera
    </a></li>
    <li><a href="{{ route('contador.facturas.create') }}" class="nav-link">
        <i class="fas fa-clock"></i> Fact. Pendientes
    </a></li>
    <li><a href="{{ route('contador.facturas.index') }}" class="nav-link">
        <i class="fas fa-exclamation-triangle"></i> Fact. Vencidas
    </a></li>
</ul>

{{-- GESTIÓN --}}
<div class="nav-section">Gestión</div>
<ul>
    <li><a href="{{ route('contador.clientes') }}" class="nav-link">
        <i class="fas fa-users"></i> Clientes
    </a></li>
    <li><a href="{{ route('contador.reportes.medicamentos-controlados') }}" class="nav-link">
        <i class="fas fa-percentage"></i> Márgenes
    </a></li>
    <li><a href="{{ route('contador.reportes.inventario') }}" class="nav-link">
        <i class="fas fa-boxes"></i> Inventario
    </a></li>
</ul>

{{-- REPORTES SUNAT --}}
<div class="nav-section">SUNAT</div>
<ul>
    <li><a href="#" class="nav-link">
        <i class="fas fa-file-invoice-dollar"></i> PLE
    </a></li>
    <li><a href="#" class="nav-link">
        <i class="fas fa-percent"></i> IGV Mensual
    </a></li>
</ul>
@endsection

@section('content')
<div class="container-fluid p-0">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center p-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white;">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-plus"></i> Nuevo Asiento Contable
            </h1>
            <p class="mb-0 opacity-75">Registrar nuevo asiento en el libro diario</p>
        </div>
        <div>
            <a href="{{ route('contador.libro-diario.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- Errores de validación --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Errores de validación:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Alertas --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form id="asientoForm" action="{{ route('contador.libro-diario.store') }}" method="POST">
        @csrf
        
        {{-- Información del Asiento --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle"></i> Información del Asiento
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Fecha *</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" 
                            value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" 
                            value="{{ auth()->user()->usuario ?? 'Contador' }}" 
                            readonly style="background-color: #f8f9fa;">
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-12">
                        <label class="form-label">Glosa / Descripción *</label>
                        <input type="text" class="form-control" id="glosa" name="glosa" 
                               placeholder="Describa el motivo del asiento contable" required>
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-12">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" 
                                  rows="3" placeholder="Observaciones adicionales (opcional)"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Plantillas predefinidas --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-layer-group"></i> Plantillas Farmacéuticas
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-primary w-100" onclick="aplicarPlantilla('ventas-medicamentos')">
                            <i class="fas fa-pills"></i><br>Venta de Medicamentos
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-primary w-100" onclick="aplicarPlantilla('compra-stock')">
                            <i class="fas fa-boxes"></i><br>Compra de Stock
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-primary w-100" onclick="aplicarPlantilla('gastos-operativos')">
                            <i class="fas fa-briefcase"></i><br>Gastos Operativos
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-warning w-100" onclick="aplicarPlantilla('medicamentos-caducos')">
                            <i class="fas fa-exclamation-triangle"></i><br>Medicamentos Caducos
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-success w-100" onclick="aplicarPlantilla('cobranzas')">
                            <i class="fas fa-money-bill-wave"></i><br>Cobranzas
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-info w-100" onclick="aplicarPlantilla('pagos-proveedores')">
                            <i class="fas fa-store"></i><br>Pagos Proveedores
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detalles del Asiento --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list"></i> Detalles del Asiento
                </h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="agregarFilaDetalle()">
                    <i class="fas fa-plus"></i> Agregar Línea
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 20%;">Cuenta Contable</th>
                                <th style="width: 30%;">Concepto</th>
                                <th style="width: 15%;">Debe</th>
                                <th style="width: 15%;">Haber</th>
                                <th style="width: 10%;">Doc. Ref.</th>
                                <th style="width: 10%;">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="detallesBody">
                            {{-- Los detalles se agregarán aquí dinámicamente --}}
                        </tbody>
                    </table>
                </div>
                
                {{-- Resumen del Balance --}}
                <div class="mt-3 p-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; border-left: 4px solid #0d6efd;">
                    <h6 class="mb-3">
                        <i class="fas fa-calculator"></i> Resumen del Balance
                    </h6>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Total Debe:</strong>
                            <div id="totalDebe" class="text-success fs-5">S/ 0.00</div>
                        </div>
                        <div class="col-md-3">
                            <strong>Total Haber:</strong>
                            <div id="totalHaber" class="text-danger fs-5">S/ 0.00</div>
                        </div>
                        <div class="col-md-6">
                            <strong>Diferencia:</strong>
                            <div id="diferencia" class="fs-5">S/ 0.00</div>
                            <div id="balanceStatus" class="badge bg-success">Balanceado</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="{{ route('contador.libro-diario.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-warning me-2" onclick="guardarBorrador()">
                            <i class="fas fa-save"></i> Guardar Borrador
                        </button>
                        <button type="submit" id="guardarAsiento" class="btn btn-success" disabled>
                            <i class="fas fa-check"></i> Guardar Asiento
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Últimos asientos como referencia --}}
    @if($ultimosAsientos->count() > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-history"></i> Últimos Asientos (Referencia)
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Glosa</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ultimosAsientos as $asiento)
                        <tr>
                            <td>{{ $asiento->numero }}</td>
                            <td>{{ \Carbon\Carbon::parse($asiento->fecha)->format('d/m/Y') }}</td>
                            <td>{{ Str::limit($asiento->glosa, 60) }}</td>
                            <td>S/ {{ number_format($asiento->total_debe, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let cuentaActual = 1;
    let totalDebe = 0;
    let totalHaber = 0;

    // Función para agregar una nueva fila
    window.agregarFilaDetalle = function () {
        const tbody = document.getElementById('detallesBody');
        const row = document.createElement('tr');
        row.id = `detalle-${cuentaActual}`;

        // Generar opciones desde el select oculto
        let opcionesHTML = '<option value="" disabled selected>Seleccionar cuenta...</option>';
        const opciones = document.querySelectorAll('#opciones-cuentas option');
        opciones.forEach(opt => {
            if (opt.value) {
                opcionesHTML += `<option value="${opt.value}">${opt.textContent}</option>`;
            }
        });

        row.innerHTML = `
            <td>
                <select name="detalles[${cuentaActual}][cuenta_contable]" 
                        class="form-select form-select-sm" 
                        onchange="actualizarBalance()" 
                        required>
                    ${opcionesHTML}
                </select>
                <div class="mt-1">
                    <button type="button" class="btn btn-sm btn-outline-success me-1"
                            onclick="seleccionarCuenta(${cuentaActual}, '1011')">
                        Efectivo
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            onclick="seleccionarCuenta(${cuentaActual}, '10411')">
                        Banco
                    </button>
                </div>
                <div class="mt-1" id="badge-${cuentaActual}"></div>
            </td>
            <td>
                <input type="text" name="detalles[${cuentaActual}][concepto]" 
                    class="form-control form-control-sm" 
                    placeholder="Concepto" 
                    required>
            </td>
            <td>
                <input type="number" name="detalles[${cuentaActual}][debe]" 
                    class="form-control form-control-sm text-end" 
                    step="0.01" min="0" value="0"
                    onchange="actualizarBalance(); actualizarBadges(${cuentaActual})">
            </td>
            <td>
                <input type="number" name="detalles[${cuentaActual}][haber]" 
                    class="form-control form-control-sm text-end" 
                    step="0.01" min="0" value="0"
                    onchange="actualizarBalance(); actualizarBadges(${cuentaActual})">
            </td>
            <td>
                <input type="text" name="detalles[${cuentaActual}][documento_referencia]" 
                    class="form-control form-control-sm" 
                    placeholder="Cheque/Nro doc">
            </td>
            <td>
                <button type="button" onclick="eliminarFila(${cuentaActual})" 
                        class="btn btn-sm btn-outline-danger" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);
        cuentaActual++;
    };

    // Funciones globales
    window.seleccionarCuenta = function (id, codigo) {
        const select = document.querySelector(`#detalle-${id} select`);
        if (select) {
            select.value = codigo;
            select.dispatchEvent(new Event('change'));
            actualizarBadges(id);
        }
    };

    window.eliminarFila = function (id) {
        const row = document.getElementById(`detalle-${id}`);
        if (row) {
            row.remove();
            actualizarBalance();
        }
    };

    window.actualizarBadges = function (id) {
        const row = document.getElementById(`detalle-${id}`);
        const debe = parseFloat(row.querySelector('input[name*="[debe]"]')?.value) || 0;
        const haber = parseFloat(row.querySelector('input[name*="[haber]"]')?.value) || 0;
        const badge = document.getElementById(`badge-${id}`);
        badge.innerHTML = debe > 0 ? '<span class="badge bg-success">Efectivo</span>' :
                           haber > 0 ? '<span class="badge bg-primary">Banco</span>' : '';
    };

    window.actualizarBalance = function () {
        totalDebe = 0;
        totalHaber = 0;
        document.querySelectorAll('#detallesBody tr').forEach(row => {
            const debe = parseFloat(row.querySelector('input[name*="[debe]"]')?.value) || 0;
            const haber = parseFloat(row.querySelector('input[name*="[haber]"]')?.value) || 0;
            totalDebe += debe;
            totalHaber += haber;
        });

        document.getElementById('totalDebe').textContent = 'S/ ' + totalDebe.toFixed(2);
        document.getElementById('totalHaber').textContent = 'S/ ' + totalHaber.toFixed(2);
        const diff = Math.abs(totalDebe - totalHaber);
        document.getElementById('diferencia').textContent = 'S/ ' + diff.toFixed(2);

        const status = document.getElementById('balanceStatus');
        const btn = document.getElementById('guardarAsiento');
        if (diff < 0.01) {
            status.className = 'badge bg-success';
            status.textContent = 'Balanceado ✓';
            btn.disabled = false;
        } else {
            status.className = 'badge bg-danger';
            status.textContent = 'No balancea ✗';
            btn.disabled = true;
        }
    };

    window.aplicarPlantilla = function (tipo) {
        const templates = {
            'ventas-medicamentos': [
                { cuenta: '10411', concepto: 'Ventas medicamentos varios' },
                { cuenta: '7011', concepto: 'Ingreso por ventas' }
            ],
            'compra-stock': [
                { cuenta: '1311', concepto: 'Medicamentos en stock' },
                { cuenta: '40111', concepto: 'Proveedores farmacéuticos' }
            ],
            'gastos-operativos': [
                { cuenta: '90111', concepto: 'Sueldos personal almacén' },
                { cuenta: '1011', concepto: 'Caja chica' }
            ],
            'medicamentos-caducos': [
                { cuenta: '6331', concepto: 'Pérdida medicamentos vencidos' },
                { cuenta: '1311', concepto: 'Afectación stock medicamentos' }
            ],
            'cobranzas': [
                { cuenta: '1011', concepto: 'Cobro facturas clientes' },
                { cuenta: '10411', concepto: 'Clientes diversos' }
            ],
            'pagos-proveedores': [
                { cuenta: '40111', concepto: 'Pago proveedores' },
                { cuenta: '1011', concepto: 'Salida efectivo' }
            ]
        };

        document.getElementById('detallesBody').innerHTML = '';
        cuentaActual = 1;

        if (templates[tipo]) {
            templates[tipo].forEach(template => {
                agregarFilaDetalle();
                const row = document.querySelector(`#detalle-${cuentaActual - 1}`);
                const select = row.querySelector('select');
                const conceptoInput = row.querySelector('input[name*="[concepto]"]');
                if (select) select.value = template.cuenta;
                if (conceptoInput) conceptoInput.value = template.concepto;
            });
        }
        actualizarBalance();
    };

    window.guardarBorrador = function () {
        alert('Función de borrador no implementada');
    };

    // Inicializar con 2 filas
    agregarFilaDetalle();
    agregarFilaDetalle();
});
</script>

{{-- Cuentas contables cargadas directamente desde la BD --}}
<select id="opciones-cuentas" style="display:none;">
    @foreach($cuentasContables as $cuenta)
        <option value="{{ $cuenta->codigo }}">{{ $cuenta->codigo }} - {{ $cuenta->nombre }}</option>
    @endforeach
</select>
@endpush