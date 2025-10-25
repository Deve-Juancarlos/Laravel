// Sistema de Inventario - Farmacia SIFANO
// Gestión completa de inventario farmacéutico

class SistemaInventario {
    constructor() {
        this.productos = [];
        this.movimientos = [];
        this.ajustes = [];
        this.categorias = [];
        this.almacenes = [];
        this.proveedores = [];
        this.ordenesCompra = [];
        this.alertasStock = [];
        this.init();
    }

    init() {
        this.cargarDatosDemo();
        this.inicializarEventos();
        this.cargarDashboard();
        this.cargarTablaInventario();
        this.configurarAlertas();
        this.inicializarGraficos();
    }

    cargarDatosDemo() {
        // Productos del inventario
        this.productos = [
            {
                id: 1,
                codigo: 'MED001',
                nombre: 'Paracetamol 500mg',
                categoria: 'Analgésicos',
                laboratorio: 'Laboratorios ACME',
                stock: 1500,
                stockMinimo: 100,
                stockMaximo: 2000,
                precioCompra: 0.15,
                precioVenta: 0.35,
                ubicacion: 'Estante A-1',
                requiereReceta: false,
                fechaVencimiento: '2025-12-31',
                ultimoMovimiento: '2024-01-25',
                diasRotacion: 45,
                valorTotal: 225.00,
                estado: 'activo'
            },
            {
                id: 2,
                codigo: 'MED002',
                nombre: 'Amoxicilina 250mg',
                categoria: 'Antibióticos',
                laboratorio: 'PharmaCorp',
                stock: 45,
                stockMinimo: 50,
                stockMaximo: 500,
                precioCompra: 0.45,
                precioVenta: 0.80,
                ubicacion: 'Refrigerador B-2',
                requiereReceta: true,
                fechaVencimiento: '2025-06-30',
                ultimoMovimiento: '2024-01-24',
                diasRotacion: 30,
                valorTotal: 20.25,
                estado: 'stock_bajo'
            },
            {
                id: 3,
                codigo: 'MED003',
                nombre: 'Ibuprofeno 400mg',
                categoria: 'Antiinflamatorios',
                laboratorio: 'MediTech',
                stock: 800,
                stockMinimo: 80,
                stockMaximo: 1000,
                precioCompra: 0.25,
                precioVenta: 0.50,
                ubicacion: 'Estante B-3',
                requiereReceta: false,
                fechaVencimiento: '2026-03-15',
                ultimoMovimiento: '2024-01-23',
                diasRotacion: 60,
                valorTotal: 200.00,
                estado: 'activo'
            },
            {
                id: 4,
                codigo: 'MED004',
                nombre: 'Omeprazol 20mg',
                categoria: 'Gastrointestinales',
                laboratorio: 'GastroPharma',
                stock: 15,
                stockMinimo: 30,
                stockMaximo: 400,
                precioCompra: 0.60,
                precioVenta: 1.20,
                ubicacion: 'Estante C-1',
                requiereReceta: false,
                fechaVencimiento: '2025-08-20',
                ultimoMovimiento: '2024-01-22',
                diasRotacion: 25,
                valorTotal: 9.00,
                estado: 'stock_bajo'
            }
        ];

        // Movimientos de inventario
        this.movimientos = [
            {
                id: 1,
                productoId: 1,
                tipo: 'entrada',
                cantidad: 500,
                fecha: '2024-01-20 10:30:00',
                usuario: 'Dr. Martinez',
                documento: 'Factura-001',
                motivo: 'Compra programada',
                precioUnitario: 0.15,
                valorTotal: 75.00,
                lote: 'PAR500-2024-001'
            },
            {
                id: 2,
                productoId: 1,
                tipo: 'salida',
                cantidad: 50,
                fecha: '2024-01-25 14:15:00',
                usuario: 'Dra. Lopez',
                documento: 'Ticket-1234',
                motivo: 'Venta a paciente',
                precioUnitario: 0.35,
                valorTotal: 17.50,
                lote: 'PAR500-2024-001'
            },
            {
                id: 3,
                productoId: 2,
                tipo: 'salida',
                cantidad: 5,
                fecha: '2024-01-24 09:45:00',
                usuario: 'Dr. Sanchez',
                documento: 'Receta-456',
                motivo: 'Venta con receta',
                precioUnitario: 0.80,
                valorTotal: 4.00,
                lote: 'AMO250-2024-002'
            }
        ];

        // Ajustes de inventario
        this.ajustes = [
            {
                id: 1,
                productoId: 1,
                stockAnterior: 1050,
                stockNuevo: 1000,
                diferencia: -50,
                motivo: 'Inventario físico',
                fecha: '2024-01-15',
                usuario: 'Jefe Almacen',
                aprobado: true,
                observaciones: 'Diferencia por merma natural'
            }
        ];

        // Categorías
        this.categorias = [
            { id: 1, nombre: 'Analgésicos', productos: 1, valor: 225.00 },
            { id: 2, nombre: 'Antibióticos', productos: 1, valor: 20.25 },
            { id: 3, nombre: 'Antiinflamatorios', productos: 1, valor: 200.00 },
            { id: 4, nombre: 'Gastrointestinales', productos: 1, valor: 9.00 }
        ];

        // Almacenes
        this.almacenes = [
            {
                id: 1,
                nombre: 'Almacén Principal',
                ubicacion: 'Planta Baja',
                capacidad: 10000,
                utilizado: 6500,
                productos: 4,
                valorTotal: 454.25
            },
            {
                id: 2,
                nombre: 'Área Refrigerada',
                ubicacion: 'Sótano',
                capacidad: 2000,
                utilizado: 800,
                productos: 1,
                valorTotal: 20.25
            }
        ];

        // Proveedores
        this.proveedores = [
            {
                id: 1,
                nombre: 'Distribuidora Pharma',
                productos: 3,
                valorCompras: 295.25,
                ultimaCompra: '2024-01-20'
            },
            {
                id: 2,
                nombre: 'Laboratorios Unidos',
                productos: 1,
                valorCompras: 159.00,
                ultimaCompra: '2024-01-18'
            }
        ];

        // Alertas de stock
        this.alertasStock = [
            {
                id: 1,
                productoId: 2,
                producto: 'Amoxicilina 250mg',
                stockActual: 45,
                stockMinimo: 50,
                tipo: 'stock_bajo',
                fecha: '2024-01-25',
                prioridad: 'alta'
            },
            {
                id: 2,
                productoId: 4,
                producto: 'Omeprazol 20mg',
                stockActual: 15,
                stockMinimo: 30,
                tipo: 'stock_bajo',
                fecha: '2024-01-24',
                prioridad: 'media'
            }
        ];
    }

