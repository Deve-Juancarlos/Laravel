@extends('layouts.app')

@section('title', 'Nueva Factura')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('ventas.dashboard.index') }}">Ventas</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('ventas.facturacion.index') }}">Facturación</a></li>
                            <li class="breadcrumb-item active">Nueva Factura</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-plus-circle text-primary"></i>
                        Nueva Factura
                    </h1>
                    <p class="text-muted mb-0">Crear una nueva factura de venta</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <a href="{{ route('ventas.facturacion.rapida') }}" class="btn btn-success">
                            <i class="fas fa-bolt"></i> Factura Rápida
                        </a>
                        <a href="{{ route('ventas.facturacion.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="facturaForm" onsubmit="event.preventDefault(); guardarFactura();">
        <div class="row">
            <!-- Información de la Factura -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary bg-opacity-10 border-0">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-info-circle me-2"></i>
                            Información de la Factura
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Número de Factura</label>
                                <input type="text" class="form-control" id="numeroFactura" value="F-{{ str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT) }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha de Emisión</label>
                                <input type="date" class="form-control" id="fechaEmision" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha de Vencimiento</label>
                                <input type="date" class="form-control" id="fechaVencimiento" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Comprobante</label>
                                <select class="form-select" id="tipoComprobante" required>
                                    <option value="factura">Factura</option>
                                    <option value="boleta">Boleta de Venta</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Moneda</label>
                                <select class="form-select" id="moneda" required>
                                    <option value="PEN" selected>PEN - Soles (S/)</option>
                                    <option value="USD">USD - Dólares ($)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Cliente -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-info bg-opacity-10 border-0">
                        <h5 class="mb-0 text-info">
                            <i class="fas fa-user me-2"></i>
                            Información del Cliente
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Buscar Cliente</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="busquedaCliente" placeholder="Buscar por DNI, RUC, nombre o email..." onkeyup="buscarCliente()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Cliente *</label>
                                <select class="form-select" id="tipoCliente" onchange="cambiarTipoCliente()" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="persona_natural">Persona Natural</option>
                                    <option value="persona_juridica">Persona Jurídica</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" id="documentoLabel">DNI *</label>
                                <input type="text" class="form-control" id="documentoCliente" maxlength="11" onblur="validarDocumento()" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Nombre/Razón Social *</label>
                                <input type="text" class="form-control" id="nombreCliente" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefonoCliente">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Dirección</label>
                                <textarea class="form-control" id="direccionCliente" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="emailCliente">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Condición de Pago</label>
                                <select class="form-select" id="condicionPago">
                                    <option value="contado">Contado</option>
                                    <option value="credito">Crédito</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos/Servicios -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-success bg-opacity-10 border-0">
                        <h5 class="mb-0 text-success">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Productos y Servicios
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Búsqueda de Productos -->
                        <div class="mb-3">
                            <label class="form-label">Buscar Producto</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="busquedaProducto" placeholder="Buscar por nombre, código o categoría..." onkeyup="buscarProducto()">
                            </div>
                        </div>

                        <!-- Botón para agregar manualmente -->
                        <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="agregarItemManual()">
                            <i class="fas fa-plus"></i> Agregar Manual
                        </button>

                        <!-- Tabla de Items -->
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tablaItems">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%">Producto/Servicio</th>
                                        <th style="width: 10%">Cant.</th>
                                        <th style="width: 15%">Precio Unit.</th>
                                        <th style="width: 10%">Desc.%</th>
                                        <th style="width: 15%">Subtotal</th>
                                        <th style="width: 10%">Stock</th>
                                        <th style="width: 5%">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsFactura">
                                    <!-- Items se agregarán dinámicamente -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Botón para agregar ítem -->
                        <div class="text-center">
                            <button type="button" class="btn btn-success" onclick="mostrarModalProductos()">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-warning bg-opacity-10 border-0">
                        <h5 class="mb-0 text-warning">
                            <i class="fas fa-sticky-note me-2"></i>
                            Observaciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" id="observaciones" rows="3" placeholder="Observaciones adicionales para la factura..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Resumen y Totales -->
            <div class="col-lg-4 mb-4">
                <!-- Información del Vendedor -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-secondary bg-opacity-10 border-0">
                        <h5 class="mb-0 text-secondary">
                            <i class="fas fa-user-tie me-2"></i>
                            Información del Vendedor
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Vendedor</label>
                                <select class="form-select" id="vendedor">
                                    <option value="">Seleccionar vendedor...</option>
                                    <option value="1" selected>Ana García - Farmacéutica Senior</option>
                                    <option value="2">Carlos López - Vendedor</option>
                                    <option value="3">María Rodríguez - Farmacéutica</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Punto de Venta</label>
                                <select class="form-select" id="puntoVenta">
                                    <option value="principal" selected>Farmacia Principal</option>
                                    <option value="sucursal_1">Sucursal Norte</option>
                                    <option value="sucursal_2">Sucursal Sur</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Totales -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-success bg-opacity-10 border-0">
                        <h5 class="mb-0 text-success">
                            <i class="fas fa-calculator me-2"></i>
                            Resumen de Totales
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal (Sin IGV):</span>
                            <strong id="subtotal">S/ 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>IGV (18%):</span>
                            <strong id="igv">S/ 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Descuento Total:</span>
                            <strong id="descuentoTotal" class="text-danger">S/ 0.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span><strong>Total:</strong></span>
                            <h4 class="text-success mb-0" id="total">S/ 0.00</h4>
                        </div>
                        
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Total en letras:</small>
                            </div>
                            <div class="alert alert-info py-2">
                                <small id="totalLetras" class="text-muted">CERO SOLES</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-primary bg-opacity-10 border-0">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-cogs me-2"></i>
                            Acciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar Factura
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="guardarBorrador()">
                                <i class="fas fa-save"></i> Guardar Borrador
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="vistaPrevia()">
                                <i class="fas fa-eye"></i> Vista Previa
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="enviarEmail()">
                                <i class="fas fa-envelope"></i> Enviar por Email
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Historial -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-info bg-opacity-10 border-0">
                        <h5 class="mb-0 text-info">
                            <i class="fas fa-history me-2"></i>
                            Historial Reciente
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1">F-001244</h6>
                                    <small class="text-muted">Luisa Martínez</small>
                                </div>
                                <span class="badge bg-success">S/ 89.50</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1">F-001243</h6>
                                    <small class="text-muted">Pedro Sánchez</small>
                                </div>
                                <span class="badge bg-warning">S/ 234.20</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1">F-001242</h6>
                                    <small class="text-muted">Ana Torres</small>
                                </div>
                                <span class="badge bg-success">S/ 125.30</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal de Productos -->
