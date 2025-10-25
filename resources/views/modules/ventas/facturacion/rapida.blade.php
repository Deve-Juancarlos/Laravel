@extends('layouts.app')

@section('title', 'Factura Rápida')

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
                            <li class="breadcrumb-item active">Factura Rápida</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-bolt text-success"></i>
                        Factura Rápida
                    </h1>
                    <p class="text-muted mb-0">Crear facturas de forma rápida y eficiente</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <a href="{{ route('ventas.facturacion.crear') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Factura Normal
                        </a>
                        <a href="{{ route('ventas.facturacion.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="facturaRapidaForm" onsubmit="event.preventDefault(); procesarFacturaRapida();">
        <div class="row">
            <!-- Panel Principal -->
            <div class="col-lg-8 mb-4">
                <!-- Información Rápida del Cliente -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info bg-opacity-10 border-0">
                        <h5 class="mb-0 text-info">
                            <i class="fas fa-user me-2"></i>
                            Cliente
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Documento</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="documentoRapido" placeholder="DNI o RUC" maxlength="11">
                                    <button class="btn btn-outline-secondary" type="button" onclick="buscarClienteRapido()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Nombre/Razón Social</label>
                                <input type="text" class="form-control" id="nombreRapido" placeholder="Nombre del cliente">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Búsqueda y Adición de Productos -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-success bg-opacity-10 border-0">
                        <h5 class="mb-0 text-success">
                            <i class="fas fa-barcode me-2"></i>
                            Agregar Productos
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Scanner de Código de Barras -->
                        <div class="alert alert-info">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">
                                        <i class="fas fa-scan me-2"></i>
                                        Escáner de Código de Barras
                                    </h6>
                                    <p class="mb-0">Usa el scanner o ingresa el código manualmente</p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="activarScanner()">
                                        <i class="fas fa-camera"></i> Activar Cámara
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Búsqueda Rápida -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control form-control-lg" id="busquedaRapida" 
                                       placeholder="Buscar producto por nombre, código o escanear..." 
                                       onkeyup="buscarProductoRapido(event)" autofocus>
                                <button class="btn btn-primary" type="button" onclick="mostrarProductosPopulares()">
                                    <i class="fas fa-star"></i> Populares
                                </button>
                            </div>
                        </div>

                        <!-- Resultados de Búsqueda -->
                        <div id="resultadosBusqueda" class="mb-3" style="display: none;">
                            <h6>Resultados de búsqueda:</h6>
                            <div class="list-group" id="listaProductos">
                                <!-- Resultados dinámicos -->
                            </div>
                        </div>

                        <!-- Productos Populares -->
                        <div id="productosPopulares">
                            <h6>Productos más vendidos:</h6>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-outline-primary w-100 p-3 h-100 d-flex flex-column align-items-center" 
                                            onclick="agregarProductoRapido('P001', 'Paracetamol 500mg', 15.50, 150)">
                                        <i class="fas fa-pills mb-2 fs-4"></i>
                                        <span class="small">Paracetamol 500mg</span>
                                        <small class="text-muted">S/ 15.50</small>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-outline-primary w-100 p-3 h-100 d-flex flex-column align-items-center" 
                                            onclick="agregarProductoRapido('P002', 'Ibuprofeno 400mg', 22.80, 85)">
                                        <i class="fas fa-capsules mb-2 fs-4"></i>
                                        <span class="small">Ibuprofeno 400mg</span>
                                        <small class="text-muted">S/ 22.80</small>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-outline-primary w-100 p-3 h-100 d-flex flex-column align-items-center" 
                                            onclick="agregarProductoRapido('P003', 'Amoxicilina 500mg', 18.90, 12)">
                                        <i class="fas fa-tablets mb-2 fs-4"></i>
                                        <span class="small">Amoxicilina 500mg</span>
                                        <small class="text-muted">S/ 18.90</small>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-outline-primary w-100 p-3 h-100 d-flex flex-column align-items-center" 
                                            onclick="agregarProductoRapido('P004', 'Omeprazol 20mg', 12.50, 65)">
                                        <i class="fas fa-circle mb-2 fs-4"></i>
                                        <span class="small">Omeprazol 20mg</span>
                                        <small class="text-muted">S/ 12.50</small>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carrito de Compras -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-warning bg-opacity-10 border-0">
                        <h5 class="mb-0 text-warning">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Carrito de Compras
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tablaCarrito">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%">Producto</th>
                                        <th style="width: 15%">Cantidad</th>
                                        <th style="width: 20%">Precio Unit.</th>
                                        <th style="width: 20%">Subtotal</th>
                                        <th style="width: 5%">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="carritoProductos">
                                    <tr id="filaVacia">
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-shopping-cart mb-2 fs-3"></i>
                                            <br>
                                            No hay productos en el carrito
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="col-lg-4 mb-4">
                <!-- Información de la Factura -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary bg-opacity-10 border-0">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-file-invoice me-2"></i>
                            Factura
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Número</label>
                            <input type="text" class="form-control" id="numeroFacturaRapida" 
                                   value="F-{{ str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT) }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fechaRapida" value="{{ date('Y-m-d') }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo Comprobante</label>
                            <select class="form-select" id="tipoComprobanteRapida">
                                <option value="boleta">Boleta</option>
                                <option value="factura">Factura</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Total y Pago -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-success bg-opacity-10 border-0">
                        <h5 class="mb-0 text-success">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            Total y Pago
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong id="subtotalRapido">S/ 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>IGV:</span>
                            <strong id="igvRapido">S/ 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span><strong>Total:</strong></span>
                            <h4 class="text-success mb-0" id="totalRapido">S/ 0.00</h4>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label">Método de Pago</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="metodoPago" id="efectivo" value="efectivo" autocomplete="off" checked>
                                <label class="btn btn-outline-success" for="efectivo">
                                    <i class="fas fa-money-bill-wave"></i><br>
                                    <small>Efectivo</small>
                                </label>

                                <input type="radio" class="btn-check" name="metodoPago" id="tarjeta" value="tarjeta" autocomplete="off">
                                <label class="btn btn-outline-primary" for="tarjeta">
                                    <i class="fas fa-credit-card"></i><br>
                                    <small>Tarjeta</small>
                                </label>

                                <input type="radio" class="btn-check" name="metodoPago" id="transferencia" value="transferencia" autocomplete="off">
                                <label class="btn btn-outline-info" for="transferencia">
                                    <i class="fas fa-university"></i><br>
                                    <small>Transferencia</small>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3" id="campoEfectivo">
                            <label class="form-label">Efectivo Recibido</label>
                            <input type="number" class="form-control form-control-lg" id="efectivoRecibido" 
                                   placeholder="0.00" min="0" step="0.01" onchange="calcularCambio()">
                            <small class="text-muted">Cambio: <strong id="cambio">S/ 0.00</strong></small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Procesar Venta
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="limpiarCarrito()">
                                <i class="fas fa-trash"></i> Limpiar Carrito
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Productos Recientes -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-info bg-opacity-10 border-0">
                        <h5 class="mb-0 text-info">
                            <i class="fas fa-history me-2"></i>
                            Ventas Recientes
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

                <!-- Atajos de Teclado -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-secondary bg-opacity-10 border-0">
                        <h5 class="mb-0 text-secondary">
                            <i class="fas fa-keyboard me-2"></i>
                            Atajos de Teclado
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <kbd>F2</kbd> Buscar
                            </div>
                            <div class="col-6">
                                <kbd>F3</kbd> Populares
                            </div>
                            <div class="col-6">
                                <kbd>F4</kbd> Cliente
                            </div>
                            <div class="col-6">
                                <kbd>F12</kbd> Procesar
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal de Confirmación de Pago -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Venta Procesada
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-success mb-2">¡Venta Exitosa!</h4>
                <p class="text-muted">La factura <strong id="numeroConfirmacion">F-000000</strong> ha sido procesada</p>
                <div class="bg-light p-3 rounded mb-3">
                    <h5>Total: <span class="text-success" id="totalConfirmacion">S/ 0.00</span></h5>
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" onclick="imprimirFacturaConfirmada()">
                        <i class="fas fa-print"></i> Imprimir Factura
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="enviarEmailConfirmado()">
                        <i class="fas fa-envelope"></i> Enviar por Email
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="nuevaVenta()">
                        <i class="fas fa-plus"></i> Nueva Venta
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let carrito = [];
let totalCarrito = 0;

