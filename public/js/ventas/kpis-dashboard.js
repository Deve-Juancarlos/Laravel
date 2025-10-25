// Dashboard de KPIs - Ventas SIFANO
// Dashboard completo con indicadores de rendimiento de ventas

class DashboardKPIs {
    constructor() {
        this.datos = {
            ventas: [],
            productos: [],
            clientes: [],
            vendedores: [],
            categorias: [],
            periodos: []
        };
        this.metricas = {};
        this.filtros = {
            fechaInicio: '',
            fechaFin: '',
            vendedor: '',
            categoria: '',
            ciudad: ''
        };
        this.init();
    }

    init() {
        this.cargarDatosDemo();
        this.inicializarEventos();
        this.cargarDashboard();
        this.inicializarGraficos();
        this.configurarActualizacionAuto();
    }

    cargarDatosDemo() {
        // Ventas históricas
        this.datos.ventas = this.generarVentasDemo();
        this.datos.productos = this.generarProductosDemo();
        this.datos.clientes = this.generarClientesDemo();
        this.datos.vendedores = this.generarVendedoresDemo();
        this.datos.categorias = this.generarCategoriasDemo();

        // Calcular métricas
        this.calcularMetricas();
    }

    generarVentasDemo() {
        const ventas = [];
        const productos = this.generarProductosDemo();
        const clientes = this.generarClientesDemo();
        const vendedores = this.generarVendedoresDemo();

        // Generar ventas de los últimos 90 días
        const hoy = new Date();
        for (let i = 0; i < 90; i++) {
            const fecha = new Date(hoy);
            fecha.setDate(fecha.getDate() - i);
            
            // Entre 5 y 20 ventas por día
            const ventasPorDia = Math.floor(Math.random() * 15) + 5;
            
            for (let j = 0; j < ventasPorDia; j++) {
                const venta = {
                    id: ventas.length + 1,
                    fecha: fecha.toISOString().split('T')[0],
                    hora: `${String(Math.floor(Math.random() * 12) + 8).padStart(2, '0')}:${String(Math.floor(Math.random() * 60)).padStart(2, '0')}`,
                    cliente: clientes[Math.floor(Math.random() * clientes.length)].id,
                    vendedor: vendedores[Math.floor(Math.random() * vendedores.length)].id,
                    items: this.generarItemsVenta(productos),
                    subtotal: 0,
                    descuento: 0,
                    impuesto: 0,
                    total: 0,
                    formaPago: ['efectivo', 'tarjeta', 'transferencia'][Math.floor(Math.random() * 3)],
                    estado: 'completada'
                };

                // Calcular totales
                venta.subtotal = venta.items.reduce((sum, item) => sum + (item.cantidad * item.precio), 0);
                venta.descuento = venta.subtotal * (Math.random() * 0.1); // Hasta 10% descuento
                venta.impuesto = (venta.subtotal - venta.descuento) * 0.18; // 18% IGV
                venta.total = venta.subtotal - venta.descuento + venta.impuesto;

                ventas.push(venta);
            }
        }

        return ventas;
    }

    generarItemsVenta(productos) {
        const items = [];
        const numItems = Math.floor(Math.random() * 4) + 1; // 1 a 4 items por venta
        
        for (let i = 0; i < numItems; i++) {
            const producto = productos[Math.floor(Math.random() * productos.length)];
            items.push({
                productoId: producto.id,
                producto: producto.nombre,
                cantidad: Math.floor(Math.random() * 3) + 1,
                precio: producto.precio,
                categoria: producto.categoria
            });
        }
        
        return items;
    }