    cargarDashboard() {
        // Actualizar métricas del dashboard
        const totalProductos = this.productos.length;
        const valorTotalInventario = this.productos.reduce((sum, p) => sum + p.valorTotal, 0);
        const productosStockBajo = this.alertasStock.length;
        const movimientosHoy = this.movimientos.filter(m => 
            new Date(m.fecha).toDateString() === new Date().toDateString()
        ).length;

        // Actualizar elementos del DOM
        document.getElementById('total-productos').textContent = totalProductos;
        document.getElementById('valor-total-inventario').textContent = `S/ ${valorTotalInventario.toFixed(2)}`;
        document.getElementById('productos-stock-bajo').textContent = productosStockBajo;
        document.getElementById('movimientos-hoy').textContent = movimientosHoy;
    }

    cargarTablaInventario() {
        const tbody = document.querySelector('#tabla-inventario tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        this.productos.forEach(producto => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>
                    <div>
                        <strong>${producto.codigo}</strong><br>
                        <small class="text-muted">${producto.ubicacion}</small>
                    </div>
                </td>
                <td>
                    <div>
                        <strong>${producto.nombre}</strong><br>
                        <small class="text-muted">${producto.laboratorio}</small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-${this.getColorCategoria(producto.categoria)}">
                        ${producto.categoria}
                    </span>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-${this.getColorStock(producto.stock, producto.stockMinimo)}">
                            ${producto.stock} unidades
                        </span>
                    </div>
                    <small class="text-muted">Min: ${producto.stockMinimo} | Max: ${producto.stockMaximo}</small>
                </td>
                <td>
                    <div class="progress mb-1" style="height: 20px;">
                        <div class="progress-bar ${this.getProgressColor(producto.stock, producto.stockMaximo)}" 
                             style="width: ${(producto.stock/producto.stockMaximo)*100}%">
                            ${Math.round((producto.stock/producto.stockMaximo)*100)}%
                        </div>
                    </div>
                    <small class="text-muted">Rotación: ${producto.diasRotacion} días</small>
                </td>
                <td>
                    <div>
                        <strong>S/ ${producto.precioVenta.toFixed(2)}</strong><br>
                        <small class="text-muted">Compra: S/ ${producto.precioCompra.toFixed(2)}</small>
                    </div>
                </td>
                <td>
                    <strong>S/ ${producto.valorTotal.toFixed(2)}</strong>
                </td>
                <td>
                    <span class="badge bg-${this.getEstadoColor(producto.estado)}">
                        ${this.getEstadoTexto(producto.estado)}
                    </span>
                    ${producto.requiereReceta ? '<br><small class="text-warning"><i class="fas fa-prescription-bottle-alt"></i> Receta</small>' : ''}
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="sistemaInventario.verProducto(${producto.id})" title="Ver">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="sistemaInventario.movimientoProducto(${producto.id}, 'entrada')" title="Entrada">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="sistemaInventario.movimientoProducto(${producto.id}, 'salida')" title="Salida">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="sistemaInventario.ajustarStock(${producto.id})" title="Ajustar">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(fila);
        });

