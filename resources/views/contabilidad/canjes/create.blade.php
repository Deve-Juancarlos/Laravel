@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Nuevo Canje de Facturas por Letras')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-exchange-alt"></i> Nuevo Canje de Facturas por Letras</h2>
                <a href="{{ route('contador.canjes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong><i class="fas fa-exclamation-triangle"></i> Errores de validación:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('contador.canjes.store') }}" id="formCanje">
        @csrf

        <!-- Paso 1: Selección de Cliente -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <strong><i class="fas fa-user"></i> Paso 1: Seleccionar Cliente</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Cliente <span class="text-danger">*</span></label>
                        <select name="cod_cliente" id="cod_cliente" class="form-select" required>
                            <option value="">-- Seleccione un cliente --</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->Codclie }}" data-deuda="{{ $cliente->deuda_total }}">
                                    {{ $cliente->Razon }} - RUC: {{ $cliente->Documento }} 
                                    (Deuda: S/ {{ number_format($cliente->deuda_total, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Deuda Total</label>
                        <input type="text" id="deuda_total_display" class="form-control" readonly placeholder="S/ 0.00">
                    </div>
                </div>
            </div>
        </div>

        <!-- Paso 2: Selección de Facturas -->
        <div class="card mb-4" id="card_facturas" style="display: none;">
            <div class="card-header bg-success text-white">
                <strong><i class="fas fa-file-invoice"></i> Paso 2: Seleccionar Facturas a Canjear</strong>
            </div>
            <div class="card-body">
                <div id="loading_facturas" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando facturas...</p>
                </div>

                <div id="facturas_container" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="check_all" title="Seleccionar todas">
                                    </th>
                                    <th>Documento</th>
                                    <th>Fecha Emisión</th>
                                    <th>Fecha Venc.</th>
                                    <th>Días Vencidos</th>
                                    <th>Importe</th>
                                    <th>Saldo</th>
                                </tr>
                            </thead>
                            <tbody id="facturas_tbody">
                                <!-- Se llenará dinámicamente -->
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3">
                        <strong><i class="fas fa-calculator"></i> Total Seleccionado:</strong> 
                        <span id="total_seleccionado" class="fs-5">S/ 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paso 3: Configuración de Letras -->
        <div class="card mb-4" id="card_letras" style="display: none;">
            <div class="card-header bg-warning text-dark">
                <strong><i class="fas fa-file-contract"></i> Paso 3: Configurar Letras</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Cantidad de Letras <span class="text-danger">*</span></label>
                        <input type="number" name="cantidad_letras" id="cantidad_letras" 
                               class="form-control" min="1" max="12" value="3" required>
                        <small class="text-muted">Máximo 12 letras</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Fecha Primera Letra <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_primera_letra" id="fecha_primera_letra" 
                               class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Días entre Cuotas</label>
                        <input type="number" name="dias_entre_cuotas" id="dias_entre_cuotas" 
                               class="form-control" value="30" min="1">
                        <small class="text-muted">Por defecto: 30 días</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Monto por Letra</label>
                        <input type="text" id="monto_por_letra" class="form-control" readonly placeholder="S/ 0.00">
                    </div>
                </div>

                <hr class="my-4">

                <div id="preview_letras" class="table-responsive">
                    <h5><i class="fas fa-eye"></i> Vista Previa de Letras:</h5>
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Letra #</th>
                                <th>Fecha Vencimiento</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody id="preview_tbody">
                            <!-- Se llenará dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="card">
            <div class="card-body text-center">
                <button type="submit" class="btn btn-success btn-lg px-5" id="btn_guardar" disabled>
                    <i class="fas fa-save"></i> Crear Canje
                </button>
                <a href="{{ route('contador.canjes.index') }}" class="btn btn-secondary btn-lg px-5">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </div>
    </form>
</div>

<style>
.table thead th {
    font-size: 0.9rem;
}
.alert-info {
    font-size: 1.1rem;
}
#preview_letras {
    max-height: 400px;
    overflow-y: auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codClienteSelect = document.getElementById('cod_cliente');
    const cardFacturas = document.getElementById('card_facturas');
    const cardLetras = document.getElementById('card_letras');
    const loadingFacturas = document.getElementById('loading_facturas');
    const facturasContainer = document.getElementById('facturas_container');
    const facturasTbody = document.getElementById('facturas_tbody');
    const totalSeleccionadoSpan = document.getElementById('total_seleccionado');
    const btnGuardar = document.getElementById('btn_guardar');
    const cantidadLetrasInput = document.getElementById('cantidad_letras');
    const fechaPrimeraLetraInput = document.getElementById('fecha_primera_letra');
    const diasEntreCuotasInput = document.getElementById('dias_entre_cuotas');
    const montoPorLetraInput = document.getElementById('monto_por_letra');
    const previewTbody = document.getElementById('preview_tbody');
    const checkAll = document.getElementById('check_all');

    let facturasData = [];
    let facturasSeleccionadas = {};

    // Cuando se selecciona un cliente
    codClienteSelect.addEventListener('change', function() {
        const codCliente = this.value;
        
        if (!codCliente) {
            cardFacturas.style.display = 'none';
            cardLetras.style.display = 'none';
            btnGuardar.disabled = true;
            return;
        }

        // Mostrar deuda total
        const selectedOption = this.options[this.selectedIndex];
        const deuda = selectedOption.dataset.deuda || '0.00';
        document.getElementById('deuda_total_display').value = 'S/ ' + parseFloat(deuda).toFixed(2);

        // Cargar facturas
        cargarFacturas(codCliente);
    });

    // Cargar facturas del cliente
    function cargarFacturas(codCliente) {
        cardFacturas.style.display = 'block';
        loadingFacturas.style.display = 'block';
        facturasContainer.style.display = 'none';

        fetch(`{{ route('contador.canjes.get-facturas') }}?cod_cliente=${codCliente}`)
            .then(response => response.json())
            .then(data => {
                facturasData = data.facturas;
                renderFacturas(facturasData);
                loadingFacturas.style.display = 'none';
                facturasContainer.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar facturas');
                loadingFacturas.style.display = 'none';
            });
    }

    // Renderizar facturas
    function renderFacturas(facturas) {
        facturasTbody.innerHTML = '';
        facturasSeleccionadas = {};

        facturas.forEach(factura => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="checkbox" class="check-factura" 
                           data-documento="${factura.Documento}" 
                           data-saldo="${factura.Saldo}">
                </td>
                <td>${factura.Documento}</td>
                <td>${formatDate(factura.FechaF)}</td>
                <td>${formatDate(factura.FechaV)}</td>
                <td>
                    <span class="badge ${factura.dias_vencidos > 0 ? 'bg-danger' : 'bg-success'}">
                        ${factura.dias_vencidos} días
                    </span>
                </td>
                <td>S/ ${parseFloat(factura.Importe).toFixed(2)}</td>
                <td><strong>S/ ${parseFloat(factura.Saldo).toFixed(2)}</strong></td>
            `;
            facturasTbody.appendChild(tr);
        });

        // Event listeners para checkboxes
        document.querySelectorAll('.check-factura').forEach(checkbox => {
            checkbox.addEventListener('change', actualizarSeleccion);
        });
    }

    // Actualizar selección de facturas
    function actualizarSeleccion() {
        facturasSeleccionadas = {};
        let total = 0;

        document.querySelectorAll('.check-factura:checked').forEach(checkbox => {
            const documento = checkbox.dataset.documento;
            const saldo = parseFloat(checkbox.dataset.saldo);
            facturasSeleccionadas[documento] = saldo;
            total += saldo;
        });

        totalSeleccionadoSpan.textContent = 'S/ ' + total.toFixed(2);

        // Mostrar card de letras si hay selección
        if (Object.keys(facturasSeleccionadas).length > 0) {
            cardLetras.style.display = 'block';
            btnGuardar.disabled = false;
            calcularLetras();
        } else {
            cardLetras.style.display = 'none';
            btnGuardar.disabled = true;
        }
    }

    // Calcular vista previa de letras
    function calcularLetras() {
        const cantidadLetras = parseInt(cantidadLetrasInput.value) || 1;
        const fechaPrimera = new Date(fechaPrimeraLetraInput.value);
        const diasEntreCuotas = parseInt(diasEntreCuotasInput.value) || 30;
        const totalSeleccionado = Object.values(facturasSeleccionadas).reduce((a, b) => a + b, 0);

        const montoPorLetra = totalSeleccionado / cantidadLetras;
        montoPorLetraInput.value = 'S/ ' + montoPorLetra.toFixed(2);

        // Generar preview
        previewTbody.innerHTML = '';
        for (let i = 1; i <= cantidadLetras; i++) {
            const fechaVenc = new Date(fechaPrimera);
            fechaVenc.setDate(fechaVenc.getDate() + ((i - 1) * diasEntreCuotas));

            let monto = montoPorLetra;
            if (i === cantidadLetras) {
                // Ajustar última letra para cubrir diferencias de redondeo
                monto = totalSeleccionado - (montoPorLetra * (cantidadLetras - 1));
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>Letra ${i} de ${cantidadLetras}</td>
                <td>${fechaVenc.toLocaleDateString('es-PE')}</td>
                <td><strong>S/ ${monto.toFixed(2)}</strong></td>
            `;
            previewTbody.appendChild(tr);
        }
    }

    // Eventos para recalcular letras
    cantidadLetrasInput.addEventListener('input', calcularLetras);
    fechaPrimeraLetraInput.addEventListener('change', calcularLetras);
    diasEntreCuotasInput.addEventListener('input', calcularLetras);

    // Seleccionar todas las facturas
    checkAll.addEventListener('change', function() {
        document.querySelectorAll('.check-factura').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        actualizarSeleccion();
    });

    // Formatear fecha
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-PE');
    }

    // Antes de enviar, agregar facturas seleccionadas al form
    document.getElementById('formCanje').addEventListener('submit', function(e) {
        // Eliminar inputs previos
        document.querySelectorAll('input[name^="facturas["]').forEach(el => el.remove());

        // Agregar facturas seleccionadas
        Object.entries(facturasSeleccionadas).forEach(([documento, saldo]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `facturas[${documento}]`;
            input.value = saldo;
            this.appendChild(input);
        });
    });
});
</script>
@endsection