    generarProductosDemo() {
        return [
            { id: 1, nombre: 'Paracetamol 500mg', categoria: 'Analgésicos', precio: 0.35 },
            { id: 2, nombre: 'Amoxicilina 250mg', categoria: 'Antibióticos', precio: 0.80 },
            { id: 3, nombre: 'Ibuprofeno 400mg', categoria: 'Antiinflamatorios', precio: 0.50 },
            { id: 4, nombre: 'Omeprazol 20mg', categoria: 'Gastrointestinales', precio: 1.20 },
            { id: 5, nombre: 'Loratadina 10mg', categoria: 'Antihistamínicos', precio: 0.45 },
            { id: 6, nombre: 'Aspirina 100mg', categoria: 'Analgésicos', precio: 0.25 },
            { id: 7, nombre: 'Dexametasona 4mg', categoria: 'Corticoides', precio: 1.50 },
            { id: 8, nombre: 'Naproxeno 250mg', categoria: 'Antiinflamatorios', precio: 0.65 }
        ];
    }

    generarClientesDemo() {
        return [
            { id: 1, nombre: 'Juan Pérez', ciudad: 'Lima' },
            { id: 2, nombre: 'María García', ciudad: 'Arequipa' },
            { id: 3, nombre: 'Carlos López', ciudad: 'Trujillo' },
            { id: 4, nombre: 'Ana Martínez', ciudad: 'Lima' },
            { id: 5, nombre: 'Luis Rodríguez', ciudad: 'Chiclayo' },
            { id: 6, nombre: 'Carmen Silva', ciudad: 'Lima' },
            { id: 7, nombre: 'Pedro Jiménez', ciudad: 'Arequipa' },
            { id: 8, nombre: 'Sofia Torres', ciudad: 'Trujillo' }
        ];
    }

    generarVendedoresDemo() {
        return [
            { id: 1, nombre: 'Dra. Lopez', turno: 'mañana' },
            { id: 2, nombre: 'Dr. Martinez', turno: 'tarde' },
            { id: 3, nombre: 'Dra. Garcia', turno: 'mañana' },
            { id: 4, nombre: 'Dr. Rodriguez', turno: 'tarde' }
        ];
    }

    generarCategoriasDemo() {
        return [
            { nombre: 'Analgésicos', color: '#FF6384' },
            { nombre: 'Antibióticos', color: '#36A2EB' },
            { nombre: 'Antiinflamatorios', color: '#FFCE56' },
            { nombre: 'Gastrointestinales', color: '#4BC0C0' },
            { nombre: 'Antihistamínicos', color: '#9966FF' },
            { nombre: 'Corticoides', color: '#FF9F40' }
        ];
    }

    calcularMetricas() {
        const ventasCompletadas = this.datos.ventas.filter(v => v.estado === 'completada');
        
        this.metricas = {
            // Ventas diarias
            ventasHoy: ventasCompletadas.filter(v => v.fecha === new Date().toISOString().split('T')[0]).length,
            ventasAyer: ventasCompletadas.filter(v => v.fecha === this.getFechaAyer()).length,
            
            // Montos
            montoHoy: ventasCompletadas.filter(v => v.fecha === new Date().toISOString().split('T')[0])
                .reduce((sum, v) => sum + v.total, 0),
            montoAyer: ventasCompletadas.filter(v => v.fecha === this.getFechaAyer())
                .reduce((sum, v) => sum + v.total, 0),
            
            // Productos más vendidos
            productosMasVendidos: this.calcularProductosMasVendidos(),
            
            // Ventas por categoría
            ventasPorCategoria: this.calcularVentasPorCategoria(),
            
            // Ventas por vendedor
            ventasPorVendedor: this.calcularVentasPorVendedor(),
            
            // Ventas por forma de pago
            ventasPorFormaPago: this.calcularVentasPorFormaPago(),
            
            // Tendencias
            tendenciaVentas: this.calcularTendenciaVentas(),
            tendenciaMontos: this.calcularTendenciaMontos(),
            
            // Clientes
            clientesAtendidosHoy: this.calcularClientesAtendidos(),
            
            // Top clientes
            topClientes: this.calcularTopClientes()
        };
    }

    getFechaAyer() {
        const ayer = new Date();
        ayer.setDate(ayer.getDate() - 1);
        return ayer.toISOString().split('T')[0];
    }

