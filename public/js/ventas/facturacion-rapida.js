// Sistema de Facturación Rápida - Ventas SIFANO
// Facturación rápida y eficiente para farmacia

class SistemaFacturacion {
    constructor() {
        this.productos = [];
        this.clientes = [];
        this.vendedor = {};
        this.itemsVenta = [];
        this.medicamentosVenta = [];
        this.descuentos = [];
        this.formasPago = [];
        this.configuracion = {};
        this.init();
    }

    init() {
        this.cargarDatosDemo();
        this.inicializarEventos();
        this.configurarFormulario();
        this.actualizarTotales();
        this.validarMedicamentos();
    }

    cargarDatosDemo() {
        // Productos farmacéuticos
        this.productos = [
            {
                id: 1,
                codigo: 'MED001',
                nombre: 'Paracetamol 500mg',
                precio: 0.35,
                stock: 1500,
                requiereReceta: false,
                descuento: 0,
                categoria: 'Analgésicos',
                activo: true
            },
            {
                id: 2,
                codigo: 'MED002',
                nombre: 'Amoxicilina 250mg',
                precio: 0.80,
                stock: 45,
                requiereReceta: true,
                descuento: 0,
                categoria: 'Antibióticos',
                activo: true
            },
            {
                id: 3,
                codigo: 'MED003',
                nombre: 'Ibuprofeno 400mg',
                precio: 0.50,
                stock: 800,
                requiereReceta: false,
                descuento: 0,
                categoria: 'Antiinflamatorios',
                activo: true
            },
            {
                id: 4,
                codigo: 'MED004',
                nombre: 'Omeprazol 20mg',
                precio: 1.20,
                stock: 15,
                requiereReceta: false,
                descuento: 0,
                categoria: 'Gastrointestinales',
                activo: true
            },
            {
                id: 5,
                codigo: 'MED005',
                nombre: 'Loratadina 10mg',
                precio: 0.45,
                stock: 200,
                requiereReceta: false,
                descuento: 0,
                categoria: 'Antihistamínicos',
                activo: true
            }
        ];

        // Clientes frecuentes
        this.clientes = [
            {
                id: 1,
                nombre: 'Juan Pérez',
                dni: '12345678',
                telefono: '+51-987-654-321',
                email: 'juan.perez@email.com',
                direccion: 'Av. Principal 123',
                descuentos: [10], // 10% por cliente frecuente
                tipo: 'frecuente'
            },
            {
                id: 2,
                nombre: 'María García',
                dni: '87654321',
                telefono: '+51-912-345-678',
                email: 'maria.garcia@email.com',
                direccion: 'Jr. Salud 456',
                descuentos: [5], // 5% por cliente frecuente
                tipo: 'frecuente'
            }
        ];

        // Descuentos disponibles
        this.descuentos = [
            { id: 1, nombre: 'Cliente Frecuente', porcentaje: 5, aplicacion: 'items' },
            { id: 2, nombre: 'Cliente VIP', porcentaje: 10, aplicacion: 'total' },
            { id: 3, nombre: 'Promoción Especial', porcentaje: 15, aplicacion: 'items' },
            { id: 4, nombre: 'Descuento por Cantidad', porcentaje: 20, aplicacion: 'items' }
        ];

        // Formas de pago
        this.formasPago = [
            { id: 1, nombre: 'Efectivo', comision: 0 },
            { id: 2, nombre: 'Tarjeta de Débito', comision: 0.02 },
            { id: 3, nombre: 'Tarjeta de Crédito', comision: 0.035 },
            { id: 4, nombre: 'Transferencia', comision: 0.01 },
            { id: 5, nombre: 'Yape', comision: 0.015 },
            { id: 6, nombre: 'Plin', comision: 0.015 }
        ];

        // Configuración de la farmacia
        this.configuracion = {
            nombre: 'Farmacia SIFANO',
            ruc: '20123456789',
            direccion: 'Av. Salud 789, Lima',
            telefono: '+51-1-234-5678',
            turno: 'Mañana',
            vendedor: 'Dra. Lopez',
            montoMinimoFacturacion: 5.00,
            requiereDni: false,
            moneda: 'PEN'
        };

        // Vendedor actual
        this.vendedor = {
            id: 1,
            nombre: 'Dra. Lopez',
            turno: 'Mañana',
            ventasHoy: 25,
            montoVendido: 1250.50
        };
    }

