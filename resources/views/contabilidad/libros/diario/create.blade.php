
@use('Illuminate\Support\Str')
{{-- resources/views/contabilidad/libros/diario/create.blade.php --}}
@extends('layouts.app') 

@section('title', 'Nuevo Asiento - Libro Diario')

<!-- 1. Título de la Cabecera -->
@section('page-title', 'Nuevo Asiento Contable')

<!-- 2. Breadcrumbs -->
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.libro-diario.index') }}">Libro Diario</a></li>
    <li class="breadcrumb-item active" aria-current="page">Nuevo Asiento</li>
@endsection

<!-- 3. Estilos CSS de esta página -->
@push('styles')
    <link href="{{ asset('css/contabilidad/libro-diario-create.css') }}" rel="stylesheet">
    <link href="{{ asset('css/contabilidad/asiento-form.css') }}" rel="stylesheet">
@endpush

<!-- 4. Contenido Principal -->
@section('content')
<div class="container-fluid">
    
    {{-- Errores de validación --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Errores de validación:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Alerta de error general (del session) --}}
    {{-- Esta ya se maneja en el layout principal app.blade.php --}}

    <form id="asientoForm" action="{{ route('contador.libro-diario.store') }}" method="POST">
        @csrf
        
        {{-- Información del Asiento --}}
        <div class="form-card mb-4">
            <div class="form-card-header">
                <h5 class="form-card-title">
                    <i class="fas fa-info-circle"></i> Información del Asiento
                </h5>
            </div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Fecha *</label>
                        <input type="date" class="form-control @error('fecha') is-invalid @enderror" id="fecha" name="fecha" 
                                value="{{ old('fecha', date('Y-m-d')) }}" required>
                        @error('fecha')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" 
                                value="{{ auth()->user()->usuario ?? 'Contador' }}" 
                                readonly disabled>
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-12">
                        <label class="form-label">Glosa / Descripción *</label>
                        <input type="text" class="form-control @error('glosa') is-invalid @enderror" id="glosa" name="glosa" 
                                value="{{ old('glosa') }}"
                                placeholder="Describa el motivo del asiento contable" required>
                        @error('glosa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-12">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" 
                                rows="3" placeholder="Observaciones adicionales (opcional)">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Plantillas predefinidas --}}
        <div class="form-card mb-4">
            <div class="form-card-header">
                <h5 class="form-card-title">
                    <i class="fas fa-layer-group"></i> Plantillas de Asientos
                </h5>
                <p class="form-card-subtitle">Selecciona una plantilla para cargar las cuentas automáticamente.</p>
            </div>
            <div class="form-card-body">
                <div class="template-grid">
                    <button type="button" class="btn-template" onclick="aplicarPlantilla('ventas-medicamentos')">
                        <i class="fas fa-pills"></i><span>Venta Medicamentos</span>
                    </button>
                    <button type="button" class="btn-template" onclick="aplicarPlantilla('compra-stock')">
                        <i class="fas fa-boxes"></i><span>Compra de Stock</span>
                    </button>
                    <button type="button" class="btn-template" onclick="aplicarPlantilla('gastos-operativos')">
                        <i class="fas fa-briefcase"></i><span>Gastos Operativos</span>
                    </button>
                    <button type="button" class="btn-template" onclick="aplicarPlantilla('medicamentos-caducos')">
                        <i class="fas fa-exclamation-triangle"></i><span>Medicamentos Caducos</span>
                    </button>
                    <button type="button" class="btn-template" onclick="aplicarPlantilla('cobranzas')">
                        <i class="fas fa-money-bill-wave"></i><span>Cobranzas</span>
                    </button>
                    <button type="button" class="btn-template" onclick="aplicarPlantilla('pagos-proveedores')">
                        <i class="fas fa-store"></i><span>Pagos Proveedores</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Detalles del Asiento --}}
        <div class="table-card mb-4">
            <div class="table-header">
                <h5 class="table-title">
                    <i class="fas fa-list"></i> Detalles del Asiento
                </h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="agregarFilaDetalle()">
                    <i class="fas fa-plus"></i> Agregar Línea
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%;">Cuenta Contable *</th>
                            <th style="width: 30%;">Concepto *</th>
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
            <div class="balance-summary mt-4">
                <h6 class="balance-title">
                    <i class="fas fa-calculator"></i> Resumen del Balance
                </h6>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Total Debe:</strong>
                        <div id="totalDebe" class="text-success fs-5 fw-bold">S/ 0.00</div>
                    </div>
                    <div class="col-md-3">
                        <strong>Total Haber:</strong>
                        <div id="totalHaber" class="text-danger fs-5 fw-bold">S/ 0.00</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Diferencia:</strong>
                        <div id="diferencia" class="fs-5 fw-bold">S/ 0.00</div>
                        <div id="balanceStatus" class="badge bg-success">Balanceado</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="{{ route('contador.libro-diario.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                    </div>
                    <div>
                        {{-- <button type="button" class="btn btn-outline-warning me-2" onclick="guardarBorrador()">
                            <i class="fas fa-save"></i> Guardar Borrador
                        </button> --}}
                        <button type="submit" id="guardarAsiento" class="btn btn-primary" disabled>
                            <i class="fas fa-check me-2"></i> Guardar Asiento
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Últimos asientos como referencia --}}
    @if(isset($ultimosAsientos) && $ultimosAsientos->count() > 0)
    <div class="table-card mt-4">
        <div class="table-header">
            <h5 class="table-title">
                <i class="fas fa-history"></i> Últimos Asientos (Referencia)
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Glosa</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ultimosAsientos as $asiento)
                    <tr>
                        <td>{{ $asiento->numero }}</td>
                        <td>{{ \Carbon\Carbon::parse($asiento->fecha)->format('d/m/Y') }}</td>
                        <td>{{ Str::limit($asiento->glosa, 60) }}</td>
                        <td class="text-end">S/ {{ number_format($asiento->total_debe, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

{{-- 
    Select oculto con todas las cuentas contables.
    El JS lo usará para construir las filas dinámicas.
--}}
<select id="opciones-cuentas" style="display:none;" multiple>
    @foreach($cuentasContables as $cuenta)
        <option value="{{ $cuenta->codigo }}">{{ $cuenta->codigo }} - {{ $cuenta->nombre }}</option>
    @endforeach
</select>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let cuentaActual = 1;
    let totalDebe = 0;
    let totalHaber = 0;
    
    // Almacenamos las opciones del select en memoria para más velocidad
    const opcionesCuentasHTML = document.getElementById('opciones-cuentas').innerHTML;

    // Función para agregar una nueva fila
    window.agregarFilaDetalle = function () {
        const tbody = document.getElementById('detallesBody');
        const row = document.createElement('tr');
        row.id = `detalle-${cuentaActual}`;
        row.classList.add('detalle-fila');

        row.innerHTML = `
            <td>
                <select name="detalles[${cuentaActual}][cuenta_contable]" 
                        class="form-select form-select-sm" 
                        onchange="actualizarBalance()" 
                        required>
                    <option value="" disabled selected>Seleccionar cuenta...</option>
                    ${opcionesCuentasHTML}
                </select>
            </td>
            <td>
                <input type="text" name="detalles[${cuentaActual}][concepto]" 
                    class="form-control form-control-sm" 
                    placeholder="Concepto de la línea" 
                    required>
            </td>
            <td>
                <input type="number" name="detalles[${cuentaActual}][debe]" 
                    class="form-control form-control-sm text-end input-debe" 
                    step="0.01" min="0" value="0"
                    onchange="actualizarBalance(); handleInput(this, 'debe')">
            </td>
            <td>
                <input type="number" name="detalles[${cuentaActual}][haber]" 
                    class="form-control form-control-sm text-end input-haber" 
                    step="0.01" min="0" value="0"
                    onchange="actualizarBalance(); handleInput(this, 'haber')">
            </td>
            <td>
                <input type="text" name="detalles[${cuentaActual}][documento_referencia]" 
                    class="form-control form-control-sm" 
                    placeholder="Doc. Ref.">
            </td>
            <td class="text-center">
                <button type="button" onclick="eliminarFila(${cuentaActual})" 
                        class="btn btn-sm btn-outline-danger" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);
        
        // Aplicar la glosa principal al concepto de la primera fila
        if (cuentaActual === 1) {
            const glosaPrincipal = document.getElementById('glosa').value;
            if (glosaPrincipal) {
                row.querySelector('input[name*="[concepto]"]').value = glosaPrincipal;
            }
        }
        
        cuentaActual++;
        actualizarBalance();
    };

    // Función para que Debe y Haber no puedan tener valor al mismo tiempo
    window.handleInput = function(element, tipo) {
        const row = element.closest('tr');
        if (tipo === 'debe' && parseFloat(element.value) > 0) {
            row.querySelector('.input-haber').value = 0;
        } else if (tipo === 'haber' && parseFloat(element.value) > 0) {
            row.querySelector('.input-debe').value = 0;
        }
    }

    // Funciones globales
    window.eliminarFila = function (id) {
        const row = document.getElementById(`detalle-${id}`);
        if (row) {
            row.remove();
            actualizarBalance();
        }
    };

    window.actualizarBalance = function () {
        totalDebe = 0;
        totalHaber = 0;
        let rowCount = 0;

        document.querySelectorAll('#detallesBody tr').forEach(row => {
            const debe = parseFloat(row.querySelector('input[name*="[debe]"]')?.value) || 0;
            const haber = parseFloat(row.querySelector('input[name*="[haber]"]')?.value) || 0;
            totalDebe += debe;
            totalHaber += haber;
            rowCount++;
        });

        document.getElementById('totalDebe').textContent = 'S/ ' + totalDebe.toFixed(2);
        document.getElementById('totalHaber').textContent = 'S/ ' + totalHaber.toFixed(2);
        const diff = totalDebe - totalHaber;
        const diffAbs = Math.abs(diff);
        document.getElementById('diferencia').textContent = 'S/ ' + diffAbs.toFixed(2);

        const status = document.getElementById('balanceStatus');
        const btn = document.getElementById('guardarAsiento');
        
        // El asiento debe estar balanceado (diferencia < 0.01)
        // Debe tener al menos 2 filas
        // El total debe y haber debe ser mayor a 0 (no un asiento vacío)
        const isBalanced = diffAbs < 0.01;
        const hasMinRows = rowCount >= 2;
        const notEmpty = totalDebe > 0 && totalHaber > 0;

        if (isBalanced && hasMinRows && notEmpty) {
            status.className = 'badge bg-success';
            status.textContent = 'Balanceado ✓';
            btn.disabled = false;
        } else {
            status.className = 'badge bg-danger';
            if (!isBalanced) {
                status.textContent = 'No balancea ✗';
            } else if (!hasMinRows) {
                status.textContent = 'Mínimo 2 filas ✗';
            } else if (!notEmpty) {
                status.textContent = 'Asiento vacío ✗';
            }
            btn.disabled = true;
        }
    };

    window.aplicarPlantilla = function (tipo) {
        const glosaInput = document.getElementById('glosa');
        const templates = {
            'ventas-medicamentos': {
                glosa: 'Por la venta de medicamentos del día',
                lineas: [
                    { cuenta: '12121', concepto: 'Facturas por cobrar', debe: 118, haber: 0 },
                    { cuenta: '40111', concepto: 'IGV - Débito Fiscal', debe: 0, haber: 18 },
                    { cuenta: '7011', concepto: 'Venta de mercaderías', debe: 0, haber: 100 }
                ]
            },
            'compra-stock': {
                glosa: 'Por la compra de mercadería a proveedor X',
                lineas: [
                    { cuenta: '6011', concepto: 'Compra de mercaderías', debe: 100, haber: 0 },
                    { cuenta: '40111', concepto: 'IGV - Crédito Fiscal', debe: 18, haber: 0 },
                    { cuenta: '42121', concepto: 'Facturas por pagar', debe: 0, haber: 118 }
                ]
            },
            'gastos-operativos': {
                glosa: 'Por el pago de servicios de luz',
                lineas: [
                    { cuenta: '6361', concepto: 'Gasto por servicio de luz', debe: 100, haber: 0 },
                    { cuenta: '40111', concepto: 'IGV - Crédito Fiscal', debe: 18, haber: 0 },
                    { cuenta: '1011', concepto: 'Salida de caja chica', debe: 0, haber: 118 }
                ]
            },
            'medicamentos-caducos': {
                glosa: 'Por la merma de medicamentos vencidos',
                lineas: [
                    { cuenta: '6331', concepto: 'Pérdida medicamentos vencidos', debe: 100, haber: 0 },
                    { cuenta: '20111', concepto: 'Ajuste de stock de mercaderías', debe: 0, haber: 100 }
                ]
            },
            'cobranzas': {
                glosa: 'Por la cobranza de factura F001-123',
                lineas: [
                    { cuenta: '10411', concepto: 'Ingreso a Banco BCP', debe: 100, haber: 0 },
                    { cuenta: '12121', concepto: 'Cancelación factura cliente', debe: 0, haber: 100 }
                ]
            },
            'pagos-proveedores': {
                glosa: 'Por el pago a proveedor X',
                lineas: [
                    { cuenta: '42121', concepto: 'Cancelación factura proveedor', debe: 100, haber: 0 },
                    { cuenta: '10411', concepto: 'Salida de Banco BCP', debe: 0, haber: 100 }
                ]
            }
        };

        const plantilla = templates[tipo];
        if (!plantilla) return;

        // Limpiar filas existentes
        document.getElementById('detallesBody').innerHTML = '';
        cuentaActual = 1;
        
        // Poner la glosa
        glosaInput.value = plantilla.glosa;

        // Agregar las líneas de la plantilla
        plantilla.lineas.forEach(linea => {
            agregarFilaDetalle();
            const row = document.querySelector(`#detalle-${cuentaActual - 1}`);
            const select = row.querySelector('select');
            const conceptoInput = row.querySelector('input[name*="[concepto]"]');
            const debeInput = row.querySelector('input[name*="[debe]"]');
            const haberInput = row.querySelector('input[name*="[haber]"]');

            if (select) select.value = linea.cuenta;
            if (conceptoInput) conceptoInput.value = linea.concepto;
            if (debeInput) debeInput.value = linea.debe;
            if (haberInput) haberInput.value = linea.haber;
        });
        
        actualizarBalance();
    };

    window.guardarBorrador = function () {
        // Implementación futura: cambiar el 'estado' del asiento a 'BORRADOR'
        Swal.fire('Función no implementada', 'Esta función guardará el asiento como borrador.', 'info');
    };

    // Copiar Glosa principal al primer detalle
    document.getElementById('glosa').addEventListener('change', function() {
        const primerConcepto = document.querySelector('#detallesBody input[name*="[concepto]"]');
        if (primerConcepto) {
            primerConcepto.value = this.value;
        }
    });

    // Inicializar con 2 filas
    agregarFilaDetalle();
    agregarFilaDetalle();
});
</script>