<div class="modal fade" id="modalProductos" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-search me-2"></i>
                    Buscar Productos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="modalBusquedaProducto" placeholder="Buscar productos..." onkeyup="filtrarModalProductos()">
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>Precio</th>
                                <th>Categoría</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="modalListaProductos">
                            <tr>
                                <td>P001</td>
                                <td>Paracetamol 500mg x20</td>
                                <td><span class="badge bg-success">150</span></td>
                                <td>S/ 15.50</td>
                                <td>Analgésicos</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="seleccionarProducto('P001', 'Paracetamol 500mg x20', 15.50)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>P002</td>
                                <td>Ibuprofeno 400mg x30</td>
                                <td><span class="badge bg-success">85</span></td>
                                <td>S/ 22.80</td>
                                <td>Analgésicos</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="seleccionarProducto('P002', 'Ibuprofeno 400mg x30', 22.80)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>P003</td>
                                <td>Amoxicilina 500mg x21</td>
                                <td><span class="badge bg-warning">12</span></td>
                                <td>S/ 18.90</td>
                                <td>Antibióticos</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="seleccionarProducto('P003', 'Amoxicilina 500mg x21', 18.90)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </td>
                            </tr>
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
let itemsFactura = [];
let totalFactura = 0;

function buscarCliente() {
    const busqueda = $('#busquedaCliente').val();
    if (busqueda.length >= 3) {
        // Simular búsqueda de cliente
        console.log('Buscando cliente:', busqueda);
    }
}

