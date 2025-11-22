@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Nueva Venta')
@section('page-title', 'Nueva Venta / Facturación')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.facturas.index') }}">Ventas</a></li>
    <li class="breadcrumb-item active" aria-current="page">Nueva Venta</li>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/ventas/crear.css') }}">    
@endpush

@section('content')

<div class="paso-indicator">
    <div class="paso-indicator-item {{ $carrito && $carrito['cliente'] ? 'active' : '' }}" id="indicador-paso1">
        <span class="numero">1</span>
        <span class="texto">Cliente</span>
    </div>
    <div class="paso-indicator-item {{ $carrito && $carrito['items']->isNotEmpty() ? 'active' : '' }}" id="indicador-paso2">
        <span class="numero">2</span>
        <span class="texto">Productos</span>
    </div>
    <div class="paso-indicator-item {{ $carrito && $carrito['items']->isNotEmpty() ? 'active' : '' }}" id="indicador-paso3">
        <span class="numero">3</span>
        <span class="texto">Finalizar</span>
    </div>
</div>

<div class="row">
    <div class="col-lg-11 mx-auto">
        <form action="{{ route('contador.facturas.store') }}" method="POST" id="formVentaFinal">
            @csrf

            <div class="card paso-card active mb-4" id="paso1_cliente">
                <div class="card-header">
                    <h5 class="card-title m-0">
                        <i class="fas fa-user-check me-2"></i>
                        Paso 1: Seleccionar Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Busque y seleccione el cliente para esta venta por RUC, DNI o Razón Social.
                    </p>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">
                                <i class="fas fa-search me-1"></i> Buscar Cliente
                            </label>
                            <select id="selectCliente" class="form-control" name="cliente_id_selector">
                                <option value="">Escriba RUC, DNI o Razón Social (mínimo 3 caracteres)...</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="resumenCliente" class="mt-4" 
                         style="{{ $carrito && $carrito['cliente'] ? '' : 'display: none;' }}">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                <i class="fas fa-user-circle fa-3x"></i>
                            </div>
                            <div class="col-md-11">
                                <h5 class="mb-2">
                                    <i class="fas fa-building me-2"></i>
                                    <span id="clienteNombre">{{ $carrito['cliente']->Razon ?? '' }}</span>
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong><i class="fas fa-id-card me-1"></i> RUC/DNI:</strong> 
                                        <span id="clienteDocumento">{{ $carrito['cliente']->Documento ?? '' }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong><i class="fas fa-map-marker-alt me-1"></i> Dirección:</strong> 
                                        <span id="clienteDireccion">{{ $carrito['cliente']->Direccion ?? '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card paso-card {{ $carrito ? 'active' : '' }} mb-4" id="paso2_items">
                <div class="card-header">
                    <h5 class="card-title m-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Paso 2: Añadir Productos al Carrito
                    </h5>
                </div>
                <div class="card-body">
                    
                    <div class="producto-form-card mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-pills me-1"></i> 1. Buscar Producto
                                </label>
                                <select id="selectProducto" class="form-control"></select>
                                <small class="form-text text-muted">Busque por nombre o código</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-box me-1"></i> 2. Seleccionar Lote (Stock)
                                </label>
                                <select id="selectLote" class="form-control" disabled>
                                    <option value="">Seleccione un producto primero...</option>
                                </select>
                                <small id="stockDisponible" class="form-text"></small>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-hashtag me-1"></i> 3. Cantidad
                                </label>
                                <input type="number" class="form-control" id="itemCantidad" 
                                       placeholder="0.00" step="0.01" min="0.01">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-dollar-sign me-1"></i> 4. Precio (S/)
                                </label>
                                <input type="number" class="form-control" id="itemPrecio" 
                                       placeholder="0.00" step="0.01" min="0.01">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-percent me-1"></i> 5. Dcto (%)
                                </label>
                                <input type="number" class="form-control" id="itemDescuento" 
                                       value="0" step="0.01" min="0" max="100">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-calculator me-1"></i> Subtotal
                                </label>
                                <input type="text" class="form-control bg-light" id="itemSubtotal" 
                                       placeholder="S/ 0.00" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-plus-circle me-1"></i> 6. Añadir
                                </label>
                                <button type="button" class="btn btn-success w-100" id="btnAgregarItem">
                                    <i class="fas fa-cart-plus me-1"></i> Agregar
                                </button>
                            </div>
                            
                            <input type="hidden" id="itemCosto">
                            <input type="hidden" id="itemVencimiento">
                            <input type="hidden" id="itemUnimed">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-items table-sm mb-0">
                            <thead>
                                <tr>
                                    <th class="py-3"><i class="fas fa-box me-1"></i> Producto</th>
                                    <th class="py-3"><i class="fas fa-barcode me-1"></i> Lote</th>
                                    <th class="text-end py-3"><i class="fas fa-hashtag me-1"></i> Cantidad</th>
                                    <th class="text-end py-3"><i class="fas fa-tag me-1"></i> Precio</th>
                                    <th class="text-end py-3"><i class="fas fa-percent me-1"></i> Dcto %</th>
                                    <th class="text-end py-3"><i class="fas fa-calculator me-1"></i> Subtotal</th>
                                    <th class="text-center py-3"><i class="fas fa-cog me-1"></i> Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tablaItems">
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-shopping-cart fa-2x mb-2 d-block"></i>
                                        El carrito está vacío. Añada productos arriba.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end py-3">
                                        <i class="fas fa-file-invoice me-1"></i> SUBTOTAL BRUTO:
                                    </td>
                                    <td class="text-end py-3" id="totalBruto">S/ 0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end py-3 text-danger">
                                        <i class="fas fa-tags me-1"></i> DESCUENTO TOTAL:
                                    </td>
                                    <td class="text-end py-3 text-danger fw-bold" id="totalDescuento">S/ 0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end py-3">
                                        <i class="fas fa-receipt me-1"></i> SUBTOTAL NETO:
                                    </td>
                                    <td class="text-end py-3 fw-bold" id="totalSubtotal">S/ 0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end py-3">
                                        <i class="fas fa-percent me-1"></i> IGV (18%):
                                    </td>
                                    <td class="text-end py-3" id="totalIgv">S/ 0.00</td>
                                    <td></td>
                                </tr>
                                <tr class="fs-5">
                                    <td colspan="5" class="text-end py-3 fw-bolder">
                                        <i class="fas fa-coins me-1"></i> IMPORTE TOTAL:
                                    </td>
                                    <td class="text-end py-3 fw-bolder text-success" id="totalTotal">S/ 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
       
            <div class="card paso-card {{ $carrito && $carrito['items']->isNotEmpty() ? 'active' : '' }} mb-4" id="paso3_pago">
                <div class="card-header">
                    <h5 class="card-title m-0">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        Paso 3: Condiciones de Venta
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="tipo_doc" class="form-label fw-bold">
                                <i class="fas fa-file-alt me-1"></i> Tipo Documento
                            </label>
                            <select class="form-select" id="tipo_doc" name="tipo_doc" required>
                                @foreach($tiposDoc as $tipo)
                                    <option value="{{ $tipo->n_numero }}" 
                                            data-requiere-ruc="{{ in_array($tipo->n_numero, [1, 4]) ? '1' : '0' }}"
                                            @selected(old('tipo_doc', $carrito['pago']['tipo_doc'] ?? 1) == $tipo->n_numero)>
                                        {{ $tipo->c_describe }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text" id="infoTipoDoc"></small>
                        </div>
                        <div class="col-md-3">
                            <label for="condicion" class="form-label fw-bold">
                                <i class="fas fa-credit-card me-1"></i> Condición de Pago
                            </label>
                            <select class="form-select" id="condicion" name="condicion" required>
                                <option value="contado" @selected(old('condicion', $carrito['pago']['condicion'] ?? 'contado') == 'contado')>
                                    Contado
                                </option>
                                <option value="credito" @selected(old('condicion', $carrito['pago']['condicion'] ?? '') == 'credito')>
                                    Crédito
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3" id="campoFechaVenc" 
                             style="{{ old('condicion', $carrito['pago']['condicion'] ?? 'contado') == 'credito' ? '' : 'display: none;' }}">
                            <label for="fecha_venc" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-1"></i> Fecha Vencimiento
                            </label>
                            <input type="date" class="form-control" id="fecha_venc" name="fecha_venc"
                                   value="{{ old('fecha_venc', $carrito['pago']['fecha_venc'] ?? now()->addDays(30)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label for="vendedor_id" class="form-label fw-bold">
                                <i class="fas fa-user-tie me-1"></i> Vendedor
                            </label>
                            <select class="form-select" id="vendedor_id" name="vendedor_id" required>
                                <option value="">Seleccione...</option>
                                @foreach($vendedores as $vendedor)
                                    <option value="{{ $vendedor->Codemp }}" 
                                            @selected(old('vendedor_id', $carrito['pago']['vendedor_id'] ?? '') == $vendedor->Codemp)>
                                        {{ $vendedor->Nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-center border-top pt-4">
                    <button type="submit" class="btn btn-submit-final">
                        <i class="fas fa-check-circle me-2"></i> 
                        Confirmar y Guardar Venta
                    </button>
                    <p class="text-muted mt-3 mb-0">
                        <i class="fas fa-shield-alt me-1"></i>
                        Al confirmar, se generará el documento electrónico y se actualizará el inventario.
                    </p>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        
        const URL_BUSCAR_CLIENTES = "{{ route('contador.facturas.api.buscarClientes') }}";
        const URL_BUSCAR_PRODUCTOS = "{{ route('contador.facturas.api.buscarProductos') }}";
        const URL_BUSCAR_LOTES = "{{ route('contador.facturas.api.buscarLotes', ['codPro' => 'CODPRO']) }}";
        const URL_CARRITO_ADD = "{{ route('contador.facturas.carrito.agregar') }}";
        const URL_CARRITO_DEL = "{{ route('contador.facturas.carrito.eliminar', ['itemId' => 'ITEMID']) }}";
        const URL_CARRITO_PAGO = "{{ route('contador.facturas.carrito.pago') }}";
        const TOKEN = '{{ csrf_token() }}';

        let clienteActual = null;

        @if($carrito && $carrito['cliente'])
            clienteActual = @json($carrito['cliente']);
        @endif

        
        $('#selectCliente').select2({
            placeholder: 'Escriba para buscar cliente...',
            minimumInputLength: 3,
            ajax: {
                url: URL_BUSCAR_CLIENTES,
                dataType: 'json',
                delay: 300,
                data: (params) => ({ q: params.term }),
                processResults: (data) => ({
                    results: $.map(data, (cliente) => ({
                        id: cliente.Codclie,
                        text: `${cliente.Razon} - ${cliente.Documento}`,
                        data: cliente
                    }))
                })
            }
        });

        // ========================================
        // EVENTOS: Selección de Cliente
        // ========================================
        $('#selectCliente').on('select2:select', function (e) {
            const cliente = e.params.data.data;
            clienteActual = cliente;
            window.location.href = "{{ route('contador.facturas.create') }}?cliente_id=" + cliente.Codclie;
        });

        // ========================================
        // 2. BUSCADOR DE PRODUCTOS
        // ========================================
        $('#selectProducto').select2({
            placeholder: 'Escriba para buscar producto...',
            minimumInputLength: 3,
            ajax: {
                url: URL_BUSCAR_PRODUCTOS,
                dataType: 'json',
                delay: 300,
                data: (params) => ({ q: params.term }),
                processResults: (data) => ({
                    results: $.map(data, (prod) => ({
                        id: prod.CodPro,
                        text: `${prod.Nombre} (Stock: ${prod.Stock})`,
                        data: prod
                    }))
                })
            }
        });

        $('#selectLote').select2({ 
            placeholder: 'Seleccione un lote',
            disabled: true 
        });

        // ========================================
        // EVENTOS: Selección de Producto
        // ========================================
        $('#selectProducto').on('select2:select', function (e) {
            const producto = e.params.data.data;
            $('#itemPrecio').val(parseFloat(producto.Precio).toFixed(2));
            $('#itemCosto').val(parseFloat(producto.Costo).toFixed(2));
            $('#itemUnimed').val(1);
            $('#itemDescuento').val(0);
            
            const $selectLote = $('#selectLote');
            $selectLote.empty().prop('disabled', true).append('<option value="">Cargando lotes...</option>');
            $('#stockDisponible').text('');
            $('#itemCantidad').val('');
            actualizarSubtotal();

            $.get(URL_BUSCAR_LOTES.replace('CODPRO', producto.CodPro), function(lotes) {
                $selectLote.empty().prop('disabled', false);
                if (lotes.length > 0) {
                    $selectLote.append('<option value="">Seleccione un lote...</option>');
                    lotes.forEach(lote => {
                        const venc = lote.vencimiento ? new Date(lote.vencimiento).toLocaleDateString('es-PE') : 'N/A';
                        $selectLote.append(
                            `<option value="${lote.lote}" data-stock="${lote.saldo}" data-vencimiento="${lote.vencimiento}">
                                Lote: ${lote.lote} | Stock: ${lote.saldo} | Vence: ${venc}
                            </option>`
                        );
                    });
                } else {
                    $selectLote.append('<option value="">¡Sin stock disponible!</option>');
                }
            });
        });

        // ========================================
        // EVENTOS: Selección de Lote
        // ========================================
        $('#selectLote').on('change', function() {
            const stock = $(this).find('option:selected').data('stock');
            const vencimiento = $(this).find('option:selected').data('vencimiento');
            if(stock) {
                $('#stockDisponible').text(`✓ Stock disponible: ${stock} unidades`);
                $('#itemCantidad').val(1).attr('max', stock);
                $('#itemVencimiento').val(vencimiento);
                actualizarSubtotal();
            }
        });

        // ========================================
        // CALCULAR SUBTOTAL CON DESCUENTO
        // ========================================
        $('#itemCantidad, #itemPrecio, #itemDescuento').on('input', actualizarSubtotal);

        function actualizarSubtotal() {
            const cantidad = parseFloat($('#itemCantidad').val()) || 0;
            const precio = parseFloat($('#itemPrecio').val()) || 0;
            const descuento = parseFloat($('#itemDescuento').val()) || 0;
            
            const subtotalBruto = cantidad * precio;
            const montoDescuento = subtotalBruto * (descuento / 100);
            const subtotalNeto = subtotalBruto - montoDescuento;
            
            $('#itemSubtotal').val('S/ ' + subtotalNeto.toFixed(2));
        }

        // ========================================
        // AÑADIR ITEM AL CARRITO
        // ========================================
        $('#btnAgregarItem').on('click', function() {
            const productoSel = $('#selectProducto').select2('data')[0];
            const loteSel = $('#selectLote').find('option:selected');
            
            if (!productoSel || !loteSel.val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Datos Incompletos',
                    text: 'Debe seleccionar un producto y un lote.',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            const item = {
                codpro: productoSel.id,
                nombre: productoSel.data.Nombre,
                lote: loteSel.val(),
                vencimiento: $('#itemVencimiento').val(),
                unimed: $('#itemUnimed').val(),
                cantidad: parseFloat($('#itemCantidad').val()),
                precio: parseFloat($('#itemPrecio').val()),
                costo: parseFloat($('#itemCosto').val()),
                stock: parseFloat(loteSel.data('stock')),
                descuento: parseFloat($('#itemDescuento').val()) || 0
            };

            if (!item.cantidad || item.cantidad <= 0 || !item.precio) {
                Swal.fire({
                    icon: 'error',
                    title: 'Datos Inválidos',
                    text: 'Debe ingresar una cantidad y precio válidos.',
                    confirmButtonColor: '#667eea'
                });
                return;
            }
            if (item.cantidad > item.stock) {
                Swal.fire({
                    icon: 'error',
                    title: 'Stock Insuficiente',
                    text: `Solo quedan ${item.stock} unidades en este lote.`,
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            $.ajax({
                url: URL_CARRITO_ADD,
                type: 'POST',
                data: JSON.stringify(item),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': TOKEN },
                success: (response) => {
                    if(response.success) {
                        actualizarVistaCarrito(response.carrito);
                        limpiarFormularioItem();
                        Swal.fire({
                            icon: 'success',
                            title: 'Producto Agregado',
                            text: 'El producto se agregó correctamente al carrito.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                },
                error: (jqXHR) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: jqXHR.responseJSON.message || 'Error al añadir item',
                        confirmButtonColor: '#667eea'
                    });
                }
            });
        });

        // ========================================
        // ELIMINAR ITEM
        // ========================================
        $('#tablaItems').on('click', '.btn-eliminar-item', function() {
            const itemId = $(this).data('item-id');
            Swal.fire({
                title: '¿Eliminar producto?',
                text: "Se quitará este producto del carrito",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: URL_CARRITO_DEL.replace('ITEMID', itemId),
                        type: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': TOKEN },
                        success: (response) => {
                            actualizarVistaCarrito(response.carrito);
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: 'Producto eliminado del carrito',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });
        });

        // ========================================
        // GUARDAR CONDICIONES DE PAGO
        // ========================================
        $('#tipo_doc, #condicion, #fecha_venc, #vendedor_id').on('change', function() {
            $('#campoFechaVenc').toggle($('#condicion').val() === 'credito');
            
            // Validar tipo de documento cuando cambia
            validarTipoDocumentoCliente();
            
            $.ajax({
                url: URL_CARRITO_PAGO,
                type: 'POST',
                data: JSON.stringify({
                    tipo_doc: $('#tipo_doc').val(),
                    condicion: $('#condicion').val(),
                    fecha_venc: $('#fecha_venc').val(),
                    vendedor_id: $('#vendedor_id').val(),
                }),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': TOKEN }
            });
        });

        // ========================================
        // VALIDAR TIPO DOCUMENTO vs CLIENTE
        // ========================================
        function validarTipoDocumentoCliente() {
            if (!clienteActual) return;
            
            const tipoDocSel = $('#tipo_doc option:selected');
            const requiereRuc = tipoDocSel.data('requiere-ruc') == '1';
            const clienteDoc = clienteActual.Documento || '';
            const clienteTieneRuc = clienteDoc.length === 11;
            const $infoTipoDoc = $('#infoTipoDoc');
            
            // Facturas (01) y Notas requieren RUC de 11 dígitos
            if (requiereRuc && !clienteTieneRuc) {
                $infoTipoDoc.html('<i class="fas fa-exclamation-triangle text-warning"></i> <span class="text-warning">Este documento requiere RUC (11 dígitos)</span>');
                
                Swal.fire({
                    icon: 'warning',
                    title: '¿Tipo de Documento Incorrecto?',
                    html: `
                        <div class="text-start">
                            <p><strong>${tipoDocSel.text()}</strong> requiere que el cliente tenga RUC de 11 dígitos.</p>
                            <p>📄 Cliente seleccionado: <strong>${clienteActual.Razon}</strong></p>
                            <p>🆔 Documento: <strong>${clienteDoc}</strong> (${clienteDoc.length} dígitos)</p>
                            <hr>
                            <p class="text-primary"><strong>¿Desea cambiar a Boleta de Venta?</strong></p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-check"></i> Sí, usar Boleta',
                    cancelButtonText: '<i class="fas fa-times"></i> No, mantener Factura'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Buscar y seleccionar Boleta (tipo 3)
                        $('#tipo_doc option').each(function() {
                            if ($(this).text().toLowerCase().includes('boleta')) {
                                $('#tipo_doc').val($(this).val());
                                
                                // Guardar el cambio en el carrito
                                $.ajax({
                                    url: URL_CARRITO_PAGO,
                                    type: 'POST',
                                    data: JSON.stringify({
                                        tipo_doc: $('#tipo_doc').val(),
                                        condicion: $('#condicion').val(),
                                        fecha_venc: $('#fecha_venc').val(),
                                        vendedor_id: $('#vendedor_id').val(),
                                    }),
                                    contentType: 'application/json',
                                    headers: { 'X-CSRF-TOKEN': TOKEN },
                                    success: function() {
                                        validarTipoDocumentoCliente();
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Cambiado a Boleta',
                                            text: 'Se ha seleccionado Boleta de Venta',
                                            timer: 2000,
                                            showConfirmButton: false
                                        });
                                    }
                                });
                                
                                return false;
                            }
                        });
                    }
                });
            } else if (!requiereRuc && clienteTieneRuc) {
                $infoTipoDoc.html('<i class="fas fa-info-circle text-info"></i> <span class="text-info">💡 Este cliente tiene RUC, puede emitir Factura</span>');
            } else if (requiereRuc && clienteTieneRuc) {
                $infoTipoDoc.html('<i class="fas fa-check-circle text-success"></i> <span class="text-success">✓ Cliente con RUC válido para Factura</span>');
            } else {
                $infoTipoDoc.html('<i class="fas fa-check-circle text-success"></i> <span class="text-success">✓ Documento válido para este cliente</span>');
            }
        }

        // ========================================
        // FUNCIONES DE AYUDA
        // ========================================
        function limpiarFormularioItem() {
            $('#selectProducto').val(null).trigger('change');
            $('#selectLote').empty().prop('disabled', true).append('<option value="">Seleccione un producto...</option>');
            $('#itemCantidad').val('');
            $('#itemPrecio').val('');
            $('#itemDescuento').val('0');
            $('#itemSubtotal').val('');
            $('#stockDisponible').text('');
        }
        
        function formatCurrency(value) {
            return 'S/ ' + parseFloat(value).toFixed(2);
        }

        function actualizarVistaCarrito(carrito) {
            if (!carrito) {
                $('#paso2_items, #paso3_pago').removeClass('active');
                $('#tablaItems').empty();
                $('#totalBruto, #totalDescuento, #totalSubtotal, #totalIgv, #totalTotal').text('S/ 0.00');
                return;
            }

            $('#paso2_items').addClass('active');
            $('#indicador-paso2').addClass('active');
            
            const $tablaItems = $('#tablaItems');
            $tablaItems.empty();
            
            if (carrito.items && Object.keys(carrito.items).length > 0) {
                $.each(carrito.items, function(itemId, item) {
                    const subtotalBruto = item.cantidad * item.precio;
                    const montoDescuento = subtotalBruto * (item.descuento / 100);
                    const subtotalNeto = subtotalBruto - montoDescuento;
                    const venc = item.vencimiento ? new Date(item.vencimiento).toLocaleDateString('es-PE') : 'N/A';
                    
                    $tablaItems.append(`
                        <tr>
                            <td>
                                <strong>${item.nombre}</strong><br>
                                <small class="text-muted">Código: ${item.codpro}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">${item.lote}</span><br>
                                <small class="text-muted">Venc: ${venc}</small>
                            </td>
                            <td class="text-end">${item.cantidad}</td>
                            <td class="text-end">${formatCurrency(item.precio)}</td>
                            <td class="text-end ${item.descuento > 0 ? 'text-danger fw-bold' : ''}">${item.descuento}%</td>
                            <td class="text-end fw-bold">${formatCurrency(subtotalNeto)}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm btn-eliminar-item" data-item-id="${itemId}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
                $('#paso3_pago').addClass('active');
                $('#indicador-paso3').addClass('active');
            } else {
                $tablaItems.append(`
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-shopping-cart fa-2x mb-2 d-block"></i>
                            El carrito está vacío. Añada productos arriba.
                        </td>
                    </tr>
                `);
                $('#paso3_pago').removeClass('active');
                $('#indicador-paso3').removeClass('active');
            }

            // Actualizar totales con descuento
            $('#totalBruto').text(formatCurrency(carrito.totales.subtotal_bruto || 0));
            $('#totalDescuento').text(formatCurrency(carrito.totales.descuento_total || 0));
            $('#totalSubtotal').text(formatCurrency(carrito.totales.subtotal));
            $('#totalIgv').text(formatCurrency(carrito.totales.igv));
            $('#totalTotal').text(formatCurrency(carrito.totales.total));
        }
        
        @if($carrito)
            actualizarVistaCarrito(@json($carrito));
            $('#tipo_doc').val("{{ $carrito['pago']['tipo_doc'] ?? 1 }}");
            $('#condicion').val("{{ $carrito['pago']['condicion'] ?? 'contado' }}").trigger('change');
            $('#fecha_venc').val("{{ $carrito['pago']['fecha_venc'] ?? '' }}");
            $('#vendedor_id').val("{{ $carrito['pago']['vendedor_id'] ?? '' }}");
        @endif

        // Validar tipo de documento al cargar la página si hay cliente
        if (clienteActual) {
            setTimeout(function() {
                validarTipoDocumentoCliente();
            }, 500);
        }

    });
    </script>
@endpush