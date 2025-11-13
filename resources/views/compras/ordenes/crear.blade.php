@extends('layouts.app')

@section('title', 'Nueva Orden de Compra')
@section('page-title', 'Nueva Orden de Compra')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.compras.index') }}">Compras</a></li>
    <li class="breadcrumb-item active" aria-current="page">Nueva O/C</li>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single { height: 38px; border: 1px solid #ced4da; padding: 6px 12px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
        .select2-container { width: 100% !important; }
        /* * ¡CORRECCIÓN!
         * Hemos eliminado la línea ".form-section { display: none; }"
         * para evitar que el CSS oculte lo que el PHP ya hizo visible.
         */
    </style>
@endpush

@section('content')

<div class="row">
    <div class="col-lg-10 mx-auto">
        <form action="{{ route('contador.compras.store') }}" method="POST" id="formCompraFinal">
            @csrf

            <div class="card shadow mb-4" id="paso1_proveedor">
                <div class="card-header">
                    <h5 class="card-title m-0"><i class="fas fa-truck me-2"></i>Paso 1: Seleccionar Proveedor</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Busque el proveedor al que le realizará la compra.</p>
                    <select id="selectProveedor" class="form-control" name="proveedor_id_selector">
                        <option value="">Buscar por RUC o Razón Social...</option>
                    </select>
                    
                    <div id="resumenProveedor" class="mt-3 alert alert-info" 
                         style="{{ $carrito && $carrito['proveedor'] ? '' : 'display: none;' }}">
                        <strong>Proveedor:</strong> <span id="provNombre">{{ $carrito['proveedor']->RazonSocial ?? '' }}</span><br>
                        <strong>RUC:</strong> <span id="provDocumento">{{ $carrito['proveedor']->Ruc ?? '' }}</span><br>
                    </div>
                </div>
            </div>

            {{-- 
              Ahora, la visibilidad inicial de este 'div' 
              depende ÚNICAMENTE del PHP, eliminando el parpadeo.
            --}}
            <div class="card shadow mb-4 form-section" id="paso2_items"
                 style="{{ $carrito ? '' : 'display: none;' }}">
                <div class="card-header">
                    <h5 class="card-title m-0"><i class="fas fa-boxes me-2"></i>Paso 2: Detalle de la Compra</h5>
                </div>
                <div class="card-body">
                    <div class="card bg-light border p-3 mb-3">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label">1. Buscar Producto (¡Aquí vemos el Laboratorio!)</label>
                                <select id="selectProducto" class="form-control"></select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">2. Cantidad</label>
                                <input type="number" class="form-control" id="itemCantidad" placeholder="0" step="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">3. Costo Unit. (S/)</label>
                                <input type="number" class="form-control" id="itemCosto" placeholder="0.00" step="0.01">
                            </div>
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-success" id="btnAgregarItem">
                                    <i class="fas fa-plus"></i> Añadir Producto
                                </button>
                            </div>
                            <input type="hidden" id="itemNombre">
                            <input type="hidden" id="itemLaboratorio">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Laboratorio</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Costo Unit.</th>
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
                                    <td colspan="4" class="text-end fw-bolder">TOTAL O/C:</td>
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
                    <h5 class="card-title m-0"><i class="fas fa-calendar-alt me-2"></i>Paso 3: Condiciones de Entrega</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="fecha_entrega" class="form-label fw-bold">Fecha de Entrega Estimada</label>
                            <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega"
                                   value="{{ $carrito['pago']['fecha_entrega'] ?? now()->addDays(3)->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="moneda" class="form-label fw-bold">Moneda</label>
                            <select class="form-select" id="moneda" name="moneda">
                                <option value="1" {{ ($carrito['pago']['moneda'] ?? 1) == 1 ? 'selected' : '' }}>Soles (PEN)</option>
                                <option value="2" {{ ($carrito['pago']['moneda'] ?? 1) == 2 ? 'selected' : '' }}>Dólares (USD)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-1"></i> Guardar Orden de Compra
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
        
        // URLs de las API
        const URL_BUSCAR_PROVEEDORES = "{{ route('contador.compras.api.buscarProveedores') }}";
        // Reutilizamos la API de productos de Ventas
        const URL_BUSCAR_PRODUCTOS = "{{ route('contador.facturas.api.buscarProductos') }}"; 
        
        const URL_CARRITO_ADD = "{{ route('contador.compras.carrito.agregar') }}";
        const URL_CARRITO_DEL = "{{ route('contador.compras.carrito.eliminar', ['itemId' => 'ITEMID']) }}";
        const URL_CARRITO_PAGO = "{{ route('contador.compras.carrito.pago') }}";
        const TOKEN = '{{ csrf_token() }}';

        // 1. Buscador de Proveedores
        $('#selectProveedor').select2({
            placeholder: 'Buscar por RUC o Razón Social...',
            minimumInputLength: 3,
            ajax: {
                url: URL_BUSCAR_PROVEEDORES,
                dataType: 'json',
                delay: 250,
                data: (params) => ({ q: params.term }),
                processResults: (data) => ({
                    results: $.map(data, (prov) => ({
                        id: prov.CodProv,
                        text: `${prov.RazonSocial} (${prov.Ruc})`,
                        data: prov
                    }))
                })
            }
        });

        // 2. Buscador de Productos (¡Aquí mostramos el Laboratorio!)
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
                        // ¡AQUÍ ESTÁ LA LÓGICA! Mostramos el Laboratorio
                        text: `${prod.Nombre} (Lab: ${prod.Laboratorio || 'N/A'})`,
                        data: prod
                    }))
                })
            }
        });

        // Al seleccionar un Proveedor (Paso 1)
        $('#selectProveedor').on('select2:select', function (e) {
            const proveedor = e.params.data.data;
            // Esta recarga de página es correcta porque el controlador
            // optimizado ya sabe cómo manejarla.
            window.location.href = "{{ route('contador.compras.create') }}?proveedor_id=" + proveedor.CodProv;
        });

        // Al seleccionar un Producto (Paso 2)
        $('#selectProducto').on('select2:select', function (e) {
            const producto = e.params.data.data;
            $('#itemCosto').val(parseFloat(producto.Costo).toFixed(2));
            $('#itemNombre').val(producto.Nombre);
            $('#itemLaboratorio').val(producto.Laboratorio || 'N/A');
            $('#itemCantidad').val(1).focus();
        });

        // Botón "Añadir Item" (Paso 2)
        $('#btnAgregarItem').on('click', function() {
            const productoSel = $('#selectProducto').select2('data')[0];
            
            if (!productoSel) {
                Swal.fire('Error', 'Debe seleccionar un producto.', 'error');
                return;
            }

            const item = {
                codpro: productoSel.id,
                nombre: $('#itemNombre').val(),
                laboratorio: $('#itemLaboratorio').val(),
                cantidad: parseFloat($('#itemCantidad').val()),
                costo: parseFloat($('#itemCosto').val()),
            };

            if (!item.cantidad || item.cantidad <= 0 || !item.costo) {
                Swal.fire('Error', 'Debe ingresar una cantidad y costo válidos.', 'error');
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
        $('#fecha_entrega, #moneda').on('change', function() {
            $.ajax({
                url: URL_CARRITO_PAGO,
                type: 'POST',
                data: JSON.stringify({
                    fecha_entrega: $('#fecha_entrega').val(),
                    moneda: $('#moneda').val(),
                }),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': TOKEN }
            });
        });

        // --- FUNCIONES DE AYUDA ---
        function limpiarFormularioItem() {
            $('#selectProducto').val(null).trigger('change');
            $('#itemCantidad').val('');
            $('#itemCosto').val('');
        }
        
        function formatCurrency(value) {
            return 'S/ ' + parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        function actualizarVistaCarrito(carrito) {
            if (!carrito) {
                $('#paso2_items, #paso3_pago').hide();
                return;
            }

            // Asegura que los pasos correctos sean visibles
            $('#paso2_items').show();
            
            const $tablaItems = $('#tablaItems');
            $tablaItems.empty();
            
            if (carrito.items && Object.keys(carrito.items).length > 0) {
                $.each(carrito.items, function(itemId, item) {
                    const subtotal = item.cantidad * item.costo;
                    $tablaItems.append(`
                        <tr>
                            <td><strong>${item.nombre}</strong><br><small class="text-muted">${item.codpro}</small></td>
                            <td>${item.laboratorio}</td>
                            <td class="text-end">${item.cantidad}</td>
                            <td class="text-end">${formatCurrency(item.costo)}</td>
                            <td class="text-end fw-bold">${formatCurrency(subtotal)}</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-eliminar-item" data-item-id="${itemId}"><i class="fas fa-trash"></i></button></td>
                        </tr>`);
                });
                $('#paso3_pago').show(); // Muestra el paso 3 si hay items
            } else {
                $tablaItems.append('<tr><td colspan="6" class="text-center text-muted p-3">El carrito está vacío</td></tr>');
                $('#paso3_pago').hide(); // Oculta el paso 3 si no hay items
            }

            $('#totalSubtotal').text(formatCurrency(carrito.totales.subtotal));
            $('#totalIgv').text(formatCurrency(carrito.totales.igv));
            $('#totalTotal').text(formatCurrency(carrito.totales.total));
        }
        
        // Cargar carrito si ya existe en la sesión
        @if($carrito)
            actualizarVistaCarrito(@json($carrito));
            // Aseguramos que los valores del formulario de pago se carguen
            $('#fecha_entrega').val("{{ $carrito['pago']['fecha_entrega'] ?? now()->addDays(3)->format('Y-m-d') }}");
            $('#moneda').val("{{ $carrito['pago']['moneda'] ?? 1 }}");
        @endif

    });
    </script>
@endpush