    // Sistema de Trazabilidad - Farmacia SIFANO
// Control completo de lote y trazabilidad de medicamentos

class SistemaTrazabilidad {
    constructor() {
        this.productos = [];
        this.lotes = [];
        this.movimientos = [];
        this.proveedores = [];
        this.almacenes = [];
        this.init();
    }

    init() {
        this.cargarDatosDemo();
        this.inicializarEventos();
        this.cargarTablaProductos();
        this.cargarTablaLotes();
        this.configurarFiltros();
        this.inicializarScanner();
    }

    cargarDatosDemo() {
        // Productos farmacéuticos con trazabilidad
        this.productos = [
            {
                id: 1,
                codigo: 'MED001',
                nombre: 'Paracetamol 500mg',
                laboratorio: 'Laboratorios ACME',
                registroSanitario: 'RS-2023-001',
                formaFarmaceutica: 'Tableta',
                concentracion: '500mg',
                categoria: 'Analgésicos',
                activo: true,
                requiereTemperatura: false,
                requiereLote: true,
                fechaVencimiento: '2025-12-31',
                precioCompra: 0.15,
                precioVenta: 0.35,
                stockActual: 1500,
                stockMinimo: 100,
                ubicacion: 'Estante A-1',
                proveedorFavorito: 1
            },
            {
                id: 2,
                codigo: 'MED002',
                nombre: 'Amoxicilina 250mg',
                laboratorio: 'PharmaCorp',
                registroSanitario: 'RS-2023-002',
                formaFarmaceutica: 'Cápsula',
                concentracion: '250mg',
                categoria: 'Antibióticos',
                activo: true,
                requiereTemperatura: true,
                requiereLote: true,
                fechaVencimiento: '2025-06-30',
                precioCompra: 0.45,
                precioVenta: 0.80,
                stockActual: 300,
                stockMinimo: 50,
                ubicacion: 'Refrigerador B-2',
                proveedorFavorito: 2
            },
            {
                id: 3,
                codigo: 'MED003',
                nombre: 'Ibuprofeno 400mg',
                laboratorio: 'MediTech',
                registroSanitario: 'RS-2023-003',
                formaFarmaceutica: 'Tableta',
                concentracion: '400mg',
                categoria: 'Antiinflamatorios',
                activo: true,
                requiereTemperatura: false,
                requiereLote: true,
                fechaVencimiento: '2026-03-15',
                precioCompra: 0.25,
                precioVenta: 0.50,
                stockActual: 800,
                stockMinimo: 80,
                ubicacion: 'Estante B-3',
                proveedorFavorito: 1
            }
        ];

        // Lotes de productos
        this.lotes = [
            {
                id: 1,
                productoId: 1,
                numeroLote: 'PAR500-2024-001',
                fechaFabricacion: '2024-01-15',
                fechaVencimiento: '2025-12-31',
                cantidadInicial: 5000,
                cantidadActual: 1500,
                costoUnitario: 0.15,
                proveedorId: 1,
                almacen: 'Principal',
                ubicacion: 'Estante A-1-Lote1',
                temperaturaMinima: null,
                temperaturaMaxima: null,
                requiereRefrigeracion: false,
                estado: 'activo',
                fechaRegistro: '2024-01-20',
                documentos: ['Factura-001.pdf', 'Certificado-CALIDAD.pdf'],
                observaciones: 'Lote en buenas condiciones'
            },
            {
                id: 2,
                productoId: 2,
                numeroLote: 'AMO250-2024-002',
                fechaFabricacion: '2024-02-01',
                fechaVencimiento: '2025-06-30',
                cantidadInicial: 2000,
                cantidadActual: 300,
                costoUnitario: 0.45,
                proveedorId: 2,
                almacen: 'Refrigerado',
                ubicacion: 'Refrigerador B-2-Lote2',
                temperaturaMinima: 2,
                temperaturaMaxima: 8,
                requiereRefrigeracion: true,
                estado: 'activo',
                fechaRegistro: '2024-02-05',
                documentos: ['Factura-002.pdf', 'Certificado-FDA.pdf'],
                observaciones: 'Mantener en refrigeración'
            }
        ];

        // Movimientos de inventario
        this.movimientos = [
            {
                id: 1,
                tipo: 'entrada',
                productoId: 1,
                loteId: 1,
                cantidad: 500,
                fecha: '2024-01-20 10:30:00',
                usuario: 'Dr. Martinez',
                razon: 'Compra - Factura 001',
                documento: 'Factura-001',
                almacenOrigen: null,
                almacenDestino: 'Principal',
                estado: 'completado',
                observaciones: 'Producto recibido en buen estado'
            },
            {
                id: 2,
                tipo: 'salida',
                productoId: 1,
                loteId: 1,
                cantidad: 50,
                fecha: '2024-01-25 14:15:00',
                usuario: 'Dra. Lopez',
                razon: 'Venta - Ticket 1234',
                documento: 'Ticket-1234',
                almacenOrigen: 'Principal',
                almacenDestino: null,
                estado: 'completado',
                observaciones: 'Venta a paciente ambulatorio'
            }
        ];

        // Proveedores
        this.proveedores = [
            {
                id: 1,
                nombre: 'Distribuidora Pharma',
                ruc: '20123456789',
                direccion: 'Av. Principal 123, Lima',
                telefono: '+51-1-234-5678',
                email: 'ventas@pharmadist.com',
                contacto: 'Carlos Rodriguez',
                calificacion: 4.5,
                activo: true,
                fechaRegistro: '2023-01-15'
            },
            {
                id: 2,
                nombre: 'Laboratorios Unidos',
                ruc: '20987654321',
                direccion: 'Jr. Salud 456, Callao',
                telefono: '+51-1-876-5432',
                email: 'comercial@labunidos.com',
                contacto: 'Maria Gonzalez',
                calificacion: 4.8,
                activo: true,
                fechaRegistro: '2023-03-20'
            }
        ];

        // Almacenes
        this.almacenes = [
            {
                id: 1,
                nombre: 'Almacén Principal',
                codigo: 'ALM001',
                tipo: 'general',
                temperatura: 'ambiente',
                humedad: 'controlada',
                capacidad: 10000,
                utilizado: 6500,
                ubicacion: 'Planta Baja',
                responsable: 'Juan Pérez',
                activo: true
            },
            {
                id: 2,
                nombre: 'Almacén Refrigerado',
                codigo: 'ALM002',
                tipo: 'refrigerado',
                temperatura: '2-8°C',
                humedad: 'controlada',
                capacidad: 2000,
                utilizado: 1200,
                ubicacion: 'Sótano',
                responsable: 'Ana García',
                activo: true
            }
        ];
    }