function buscarProductoRapido(event) {
    if (event.key === 'Enter') {
        const busqueda = $('#busquedaRapida').val().trim();
        if (busqueda.length >= 2) {
            // Simular búsqueda
            console.log('Buscando:', busqueda);
            mostrarResultadosBusqueda(busqueda);
        }
    } else if (event.key === 'Escape') {
        $('#resultadosBusqueda').hide();
        $('#productosPopulares').show();
        $('#busquedaRapida').val('');
    }
}

function mostrarResultadosBusqueda(busqueda) {
    const productos = [
        { codigo: 'P001', nombre: 'Paracetamol 500mg x20', precio: 15.50, stock: 150 },
        { codigo: 'P002', nombre: 'Ibuprofeno 400mg x30', precio: 22.80, stock: 85 },
        { codigo: 'P003', nombre: 'Amoxicilina 500mg x21', precio: 18.90, stock: 12 },
        { codigo: 'P004', nombre: 'Omeprazol 20mg x14', precio: 12.50, stock: 65 }
    ];

    const resultados = productos.filter(p => 
        p.nombre.toLowerCase().includes(busqueda.toLowerCase()) || 
        p.codigo.toLowerCase().includes(busqueda.toLowerCase())
    );

    const lista = $('#listaProductos');
    lista.empty();

    if (resultados.length > 0) {
        $('#productosPopulares').hide();
        $('#resultadosBusqueda').show();

        resultados.forEach(producto => {
            const item = `
                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
                        onclick="agregarProductoRapido('${producto.codigo}', '${producto.nombre}', ${producto.precio}, ${producto.stock})">
                    <div>
                        <h6 class="mb-1">${producto.nombre}</h6>
                        <small class="text-muted">Código: ${producto.codigo}</small>
                    </div>
                    <div class="text-end">
                        <h6 class="mb-1 text-success">S/ ${producto.precio.toFixed(2)}</h6>
                        <small class="text-muted">Stock: ${producto.stock}</small>
                    </div>
                </button>
            `;
            lista.append(item);
        });
    } else {
        lista.append(`
            <div class="list-group-item text-center text-muted">
                <i class="fas fa-search mb-2"></i>
                <p class="mb-0">No se encontraron productos</p>
            </div>
        `);
    }
}