function cambiarTipoCliente() {
    const tipo = $('#tipoCliente').val();
    const label = $('#documentoLabel');
    
    if (tipo === 'persona_natural') {
        label.text('DNI *');
        $('#documentoCliente').attr('maxlength', '8').attr('placeholder', '12345678');
    } else if (tipo === 'persona_juridica') {
        label.text('RUC *');
        $('#documentoCliente').attr('maxlength', '11').attr('placeholder', '20123456789');
    }
}

function validarDocumento() {
    const documento = $('#documentoCliente').val();
    const tipo = $('#tipoCliente').val();
    
    if (documento) {
        if (tipo === 'persona_natural' && documento.length !== 8) {
            Swal.fire({
                icon: 'error',
                title: 'DNI inválido',
                text: 'El DNI debe tener 8 dígitos'
            });
        } else if (tipo === 'persona_juridica' && documento.length !== 11) {
            Swal.fire({
                icon: 'error',
                title: 'RUC inválido',
                text: 'El RUC debe tener 11 dígitos'
            });
        }
    }
}

function buscarProducto() {
    const busqueda = $('#busquedaProducto').val();
    if (busqueda.length >= 3) {
        // Simular búsqueda de producto
        console.log('Buscando producto:', busqueda);
    }
}

function mostrarModalProductos() {
    const modal = new bootstrap.Modal(document.getElementById('modalProductos'));
    modal.show();
}

function filtrarModalProductos() {
    const busqueda = $('#modalBusquedaProducto').val().toLowerCase();
    // Simular filtrado
    console.log('Filtrando productos:', busqueda);
}

function seleccionarProducto(codigo, nombre, precio) {
    agregarItem({
        codigo: codigo,
        nombre: nombre,
        cantidad: 1,
        precioUnitario: precio,
        descuento: 0,
        stock: 100
    });
    
    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalProductos'));
    modal.hide();
}

function agregarItemManual() {
    const item = {
        codigo: 'MAN' + Math.random().toString(36).substr(2, 6).toUpperCase(),
        nombre: '',
        cantidad: 1,
        precioUnitario: 0,
        descuento: 0,
        stock: 999
    };
    
    agregarItem(item);
}

function agregarItem(item) {
    itemsFactura.push(item);
    actualizarTablaItems();
    calcularTotales();
    
    Swal.fire({
        icon: 'success',
        title: 'Producto agregado',
        text: `${item.nombre} agregado a la factura`,
        timer: 1500,
        showConfirmButton: false
    });
}

function eliminarItem(index) {
    itemsFactura.splice(index, 1);
    actualizarTablaItems();
    calcularTotales();
}

function actualizarCantidad(index, cantidad) {
    if (cantidad > 0 && cantidad <= itemsFactura[index].stock) {
        itemsFactura[index].cantidad = parseFloat(cantidad);
        actualizarTablaItems();
        calcularTotales();
    }
}

function actualizarPrecio(index, precio) {
    if (precio >= 0) {
        itemsFactura[index].precioUnitario = parseFloat(precio);
        actualizarTablaItems();
        calcularTotales();
    }
}

function actualizarDescuento(index, descuento) {
    if (descuento >= 0 && descuento <= 100) {
        itemsFactura[index].descuento = parseFloat(descuento);
        actualizarTablaItems();
        calcularTotales();
    }
}