        // Inicializar DataTables
        if ($.fn.DataTable) {
            $('#tabla-inventario').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                order: [[1, 'asc']]
            });
        }
    }

    getColorCategoria(categoria) {
        const colores = {
            'Analgésicos': 'primary',
            'Antibióticos': 'danger',
            'Antiinflamatorios': 'warning',
            'Gastrointestinales': 'success'
        };
        return colores[categoria] || 'secondary';
    }

    getColorStock(stock, stockMinimo) {
        if (stock <= stockMinimo) return 'danger';
        if (stock <= stockMinimo * 1.5) return 'warning';
        return 'success';
    }

    getProgressColor(stock, stockMaximo) {
        const porcentaje = (stock / stockMaximo) * 100;
        if (porcentaje <= 10) return 'bg-danger';
        if (porcentaje <= 25) return 'bg-warning';
        if (porcentaje <= 50) return 'bg-info';
        return 'bg-success';
    }

    getEstadoColor(estado) {
        const colores = {
            'activo': 'success',
            'stock_bajo': 'warning',
            'agotado': 'danger',
            'vencido': 'dark'
        };
        return colores[estado] || 'secondary';
    }

    getEstadoTexto(estado) {
        const textos = {
            'activo': 'Activo',
            'stock_bajo': 'Stock Bajo',
            'agotado': 'Agotado',
            'vencido': 'Vencido'
        };
        return textos[estado] || 'Desconocido';
    }

    inicializarEventos() {
        // Filtros
        document.getElementById('filtro-categoria-inventario')?.addEventListener('change', () => {
            this.filtrarInventario();
        });

        document.getElementById('filtro-estado-inventario')?.addEventListener('change', () => {
            this.filtrarInventario();
        });

        document.getElementById('buscar-inventario')?.addEventListener('input', (e) => {
            this.buscarEnInventario(e.target.value);
        });

        // Botones de acción
        document.getElementById('btn-nuevo-producto-inv')?.addEventListener('click', () => {
            this.mostrarFormularioProducto();
        });

        document.getElementById('btn-importar-inventario')?.addEventListener('click', () => {
            this.importarInventario();
        });

        document.getElementById('btn-exportar-inventario')?.addEventListener('click', () => {
            this.exportarInventario();
        });

        document.getElementById('btn-generar-reporte-inv')?.addEventListener('click', () => {
            this.generarReporteInventario();
        });

        document.getElementById('btn-orden-compra')?.addEventListener('click', () => {
            this.crearOrdenCompra();
        });
    }

    configurarAlertas() {
        // Actualizar alertas de stock bajo
        const alertasContainer = document.getElementById('alertas-stock-container');
        if (!alertasContainer) return;

        if (this.alertasStock.length === 0) {
            alertasContainer.innerHTML = `
                <div class="alert alert-success d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <span>Todos los productos tienen stock adecuado</span>
                </div>
            `;
            return;
        }

        alertasContainer.innerHTML = this.alertasStock.map(alerta => `
            <div class="alert alert-${alerta.prioridad === 'alta' ? 'danger' : 'warning'} alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong>${alerta.producto}</strong><br>
                        <small>Stock actual: ${alerta.stockActual} | Stock mínimo: ${alerta.stockMinimo}</small>
                    </div>
                </div>
                <button type="button" class="btn-close" onclick="sistemaInventario.dismissAlert(${alerta.id})"></button>
                <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="sistemaInventario.crearOrdenCompra(${alerta.productoId})">
                    <i class="fas fa-shopping-cart"></i> Orden de Compra
                </button>
            </div>
        `).join('');
    }

    inicializarGraficos() {
        // Gráfico de rotación de productos
        const ctxRotacion = document.getElementById('grafico-rotacion');
        if (ctxRotacion) {
            new Chart(ctxRotacion, {
                type: 'doughnut',
                data: {
                    labels: ['Rotación Rápida', 'Rotación Normal', 'Rotación Lenta'],
                    datasets: [{
                        data: [1, 2, 1],
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Rotación de Inventario'
                        }
                    }
                }
            });
        }

        // Gráfico de valor por categoría
        const ctxCategorias = document.getElementById('grafico-categorias');
        if (ctxCategorias) {
            new Chart(ctxCategorias, {
                type: 'bar',
                data: {
                    labels: this.categorias.map(c => c.nombre),
                    datasets: [{
                        label: 'Valor (S/)',
                        data: this.categorias.map(c => c.valor),
                        backgroundColor: ['#007bff', '#dc3545', '#ffc107', '#28a745']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Valor de Inventario por Categoría'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Valor (S/)'
                            }
                        }
                    }
                }
            });
        }
    }

    filtrarInventario() {
        const categoria = document.getElementById('filtro-categoria-inventario').value;
        const estado = document.getElementById('filtro-estado-inventario').value;
        
        let productosFiltrados = this.productos;
        
        if (categoria) {
            productosFiltrados = productosFiltrados.filter(p => p.categoria === categoria);
        }
        
        if (estado) {
            productosFiltrados = productosFiltrados.filter(p => p.estado === estado);
        }
        
        this.mostrarProductosFiltrados(productosFiltrados);
    }

    buscarEnInventario(termino) {
        const productosFiltrados = this.productos.filter(producto =>
            producto.nombre.toLowerCase().includes(termino.toLowerCase()) ||
            producto.codigo.toLowerCase().includes(termino.toLowerCase()) ||
            producto.laboratorio.toLowerCase().includes(termino.toLowerCase())
        );
        
        this.mostrarProductosFiltrados(productosFiltrados);
    }

    mostrarProductosFiltrados(productos) {
        const tbody = document.querySelector('#tabla-inventario tbody');
        tbody.innerHTML = '';

        productos.forEach(producto => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>
                    <div>
                        <strong>${producto.codigo}</strong><br>
                        <small class="text-muted">${producto.ubicacion}</small>
                    </div>
                </td>
                <td>
                    <div>
                        <strong>${producto.nombre}</strong><br>
                        <small class="text-muted">${producto.laboratorio}</small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-${this.getColorCategoria(producto.categoria)}">
                        ${producto.categoria}
                    </span>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-${this.getColorStock(producto.stock, producto.stockMinimo)}">
                            ${producto.stock} unidades
                        </span>
                    </div>
                    <small class="text-muted">Min: ${producto.stockMinimo} | Max: ${producto.stockMaximo}</small>
                </td>
                <td>
                    <div class="progress mb-1" style="height: 20px;">
                        <div class="progress-bar ${this.getProgressColor(producto.stock, producto.stockMaximo)}" 
                             style="width: ${(producto.stock/producto.stockMaximo)*100}%">
                            ${Math.round((producto.stock/producto.stockMaximo)*100)}%
                        </div>
                    </div>
                    <small class="text-muted">Rotación: ${producto.diasRotacion} días</small>
                </td>
                <td>
                    <div>
                        <strong>S/ ${producto.precioVenta.toFixed(2)}</strong><br>
                        <small class="text-muted">Compra: S/ ${producto.precioVenta.toFixed(2)}</small>
                    </div>
                </td>
                <td>
                    <strong>S/ ${producto.valorTotal.toFixed(2)}</strong>
                </td>
                <td>
                    <span class="badge bg-${this.getEstadoColor(producto.estado)}">
                        ${this.getEstadoTexto(producto.estado)}
                    </span>
                    ${producto.requiereReceta ? '<br><small class="text-warning"><i class="fas fa-prescription-bottle-alt"></i> Receta</small>' : ''}
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="sistemaInventario.verProducto(${producto.id})" title="Ver">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="sistemaInventario.movimientoProducto(${producto.id}, 'entrada')" title="Entrada">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="sistemaInventario.movimientoProducto(${producto.id}, 'salida')" title="Salida">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="sistemaInventario.ajustarStock(${producto.id})" title="Ajustar">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(fila);
        });
    }

    verProducto(id) {
        const producto = this.productos.find(p => p.id === id);
        if (!producto) return;

        const movimientosProducto = this.movimientos.filter(m => m.productoId === id);

        Swal.fire({
            title: 'Información del Producto',
            html: `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Código:</strong> ${producto.codigo}</p>
                        <p><strong>Nombre:</strong> ${producto.nombre}</p>
                        <p><strong>Categoría:</strong> ${producto.categoria}</p>
                        <p><strong>Laboratorio:</strong> ${producto.laboratorio}</p>
                        <p><strong>Stock Actual:</strong> ${producto.stock} unidades</p>
                        <p><strong>Stock Mínimo:</strong> ${producto.stockMinimo}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Stock Máximo:</strong> ${producto.stockMaximo}</p>
                        <p><strong>Precio Compra:</strong> S/ ${producto.precioCompra.toFixed(2)}</p>
                        <p><strong>Precio Venta:</strong> S/ ${producto.precioVenta.toFixed(2)}</p>
                        <p><strong>Valor Total:</strong> S/ ${producto.valorTotal.toFixed(2)}</p>
                        <p><strong>Ubicación:</strong> ${producto.ubicacion}</p>
                        <p><strong>Último Movimiento:</strong> ${producto.ultimoMovimiento}</p>
                    </div>
                </div>
                <hr>
                <h6>Movimientos Recientes:</h6>
                ${movimientosProducto.slice(0, 5).map(mov => `
                    <div class="alert alert-info">
                        <strong>${mov.tipo.toUpperCase()}</strong> - ${mov.cantidad} unidades - ${mov.fecha}
                        <br><small>${mov.motivo} - ${mov.documento}</small>
                    </div>
                `).join('')}
            `,
            width: '800px',
            confirmButtonText: 'Cerrar'
        });
    }

    movimientoProducto(productoId, tipo) {
        const producto = this.productos.find(p => p.id === productoId);
        if (!producto) return;

        Swal.fire({
            title: `Movimiento de ${tipo === 'entrada' ? 'Entrada' : 'Salida'}`,
            html: `
                <div class="mb-3">
                    <label class="form-label">Producto:</label>
                    <p><strong>${producto.nombre}</strong></p>
                    <p>Stock actual: <span class="badge bg-info">${producto.stock}</span></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cantidad:</label>
                    <input type="number" id="cantidad-movimiento-inv" class="form-control" 
                           min="1" max="${tipo === 'entrada' ? '9999' : producto.stock}" value="1">
                </div>
                <div class="mb-3">
                    <label class="form-label">Precio Unitario:</label>
                    <input type="number" id="precio-movimiento-inv" class="form-control" 
                           step="0.01" value="${tipo === 'entrada' ? producto.precioCompra : producto.precioVenta}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Documento:</label>
                    <input type="text" id="documento-movimiento-inv" class="form-control" 
                           placeholder="Número de documento">
                </div>
                <div class="mb-3">
                    <label class="form-label">Motivo:</label>
                    <textarea id="motivo-movimiento-inv" class="form-control" rows="2" 
                              placeholder="Ingrese el motivo del movimiento"></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: `Registrar ${tipo === 'entrada' ? 'Entrada' : 'Salida'}`,
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const cantidad = parseInt(document.getElementById('cantidad-movimiento-inv').value);
                const precio = parseFloat(document.getElementById('precio-movimiento-inv').value);
                const documento = document.getElementById('documento-movimiento-inv').value;
                const motivo = document.getElementById('motivo-movimiento-inv').value;
                
                if (!cantidad || cantidad < 1) {
                    Swal.showValidationMessage('Cantidad inválida');
                    return false;
                }
                
                if (tipo === 'salida' && cantidad > producto.stock) {
                    Swal.showValidationMessage('Cantidad excede el stock disponible');
                    return false;
                }
                
                if (!precio || precio < 0) {
                    Swal.showValidationMessage('Precio inválido');
                    return false;
                }
                
                if (!documento.trim()) {
                    Swal.showValidationMessage('Documento es obligatorio');
                    return false;
                }
                
                if (!motivo.trim()) {
                    Swal.showValidationMessage('Motivo es obligatorio');
                    return false;
                }
                
                return { cantidad, precio, documento, motivo };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.registrarMovimientoInventario(productoId, tipo, result.value);
            }
        });
    }

    registrarMovimientoInventario(productoId, tipo, datos) {
        const producto = this.productos.find(p => p.id === productoId);
        const nuevoMovimiento = {
            id: this.movimientos.length + 1,
            productoId: productoId,
            tipo: tipo,
            cantidad: datos.cantidad,
            fecha: new Date().toLocaleString(),
            usuario: 'Usuario Actual',
            documento: datos.documento,
            motivo: datos.motivo,
            precioUnitario: datos.precio,
            valorTotal: datos.cantidad * datos.precio,
            lote: `LOTE-${Date.now()}`
        };
        
        // Actualizar stock del producto
        if (tipo === 'entrada') {
            producto.stock += datos.cantidad;
        } else {
            producto.stock -= datos.cantidad;
        }
        
        // Actualizar valor total
        producto.valorTotal = producto.stock * producto.precioCompra;
        producto.ultimoMovimiento = new Date().toLocaleString();
        
        // Actualizar estado del producto
        if (producto.stock <= 0) {
            producto.estado = 'agotado';
        } else if (producto.stock <= producto.stockMinimo) {
            producto.estado = 'stock_bajo';
        } else {
            producto.estado = 'activo';
        }
        
        this.movimientos.push(nuevoMovimiento);
        
        // Actualizar alertas
        this.actualizarAlertas();
        
        // Recargar tablas y dashboard
        this.cargarTablaInventario();
        this.cargarDashboard();
        
        Swal.fire({
            title: '¡Movimiento Registrado!',
            text: `El movimiento de ${tipo} ha sido registrado exitosamente.`,
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }

    ajustarStock(productoId) {
        const producto = this.productos.find(p => p.id === productoId);
        if (!producto) return;

        Swal.fire({
            title: 'Ajustar Stock',
            html: `
                <div class="mb-3">
                    <label class="form-label">Producto:</label>
                    <p><strong>${producto.nombre}</strong></p>
                    <p>Stock actual: <span class="badge bg-info">${producto.stock}</span></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nuevo Stock:</label>
                    <input type="number" id="nuevo-stock" class="form-control" 
                           min="0" value="${producto.stock}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Motivo del Ajuste:</label>
                    <textarea id="motivo-ajuste" class="form-control" rows="3" 
                              placeholder="Explique el motivo del ajuste de stock"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones:</label>
                    <textarea id="observaciones-ajuste" class="form-control" rows="2" 
                              placeholder="Observaciones adicionales"></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Aplicar Ajuste',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const nuevoStock = parseInt(document.getElementById('nuevo-stock').value);
                const motivo = document.getElementById('motivo-ajuste').value;
                const observaciones = document.getElementById('observaciones-ajuste').value;
                
                if (nuevoStock < 0 || nuevoStock === producto.stock) {
                    Swal.showValidationMessage('Stock inválido o igual al actual');
                    return false;
                }
                
                if (!motivo.trim()) {
                    Swal.showValidationMessage('El motivo es obligatorio');
                    return false;
                }
                
                return { nuevoStock, motivo, observaciones };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.aplicarAjusteStock(productoId, result.value);
            }
        });
    }

    aplicarAjusteStock(productoId, datos) {
        const producto = this.productos.find(p => p.id === productoId);
        const nuevoAjuste = {
            id: this.ajustes.length + 1,
            productoId: productoId,
            stockAnterior: producto.stock,
            stockNuevo: datos.nuevoStock,
            diferencia: datos.nuevoStock - producto.stock,
            motivo: datos.motivo,
            fecha: new Date().toLocaleString(),
            usuario: 'Usuario Actual',
            aprobado: true,
            observaciones: datos.observaciones
        };
        
        // Aplicar el ajuste
        producto.stock = datos.nuevoStock;
        producto.valorTotal = producto.stock * producto.precioCompra;
        producto.ultimoMovimiento = new Date().toLocaleString();
        
        // Actualizar estado
        if (producto.stock <= 0) {
            producto.estado = 'agotado';
        } else if (producto.stock <= producto.stockMinimo) {
            producto.estado = 'stock_bajo';
        } else {
            producto.estado = 'activo';
        }
        
        this.ajustes.push(nuevoAjuste);
        
        // Actualizar alertas
        this.actualizarAlertas();
        
        // Recargar
        this.cargarTablaInventario();
        this.cargarDashboard();
        
        Swal.fire({
            title: '¡Ajuste Aplicado!',
            text: `Stock ajustado de ${nuevoAjuste.stockAnterior} a ${nuevoAjuste.stockNuevo} unidades.`,
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }

    actualizarAlertas() {
        // Limpiar alertas existentes
        this.alertasStock = [];
        
        // Generar nuevas alertas
        this.productos.forEach(producto => {
            if (producto.stock <= producto.stockMinimo) {
                this.alertasStock.push({
                    id: Date.now() + Math.random(),
                    productoId: producto.id,
                    producto: producto.nombre,
                    stockActual: producto.stock,
                    stockMinimo: producto.stockMinimo,
                    tipo: 'stock_bajo',
                    fecha: new Date().toLocaleString(),
                    prioridad: producto.stock <= producto.stockMinimo * 0.5 ? 'alta' : 'media'
                });
            }
        });
        
        this.configurarAlertas();
    }

    dismissAlert(alertId) {
        this.alertasStock = this.alertasStock.filter(a => a.id !== alertId);
        this.configurarAlertas();
    }

    crearOrdenCompra(productoId = null) {
        const productosParaOC = productoId ? 
            this.productos.filter(p => p.id === productoId && p.estado === 'stock_bajo') :
            this.productos.filter(p => p.estado === 'stock_bajo');
        
        if (productosParaOC.length === 0) {
            Swal.fire({
                title: 'No hay productos con stock bajo',
                text: 'No hay productos que requieran orden de compra.',
                icon: 'info',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        const nuevaOC = {
            id: this.ordenesCompra.length + 1,
            numero: `OC-2024-${String(this.ordenesCompra.length + 1).padStart(3, '0')}`,
            fecha: new Date().toLocaleDateString(),
            productos: productosParaOC.map(p => ({
                productoId: p.id,
                producto: p.nombre,
                stockActual: p.stock,
                stockMinimo: p.stockMinimo,
                cantidad: p.stockMaximo - p.stockMinimo,
                precioUnitario: p.precioCompra,
                valorTotal: (p.stockMaximo - p.stockMinimo) * p.precioCompra
            })),
            valorTotal: productosParaOC.reduce((sum, p) => 
                sum + ((p.stockMaximo - p.stockMinimo) * p.precioCompra), 0
            ),
            estado: 'pendiente',
            proveedor: productosParaOC[0].laboratorio
        };
        
        this.ordenesCompra.push(nuevaOC);
        
        // Mostrar resumen de la orden
        Swal.fire({
            title: 'Orden de Compra Generada',
            html: `
                <div class="mb-3">
                    <p><strong>Número:</strong> ${nuevaOC.numero}</p>
                    <p><strong>Fecha:</strong> ${nuevaOC.fecha}</p>
                    <p><strong>Proveedor:</strong> ${nuevaOC.proveedor}</p>
                    <p><strong>Valor Total:</strong> S/ ${nuevaOC.valorTotal.toFixed(2)}</p>
                </div>
                <hr>
                <h6>Productos Solicitados:</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${nuevaOC.productos.map(p => `
                                <tr>
                                    <td>${p.producto}</td>
                                    <td>${p.cantidad}</td>
                                    <td>S/ ${p.precioUnitario.toFixed(2)}</td>
                                    <td>S/ ${p.valorTotal.toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `,
            width: '800px',
            confirmButtonText: 'Aceptar'
        });
    }

    mostrarFormularioProducto() {
        Swal.fire({
            title: 'Nuevo Producto',
            text: 'Funcionalidad en desarrollo',
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
    }

    importarInventario() {
        Swal.fire({
            title: 'Importar Inventario',
            text: 'Funcionalidad en desarrollo',
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
    }

    exportarInventario() {
        // Generar CSV del inventario
        let csv = 'Código,Nombre,Categoría,Stock,Stock Mínimo,Stock Máximo,Precio Compra,Precio Venta,Valor Total,Estado\n';
        
        this.productos.forEach(producto => {
            csv += `${producto.codigo},${producto.nombre},${producto.categoria},${producto.stock},${producto.stockMinimo},${producto.stockMaximo},${producto.precioCompra},${producto.precioVenta},${producto.valorTotal},${producto.estado}\n`;
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `inventario-${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
        
        Swal.fire({
            title: '¡Exportado!',
            text: 'El inventario ha sido exportado exitosamente.',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }

    generarReporteInventario() {
        const reporte = {
            fecha: new Date(),
            totalProductos: this.productos.length,
            valorTotalInventario: this.productos.reduce((sum, p) => sum + p.valorTotal, 0),
            productosStockBajo: this.productos.filter(p => p.estado === 'stock_bajo').length,
            productosAgotados: this.productos.filter(p => p.estado === 'agotado').length,
            movimientosHoy: this.movimientos.filter(m => 
                new Date(m.fecha).toDateString() === new Date().toDateString()
            ).length,
            ajustesPendientes: this.ajustes.filter(a => !a.aprobado).length
        };
        
        // Crear ventana para mostrar reporte
        const ventanaReporte = window.open('', '_blank', 'width=800,height=600');
        ventanaReporte.document.write(`
            <html>
                <head>
                    <title>Reporte de Inventario</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
                        .stats { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0; }
                        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Reporte de Inventario</h1>
                        <p><strong>Fecha:</strong> ${reporte.fecha.toLocaleString()}</p>
                    </div>
                    
                    <div class="stats">
                        <h3>Resumen Ejecutivo</h3>
                        <p><strong>Total de Productos:</strong> ${reporte.totalProductos}</p>
                        <p><strong>Valor Total del Inventario:</strong> S/ ${reporte.valorTotalInventario.toFixed(2)}</p>
                        <p><strong>Productos con Stock Bajo:</strong> ${reporte.productosStockBajo}</p>
                        <p><strong>Productos Agotados:</strong> ${reporte.productosAgotados}</p>
                        <p><strong>Movimientos Hoy:</strong> ${reporte.movimientosHoy}</p>
                        <p><strong>Ajustes Pendientes:</strong> ${reporte.ajustesPendientes}</p>
                    </div>
                    
                    <h3>Inventario Detallado</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Stock</th>
                                <th>Valor</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${this.productos.map(p => `
                                <tr>
                                    <td>${p.codigo}</td>
                                    <td>${p.nombre}</td>
                                    <td>${p.stock}</td>
                                    <td>S/ ${p.valorTotal.toFixed(2)}</td>
                                    <td>${p.estado}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </body>
            </html>
        `);
        ventanaReporte.document.close();
    }
}

// Inicializar el sistema de inventario
let sistemaInventario;

document.addEventListener('DOMContentLoaded', function() {
    sistemaInventario = new SistemaInventario();
});

// Exportar para uso global
window.SistemaInventario = SistemaInventario;