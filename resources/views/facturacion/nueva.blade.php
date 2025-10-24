<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Factura - Sistema Farmacéutico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 30px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: #e9ecef;
            z-index: 1;
        }
        .step {
            text-align: center;
            padding: 1rem;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            position: relative;
            z-index: 2;
            border: 3px solid white;
        }
        .step.active {
            background: #007bff;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .cliente-seleccionado {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .producto-agregado {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            border-radius: 5px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .resumen-total {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
        }
        .stock-info {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
        }
        .stock-suficiente { background: #d4edda; color: #155724; }
        .stock-insuficiente { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-file-invoice text-primary"></i> Nueva Factura</h2>
                        <p class="text-muted mb-0">Crear nueva factura con búsqueda automática de cliente</p>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('facturacion.listar') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <hr>
            </div>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="text-center">
                <div class="step active" id="step1">1</div>
                <small>Cliente</small>
            </div>
            <div class="text-center">
                <div class="step" id="step2">2</div>
                <small>Productos</small>
            </div>
            <div class="text-center">
                <div class="step" id="step3">3</div>
                <small>Datos</small>
            </div>
            <div class="text-center">
                <div class="step" id="step4">4</div>
                <small>Confirmar</small>
            </div>
        </div>

        <form id="formFactura" action="{{ route('facturacion.guardar') }}" method="POST">
            @csrf
            
            <!-- Paso 1: Seleccionar Cliente -->
            <div class="form-section" id="paso1">
                <h4><i class="fas fa-user"></i> 1. Seleccionar Cliente</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Buscar Cliente</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="buscarCliente"
                                   placeholder="Buscar por nombre, DNI o razón social">
                            <button type="button" class="btn btn-outline-secondary" onclick="buscarCliente()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-primary w-100" onclick="abrirBusquedaCliente()">
                            <i class="fas fa-search-plus"></i> Búsqueda con RENIEC
                        </button>
                    </div>
                </div>

                <!-- Resultados de búsqueda -->
                <div id="resultadosCliente" class="mt-3" style="display: none;">
                    <h6>Resultados de búsqueda:</h6>
                    <div id="listaClientes" class="list-group"></div>
                </div>

                <!-- Cliente seleccionado -->
                <div id="clienteSeleccionado" class="cliente-seleccionado" style="display: none;">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 id="clienteNombre"></h5>
                            <p class="mb-1"><strong>DNI/RUC:</strong> <span id="clienteDocumento"></span></p>
                            <p class="mb-1"><strong>Dirección:</strong> <span id="clienteDireccion"></span></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-light" onclick="cambiarCliente()">
                                <i class="fas fa-edit"></i> Cambiar
                            </button>
                            <input type="hidden" name="cliente_id" id="clienteId">
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button type="button" class="btn btn-primary" onclick="siguientePaso(2)" id="btnSiguiente1">
                        <i class="fas fa-arrow-right"></i> Siguiente
                    </button>
                </div>
            </div>

            <!-- Paso 2: Agregar Productos -->
            <div class="form-section" id="paso2" style="display: none;">
                <h4><i class="fas fa-shopping-cart"></i> 2. Agregar Productos</h4>
                
                <div class="row">
                    <div class="col-md-8">
                        <label class="form-label">Buscar Producto</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="buscarProducto"
                                   placeholder="Buscar por nombre o código de barras">
                            <button type="button" class="btn btn-outline-secondary" onclick="buscarProducto()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-info w-100" onclick="limpiarProductos()">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                    </div>
                </div>

                <!-- Resultados de productos -->
                <div id="resultadosProducto" class="mt-3" style="display: none;">
                    <h6>Resultados de búsqueda:</h6>
                    <div id="listaProductos" class="list-group"></div>
                </div>

                <!-- Productos agregados -->
                <div id="productosAgregados" class="mt-3">
                    <h6>Productos en la Factura:</h6>
                    <div id="listaProductosAgregados"></div>
                </div>

                <div class="text-center mt-3">
                    <button type="button" class="btn btn-secondary" onclick="anteriorPaso(1)">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-primary" onclick="siguientePaso(3)" id="btnSiguiente2">
                        <i class="fas fa-arrow-right"></i> Siguiente
                    </button>
                </div>
            </div>

            <!-- Paso 3: Datos de la Factura -->
            <div class="form-section" id="paso3" style="display: none;">
                <h4><i class="fas fa-edit"></i> 3. Datos de la Factura</h4>
                
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-hashtag text-primary"></i> Serie
                        </label>
                        <select name="serie" class="form-select" required>
                            <option value="">Seleccionar serie</option>
                            @foreach($series as $serie)
                            <option value="{{ $serie->Serie }}">{{ $serie->Serie }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-list-ol text-success"></i> Número
                        </label>
                        <input type="number" name="numero" class="form-control" required min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-calendar text-info"></i> Forma de Pago
                        </label>
                        <select name="forma_pago" class="form-select" required>
                            <option value="">Seleccionar</option>
                            @foreach($formasPago as $forma)
                            <option value="{{ $forma->CodFor }}">{{ $forma->NomFor }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-calendar text-warning"></i> Fecha de Emisión
                        </label>
                        <input type="date" name="fecha_emision" class="form-control" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-calendar-check text-danger"></i> Fecha de Vencimiento
                        </label>
                        <input type="date" name="fecha_vencimiento" class="form-control" 
                               value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-tag text-secondary"></i> Descuento Global (%)
                        </label>
                        <input type="number" name="descuento_global" class="form-control" 
                               value="0" min="0" max="100" step="0.1" id="descuentoGlobal">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-percentage text-primary"></i> Los precios incluyen IGV
                        </label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="incluye_igv" class="form-check-input" id="incluyeIgv" checked>
                            <label class="form-check-label" for="incluyeIgv">
                                Sí, los precios incluyen IGV
                            </label>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button type="button" class="btn btn-secondary" onclick="anteriorPaso(2)">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-primary" onclick="calcularTotales()" id="btnCalcular">
                        <i class="fas fa-calculator"></i> Calcular Totales
                    </button>
                </div>
            </div>

            <!-- Paso 4: Confirmar y Guardar -->
            <div class="form-section" id="paso4" style="display: none;">
                <h4><i class="fas fa-check-circle"></i> 4. Confirmar Factura</h4>
                
                <!-- Resumen de la factura -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-user"></i> Cliente</h6>
                            </div>
                            <div class="card-body">
                                <div id="resumenCliente"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="resumen-total">
                            <h3>Total a Cobrar</h3>
                            <h2 id="totalFactura">S/ 0.00</h2>
                            <div id="detalleTotales" class="mt-3">
                                <p>Base: S/ 0.00</p>
                                <p>IGV: S/ 0.00</p>
                                <p>Descuento: S/ 0.00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button type="button" class="btn btn-secondary" onclick="anteriorPaso(3)">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="submit" class="btn btn-success" id="btnGuardar" disabled>
                        <i class="fas fa-save"></i> Guardar Factura
                    </button>
                </div>
            </div>

            <!-- Campos ocultos para datos calculados -->
            <input type="hidden" name="productos" id="productosData">
            <input type="hidden" name="total" id="totalInput">
            <input type="hidden" name="base_imponible" id="baseInput">
            <input type="hidden" name="igv" id="igvInput">
            <input type="hidden" name="descuento_total" id="descuentoInput">
        </form>
    </div>

    <!-- Modal de búsqueda RENIEC -->
    @include('contabilidad.modals.busqueda-cliente-dni')

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let clienteData = null;
        let productos = [];

        function buscarCliente() {
            const termino = $('#buscarCliente').val();
            if (termino.length < 2) {
                alert('Ingrese al menos 2 caracteres');
                return;
            }

            $.ajax({
                url: '/contabilidad/facturacion/buscar-cliente',
                method: 'POST',
                data: {
                    termino: termino,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success && response.clientes.length > 0) {
                        mostrarResultadosCliente(response.clientes);
                    } else {
                        alert('No se encontraron clientes');
                    }
                },
                error: function() {
                    alert('Error en la búsqueda');
                }
            });
        }

        function mostrarResultadosCliente(clientes) {
            const lista = $('#listaClientes');
            lista.empty();
            
            clientes.forEach(function(cliente) {
                const item = $(`
                    <a href="#" class="list-group-item list-group-item-action" onclick='seleccionarCliente(${JSON.stringify(cliente).replace(/"/g, '&quot;')})'>
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${cliente.Razon}</h6>
                            <small>${cliente.RucDni || 'Sin documento'}</small>
                        </div>
                        <small>${cliente.Direccion || 'Sin dirección'}</small>
                    </a>
                `);
                lista.append(item);
            });
            
            $('#resultadosCliente').show();
        }

        function seleccionarCliente(cliente) {
            clienteData = cliente;
            
            $('#clienteNombre').text(cliente.Razon);
            $('#clienteDocumento').text(cliente.RucDni || 'Sin documento');
            $('#clienteDireccion').text(cliente.Direccion || 'Sin dirección');
            $('#clienteId').val(cliente.Codclie);
            
            $('#clienteSeleccionado').show();
            $('#resultadosCliente').hide();
            $('#buscarCliente').val('');
        }

        function abrirBusquedaCliente() {
            abrirBusquedaCliente(function(cliente) {
                clienteData = cliente;
                $('#clienteNombre').text(cliente.Razon);
                $('#clienteDocumento').text(cliente.RucDni || 'Sin documento');
                $('#clienteDireccion').text(cliente.Direccion || 'Sin dirección');
                $('#clienteId').val(cliente.Codclie);
                $('#clienteSeleccionado').show();
            });
        }

        function cambiarCliente() {
            clienteData = null;
            $('#clienteSeleccionado').hide();
            $('#clienteId').val('');
        }

        function buscarProducto() {
            const termino = $('#buscarProducto').val();
            if (termino.length < 2) {
                alert('Ingrese al menos 2 caracteres');
                return;
            }

            $.ajax({
                url: '/contabilidad/facturacion/buscar-productos',
                method: 'POST',
                data: {
                    termino: termino,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success && response.productos.length > 0) {
                        mostrarResultadosProducto(response.productos);
                    } else {
                        alert('No se encontraron productos');
                    }
                },
                error: function() {
                    alert('Error en la búsqueda');
                }
            });
        }

        function mostrarResultadosProducto(productos) {
            const lista = $('#listaProductos');
            lista.empty();
            
            productos.forEach(function(producto) {
                const item = $(`
                    <a href="#" class="list-group-item list-group-item-action" onclick='agregarProducto(${JSON.stringify(producto).replace(/"/g, '&quot;')})'>
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${producto.NomPro}</h6>
                            <small>S/ ${parseFloat(producto.precio_venta).toFixed(2)}</small>
                        </div>
                        <small>Código: ${producto.CodPro} | Stock: ${producto.Cantidad} ${producto.Unidad}</small>
                        ${producto.Cantidad <= 5 ? '<span class="badge bg-warning">Stock bajo</span>' : ''}
                    </a>
                `);
                lista.append(item);
            });
            
            $('#resultadosProducto').show();
        }

        function agregarProducto(producto) {
            // Verificar si ya está agregado
            if (productos.find(p => p.codigo === producto.CodPro && p.lote === producto.Lote)) {
                alert('Este producto ya está agregado');
                return;
            }

            const productoFormateado = {
                codigo: producto.CodPro,
                nombre: producto.NomPro,
                lote: producto.Lote,
                precio: parseFloat(producto.precio_venta),
                stock: producto.Cantidad,
                unidad: producto.Unidad,
                cantidad: 1,
                descuento: 0
            };

            productos.push(productoFormateado);
            actualizarListaProductos();
            $('#resultadosProducto').hide();
            $('#buscarProducto').val('');
        }

        function actualizarListaProductos() {
            const lista = $('#listaProductosAgregados');
            lista.empty();

            productos.forEach(function(producto, index) {
                const item = $(`
                    <div class="producto-agregado">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <strong>${producto.nombre}</strong><br>
                                <small class="text-muted">Código: ${producto.codigo}</small>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted">Lote:</small><br>
                                <small>${producto.lote}</small>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted">Precio:</small><br>
                                <input type="number" class="form-control form-control-sm" 
                                       value="${producto.precio}" step="0.01" min="0.01"
                                       onchange="actualizarProducto(${index}, 'precio', this.value)">
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted">Cantidad:</small><br>
                                <input type="number" class="form-control form-control-sm" 
                                       value="${producto.cantidad}" min="1" max="${producto.stock}"
                                       onchange="actualizarProducto(${index}, 'cantidad', this.value)">
                                <small class="text-muted">Stock: ${producto.stock}</small>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted">Descuento %:</small><br>
                                <input type="number" class="form-control form-control-sm" 
                                       value="${producto.descuento}" min="0" max="100" step="0.1"
                                       onchange="actualizarProducto(${index}, 'descuento', this.value)">
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="eliminarProducto(${index})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `);
                lista.append(item);
            });

            $('#productosData').val(JSON.stringify(productos));
        }

        function actualizarProducto(index, campo, valor) {
            if (campo === 'precio' || campo === 'cantidad') {
                valor = parseFloat(valor);
            }
            productos[index][campo] = valor;
            $('#productosData').val(JSON.stringify(productos));
        }

        function eliminarProducto(index) {
            productos.splice(index, 1);
            actualizarListaProductos();
        }

        function limpiarProductos() {
            productos = [];
            actualizarListaProductos();
        }

        function calcularTotales() {
            if (productos.length === 0) {
                alert('Debe agregar al menos un producto');
                return;
            }

            const descuentoGlobal = parseFloat($('#descuentoGlobal').val()) || 0;
            const incluyeIgv = $('#incluyeIgv').is(':checked');

            $.ajax({
                url: '/contabilidad/facturacion/calcular-totales',
                method: 'POST',
                data: {
                    productos: JSON.stringify(productos),
                    descuento_global: descuentoGlobal,
                    incluye_igv: incluyeIgv.toString(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        mostrarTotales(response);
                        siguientePaso(4);
                    } else {
                        alert('Error: ' + response.error);
                    }
                },
                error: function() {
                    alert('Error calculando totales');
                }
            });
        }

        function mostrarTotales(datos) {
            $('#totalFactura').text(`S/ ${datos.total.toFixed(2)}`);
            $('#detalleTotales').html(`
                <p>Base: S/ ${datos.base_imponible.toFixed(2)}</p>
                <p>IGV: S/ ${datos.igv.toFixed(2)}</p>
                <p>Descuento: S/ ${datos.descuento_total.toFixed(2)}</p>
            `);
            
            $('#totalInput').val(datos.total);
            $('#baseInput').val(datos.base_imponible);
            $('#igvInput').val(datos.igv);
            $('#descuentoInput').val(datos.descuento_total);
            
            $('#btnGuardar').prop('disabled', false);
        }

        function siguientePaso(paso) {
            if (paso === 2 && !clienteData) {
                alert('Debe seleccionar un cliente primero');
                return;
            }
            
            if (paso === 3 && productos.length === 0) {
                alert('Debe agregar al menos un producto');
                return;
            }
            
            if (paso === 4) {
                generarResumen();
            }
            
            document.getElementById('paso' + (paso - 1)).style.display = 'none';
            document.getElementById('paso' + paso).style.display = 'block';
            actualizarIndicadorPasos(paso);
        }

        function anteriorPaso(paso) {
            document.getElementById('paso' + (paso + 1)).style.display = 'none';
            document.getElementById('paso' + paso).style.display = 'block';
            actualizarIndicadorPasos(paso);
        }

        function actualizarIndicadorPasos(pasoActual) {
            for (let i = 1; i <= 4; i++) {
                const step = document.getElementById('step' + i);
                step.classList.remove('active', 'completed');
                
                if (i < pasoActual) {
                    step.classList.add('completed');
                } else if (i === pasoActual) {
                    step.classList.add('active');
                }
            }
        }

        function generarResumen() {
            if (clienteData) {
                $('#resumenCliente').html(`
                    <strong>${clienteData.Razon}</strong><br>
                    ${clienteData.RucDni || 'Sin documento'}<br>
                    ${clienteData.Direccion || 'Sin dirección'}
                `);
            }
        }

        // Manejar envío del formulario
        $('#formFactura').on('submit', function(e) {
            if (productos.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un producto');
                return;
            }
            
            if (!clienteData) {
                e.preventDefault();
                alert('Debe seleccionar un cliente');
                return;
            }
        });
    </script>
</body>
</html>