function actualizarTablaItems() {
    const tbody = $('#itemsFactura');
    tbody.empty();
    
    itemsFactura.forEach((item, index) => {
        const subtotal = (item.cantidad * item.precioUnitario * (1 - item.descuento / 100));
        
        const row = `
            <tr>
                <td>
                    <div>
                        <strong>${item.codigo}</strong>
                        <br>
                        <small class="text-muted">${item.nombre || 'Producto manual'}</small>
                    </div>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" value="${item.cantidad}" 
                           min="1" max="${item.stock}" 
                           onchange="actualizarCantidad(${index}, this.value)">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" value="${item.precioUnitario}" 
                           min="0" step="0.01"
                           onchange="actualizarPrecio(${index}, this.value)">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" value="${item.descuento}" 
                           min="0" max="100" step="0.1"
                           onchange="actualizarDescuento(${index}, this.value)">
                </td>
                <td>
                    <strong>S/ ${subtotal.toFixed(2)}</strong>
                </td>
                <td>
                    <span class="badge ${item.stock > 10 ? 'bg-success' : item.stock > 0 ? 'bg-warning' : 'bg-danger'}">
                        ${item.stock}
                    </span>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarItem(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function calcularTotales() {
    let subtotal = 0;
    let descuentoTotal = 0;
    
    itemsFactura.forEach(item => {
        const subtotalItem = item.cantidad * item.precioUnitario;
        const descuentoItem = subtotalItem * (item.descuento / 100);
        subtotal += subtotalItem;
        descuentoTotal += descuentoItem;
    });
    
    const igv = subtotal * 0.18;
    const total = subtotal + igv - descuentoTotal;
    
    $('#subtotal').text('S/ ' + subtotal.toFixed(2));
    $('#igv').text('S/ ' + igv.toFixed(2));
    $('#descuentoTotal').text('S/ ' + descuentoTotal.toFixed(2));
    $('#total').text('S/ ' + total.toFixed(2));
    $('#totalLetras').text(convertirNumeroALetras(total));
    
    totalFactura = total;
}

function convertirNumeroALetras(numero) {
    const unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    const decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    const especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    
    if (numero === 0) return 'CERO';
    
    // Simplificado para demo
    if (numero < 100) {
        if (numero < 10) return unidades[numero];
        if (numero < 20) return especiales[numero - 10];
        if (numero % 10 === 0) return decenas[numero / 10];
        return decenas[Math.floor(numero / 10)] + ' Y ' + unidades[numero % 10];
    }
    
    return 'CIENTO VEINTE SOLES'; // Simplificado para demo
}

function guardarFactura() {
    if (itemsFactura.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe agregar al menos un producto a la factura'
        });
        return;
    }
    
    if (!$('#nombreCliente').val()) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe completar la información del cliente'
        });
        return;
    }
    
    // Simular guardado
    Swal.fire({
        title: 'Guardando factura...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Factura guardada',
            text: `La factura ${$('#numeroFactura').val()} ha sido creada exitosamente`,
            showCancelButton: true,
            confirmButtonText: 'Ver Factura',
            cancelButtonText: 'Crear Otra'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/ventas/facturacion/ver/${$('#numeroFactura').val()}`;
            } else {
                window.location.reload();
            }
        });
    }, 2000);
}

function guardarBorrador() {
    Swal.fire({
        icon: 'success',
        title: 'Borrador guardado',
        text: 'La factura se ha guardado como borrador'
    });
}

function vistaPrevia() {
    window.open(`/ventas/facturacion/vista-previa/${$('#numeroFactura').val()}`, '_blank');
}

function enviarEmail() {
    if (!totalFactura) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No hay productos en la factura'
        });
        return;
    }
    
    Swal.fire({
        title: 'Enviar por Email',
        html: `
            <div class="text-left">
                <div class="mb-3">
                    <label class="form-label">Email del cliente:</label>
                    <input type="email" class="form-control" id="emailEnvio" placeholder="cliente@email.com" value="${$('#emailCliente').val()}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Asunto:</label>
                    <input type="text" class="form-control" id="asuntoEmail" value="Factura ${$('#numeroFactura').val()} - Total: S/ ${totalFactura.toFixed(2)}">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const email = document.getElementById('emailEnvio').value;
            if (!email) {
                Swal.showValidationMessage('El email es requerido');
                return false;
            }
            return { email: email, asunto: document.getElementById('asuntoEmail').value };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Email enviado',
                text: `Factura enviada a ${result.value.email}`
            });
        }
    });
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    calcularTotales();
});
</script>
@endsection

@section('styles')
<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}

.modal-xl {
    max-width: 90%;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-group .btn {
    border-radius: 0.375rem;
}

.sticky-top {
    position: sticky;
    top: 0;
    background: white;
    z-index: 1;
}

.list-group-item {
    border: none;
    padding-left: 0;
    padding-right: 0;
}

.list-group-item:not(:last-child) {
    border-bottom: 1px solid #dee2e6;
}
</style>
@endsection