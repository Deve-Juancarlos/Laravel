@extends('layouts.app')

@section('title', 'Nueva Venta')
@section('page-title', 'Nueva Venta / Facturación')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.facturas.index') }}">Ventas</a></li>
    <li class="breadcrumb-item active" aria-current="page">Nueva Venta</li>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single { height: 38px; border: 1px solid #ced4da; padding: 6px 12px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
        .select2-container { width: 100% !important; }
        .form-section { display: none; }
        .select2-dropdown { z-index: 1060; }
        #tablaItems .btn-danger { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
    </style>
@endpush

@section('content')

<div class="row">
    <div class="col-lg-10 mx-auto">
        <form action="{{ route('contador.facturas.store') }}" method="POST" id="formVentaFinal">
            @csrf

            <div class="card shadow mb-4" id="paso1_cliente">
                <div class="card-header">
                    <h5 class="card-title m-0"><i class="fas fa-user-check me-2"></i>Paso 1: Seleccionar Cliente</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Busque y seleccione el cliente para esta venta.</p>
                    <select id="selectCliente" class="form-control" name="cliente_id_selector">
                        <option value="">Buscar por RUC o Razón Social...</option>
                    </select>
                    
                    <div id="resumenCliente" class="mt-3 alert alert-info" 
                         style="{{ $carrito && $carrito['cliente'] ? '' : 'display: none;' }}">
                        <strong>Cliente:</strong> <span id="clienteNombre">{{ $carrito['cliente']->Razon ?? '' }}</span><br>
                        <strong>RUC/DNI:</strong> <span id="clienteDocumento">{{ $carrito['cliente']->Documento ?? '' }}</span><br>
                        <strong>Dirección:</strong> <span id="clienteDireccion">{{ $carrito['cliente']->Direccion ?? '' }}</span>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4 form-section" id="paso2_items"
                 style="{{ $carrito ? '' : 'display: none;' }}">
                <div class="card-header">
                    <h5 class="card-title m-0"><i class="fas fa-shopping-cart me-2"></i>Paso 2: Añadir Productos al Carrito</h5>
                </div>
                <div class="card-body">
                    <div class="card bg-light border p-3 mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">1. Buscar Producto</label>
                                <select id="selectProducto" class="form-control"></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">2. Seleccionar Lote (Stock)</label>
                                <select id="selectLote" class="form-control" disabled>
                                    <option value="">Seleccione un producto primero...</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">3. Cantidad</label>
                                <input type="number" class="form-control" id="itemCantidad" placeholder="0.00" step="0.01">
                                <small id="stockDisponible" class="form-text text-muted"></small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">4. Precio (S/)</label>
                                <input type="number" class="form-control" id="itemPrecio" placeholder="0.00" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">5. Añadir</label>
                                <button type="button" class="btn btn-success w-100" id="btnAgregarItem">
                                    <i class="fas fa-plus"></i> Añadir
                                </button>
                            </div>
                            <input type="hidden" id="itemCosto">
                            <input type="hidden" id="itemVencimiento">
                            <input type="hidden" id="itemUnimed">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Lote</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tablaItems"></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">SUBTOTAL:</td>
                                    <td class="text-end fw-bold" id="totalSubtotal">S/ 0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">IGV (18%):</td>
                                    <td class="text-end fw-bold" id="totalIgv">S/ 0.00</td>
                                    <td></td>
                                </tr>
                                <tr class="fs-5">
                                    <td colspan="4" class="text-end fw-bolder">TOTAL:</td>
                                    <td class="text-end fw-bolder" id="totalTotal">S/ 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4 form-section" id="paso3_pago"
                 style="{{ $carrito && $carrito['items']->isNotEmpty() ? '' : 'display: none;' }}">
                <div class="card-header">
                    <h5 class="card-title m-0"><i class="fas fa-file-invoice-dollar me-2"></i>Paso 3: Condiciones de Venta</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="tipo_doc" class="form-label fw-bold">Tipo Documento</label>
                            <select class="form-select" id="tipo_doc" name="tipo_doc">
                                @foreach($tiposDoc as $tipo)
                                    <option value="{{ $tipo->n_numero }}">{{ $tipo->c_describe }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="condicion" class="form-label fw-bold">Condición de Pago</label>
                            <select class="form-select" id="condicion" name="condicion">
                                <option value="contado">Contado</option>
                                <option value="credito">Crédito</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3" id="campoFechaVenc" style="display: none;">
                            <label for="fecha_venc" class="form-label fw-bold">Fecha Vencimiento</label>
                            <input type="date" class="form-control" id="fecha_venc" name="fecha_venc"
                                   value="{{ $carrito['pago']['fecha_venc'] ?? now()->addDays(30)->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="vendedor_id" class="form-label fw-bold">Vendedor</label>
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
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-1"></i> Confirmar y Guardar Venta
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
    $(document).ready(function() {
        
        const URL_BUSCAR_CLIENTES = "{{ route('contador.facturas.api.buscarClientes') }}";
        const URL_BUSCAR_PRODUCTOS = "{{ route('contador.facturas.api.buscarProductos') }}";
        const URL_BUSCAR_LOTES = "{{ route('contador.facturas.api.buscarLotes', ['codPro' => 'CODPRO']) }}";
        const URL_CARRITO_ADD = "{{ route('contador.facturas.carrito.agregar') }}";
        const URL_CARRITO_DEL = "{{ route('contador.facturas.carrito.eliminar', ['itemId' => 'ITEMID']) }}";
        const URL_CARRITO_PAGO = "{{ route('contador.facturas.carrito.pago') }}";
        const TOKEN = '{{ csrf_token() }}';

        // 1. Buscador de Clientes
        $('#selectCliente').select2({
            placeholder: 'Buscar por RUC o Razón Social...',
            minimumInputLength: 3,
            ajax: {
                url: URL_BUSCAR_CLIENTES,
                dataType: 'json',
                delay: 250,
                data: (params) => ({ q: params.term }),
                processResults: (data) => ({
                    results: $.map(data, (cliente) => ({
                        id: cliente.Codclie,
                        text: `${cliente.Razon} (${cliente.Documento})`,
                        data: cliente
                    }))
                })
            }
        });

        // 2. Buscador de Productos
        $('#selectProducto').select2({
            placeholder: 'Buscar producto por nombre o código...',
            minimumInputLength: 3,
            ajax: {
                url: URL_BUSCAR_PRODUCTOS,
                dataType: 'json',
                delay: 250,
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

        $('#selectLote').select2({ placeholder: 'Seleccione un lote' });

        // Al seleccionar un Cliente (Paso 1)
        $('#selectCliente').on('select2:select', function (e) {
            const cliente = e.params.data.data;
            window.location.href = "{{ route('contador.facturas.create') }}?cliente_id=" + cliente.Codclie;
        });

        // Al seleccionar un Producto (Paso 2)
        $('#selectProducto').on('select2:select', function (e) {
            const producto = e.params.data.data;
            $('#itemPrecio').val(parseFloat(producto.Precio).toFixed(2));
            $('#itemCosto').val(parseFloat(producto.Costo).toFixed(2));
            $('#itemUnimed').val(producto.Unimed || 1);
            
            const $selectLote = $('#selectLote');
            $selectLote.empty().prop('disabled', true).append('<option value="">Cargando lotes...</option>');
            $('#stockDisponible').text('');

            $.get(URL_BUSCAR_LOTES.replace('CODPRO', producto.CodPro), function(lotes) {
                $selectLote.empty().prop('disabled', false);
                if (lotes.length > 0) {
                    $selectLote.append('<option value="">Seleccione un lote...</option>');
                    lotes.forEach(lote => {
                        const venc = lote.vencimiento ? new Date(lote.vencimiento).toLocaleDateString('es-PE') : 'N/A';
                        $selectLote.append(
                            `<option value="${lote.lote}" data-stock="${lote.saldo}" data-vencimiento="${lote.vencimiento}">
                                Lote: ${lote.lote} (Stock: ${lote.saldo}) - Vence: ${venc}
                            </option>`
                        );
                    });
                } else {
                    $selectLote.append('<option value="">¡No hay stock para este producto!</option>');
                }
            });
        });

        // Al seleccionar un Lote (Paso 2)
        $('#selectLote').on('change', function() {
            const stock = $(this).find('option:selected').data('stock');
            const vencimiento = $(this).find('option:selected').data('vencimiento');
            $('#stockDisponible').text(`Stock disponible en lote: ${stock}`);
            $('#itemCantidad').val(1);
            $('#itemVencimiento').val(vencimiento);
        });

        // Botón "Añadir Item" (Paso 2)
        $('#btnAgregarItem').on('click', function() {
            const productoSel = $('#selectProducto').select2('data')[0];
            const loteSel = $('#selectLote').find('option:selected');
            
            if (!productoSel || !loteSel.val()) {
                Swal.fire('Error', 'Debe seleccionar un producto y un lote.', 'error');
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
                stock: parseFloat(loteSel.data('stock'))
            };

            if (!item.cantidad || item.cantidad <= 0 || !item.precio) {
                Swal.fire('Error', 'Debe ingresar una cantidad y precio válidos.', 'error');
                return;
            }
            if (item.cantidad > item.stock) {
                Swal.fire('Error', `Stock insuficiente. Solo quedan ${item.stock} unidades en este lote.`, 'error');
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
                    }
                },
                error: (jqXHR) => Swal.fire('Error', jqXHR.responseJSON.message || 'Error al añadir item', 'error')
            });
        });

        // Eliminar item
        $('#tablaItems').on('click', '.btn-eliminar-item', function() {
            const itemId = $(this).data('item-id');
            $.ajax({
                url: URL_CARRITO_DEL.replace('ITEMID', itemId),
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': TOKEN },
                success: (response) => actualizarVistaCarrito(response.carrito)
            });
        });

        // Guardar cambios de pago en sesión
        $('#tipo_doc, #condicion, #fecha_venc, #vendedor_id').on('change', function() {
            $('#campoFechaVenc').toggle($('#condicion').val() === 'credito');
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

        // --- FUNCIONES DE AYUDA ---
        function limpiarFormularioItem() {
            $('#selectProducto').val(null).trigger('change');
            $('#selectLote').empty().prop('disabled', true).append('<option value="">Seleccione un producto...</option>');
            $('#itemCantidad').val('');
            $('#itemPrecio').val('');
            $('#stockDisponible').text('');
        }
        
        function formatCurrency(value) {
            return 'S/ ' + parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        function actualizarVistaCarrito(carrito) {
            if (!carrito) {
                $('#paso2_items, #paso3_pago').hide();
                $('#tablaItems').empty();
                $('#totalSubtotal, #totalIgv, #totalTotal').text('S/ 0.00');
                return;
            }

            $('#paso2_items').show();
            const $tablaItems = $('#tablaItems');
            $tablaItems.empty();
            
            if (carrito.items && Object.keys(carrito.items).length > 0) {
                $.each(carrito.items, function(itemId, item) {
                    const subtotal = item.cantidad * item.precio;
                    $tablaItems.append(`
                        <tr>
                            <td><strong>${item.nombre}</strong><br><small class="text-muted">${item.codpro}</small></td>
                            <td>${item.lote}</td>
                            <td class="text-end">${item.cantidad}</td>
                            <td class="text-end">${formatCurrency(item.precio)}</td>
                            <td class="text-end fw-bold">${formatCurrency(subtotal)}</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-eliminar-item" data-item-id="${itemId}"><i class="fas fa-trash"></i></button></td>
                        </tr>`);
                });
                $('#paso3_pago').show();
            } else {
                $tablaItems.append('<tr><td colspan="6" class="text-center text-muted p-3">El carrito está vacío</td></tr>');
                $('#paso3_pago').hide();
            }

            $('#totalSubtotal').text(formatCurrency(carrito.totales.subtotal));
            $('#totalIgv').text(formatCurrency(carrito.totales.igv));
            $('#totalTotal').text(formatCurrency(carrito.totales.total));
        }
        
        // Cargar carrito si ya existe en la sesión
        @if($carrito)
            actualizarVistaCarrito(@json($carrito));
            $('#tipo_doc').val("{{ $carrito['pago']['tipo_doc'] ?? 1 }}");
            $('#condicion').val("{{ $carrito['pago']['condicion'] ?? 'contado' }}").trigger('change');
            $('#fecha_venc').val("{{ $carrito['pago']['fecha_venc'] ?? '' }}");
            $('#vendedor_id').val("{{ $carrito['pago']['vendedor_id'] ?? '' }}");
        @endif

    });
    </script>
@endpush