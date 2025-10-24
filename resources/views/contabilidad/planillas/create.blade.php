{{-- resources/views/contabilidad/planillas/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nueva Planilla de Cobranza')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">➕ Nueva Planilla de Cobranza</h4>
        </div>
        <div class="card-body">
            <!-- Paso 1: Cabecera -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <label>Serie - Número</label>
                    <p class="form-control-plaintext"><strong>{{ $serie }} - {{ $numero }}</strong></p>
                </div>
                <div class="col-md-3">
                    <label>Vendedor *</label>
                    <input type="text" id="vendedor_codigo" class="form-control" placeholder="Código">
                    <div id="vendedor-nombre" class="text-muted small"></div>
                </div>
                <div class="col-md-3">
                    <label>Fecha de Cobranza *</label>
                    <input type="date" id="fecha_cobranza" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label>Moneda</label>
                    <select id="moneda" class="form-control">
                        <option value="1">Soles (S/)</option>
                        <option value="2">Dólares (US$)</option>
                    </select>
                </div>
            </div>

            <hr>

            <!-- Paso 2: Detalle (Tabla dinámica) -->
            <h5>📄 Documentos a Cobrar</h5>
            <button type="button" class="btn btn-outline-primary btn-sm mb-2" onclick="abrirModalBuscarDocumento()">
                <i class="fas fa-search"></i> Buscar Documento
            </button>

            <table class="table table-bordered" id="tabla-documentos">
                <thead class="thead-light">
                    <tr>
                        <th>Tipo</th>
                        <th>Serie-Número</th>
                        <th>Cliente</th>
                        <th>Saldo Pendiente</th>
                        <th>Monto a Aplicar</th>
                        <th>Forma de Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpo-documentos">
                    <!-- Se llena dinámicamente -->
                </tbody>
            </table>

            <div class="text-right mt-3">
                <strong>Total Cobrado: S/ <span id="total-cobrado">0.00</span></strong>
            </div>

            <hr>

            <!-- Paso 3: Confirmación -->
            <div class="text-right">
                <button type="button" class="btn btn-success" onclick="guardarPlanilla()">
                    <i class="fas fa-save"></i> Registrar Planilla
                </button>
                <a href="{{ route('contabilidad.planillas.index') }}" class="btn btn-secondary">
                    ← Cancelar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Buscar Documento por DNI o N° de Documento -->
<div class="modal fade" id="modalBuscarDocumento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">🔍 Buscar Documento Pendiente</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>DNI del Cliente</label>
                    <input type="text" id="dni-buscar" class="form-control" maxlength="8" placeholder="12345678">
                </div>
                <button class="btn btn-primary btn-sm" onclick="buscarDocumentosPorDNI()">
                    <i class="fas fa-search"></i> Buscar Deudas
                </button>
                <div id="lista-documentos-pendientes" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
let documentosSeleccionados = [];

function abrirModalBuscarDocumento() {
    $('#modalBuscarDocumento').modal('show');
}

function buscarDocumentosPorDNI() {
    const dni = $('#dni-buscar').val().trim();
    if (dni.length !== 8) {
        alert('Ingrese un DNI válido de 8 dígitos.');
        return;
    }

    fetch('{{ route("contabilidad.ctacliente.buscar-por-dni") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ dni })
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            $('#lista-documentos-pendientes').html(`<div class="alert alert-warning">${data.error}</div>`);
        } else {
            let html = '<ul class="list-group">';
            data.documentos.forEach(doc => {
                html += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    ${doc.tipo_doc} ${doc.serie}-${doc.numero}
                    <span>S/ ${parseFloat(doc.saldo_actual).toFixed(2)}</span>
                    <button class="btn btn-sm btn-outline-success" onclick="seleccionarDocumento(${JSON.stringify(doc).replace(/"/g, '&quot;')})">
                        Seleccionar
                    </button>
                </li>`;
            });
            html += '</ul>';
            $('#lista-documentos-pendientes').html(html);
        }
    });
}

function seleccionarDocumento(doc) {
    // Verificar si ya está en la tabla
    if (documentosSeleccionados.some(d => d.id === doc.id)) {
        alert('Documento ya agregado.');
        return;
    }

    documentosSeleccionados.push(doc);
    actualizarTablaDocumentos();
    $('#modalBuscarDocumento').modal('hide');
}

function actualizarTablaDocumentos() {
    const tbody = $('#cuerpo-documentos');
    tbody.empty();
    let total = 0;

    documentosSeleccionados.forEach((doc, index) => {
        const monto = parseFloat(doc.saldo_actual);
        total += monto;
        tbody.append(`
            <tr>
                <td>${doc.tipo_doc === '01' ? 'Factura' : 'Boleta'}</td>
                <td>${doc.serie}-${doc.numero}</td>
                <td>${doc.cliente_nombre}</td>
                <td>S/ ${monto.toFixed(2)}</td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           value="${monto.toFixed(2)}" 
                           min="0" max="${monto}" 
                           onchange="actualizarMonto(${index}, this.value)">
                </td>
                <td>
                    <select class="form-control form-control-sm">
                        <option>Efectivo</option>
                        <option>Cheque</option>
                        <option>Nota de Crédito</option>
                    </select>
                </td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="eliminarDocumento(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });

    $('#total-cobrado').text(total.toFixed(2));
}

function eliminarDocumento(index) {
    documentosSeleccionados.splice(index, 1);
    actualizarTablaDocumentos();
}

function guardarPlanilla() {
    if (documentosSeleccionados.length === 0) {
        alert('Debe agregar al menos un documento.');
        return;
    }

    const payload = {
        serie = '{{ $serie }}',
        numero: {{ $numero }};
        vendedor_codigo: $('#vendedor_codigo').val(),
        fecha_cobranza= $('#fecha_cobranza').val(),
        moneda = $('#moneda').val(),
        documentos = documentosSeleccionados.map((doc, i) => ({
            ctacliente_id: doc.id,
            monto: parseFloat($(`#cuerpo-documentos tr:eq(${i}) input`).val()),
            forma_pago: $(`#cuerpo-documentos tr:eq(${i}) select`).val()
        })),
    };

    fetch('{{ route("contabilidad.planillas.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Planilla registrada correctamente.');
            window.location.href = '{{ route("contabilidad.planillas.show", ["id" => "TEMP"]) }}'.replace('TEMP', data.planilla_id);
        } else {
            alert(' Error: ' + data.message);
        }
    });
};
</script>
@endsection