    cargarTablaProductos() {
        const tabla = document.getElementById('tabla-productos');
        if (!tabla) return;

        const tbody = tabla.querySelector('tbody');
        tbody.innerHTML = '';

        this.productos.forEach(producto => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${producto.codigo}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div>
                            <strong>${producto.nombre}</strong><br>
                            <small class="text-muted">${producto.concentracion} - ${producto.formaFarmaceutica}</small>
                        </div>
                    </div>
                </td>
                <td>${producto.laboratorio}</td>
                <td>
                    <span class="badge bg-${producto.requiereTemperatura ? 'warning' : 'secondary'}">
                        ${producto.requiereTemperatura ? '<i class="fas fa-snowflake me-1"></i>Refrigerado' : 'Ambiente'}
                    </span>
                </td>
                <td>
                    <span class="badge bg-${producto.stockActual <= producto.stockMinimo ? 'danger' : 'success'}">
                        ${producto.stockActual} unidades
                    </span>
                </td>
                <td>${producto.ubicacion}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="sistemaTrazabilidad.verProducto(${producto.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="sistemaTrazabilidad.editarProducto(${producto.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-info" onclick="sistemaTrazabilidad.verLotes(${producto.id})">
                            <i class="fas fa-boxes"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(fila);
        });

        // Inicializar DataTables
        if ($.fn.DataTable) {
            $('#tabla-productos').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                order: [[1, 'asc']]
            });
        }
    }

    cargarTablaLotes() {
        const tabla = document.getElementById('tabla-lotes');
        if (!tabla) return;

        const tbody = tabla.querySelector('tbody');
        tbody.innerHTML = '';

        this.lotes.forEach(lote => {
            const producto = this.productos.find(p => p.id === lote.productoId);
            const diasVencimiento = this.calcularDiasVencimiento(lote.fechaVencimiento);
            
            fila.innerHTML = `
                <td>${lote.numeroLote}</td>
                <td>
                    <div>
                        <strong>${producto.nombre}</strong><br>
                        <small class="text-muted">${producto.concentracion}</small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-${this.getColorPorDias(diasVencimiento)}">
                        ${diasVencimiento} días
                    </span>
                    <br><small class="text-muted">${lote.fechaVencimiento}</small>
                </td>
                <td>${lote.cantidadActual} / ${lote.cantidadInicial}</td>
                <td>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar ${this.getProgressColor(lote.cantidadActual, lote.cantidadInicial)}" 
                             style="width: ${(lote.cantidadActual/lote.cantidadInicial)*100}%">
                            ${Math.round((lote.cantidadActual/lote.cantidadInicial)*100)}%
                        </div>
                    </div>
                </td>
                <td>${lote.ubicacion}</td>
                <td>
                    <span class="badge bg-${lote.requiereRefrigeracion ? 'warning' : 'secondary'}">
                        ${lote.requiereRefrigeracion ? 'Refrigerado' : 'Ambiente'}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="sistemaTrazabilidad.verLote(${lote.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="sistemaTrazabilidad.movimientoLote(${lote.id}, 'entrada')">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="sistemaTrazabilidad.movimientoLote(${lote.id}, 'salida')">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(fila);
        });

        // Inicializar DataTables
        if ($.fn.DataTable) {
            $('#tabla-lotes').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                order: [[3, 'desc']]
            });
        }
    }

    calcularDiasVencimiento(fechaVencimiento) {
        const hoy = new Date();
        const vencimiento = new Date(fechaVencimiento);
        const diferencia = vencimiento - hoy;
        return Math.ceil(diferencia / (1000 * 60 * 60 * 24));
    }

    getColorPorDias(dias) {
        if (dias <= 30) return 'danger';
        if (dias <= 90) return 'warning';
        if (dias <= 180) return 'info';
        return 'success';
    }

    getProgressColor(actual, inicial) {
        const porcentaje = (actual / inicial) * 100;
        if (porcentaje <= 10) return 'bg-danger';
        if (porcentaje <= 25) return 'bg-warning';
        if (porcentaje <= 50) return 'bg-info';
        return 'bg-success';
    }

    inicializarEventos() {
        // Filtros de productos
        document.getElementById('filtro-categoria')?.addEventListener('change', () => {
            this.filtrarProductos();
        });

        document.getElementById('filtro-requiere-temperatura')?.addEventListener('change', () => {
            this.filtrarProductos();
        });

        document.getElementById('buscar-producto')?.addEventListener('input', (e) => {
            this.buscarProductos(e.target.value);
        });

        // Botones de acción
        document.getElementById('btn-nuevo-producto')?.addEventListener('click', () => {
            this.mostrarFormularioProducto();
        });

        document.getElementById('btn-nuevo-lote')?.addEventListener('click', () => {
            this.mostrarFormularioLote();
        });

        document.getElementById('btn-nuevo-movimiento')?.addEventListener('click', () => {
            this.mostrarFormularioMovimiento();
        });

        document.getElementById('btn-generar-reporte')?.addEventListener('click', () => {
            this.generarReporte();
        });

        // Scanner de códigos
        document.getElementById('btn-scan-codigo')?.addEventListener('click', () => {
            this.inicializarScanner();
        });
    }

    configurarFiltros() {
        // Llenar select de categorías
        const selectCategoria = document.getElementById('filtro-categoria');
        if (selectCategoria) {
            const categorias = [...new Set(this.productos.map(p => p.categoria))];
            selectCategoria.innerHTML = '<option value="">Todas las categorías</option>' +
                categorias.map(cat => `<option value="${cat}">${cat}</option>`).join('');
        }
    }

    filtrarProductos() {
        const categoria = document.getElementById('filtro-categoria').value;
        const requiereTemp = document.getElementById('filtro-requiere-temperatura').value;
        
        let productosFiltrados = this.productos;
        
        if (categoria) {
            productosFiltrados = productosFiltrados.filter(p => p.categoria === categoria);
        }
        
        if (requiereTemp !== '') {
            productosFiltrados = productosFiltrados.filter(p => 
                p.requiereTemperatura === (requiereTemp === 'true')
            );
        }
        
        this.mostrarProductosFiltrados(productosFiltrados);
    }

    buscarProductos(termino) {
        const productosFiltrados = this.productos.filter(producto =>
            producto.nombre.toLowerCase().includes(termino.toLowerCase()) ||
            producto.codigo.toLowerCase().includes(termino.toLowerCase()) ||
            producto.laboratorio.toLowerCase().includes(termino.toLowerCase())
        );
        
        this.mostrarProductosFiltrados(productosFiltrados);
    }

    mostrarProductosFiltrados(productos) {
        const tbody = document.querySelector('#tabla-productos tbody');
        tbody.innerHTML = '';

        productos.forEach(producto => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${producto.codigo}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div>
                            <strong>${producto.nombre}</strong><br>
                            <small class="text-muted">${producto.concentracion} - ${producto.formaFarmaceutica}</small>
                        </div>
                    </div>
                </td>
                <td>${producto.laboratorio}</td>
                <td>
                    <span class="badge bg-${producto.requiereTemperatura ? 'warning' : 'secondary'}">
                        ${producto.requiereTemperatura ? '<i class="fas fa-snowflake me-1"></i>Refrigerado' : 'Ambiente'}
                    </span>
                </td>
                <td>
                    <span class="badge bg-${producto.stockActual <= producto.stockMinimo ? 'danger' : 'success'}">
                        ${producto.stockActual} unidades
                    </span>
                </td>
                <td>${producto.ubicacion}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="sistemaTrazabilidad.verProducto(${producto.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="sistemaTrazabilidad.editarProducto(${producto.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-info" onclick="sistemaTrazabilidad.verLotes(${producto.id})">
                            <i class="fas fa-boxes"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(fila);
        });
    }

    inicializarScanner() {
        // Simular scanner de códigos de barras
        const scannerInput = document.getElementById('scanner-input');
        if (scannerInput) {
            scannerInput.focus();
            
            scannerInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    const codigo = e.target.value.trim();
                    if (codigo) {
                        this.procesarCodigoEscaneado(codigo);
                        e.target.value = '';
                    }
                }
            });
        }
    }

    procesarCodigoEscaneado(codigo) {
        const producto = this.productos.find(p => 
            p.codigo === codigo || p.registroSanitario === codigo
        );
        
        const lote = this.lotes.find(l => l.numeroLote === codigo);
        
        if (producto) {
            this.mostrarInfoProducto(producto);
        } else if (lote) {
            this.mostrarInfoLote(lote);
        } else {
            Swal.fire({
                title: 'Código No Encontrado',
                text: `El código "${codigo}" no está registrado en el sistema.`,
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
        }
    }

    mostrarInfoProducto(producto) {
        const lotesProducto = this.lotes.filter(l => l.productoId === producto.id);
        
        Swal.fire({
            title: 'Información del Producto',
            html: `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Código:</strong> ${producto.codigo}</p>
                        <p><strong>Nombre:</strong> ${producto.nombre}</p>
                        <p><strong>Laboratorio:</strong> ${producto.laboratorio}</p>
                        <p><strong>Concentración:</strong> ${producto.concentracion}</p>
                        <p><strong>Forma:</strong> ${producto.formaFarmaceutica}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Stock Actual:</strong> ${producto.stockActual}</p>
                        <p><strong>Ubicación:</strong> ${producto.ubicacion}</p>
                        <p><strong>Vencimiento:</strong> ${producto.fechaVencimiento}</p>
                        <p><strong>Lotes:</strong> ${lotesProducto.length}</p>
                        <p><strong>Temperatura:</strong> ${producto.requiereTemperatura ? 'Refrigerado' : 'Ambiente'}</p>
                    </div>
                </div>
                <hr>
                <h6>Lotes Disponibles:</h6>
                ${lotesProducto.map(lote => `
                    <div class="alert alert-info">
                        <strong>${lote.numeroLote}</strong> - ${lote.cantidadActual} unidades - Vence: ${lote.fechaVencimiento}
                    </div>
                `).join('')}
            `,
            width: '800px',
            confirmButtonText: 'Cerrar'
        });
    }

    mostrarInfoLote(lote) {
        const producto = this.productos.find(p => p.id === lote.productoId);
        
        Swal.fire({
            title: 'Información del Lote',
            html: `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Número de Lote:</strong> ${lote.numeroLote}</p>
                        <p><strong>Producto:</strong> ${producto.nombre}</p>
                        <p><strong>Fabricación:</strong> ${lote.fechaFabricacion}</p>
                        <p><strong>Vencimiento:</strong> ${lote.fechaVencimiento}</p>
                        <p><strong>Cantidad Inicial:</strong> ${lote.cantidadInicial}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Cantidad Actual:</strong> ${lote.cantidadActual}</p>
                        <p><strong>Ubicación:</strong> ${lote.ubicacion}</p>
                        <p><strong>Costo Unitario:</strong> S/ ${lote.costoUnitario}</p>
                        <p><strong>Temperatura:</strong> ${lote.requiereRefrigeracion ? `${lote.temperaturaMinima}°C - ${lote.temperaturaMaxima}°C` : 'Ambiente'}</p>
                        <p><strong>Estado:</strong> <span class="badge bg-success">${lote.estado}</span></p>
                    </div>
                </div>
                <hr>
                <p><strong>Observaciones:</strong> ${lote.observaciones}</p>
                <p><strong>Documentos:</strong></p>
                <ul>
                    ${lote.documentos.map(doc => `<li><a href="#" onclick="sistemaTrazabilidad.verDocumento('${doc}')">${doc}</a></li>`).join('')}
                </ul>
            `,
            width: '800px',
            confirmButtonText: 'Cerrar'
        });
    }

    verProducto(id) {
        const producto = this.productos.find(p => p.id === id);
        if (producto) {
            this.mostrarInfoProducto(producto);
        }
    }

    verLotes(id) {
        const producto = this.productos.find(p => p.id === id);
        const lotesProducto = this.lotes.filter(l => l.productoId === id);
        
        Swal.fire({
            title: `Lotes de ${producto.nombre}`,
            html: `
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Número de Lote</th>
                                <th>Vencimiento</th>
                                <th>Cantidad</th>
                                <th>Ubicación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${lotesProducto.map(lote => `
                                <tr>
                                    <td>${lote.numeroLote}</td>
                                    <td>${lote.fechaVencimiento}</td>
                                    <td>${lote.cantidadActual}</td>
                                    <td>${lote.ubicacion}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="sistemaTrazabilidad.verLote(${lote.id})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `,
            width: '800px',
            confirmButtonText: 'Cerrar'
        });
    }

    verLote(id) {
        const lote = this.lotes.find(l => l.id === id);
        if (lote) {
            this.mostrarInfoLote(lote);
        }
    }

    movimientoLote(loteId, tipo) {
        const lote = this.lotes.find(l => l.id === loteId);
        
        Swal.fire({
            title: `Movimiento de ${tipo === 'entrada' ? 'Entrada' : 'Salida'}`,
            html: `
                <div class="mb-3">
                    <label class="form-label">Lote:</label>
                    <p><strong>${lote.numeroLote}</strong> - ${lote.cantidadActual} unidades disponibles</p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cantidad:</label>
                    <input type="number" id="cantidad-movimiento" class="form-control" 
                           min="1" max="${lote.cantidadActual}" value="1">
                </div>
                <div class="mb-3">
                    <label class="form-label">Motivo:</label>
                    <textarea id="motivo-movimiento" class="form-control" rows="2" 
                              placeholder="Ingrese el motivo del movimiento"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Documento:</label>
                    <input type="text" id="documento-movimiento" class="form-control" 
                           placeholder="Número de documento">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: `Registrar ${tipo === 'entrada' ? 'Entrada' : 'Salida'}`,
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const cantidad = parseInt(document.getElementById('cantidad-movimiento').value);
                const motivo = document.getElementById('motivo-movimiento').value;
                const documento = document.getElementById('documento-movimiento').value;
                
                if (!cantidad || cantidad < 1 || cantidad > lote.cantidadActual) {
                    Swal.showValidationMessage('Cantidad inválida');
                    return false;
                }
                
                if (!motivo.trim()) {
                    Swal.showValidationMessage('El motivo es obligatorio');
                    return false;
                }
                
                return { cantidad, motivo, documento };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.registrarMovimiento(loteId, tipo, result.value);
            }
        });
    }

    registrarMovimiento(loteId, tipo, datos) {
        const lote = this.lotes.find(l => l.id === loteId);
        const nuevoMovimiento = {
            id: this.movimientos.length + 1,
            tipo: tipo,
            productoId: lote.productoId,
            loteId: loteId,
            cantidad: datos.cantidad,
            fecha: new Date().toLocaleString(),
            usuario: 'Usuario Actual',
            razon: datos.motivo,
            documento: datos.documento,
            almacenOrigen: tipo === 'salida' ? lote.almacen : null,
            almacenDestino: tipo === 'entrada' ? lote.almacen : null,
            estado: 'completado',
            observaciones: datos.motivo
        };
        
        // Actualizar cantidad del lote
        if (tipo === 'entrada') {
            lote.cantidadActual += datos.cantidad;
        } else {
            lote.cantidadActual -= datos.cantidad;
        }
        
        // Actualizar stock del producto
        const producto = this.productos.find(p => p.id === lote.productoId);
        if (tipo === 'entrada') {
            producto.stockActual += datos.cantidad;
        } else {
            producto.stockActual -= datos.cantidad;
        }
        
        this.movimientos.push(nuevoMovimiento);
        
        // Recargar tablas
        this.cargarTablaLotes();
        this.cargarTablaProductos();
        
        Swal.fire({
            title: '¡Movimiento Registrado!',
            text: `El movimiento de ${tipo} ha sido registrado exitosamente.`,
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }

    mostrarFormularioProducto() {
        // Implementar formulario de nuevo producto
        Swal.fire({
            title: 'Formulario de Producto',
            text: 'Funcionalidad en desarrollo',
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
    }

    mostrarFormularioLote() {
        // Implementar formulario de nuevo lote
        Swal.fire({
            title: 'Formulario de Lote',
            text: 'Funcionalidad en desarrollo',
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
    }

    mostrarFormularioMovimiento() {
        // Implementar formulario de nuevo movimiento
        Swal.fire({
            title: 'Formulario de Movimiento',
            text: 'Funcionalidad en desarrollo',
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
    }

    generarReporte() {
        const reporte = {
            fecha: new Date(),
            productos: this.productos.length,
            lotesActivos: this.lotes.filter(l => l.estado === 'activo').length,
            productosStockBajo: this.productos.filter(p => p.stockActual <= p.stockMinimo).length,
            productosProximosVencer: this.lotes.filter(l => this.calcularDiasVencimiento(l.fechaVencimiento) <= 30).length,
            movimientosHoy: this.movimientos.filter(m => 
                new Date(m.fecha).toDateString() === new Date().toDateString()
            ).length
        };
        
        // Crear ventana para mostrar reporte
        const ventanaReporte = window.open('', '_blank', 'width=800,height=600');
        ventanaReporte.document.write(`
            <html>
                <head>
                    <title>Reporte de Trazabilidad</title>
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
                        <h1>Reporte de Trazabilidad</h1>
                        <p><strong>Fecha:</strong> ${reporte.fecha.toLocaleString()}</p>
                    </div>
                    
                    <div class="stats">
                        <h3>Resumen Ejecutivo</h3>
                        <p><strong>Total de Productos:</strong> ${reporte.productos}</p>
                        <p><strong>Lotes Activos:</strong> ${reporte.lotesActivos}</p>
                        <p><strong>Productos con Stock Bajo:</strong> ${reporte.productosStockBajo}</p>
                        <p><strong>Productos Próximos a Vencer:</strong> ${reporte.productosProximosVencer}</p>
                        <p><strong>Movimientos Hoy:</strong> ${reporte.movimientosHoy}</p>
                    </div>
                </body>
            </html>
        `);
        ventanaReporte.document.close();
    }

    verDocumento(nombreDoc) {
        Swal.fire({
            title: 'Visualizar Documento',
            text: `Funcionalidad para ver ${nombreDoc}`,
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
    }
}

// Inicializar el sistema de trazabilidad
let sistemaTrazabilidad;

document.addEventListener('DOMContentLoaded', function() {
    sistemaTrazabilidad = new SistemaTrazabilidad();
});

// Exportar para uso global
window.SistemaTrazabilidad = SistemaTrazabilidad;