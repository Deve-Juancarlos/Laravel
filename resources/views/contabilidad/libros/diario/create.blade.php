{{-- Vista create.blade.php CORREGIDA para contador.libro-diario.create --}}
@extends('layouts.app') 

@section('title', 'Nuevo Asiento - Libro Diario')

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
                    <div class="col-md-4">
                        <label class="form-label">Número de Asiento *</label>
                        <input type="text" class="form-control" id="numero" name="numero" 
                               value="{{ $siguienteNumero }}" required readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha *</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" value="Contador" readonly style="background-color: #f8f9fa;">
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
    let cuentaActual = 1;
    let totalDebe = 0;
    let totalHaber = 0;

    // Cuentas contables agrupadas por tipo
    const cuentasContables = @json($cuentasContables);

    // Inicializar formulario
    document.addEventListener('DOMContentLoaded', function() {
        agregarFilaDetalle();
        agregarFilaDetalle();
    });

    function agregarFilaDetalle() {
        const tbody = document.getElementById('detallesBody');
        const row = document.createElement('tr');
        row.id = `detalle-${cuentaActual}`;

        row.innerHTML = `
            <td>
                <select name="detalles[${cuentaActual}][cuenta_contable]" class="form-select form-select-sm" onchange="actualizarBalance()">
                    <option value="">Seleccionar...</option>
                    ${generarOpcionesCuentas()}
                </select>
                <div class="mt-1" id="badge-${cuentaActual}"></div> <!-- Contenedor del badge -->
            </td>
            <td>
                <input type="text" name="detalles[${cuentaActual}][concepto]" 
                    class="form-control form-control-sm" placeholder="Concepto">
            </td>
            <td>
                <input type="number" name="detalles[${cuentaActual}][debe]" 
                    class="form-control form-control-sm text-end" step="0.01" min="0" value="0"
                    onchange="actualizarBalance(); actualizarBadges(${cuentaActual})">
            </td>
            <td>
                <input type="number" name="detalles[${cuentaActual}][haber]" 
                    class="form-control form-control-sm text-end" step="0.01" min="0" value="0"
                    onchange="actualizarBalance(); actualizarBadges(${cuentaActual})">
            </td>
            <td>
                <input type="text" name="detalles[${cuentaActual}][documento_referencia]" 
                    class="form-control form-control-sm" placeholder="Cheque/Nro doc"
                    onchange="actualizarBadges(${cuentaActual})">
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
    }

    // Función para actualizar badges Efectivo / Banco / Cheque
    function actualizarBadges(id) {
        const row = document.getElementById(`detalle-${id}`);
        const debe = parseFloat(row.querySelector('input[name*="[debe]"]').value) || 0;
        const haber = parseFloat(row.querySelector('input[name*="[haber]"]').value) || 0;
        const docRef = row.querySelector('input[name*="[documento_referencia]"]').value;

        const badgeContainer = document.getElementById(`badge-${id}`);
        badgeContainer.innerHTML = ''; // Limpiar contenido

        if (debe > 0) {
            badgeContainer.innerHTML = `<span class="badge bg-success">Efectivo</span>`;
        } else if (haber > 0) {
            let badgeText = `Banco: ${haber.toFixed(2)}`;
            if (docRef) badgeText += ` (Cheque: ${docRef})`;
            badgeContainer.innerHTML = `<span class="badge bg-primary">${badgeText}</span>`;
        }
    }



    function eliminarFila(id) {
        const row = document.getElementById(`detalle-${id}`);
        if (row) {
            row.remove();
            actualizarBalance();
        }
    }

    function generarOpcionesCuentas() {
        let opciones = '';
        cuentasContables.forEach(grupo => {
            grupo.forEach(cuenta => {
                opciones += `<option value="${cuenta.codigo}">${cuenta.codigo} - ${cuenta.nombre}</option>`;
            });
        });
        return opciones;
    }

    function actualizarBalance() {
        totalDebe = 0;
        totalHaber = 0;
        
        // Recorrer todas las filas de detalles
        const rows = document.querySelectorAll('#detallesBody tr');
        rows.forEach(row => {
            const debeInput = row.querySelector('input[name*="[debe]"]');
            const haberInput = row.querySelector('input[name*="[haber]"]');
            
            if (debeInput && debeInput.value) {
                totalDebe += parseFloat(debeInput.value) || 0;
            }
            if (haberInput && haberInput.value) {
                totalHaber += parseFloat(haberInput.value) || 0;
            }
        });
        
        // Actualizar display
        document.getElementById('totalDebe').textContent = 'S/ ' + totalDebe.toFixed(2);
        document.getElementById('totalHaber').textContent = 'S/ ' + totalHaber.toFixed(2);
        
        const diferencia = totalDebe - totalHaber;
        document.getElementById('diferencia').textContent = 'S/ ' + Math.abs(diferencia).toFixed(2);
        
        const balanceStatus = document.getElementById('balanceStatus');
        const guardarBtn = document.getElementById('guardarAsiento');
        
        if (Math.abs(diferencia) < 0.01) {
            balanceStatus.className = 'badge bg-success';
            balanceStatus.textContent = 'Balanceado ✓';
            guardarBtn.disabled = false;
        } else {
            balanceStatus.className = 'badge bg-danger';
            balanceStatus.textContent = 'No balancea ✗';
            guardarBtn.disabled = true;
        }
    }

    function aplicarPlantilla(tipo) {
        const templates = {
            'ventas-medicamentos': [
                { cuenta: '10411', concepto: 'Ventas medicamentos varios', debe: 0, haber: 0 },
                { cuenta: '7011', concepto: 'Ingreso por ventas', debe: 0, haber: 0 }
            ],
            'compra-stock': [
                { cuenta: '1311', concepto: 'Medicamentos en stock', debe: 0, haber: 0 },
                { cuenta: '40111', concepto: 'Proveedores farmacéuticos', debe: 0, haber: 0 }
            ],
            'gastos-operativos': [
                { cuenta: '90111', concepto: 'Sueldos personal almacén', debe: 0, haber: 0 },
                { cuenta: '1011', concepto: 'Caja chica', debe: 0, haber: 0 }
            ],
            'medicamentos-caducos': [
                { cuenta: '6331', concepto: 'Pérdida medicamentos vencidos', debe: 0, haber: 0 },
                { cuenta: '1311', concepto: 'Afectación stock medicamentos', debe: 0, haber: 0 }
            ],
            'cobranzas': [
                { cuenta: '1011', concepto: 'Cobro facturas clientes', debe: 0, haber: 0 },
                { cuenta: '10411', concepto: 'Clientes diversos', debe: 0, haber: 0 }
            ],
            'pagos-proveedores': [
                { cuenta: '40111', concepto: 'Pago proveedores', debe: 0, haber: 0 },
                { cuenta: '1011', concepto: 'Salida efectivo', debe: 0, haber: 0 }
            ]
        };

        // Limpiar detalles actuales
        document.getElementById('detallesBody').innerHTML = '';
        cuentaActual = 1;

        // Agregar plantillas
        if (templates[tipo]) {
            templates[tipo].forEach(template => {
                agregarFilaDetalle();
                const row = document.querySelector(`#detalle-${cuentaActual - 1}`);
                const select = row.querySelector('select');
                const conceptoInput = row.querySelector('input[name*="[concepto]"]');
                
                select.value = template.cuenta;
                conceptoInput.value = template.concepto;
            });
        }

        actualizarBalance();
    }

    function guardarBorrador() {
        alert('Función de borrador暂时未实现 (Función de borrador temporalmente no implementada)');
    }
</script>
@endpush