    calcularProductosMasVendidos() {
        const productosVendidos = {};
        
        this.datos.ventas.forEach(venta => {
            venta.items.forEach(item => {
                if (!productosVendidos[item.productoId]) {
                    productosVendidos[item.productoId] = {
                        id: item.productoId,
                        nombre: item.producto,
                        categoria: item.categoria,
                        cantidad: 0,
                        ingresos: 0
                    };
                }
                productosVendidos[item.productoId].cantidad += item.cantidad;
                productosVendidos[item.productoId].ingresos += item.cantidad * item.precio;
            });
        });

        return Object.values(productosVendidos)
            .sort((a, b) => b.cantidad - a.cantidad)
            .slice(0, 10);
    }

    calcularVentasPorCategoria() {
        const ventasPorCategoria = {};
        
        this.datos.ventas.forEach(venta => {
            venta.items.forEach(item => {
                if (!ventasPorCategoria[item.categoria]) {
                    ventasPorCategoria[item.categoria] = { cantidad: 0, ingresos: 0 };
                }
                ventasPorCategoria[item.categoria].cantidad += item.cantidad;
                ventasPorCategoria[item.categoria].ingresos += item.cantidad * item.precio;
            });
        });

        return Object.entries(ventasPorCategoria).map(([categoria, datos]) => ({
            categoria,
            ...datos
        })).sort((a, b) => b.ingresos - a.ingresos);
    }

    calcularVentasPorVendedor() {
        const ventasPorVendedor = {};
        
        this.datos.ventas.forEach(venta => {
            if (!ventasPorVendedor[venta.vendedor]) {
                ventasPorVendedor[venta.vendedor] = {
                    cantidad: 0,
                    ingresos: 0,
                    nombre: this.datos.vendedores.find(v => v.id === venta.vendedor)?.nombre
                };
            }
            ventasPorVendedor[venta.vendedor].cantidad++;
            ventasPorVendedor[venta.vendedor].ingresos += venta.total;
        });

        return Object.entries(ventasPorVendedor).map(([vendedorId, datos]) => ({
            vendedorId: parseInt(vendedorId),
            ...datos
        })).sort((a, b) => b.ingresos - a.ingresos);
    }

    calcularVentasPorFormaPago() {
        const ventasPorFormaPago = {};
        
        this.datos.ventas.forEach(venta => {
            if (!ventasPorFormaPago[venta.formaPago]) {
                ventasPorFormaPago[venta.formaPago] = { cantidad: 0, ingresos: 0 };
            }
            ventasPorFormaPago[venta.formaPago].cantidad++;
            ventasPorFormaPago[venta.formaPago].ingresos += venta.total;
        });

        return Object.entries(ventasPorFormaPago).map(([forma, datos]) => ({
            forma,
            ...datos
        })).sort((a, b) => b.ingresos - a.ingresos);
    }

    calcularTendenciaVentas() {
        const ultimos30Dias = [];
        const hoy = new Date();
        
        for (let i = 29; i >= 0; i--) {
            const fecha = new Date(hoy);
            fecha.setDate(fecha.getDate() - i);
            const fechaStr = fecha.toISOString().split('T')[0];
            
            const ventasDia = this.datos.ventas.filter(v => v.fecha === fechaStr && v.estado === 'completada').length;
            ultimos30Dias.push({ fecha: fechaStr, ventas: ventasDia });
        }
        
        return ultimos30Dias;
    }

    calcularTendenciaMontos() {
        const ultimos30Dias = [];
        const hoy = new Date();
        
        for (let i = 29; i >= 0; i--) {
            const fecha = new Date(hoy);
            fecha.setDate(fecha.getDate() - i);
            const fechaStr = fecha.toISOString().split('T')[0];
            
            const montoDia = this.datos.ventas
                .filter(v => v.fecha === fechaStr && v.estado === 'completada')
                .reduce((sum, v) => sum + v.total, 0);
            ultimos30Dias.push({ fecha: fechaStr, monto: montoDia });
        }
        
        return ultimos30Dias;
    }

    calcularClientesAtendidos() {
        const clientesHoy = this.datos.ventas
            .filter(v => v.fecha === new Date().toISOString().split('T')[0] && v.estado === 'completada')
            .map(v => v.cliente);
        
        return new Set(clientesHoy).size;
    }