function agregarProductoRapido(codigo, nombre, precio, stock) {
    // Verificar si ya existe en el carrito
    const itemExistente = carrito.find(item => item.codigo === codigo);
    
    if (itemExistente) {
        itemExistente.cantidad += 1;
    } else {
        carrito.push({
            codigo: codigo,
            nombre: nombre,
            precio: precio,
            cantidad: 1,
            stock: stock
        });
    }
    
    actualizarCarrito();
    limpiarBusqueda();
    
    // Sonido de confirmación
    playBeep();
}

function eliminarProducto(index) {
    carrito.splice(index, 1);
    actualizarCarrito();
}

function actualizarCantidad(index, cantidad) {
    if (cantidad >= 1 && cantidad <= carrito[index].stock) {
        carrito[index].cantidad = parseInt(cantidad);
        actualizarCarrito();
    }
}

function actualizarCarrito() {
    const tbody = $('#carritoProductos');
    tbody.empty();
    
    if (carrito.length === 0) {
        tbody.append(`
            <tr id="filaVacia">
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="fas fa-shopping-cart mb-2 fs-3"></i>
                    <br>
                    No hay productos en el carrito
                </td>
            </tr>
        `);
    } else {
        carrito.forEach((item, index) => {
            const subtotal = item.cantidad * item.precio;
            const row = `
                <tr>
                    <td>
                        <div>
                            <strong>${item.codigo}</strong>
                            <br>
                            <small class="text-muted">${item.nombre}</small>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cambiarCantidad(${index}, -1)">-</button>
                            <input type="number" class="form-control text-center" value="${item.cantidad}" 
                                   min="1" max="${item.stock}" 
                                   onchange="actualizarCantidad(${index}, this.value)">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cambiarCantidad(${index}, 1)">+</button>
                        </div>
                    </td>
                    <td>
                        <div class="text-end">
                            <strong>S/ ${item.precio.toFixed(2)}</strong>
                            <br>
                            <small class="text-muted">Stock: ${item.stock}</small>
                        </div>
                    </td>
                    <td>
                        <strong>S/ ${subtotal.toFixed(2)}</strong>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProducto(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    calcularTotales();
}

function cambiarCantidad(index, cambio) {
    const nuevaCantidad = carrito[index].cantidad + cambio;
    if (nuevaCantidad >= 1 && nuevaCantidad <= carrito[index].stock) {
        carrito[index].cantidad = nuevaCantidad;
        actualizarCarrito();
    }
}

function calcularTotales() {
    let subtotal = 0;
    carrito.forEach(item => {
        subtotal += item.cantidad * item.precio;
    });
    
    const igv = subtotal * 0.18;
    const total = subtotal + igv;
    
    $('#subtotalRapido').text('S/ ' + subtotal.toFixed(2));
    $('#igvRapido').text('S/ ' + igv.toFixed(2));
    $('#totalRapido').text('S/ ' + total.toFixed(2));
    
    totalCarrito = total;
}

function calcularCambio() {
    const efectivo = parseFloat($('#efectivoRecibido').val()) || 0;
    const cambio = efectivo - totalCarrito;
    
    if (cambio >= 0) {
        $('#cambio').text('S/ ' + cambio.toFixed(2));
    } else {
        $('#cambio').text('Faltante: S/ ' + Math.abs(cambio).toFixed(2));
    }
}

function limpiarCarrito() {
    Swal.fire({
        title: 'Limpiar Carrito',
        text: '¿Estás seguro de eliminar todos los productos del carrito?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            carrito = [];
            actualizarCarrito();
            $('#busquedaRapida').focus();
        }
    });
}

function buscarClienteRapido() {
    const documento = $('#documentoRapido').val();
    if (documento.length >= 6) {
        // Simular búsqueda de cliente
        console.log('Buscando cliente:', documento);
        $('#nombreRapido').val('Cliente encontrado'); // Simulación
    }
}

function mostrarProductosPopulares() {
    $('#resultadosBusqueda').hide();
    $('#productosPopulares').show();
    $('#busquedaRapida').val('');
}

function activarScanner() {
    Swal.fire({
        title: 'Escáner de Código',
        text: 'Función de escáner en desarrollo. Por favor, ingresa el código manualmente.',
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
}

function playBeep() {
    // Simular sonido de confirmación
    console.log('🔊 Beep!');
}

function limpiarBusqueda() {
    $('#busquedaRapida').val('');
    $('#resultadosBusqueda').hide();
    $('#productosPopulares').show();
}

function procesarFacturaRapida() {
    if (carrito.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Agregue al menos un producto al carrito'
        });
        return;
    }
    
    if (!$('#nombreRapido').val()) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ingrese el nombre del cliente'
        });
        return;
    }
    
    const metodoPago = $('input[name="metodoPago"]:checked').val();
    if (metodoPago === 'efectivo') {
        const efectivo = parseFloat($('#efectivoRecibido').val()) || 0;
        if (efectivo < totalCarrito) {
            Swal.fire({
                icon: 'error',
                title: 'Efectivo insuficiente',
                text: `Faltan S/ ${(totalCarrito - efectivo).toFixed(2)}`
            });
            return;
        }
    }
    
    // Simular procesamiento
    Swal.fire({
        title: 'Procesando venta...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });
    
    setTimeout(() => {
        Swal.close();
        
        // Mostrar modal de confirmación
        $('#numeroConfirmacion').text($('#numeroFacturaRapida').val());
        $('#totalConfirmacion').text('S/ ' + totalCarrito.toFixed(2));
        
        const modal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
        modal.show();
        
        // Limpiar formulario
        // limpiarCarrito();
    }, 2000);
}

function imprimirFacturaConfirmada() {
    window.open(`/ventas/facturacion/ver/${$('#numeroConfirmacion').text()}`, '_blank');
}

function enviarEmailConfirmado() {
    if (!$('#nombreRapido').val()) {
        Swal.fire({
            icon: 'error',
            title: 'Email requerido',
            text: 'Ingrese el email del cliente'
        });
        return;
    }
    
    Swal.fire({
        title: 'Enviar por Email',
        html: `
            <div class="text-left">
                <p>Enviar factura <strong>${$('#numeroConfirmacion').text()}</strong></p>
                <div class="mb-3">
                    <label class="form-label">Email:</label>
                    <input type="email" class="form-control" id="emailFinal" placeholder="cliente@email.com">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const email = document.getElementById('emailFinal').value;
            if (!email) {
                Swal.showValidationMessage('El email es requerido');
                return false;
            }
            return email;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Email enviado',
                text: `Factura enviada a ${result.value}`
            });
        }
    });
}