    inicializarEventos() {
        // Búsqueda de productos
        const inputProducto = document.getElementById('buscar-producto-venta');
        if (inputProducto) {
            inputProducto.addEventListener('input', (e) => {
                this.buscarProductos(e.target.value);
            });

            inputProducto.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.agregarProductoSeleccionado();
                }
            });
        }

        // Búsqueda de clientes
        const inputCliente = document.getElementById('buscar-cliente-venta');
        if (inputCliente) {
            inputCliente.addEventListener('input', (e) => {
                this.buscarClientes(e.target.value);
            });
        }

        // Selector de forma de pago
        const selectPago = document.getElementById('forma-pago');
        if (selectPago) {
            selectPago.addEventListener('change', () => {
                this.actualizarTotales();
            });
        }

        // Botones de acción
        document.getElementById('btn-buscar-producto')?.addEventListener('click', () => {
            this.abrirBuscadorProductos();
        });

        document.getElementById('btn-buscar-cliente')?.addEventListener('click', () => {
            this.abrirBuscadorClientes();
        });

        document.getElementById('btn-procesar-venta')?.addEventListener('click', () => {
            this.procesarVenta();
        });

        document.getElementById('btn-limpiar-venta')?.addEventListener('click', () => {
            this.limpiarVenta();
        });

        document.getElementById('btn-guardar-borrador')?.addEventListener('click', () => {
            this.guardarBorrador();
        });

        document.getElementById('btn-aplicar-descuento')?.addEventListener('click', () => {
            this.aplicarDescuento();
        });

        // Validar receta para medicamentos
        document.getElementById('validar-receta')?.addEventListener('change', (e) => {
            this.toggleValidacionReceta(e.target.checked);
        });
    }

    configurarFormulario() {
        // Llenar forma de pago
        const selectPago = document.getElementById('forma-pago');
        if (selectPago) {
            selectPago.innerHTML = '<option value="">Seleccionar forma de pago</option>' +
                this.formasPago.map(fp => `<option value="${fp.id}">${fp.nombre} (${fp.comision * 100}% comisión)</option>`).join('');
        }

        // Llenar descuentos
        const selectDescuento = document.getElementById('tipo-descuento');
        if (selectDescuento) {
            selectDescuento.innerHTML = '<option value="">Sin descuento</option>' +
                this.descuentos.map(d => `<option value="${d.id}">${d.nombre} (${d.porcentaje}%)</option>`).join('');
        }
    }

    buscarProductos(termino) {
        const resultados = this.productos.filter(producto => 
            producto.activo &&
            (producto.nombre.toLowerCase().includes(termino.toLowerCase()) ||
             producto.codigo.toLowerCase().includes(termino.toLowerCase()) ||
             producto.categoria.toLowerCase().includes(termino.toLowerCase()))
        );

        this.mostrarResultadosProductos(resultados);
    }

    mostrarResultadosProductos(productos) {
        const container = document.getElementById('resultados-productos');
        if (!container) return;

        if (productos.length === 0) {
            container.innerHTML = '<div class="text-muted">No se encontraron productos</div>';
            return;
        }

        container.innerHTML = productos.map(producto => `
            <div class="producto-item ${!producto.activo ? 'producto-inactivo' : ''} ${producto.requiereReceta ? 'producto-receta' : ''}" 
                 onclick="sistemaFacturacion.seleccionarProducto(${producto.id})">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${producto.nombre}</strong><br>
                        <small class="text-muted">${producto.codigo} - ${producto.categoria}</small><br>
                        ${producto.requiereReceta ? '<span class="badge bg-warning"><i class="fas fa-prescription-bottle-alt"></i> Receta</span>' : ''}
                    </div>
                    <div class="text-end">
                        <div class="h5 mb-0">S/ ${producto.precio.toFixed(2)}</div>
                        <small class="text-muted">Stock: ${producto.stock}</small>
                    </div>
                </div>
            </div>
        `).join('');
    }

    buscarClientes(termino) {
        const resultados = this.clientes.filter(cliente => 
            cliente.nombre.toLowerCase().includes(termino.toLowerCase()) ||
            cliente.dni.includes(termino)
        );

        this.mostrarResultadosClientes(resultados);
    }

    mostrarResultadosClientes(clientes) {
        const container = document.getElementById('resultados-clientes');
        if (!container) return;

        if (clientes.length === 0) {
            container.innerHTML = '<div class="text-muted">No se encontraron clientes</div>';
            return;
        }

        container.innerHTML = clientes.map(cliente => `
            <div class="cliente-item" onclick="sistemaFacturacion.seleccionarCliente(${cliente.id})">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${cliente.nombre}</strong><br>
                        <small class="text-muted">DNI: ${cliente.dni}</small><br>
                        <small class="text-muted">${cliente.telefono}</small>
                    </div>
                    <div>
                        ${cliente.descuentos.length > 0 ? `<span class="badge bg-success">${cliente.descuentos[0]}% descuento</span>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
    }

    seleccionarProducto(productoId) {
        const producto = this.productos.find(p => p.id === productoId);
        if (!producto) return;

        // Verificar si requiere receta
        if (producto.requiereReceta) {
            const validarReceta = document.getElementById('validar-receta');
            if (validarReceta && !validarReceta.checked) {
                Swal.fire({
                    title: 'Receta Requerida',
                    text: 'Este medicamento requiere receta médica válida.',
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
                return;
            }
        }

        // Verificar stock
        if (producto.stock <= 0) {
            Swal.fire({
                title: 'Sin Stock',
                text: 'Este producto no tiene stock disponible.',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Agregar a la venta
        this.agregarItemVenta(producto);
        
        // Limpiar búsqueda
        document.getElementById('buscar-producto-venta').value = '';
        document.getElementById('resultados-productos').innerHTML = '';
        
        // Focus en cantidad
        setTimeout(() => {
            document.getElementById(`cantidad-${productoId}`)?.focus();
        }, 100);
    }

    agregarItemVenta(producto) {
        // Verificar si ya existe en la venta
        const itemExistente = this.itemsVenta.find(item => item.producto.id === producto.id);
        
        if (itemExistente) {
            if (itemExistente.cantidad + 1 > producto.stock) {
                Swal.fire({
                    title: 'Stock Insuficiente',
                    text: `No hay suficiente stock. Máximo disponible: ${producto.stock}`,
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
                return;
            }
            itemExistente.cantidad += 1;
        } else {
            const nuevoItem = {
                id: Date.now(),
                producto: producto,
                cantidad: 1,
                precioUnitario: producto.precio,
                descuento: 0,
                subtotal: producto.precio,
                descuentoAplicado: 0,
                total: producto.precio
            };
            this.itemsVenta.push(nuevoItem);
        }
        
        this.actualizarTablaVenta();
        this.actualizarTotales();
        this.validarMedicamentos();
    }

    actualizarTablaVenta() {
        const tbody = document.querySelector('#tabla-items-venta tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        this.itemsVenta.forEach(item => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>
                    <strong>${item.producto.nombre}</strong><br>
                    <small class="text-muted">${item.producto.codigo}</small>
                    ${item.producto.requiereReceta ? '<br><span class="badge bg-warning"><i class="fas fa-prescription-bottle-alt"></i> Receta</span>' : ''}
                </td>
                <td>
                    <div class="input-group">
                        <input type="number" class="form-control form-control-sm text-center" 
                               id="cantidad-${item.producto.id}"
                               value="${item.cantidad}" 
                               min="1" max="${item.producto.stock}"
                               onchange="sistemaFacturacion.actualizarCantidad(${item.producto.id}, this.value)">
                        <span class="input-group-text">/ ${item.producto.stock}</span>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">S/</span>
                        <input type="number" class="form-control" 
                               value="${item.precioUnitario.toFixed(2)}"
                               step="0.01"
                               onchange="sistemaFacturacion.actualizarPrecio(${item.producto.id}, this.value)">
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control" 
                               value="${item.descuento}"
                               min="0" max="100"
                               onchange="sistemaFacturacion.actualizarDescuento(${item.producto.id}, this.value)">
                        <span class="input-group-text">%</span>
                    </div>
                </td>
                <td><strong>S/ ${item.subtotal.toFixed(2)}</strong></td>
                <td><strong>S/ ${item.descuentoAplicado.toFixed(2)}</strong></td>
                <td><strong>S/ ${item.total.toFixed(2)}</strong></td>
                <td>
                    <button class="btn btn-sm btn-outline-danger" onclick="sistemaFacturacion.eliminarItem(${item.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(fila);
        });
    }

    actualizarCantidad(productoId, nuevaCantidad) {
        const item = this.itemsVenta.find(item => item.producto.id === productoId);
        if (!item) return;

        const cantidad = parseInt(nuevaCantidad);
        if (cantidad < 1 || cantidad > item.producto.stock) {
            Swal.fire({
                title: 'Cantidad Inválida',
                text: `La cantidad debe estar entre 1 y ${item.producto.stock}`,
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            document.getElementById(`cantidad-${productoId}`).value = item.cantidad;
            return;
        }

        item.cantidad = cantidad;
        this.calcularTotalesItem(item);
        this.actualizarTablaVenta();
        this.actualizarTotales();
    }

    actualizarPrecio(productoId, nuevoPrecio) {
        const item = this.itemsVenta.find(item => item.producto.id === productoId);
        if (!item) return;

        const precio = parseFloat(nuevoPrecio);
        if (precio < 0) {
            Swal.fire({
                title: 'Precio Inválido',
                text: 'El precio no puede ser negativo',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            document.getElementById(`precio-${productoId}`).value = item.precioUnitario.toFixed(2);
            return;
        }

        item.precioUnitario = precio;
        this.calcularTotalesItem(item);
        this.actualizarTablaVenta();
        this.actualizarTotales();
    }

    actualizarDescuento(productoId, nuevoDescuento) {
        const item = this.itemsVenta.find(item => item.producto.id === productoId);
        if (!item) return;

        const descuento = parseFloat(nuevoDescuento);
        if (descuento < 0 || descuento > 100) {
            Swal.fire({
                title: 'Descuento Inválido',
                text: 'El descuento debe estar entre 0% y 100%',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            document.getElementById(`descuento-${productoId}`).value = item.descuento;
            return;
        }

        item.descuento = descuento;
        this.calcularTotalesItem(item);
        this.actualizarTablaVenta();
        this.actualizarTotales();
    }

    calcularTotalesItem(item) {
        item.subtotal = item.cantidad * item.precioUnitario;
        item.descuentoAplicado = item.subtotal * (item.descuento / 100);
        item.total = item.subtotal - item.descuentoAplicado;
    }

    eliminarItem(itemId) {
        this.itemsVenta = this.itemsVenta.filter(item => item.id !== itemId);
        this.actualizarTablaVenta();
        this.actualizarTotales();
        this.validarMedicamentos();
    }

    seleccionarCliente(clienteId) {
        const cliente = this.clientes.find(c => c.id === clienteId);
        if (!cliente) return;

        // Mostrar información del cliente
        document.getElementById('cliente-seleccionado').innerHTML = `
            <div class="alert alert-info">
                <strong>Cliente:</strong> ${cliente.nombre}<br>
                <strong>DNI:</strong> ${cliente.dni}<br>
                <strong>Teléfono:</strong> ${cliente.telefono}<br>
                ${cliente.descuentos.length > 0 ? `<strong>Descuento disponible:</strong> ${cliente.descuentos[0]}%` : ''}
            </div>
        `;

        // Aplicar descuento automático si existe
        if (cliente.descuentos.length > 0) {
            this.aplicarDescuentoAutomatico(cliente.descuentos[0]);
        }

        // Limpiar búsqueda
        document.getElementById('buscar-cliente-venta').value = '';
        document.getElementById('resultados-clientes').innerHTML = '';
    }

    aplicarDescuentoAutomatico(porcentaje) {
        this.itemsVenta.forEach(item => {
            item.descuento = porcentaje;
            this.calcularTotalesItem(item);
        });
        this.actualizarTablaVenta();
        this.actualizarTotales();
    }

    aplicarDescuento() {
        const selectDescuento = document.getElementById('tipo-descuento');
        if (!selectDescuento || !selectDescuento.value) {
            Swal.fire({
                title: 'Seleccionar Descuento',
                text: 'Debe seleccionar un tipo de descuento',
                icon: 'info',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        const descuento = this.descuentos.find(d => d.id == selectDescuento.value);
        if (!descuento) return;

        this.itemsVenta.forEach(item => {
            item.descuento = descuento.porcentaje;
            this.calcularTotalesItem(item);
        });

        this.actualizarTablaVenta();
        this.actualizarTotales();

        Swal.fire({
            title: 'Descuento Aplicado',
            text: `Se ha aplicado un ${descuento.porcentaje}% de descuento`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }

    actualizarTotales() {
        const subtotal = this.itemsVenta.reduce((sum, item) => sum + item.subtotal, 0);
        const descuentosTotal = this.itemsVenta.reduce((sum, item) => sum + item.descuentoAplicado, 0);
        const subtotalConDescuentos = subtotal - descuentosTotal;
        
        const selectPago = document.getElementById('forma-pago');
        let comisionPago = 0;
        if (selectPago && selectPago.value) {
            const formaPago = this.formasPago.find(fp => fp.id == selectPago.value);
            if (formaPago) {
                comisionPago = subtotalConDescuentos * formaPago.comision;
            }
        }
        
        const total = subtotalConDescuentos + comisionPago;

        // Actualizar elementos del DOM
        const subtotalEl = document.getElementById('subtotal-venta');
        if (subtotalEl) subtotalEl.textContent = `S/ ${subtotal.toFixed(2)}`;

        const descuentosEl = document.getElementById('descuentos-venta');
        if (descuentosEl) descuentosEl.textContent = `- S/ ${descuentosTotal.toFixed(2)}`;

        const comisionEl = document.getElementById('comision-venta');
        if (comisionEl) comisionEl.textContent = `S/ ${comisionPago.toFixed(2)}`;

        const totalEl = document.getElementById('total-venta');
        if (totalEl) {
            totalEl.textContent = `S/ ${total.toFixed(2)}`;
            totalEl.className = `h4 mb-0 ${total >= 100 ? 'text-danger' : total >= 50 ? 'text-warning' : 'text-success'}`;
        }

        // Actualizar botón de procesar
        const btnProcesar = document.getElementById('btn-procesar-venta');
        if (btnProcesar) {
            btnProcesar.disabled = this.itemsVenta.length === 0 || (this.configuracion.montoMinimoFacturacion && total < this.configuracion.montoMinimoFacturacion);
            btnProcesar.textContent = `Procesar Venta (S/ ${total.toFixed(2)})`;
        }

        return {
            subtotal,
            descuentosTotal,
            comisionPago,
            total
        };
    }

    validarMedicamentos() {
        const medicamentosConReceta = this.itemsVenta.filter(item => item.producto.requiereReceta);
        
        const medicinaContainer = document.getElementById('validacion-medicamentos');
        if (!medicinaContainer) return;

        if (medicamentosConReceta.length === 0) {
            medicinaContainer.style.display = 'none';
            return;
        }

        medicinaContainer.style.display = 'block';
        medicinaContainer.innerHTML = `
            <div class="alert alert-warning">
                <strong><i class="fas fa-prescription-bottle-alt"></i> Validación de Medicamentos</strong><br>
                La venta incluye ${medicamentosConReceta.length} medicamento(s) que requieren receta médica.
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="validar-receta" checked>
                    <label class="form-check-label" for="validar-receta">
                        Confirmo que se verificó la receta médica para todos los medicamentos
                    </label>
                </div>
            </div>
        `;
    }

    toggleValidacionReceta(activado) {
        if (!activado) {
            // Si se desactiva la validación, remover medicamentos que requieren receta
            this.itemsVenta = this.itemsVenta.filter(item => !item.producto.requiereReceta);
            this.actualizarTablaVenta();
            this.actualizarTotales();
        }
    }

    abrirBuscadorProductos() {
        // Implementar modal de buscador de productos
        Swal.fire({
            title: 'Buscador de Productos',
            html: `
                <input type="text" id="modal-busqueda-producto" class="form-control" placeholder="Buscar producto..." autofocus>
                <div id="modal-resultados-producto" class="mt-3" style="max-height: 300px; overflow-y: auto;">
                </div>
            `,
            width: '600px',
            showConfirmButton: false,
            didOpen: () => {
                const input = document.getElementById('modal-busqueda-producto');
                input.addEventListener('input', (e) => {
                    this.buscarProductosModal(e.target.value);
                });
            }
        });
    }

    buscarProductosModal(termino) {
        const productos = this.productos.filter(producto => 
            producto.activo &&
            (producto.nombre.toLowerCase().includes(termino.toLowerCase()) ||
             producto.codigo.toLowerCase().includes(termino.toLowerCase()))
        );

        const container = document.getElementById('modal-resultados-producto');
        if (container) {
            container.innerHTML = productos.map(producto => `
                <div class="list-group-item list-group-item-action" onclick="sistemaFacturacion.seleccionarProducto(${producto.id}); Swal.close();">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${producto.nombre}</strong><br>
                            <small class="text-muted">${producto.codigo} - Stock: ${producto.stock}</small>
                        </div>
                        <div class="text-end">
                            <strong>S/ ${producto.precio.toFixed(2)}</strong>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    }

    abrirBuscadorClientes() {
        // Implementar modal de buscador de clientes
        Swal.fire({
            title: 'Buscador de Clientes',
            html: `
                <input type="text" id="modal-busqueda-cliente" class="form-control" placeholder="Buscar cliente..." autofocus>
                <div id="modal-resultados-cliente" class="mt-3" style="max-height: 300px; overflow-y: auto;">
                </div>
            `,
            width: '500px',
            showConfirmButton: false,
            didOpen: () => {
                const input = document.getElementById('modal-busqueda-cliente');
                input.addEventListener('input', (e) => {
                    this.buscarClientesModal(e.target.value);
                });
            }
        });
    }

    buscarClientesModal(termino) {
        const clientes = this.clientes.filter(cliente => 
            cliente.nombre.toLowerCase().includes(termino.toLowerCase()) ||
            cliente.dni.includes(termino)
        );

        const container = document.getElementById('modal-resultados-cliente');
        if (container) {
            container.innerHTML = clientes.map(cliente => `
                <div class="list-group-item list-group-item-action" onclick="sistemaFacturacion.seleccionarCliente(${cliente.id}); Swal.close();">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${cliente.nombre}</strong><br>
                            <small class="text-muted">DNI: ${cliente.dni}</small>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    }

    procesarVenta() {
        if (this.itemsVenta.length === 0) {
            Swal.fire({
                title: 'Sin Productos',
                text: 'Debe agregar al menos un producto a la venta',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        const totales = this.actualizarTotales();
        const selectPago = document.getElementById('forma-pago');

        if (!selectPago.value) {
            Swal.fire({
                title: 'Forma de Pago Requerida',
                text: 'Debe seleccionar una forma de pago',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Validar medicamentos que requieren receta
        const medicamentosConReceta = this.itemsVenta.filter(item => item.producto.requiereReceta);
        const validarReceta = document.getElementById('validar-receta');
        
        if (medicamentosConReceta.length > 0 && (!validarReceta || !validarReceta.checked)) {
            Swal.fire({
                title: 'Validación de Receta Requerida',
                text: 'Debe confirmar la verificación de recetas médicas para medicamentos',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Confirmar venta
        Swal.fire({
            title: 'Confirmar Venta',
            html: `
                <div class="text-start">
                    <p><strong>Total de la venta:</strong> S/ ${totales.total.toFixed(2)}</p>
                    <p><strong>Forma de pago:</strong> ${this.formasPago.find(fp => fp.id == selectPago.value).nombre}</p>
                    <p><strong>Número de productos:</strong> ${this.itemsVenta.length}</p>
                    <hr>
                    <h6>Productos:</h6>
                    ${this.itemsVenta.map(item => `
                        <div class="d-flex justify-content-between">
                            <span>${item.producto.nombre} x ${item.cantidad}</span>
                            <span>S/ ${item.total.toFixed(2)}</span>
                        </div>
                    `).join('')}
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Confirmar Venta',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.finalizarVenta();
            }
        });
    }

    finalizarVenta() {
        // Simular procesamiento de venta
        const numeroVenta = `V-${Date.now()}`;
        
        // Actualizar estadísticas del vendedor
        this.vendedor.ventasHoy += 1;
        const totales = this.actualizarTotales();
        this.vendedor.montoVendido += totales.total;

        // Mostrar ticket
        this.mostrarTicketVenta(numeroVenta);

        // Limpiar venta
        this.limpiarVenta();
    }

    mostrarTicketVenta(numeroVenta) {
        const totales = this.actualizarTotales();
        const clienteInfo = document.getElementById('cliente-seleccionado');
        const clienteHTML = clienteInfo ? clienteInfo.innerHTML : '<div class="text-muted">Cliente anónimo</div>';

        const ticket = `
            <div style="font-family: monospace; font-size: 12px; max-width: 300px;">
                <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px;">
                    <h5>${this.configuracion.nombre}</h5>
                    <p>RUC: ${this.configuracion.ruc}<br>
                    ${this.configuracion.direccion}<br>
                    Tel: ${this.configuracion.telefono}</p>
                    <p><strong>TICKET DE VENTA</strong><br>
                    N°: ${numeroVenta}<br>
                    Fecha: ${new Date().toLocaleString()}<br>
                    Vendedor: ${this.vendedor.nombre}</p>
                </div>
                
                <div style="border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px;">
                    ${this.itemsVenta.map(item => `
                        <div style="margin-bottom: 5px;">
                            <strong>${item.producto.nombre}</strong><br>
                            ${item.cantidad} x S/ ${item.precioUnitario.toFixed(2)} = S/ ${item.total.toFixed(2)}
                        </div>
                    `).join('')}
                </div>
                
                <div style="border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Subtotal:</span><span>S/ ${totales.subtotal.toFixed(2)}</span>
                    </div>
                    ${totales.descuentosTotal > 0 ? `
                        <div style="display: flex; justify-content: space-between; color: red;">
                            <span>Descuentos:</span><span>-S/ ${totales.descuentosTotal.toFixed(2)}</span>
                        </div>
                    ` : ''}
                    ${totales.comisionPago > 0 ? `
                        <div style="display: flex; justify-content: space-between;">
                            <span>Comisión:</span><span>S/ ${totales.comisionPago.toFixed(2)}</span>
                        </div>
                    ` : ''}
                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px;">
                        <span>TOTAL:</span><span>S/ ${totales.total.toFixed(2)}</span>
                    </div>
                </div>
                
                <div style="text-align: center; border-top: 1px dashed #000; padding-top: 10px;">
                    <p>¡Gracias por su compra!</p>
                    <p>Visítenos nuevamente</p>
                </div>
            </div>
        `;

        // Crear ventana de ticket
        const ventanaTicket = window.open('', '_blank', 'width=400,height=600');
        ventanaTicket.document.write(ticket);
        ventanaTicket.document.close();
        ventanaTicket.print();
    }

    limpiarVenta() {
        this.itemsVenta = [];
        this.actualizarTablaVenta();
        this.actualizarTotales();
        this.validarMedicamentos();
        
        // Limpiar cliente
        document.getElementById('cliente-seleccionado').innerHTML = '';
        document.getElementById('buscar-cliente-venta').value = '';
        
        // Limpiar forma de pago
        document.getElementById('forma-pago').value = '';
        
        // Focus en búsqueda de producto
        document.getElementById('buscar-producto-venta').focus();
    }

    guardarBorrador() {
        if (this.itemsVenta.length === 0) {
            Swal.fire({
                title: 'Sin Productos',
                text: 'No hay productos para guardar como borrador',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        const borrador = {
            id: Date.now(),
            fecha: new Date().toLocaleString(),
            items: [...this.itemsVenta],
            vendedor: this.vendedor.nombre
        };

        // Guardar en localStorage
        let borradores = JSON.parse(localStorage.getItem('borradores_venta') || '[]');
        borradores.push(borrador);
        localStorage.setItem('borradores_venta', JSON.stringify(borradores));

        Swal.fire({
            title: 'Borrador Guardado',
            text: 'La venta ha sido guardada como borrador',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }
}

// Inicializar el sistema de facturación
let sistemaFacturacion;

document.addEventListener('DOMContentLoaded', function() {
    sistemaFacturacion = new SistemaFacturacion();
});

// Exportar para uso global
window.SistemaFacturacion = SistemaFacturacion;