    calcularTopClientes() {
        const clientesMonto = {};
        
        this.datos.ventas.forEach(venta => {
            if (!clientesMonto[venta.cliente]) {
                clientesMonto[venta.cliente] = {
                    clienteId: venta.cliente,
                    nombre: this.datos.clientes.find(c => c.id === venta.cliente)?.nombre,
                    cantidad: 0,
                    monto: 0
                };
            }
            clientesMonto[venta.cliente].cantidad++;
            clientesMonto[venta.cliente].monto += venta.total;
        });

        return Object.values(clientesMonto)
            .sort((a, b) => b.monto - a.monto)
            .slice(0, 5);
    }

    inicializarEventos() {
        // Filtros de fecha
        document.getElementById('fecha-inicio')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        document.getElementById('fecha-fin')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        // Filtro de vendedor
        document.getElementById('filtro-vendedor')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        // Filtro de categoría
        document.getElementById('filtro-categoria')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        // Botones de exportación
        document.getElementById('btn-exportar-dashboard')?.addEventListener('click', () => {
            this.exportarDashboard();
        });

        document.getElementById('btn-refrescar-dashboard')?.addEventListener('click', () => {
            this.refrescarDashboard();
        });

        document.getElementById('btn-imprimir-dashboard')?.addEventListener('click', () => {
            this.imprimirDashboard();
        });
    }

    cargarDashboard() {
        // Actualizar tarjetas de métricas
        this.actualizarTarjetasMetricas();
        this.actualizarTablas();
    }

    actualizarTarjetasMetricas() {
        // Ventas hoy vs ayer
        const cardVentas = document.getElementById('card-ventas-hoy');
        if (cardVentas) {
            const crecimiento = this.metricas.ventasAyer > 0 ? 
                ((this.metricas.ventasHoy - this.metricas.ventasAyer) / this.metricas.ventasAyer * 100) : 0;
            
            cardVentas.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Ventas Hoy</h6>
                            <h2 class="card-title">${this.metricas.ventasHoy}</h2>
                            <small class="text-muted">Ayer: ${this.metricas.ventasAyer}</small>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-shopping-cart text-primary" style="font-size: 2rem;"></i>
                            <div class="mt-2">
                                <span class="badge bg-${crecimiento >= 0 ? 'success' : 'danger'}">
                                    <i class="fas fa-${crecimiento >= 0 ? 'arrow-up' : 'arrow-down'}"></i>
                                    ${Math.abs(crecimiento).toFixed(1)}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Monto hoy vs ayer
        const cardMonto = document.getElementById('card-monto-hoy');
        if (cardMonto) {
            const crecimientoMonto = this.metricas.montoAyer > 0 ? 
                ((this.metricas.montoHoy - this.metricas.montoAyer) / this.metricas.montoAyer * 100) : 0;
            
            cardMonto.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Ingresos Hoy</h6>
                            <h2 class="card-title">S/ ${this.metricas.montoHoy.toFixed(2)}</h2>
                            <small class="text-muted">Ayer: S/ ${this.metricas.montoAyer.toFixed(2)}</small>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-dollar-sign text-success" style="font-size: 2rem;"></i>
                            <div class="mt-2">
                                <span class="badge bg-${crecimientoMonto >= 0 ? 'success' : 'danger'}">
                                    <i class="fas fa-${crecimientoMonto >= 0 ? 'arrow-up' : 'arrow-down'}"></i>
                                    ${Math.abs(crecimientoMonto).toFixed(1)}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Clientes atendidos hoy
        const cardClientes = document.getElementById('card-clientes-hoy');
        if (cardClientes) {
            cardClientes.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Clientes Atendidos</h6>
                            <h2 class="card-title">${this.metricas.clientesAtendidosHoy}</h2>
                            <small class="text-muted">Total registrados</small>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-users text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            `;
        }

        // Ticket promedio
        const ticketPromedio = this.metricas.ventasHoy > 0 ? this.metricas.montoHoy / this.metricas.ventasHoy : 0;
        const cardTicket = document.getElementById('card-ticket-promedio');
        if (cardTicket) {
            cardTicket.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Ticket Promedio</h6>
                            <h2 class="card-title">S/ ${ticketPromedio.toFixed(2)}</h2>
                            <small class="text-muted">Por venta</small>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-receipt text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    actualizarTablas() {
        // Tabla de productos más vendidos
        this.actualizarTablaProductosVendidos();
        
        // Tabla de vendedores
        this.actualizarTablaVendedores();
        
        // Tabla de top clientes
        this.actualizarTablaTopClientes();
    }

    actualizarTablaProductosVendidos() {
        const tbody = document.querySelector('#tabla-productos-vendidos tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        this.metricas.productosMasVendidos.slice(0, 5).forEach((producto, index) => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td><span class="badge bg-primary">${index + 1}</span></td>
                <td>
                    <div>
                        <strong>${producto.nombre}</strong><br>
                        <small class="text-muted">${producto.categoria}</small>
                    </div>
                </td>
                <td><span class="badge bg-success">${producto.cantidad}</span></td>
                <td><strong>S/ ${producto.ingresos.toFixed(2)}</strong></td>
            `;
            tbody.appendChild(fila);
        });
    }

    actualizarTablaVendedores() {
        const tbody = document.querySelector('#tabla-vendedores tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        this.metricas.ventasPorVendedor.forEach((vendedor, index) => {
            const fila = document.createElement('tr');
            const ticketPromedio = vendedor.cantidad > 0 ? vendedor.ingresos / vendedor.cantidad : 0;
            
            fila.innerHTML = `
                <td><span class="badge bg-primary">${index + 1}</span></td>
                <td><strong>${vendedor.nombre}</strong></td>
                <td><span class="badge bg-info">${vendedor.cantidad}</span></td>
                <td><strong>S/ ${vendedor.ingresos.toFixed(2)}</strong></td>
                <td>S/ ${ticketPromedio.toFixed(2)}</td>
            `;
            tbody.appendChild(fila);
        });
    }