function nuevaVenta() {
    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmacion'));
    modal.hide();
    
    // Limpiar formulario
    carrito = [];
    actualizarCarrito();
    $('#busquedaRapida').val('');
    $('#documentoRapido').val('');
    $('#nombreRapido').val('');
    $('#efectivoRecibido').val('');
    $('#cambio').text('S/ 0.00');
    $('#numeroFacturaRapida').value = 'F-' + String(Math.floor(Math.random() * 999999) + 1).padStart(6, '0');
    
    // Enfocar búsqueda
    $('#busquedaRapida').focus();
}

// Atajos de teclado
document.addEventListener('keydown', function(event) {
    switch(event.key) {
        case 'F2':
            event.preventDefault();
            $('#busquedaRapida').focus();
            break;
        case 'F3':
            event.preventDefault();
            mostrarProductosPopulares();
            break;
        case 'F4':
            event.preventDefault();
            $('#documentoRapido').focus();
            break;
        case 'F12':
            event.preventDefault();
            procesarFacturaRapida();
            break;
        case 'Escape':
            if ($('#resultadosBusqueda').is(':visible')) {
                limpiarBusqueda();
            }
            break;
    }
});

// Event listeners
$('input[name="metodoPago"]').on('change', function() {
    const metodo = $(this).val();
    if (metodo === 'efectivo') {
        $('#campoEfectivo').show();
        $('#efectivoRecibido').focus();
    } else {
        $('#campoEfectivo').hide();
    }
});

$('#efectivoRecibido').on('input', calcularCambio);

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    $('#busquedaRapida').focus();
    actualizarCarrito();
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

.form-control-lg {
    font-size: 1.1rem;
}

.btn-check:checked + .btn {
    background-color: var(--bs-success);
    border-color: var(--bs-success);
    color: white;
}

.table td {
    vertical-align: middle;
}

.kbd {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 0.125rem 0.375rem;
    font-size: 0.75rem;
    font-family: monospace;
}

.list-group-item {
    border: none;
    padding-left: 0;
    padding-right: 0;
}

.list-group-item:not(:last-child) {
    border-bottom: 1px solid #dee2e6;
}

.modal-dialog {
    max-width: 400px;
}

.alert {
    margin-bottom: 1rem;
}

.btn-outline-primary:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-outline-success:hover {
    background-color: #198754;
    border-color: #198754;
}
</style>
@endsection