    actualizarTablaTopClientes() {
        const tbody = document.querySelector('#tabla-top-clientes tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        this.metricas.topClientes.forEach((cliente, index) => {
            const fila = document.createElement('tr');
            const ticketPromedio = cliente.cantidad > 0 ? cliente.monto / cliente.cantidad : 0;
            
            fila.innerHTML = `
                <td><span class="badge bg-primary">${index + 1}</span></td>
                <td><strong>${cliente.nombre}</strong></td>
                <td><span class="badge bg-info">${cliente.cantidad}</span></td>
                <td><strong>S/ ${cliente.monto.toFixed(2)}</strong></td>
                <td>S/ ${ticketPromedio.toFixed(2)}</td>
            `;
            tbody.appendChild(fila);
        });
    }

    inicializarGraficos() {
        this.inicializarGraficoVentas();
        this.inicializarGraficoCategorias();
        this.inicializarGraficoFormasPago();
        this.inicializarGraficoTendencia();
    }

    inicializarGraficoVentas() {
        const ctx = document.getElementById('grafico-ventas-vendedores');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: this.metricas.ventasPorVendedor.map(v => v.nombre),
                datasets: [{
                    label: 'Ventas',
                    data: this.metricas.ventasPorVendedor.map(v => v.cantidad),
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545'],
                    borderColor: ['#0056b3', '#1e7e34', '#d39e00', '#bd2130'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Ventas por Vendedor'
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Número de Ventas'
                        }
                    }
                }
            }
        });
    }

    inicializarGraficoCategorias() {
        const ctx = document.getElementById('grafico-categorias');
        if (!ctx) return;

        const categorias = this.metricas.ventasPorCategoria.slice(0, 6);
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: categorias.map(c => c.categoria),
                datasets: [{
                    data: categorias.map(c => c.ingresos),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', 
                        '#4BC0C0', '#9966FF', '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Ingresos por Categoría'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    inicializarGraficoFormasPago() {
        const ctx = document.getElementById('grafico-formas-pago');
        if (!ctx) return;

        const formasPago = this.metricas.ventasPorFormaPago;
        
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: formasPago.map(fp => fp.forma),
                datasets: [{
                    data: formasPago.map(fp => fp.cantidad),
                    backgroundColor: ['#28a745', '#007bff', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Ventas por Forma de Pago'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    inicializarGraficoTendencia() {
        const ctx = document.getElementById('grafico-tendencia');
        if (!ctx) return;

        const ultimos7Dias = this.metricas.tendenciaVentas.slice(-7);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ultimos7Dias.map(d => new Date(d.fecha).toLocaleDateString()),
                datasets: [{
                    label: 'Ventas',
                    data: ultimos7Dias.map(d => d.ventas),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Tendencia de Ventas - Últimos 7 días'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Número de Ventas'
                        }
                    }
                }
            }
        });
    }

    aplicarFiltros() {
        this.filtros.fechaInicio = document.getElementById('fecha-inicio')?.value || '';
        this.filtros.fechaFin = document.getElementById('fecha-fin')?.value || '';
        this.filtros.vendedor = document.getElementById('filtro-vendedor')?.value || '';
        this.filtros.categoria = document.getElementById('filtro-categoria')?.value || '';

        // TODO: Implementar filtrado de datos
        this.cargarDashboard();
        this.inicializarGraficos();
    }

    configurarActualizacionAuto() {
        // Actualizar cada 5 minutos
        setInterval(() => {
            this.refrescarDashboard();
        }, 300000);
    }

    refrescarDashboard() {
        // Simular carga de nuevos datos
        this.calcularMetricas();
        this.cargarDashboard();
        this.inicializarGraficos();

        Swal.fire({
            title: 'Dashboard Actualizado',
            text: 'Los datos han sido actualizados exitosamente.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }

    exportarDashboard() {
        // Crear reporte en PDF o Excel
        const reporte = {
            fecha: new Date(),
            metricas: this.metricas,
            filtros: this.filtros
        };

        // Crear ventana para mostrar reporte
        const ventanaReporte = window.open('', '_blank', 'width=800,height=600');
        ventanaReporte.document.write(`
            <html>
                <head>
                    <title>Reporte de KPIs - Ventas</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
                        .metricas { display: flex; flex-wrap: wrap; gap: 20px; margin: 20px 0; }
                        .metrica { background: #f8f9fa; padding: 15px; border-radius: 8px; flex: 1; min-width: 200px; }
                        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Reporte de KPIs - Ventas</h1>
                        <p><strong>Fecha:</strong> ${reporte.fecha.toLocaleString()}</p>
                        <p><strong>Farmacia SIFANO</strong></p>
                    </div>
                    
                    <div class="metricas">
                        <div class="metrica">
                            <h3>${this.metricas.ventasHoy}</h3>
                            <p>Ventas Hoy</p>
                        </div>
                        <div class="metrica">
                            <h3>S/ ${this.metricas.montoHoy.toFixed(2)}</h3>
                            <p>Ingresos Hoy</p>
                        </div>
                        <div class="metrica">
                            <h3>${this.metricas.clientesAtendidosHoy}</h3>
                            <p>Clientes Atendidos</p>
                        </div>
                        <div class="metrica">
                            <h3>S/ ${(this.metricas.montoHoy / this.metricas.ventasHoy).toFixed(2)}</h3>
                            <p>Ticket Promedio</p>
                        </div>
                    </div>
                    
                    <h3>Productos Más Vendidos</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${this.metricas.productosMasVendidos.slice(0, 10).map(p => `
                                <tr>
                                    <td>${p.nombre}</td>
                                    <td>${p.cantidad}</td>
                                    <td>S/ ${p.ingresos.toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </body>
            </html>
        `);
        ventanaReporte.document.close();
    }

    imprimirDashboard() {
        window.print();
    }
}

// Inicializar el dashboard de KPIs
let dashboardKPIs;

document.addEventListener('DOMContentLoaded', function() {
    dashboardKPIs = new DashboardKPIs();
});

// Exportar para uso global
window.DashboardKPIs = DashboardKPIs;