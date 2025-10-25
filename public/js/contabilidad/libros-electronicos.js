// Sistema de Libros Electrónicos - Contabilidad SIFANO
// Gestión completa de libros contables electrónicos

class LibrosElectronicos {
    constructor() {
        this.libros = {
            diario: [],
            mayor: [],
            balance: [],
            estadoResultados: [],
            flujosEfectivo: [],
            patrimonio: []
        };
        this.cuentas = [];
        this.asientos = [];
        this.periodos = [];
        this.configuracion = {};
        this.init();
    }

    init() {
        this.cargarDatosDemo();
        this.inicializarEventos();
        this.cargarLibroDiario();
        this.cargarLibroMayor();
        this.cargarEstadosFinancieros();
        this.configurarValidaciones();
    }

    cargarDatosDemo() {
        // Plan de cuentas
        this.cuentas = [
            // Activos
            { codigo: '10', nombre: 'EFECTIVO Y EQUIVALENTES DE EFECTIVO', tipo: 'activo', naturaleza: 'deudora' },
            { codigo: '101', nombre: 'Caja', tipo: 'activo', naturaleza: 'deudora' },
            { codigo: '102', nombre: 'Bancos', tipo: 'activo', naturaleza: 'deudora' },
            { codigo: '12', nombre: 'CUENTAS POR COBRAR', tipo: 'activo', naturaleza: 'deudora' },
            { codigo: '121', nombre: 'Cuentas por cobrar - terceros', tipo: 'activo', naturaleza: 'deudora' },
            { codigo: '20', nombre: 'MERCADERÍAS', tipo: 'activo', naturaleza: 'deudora' },
            { codigo: '201', nombre: 'Mercaderías manufacturadas', tipo: 'activo', naturaleza: 'deudora' },
            
            // Pasivos
            { codigo: '40', nombre: 'CUENTAS POR PAGAR', tipo: 'pasivo', naturaleza: 'acreedora' },
            { codigo: '401', nombre: 'Cuentas por pagar - terceros', tipo: 'pasivo', naturaleza: 'acreedora' },
            { codigo: '42', nombre: 'CUENTAS POR PAGAR', tipo: 'pasivo', naturaleza: 'acreedora' },
            { codigo: '421', nombre: 'Efectos por pagar', tipo: 'pasivo', naturaleza: 'acreedora' },
            { codigo: '50', nombre: 'TRIBUTOS', tipo: 'pasivo', naturaleza: 'acreedora' },
            { codigo: '501', nombre: 'IGV', tipo: 'pasivo', naturaleza: 'acreedora' },
            
            // Patrimonio
            { codigo: '50', nombre: 'CAPITAL', tipo: 'patrimonio', naturaleza: 'acreedora' },
            { codigo: '501', nombre: 'Capital social', tipo: 'patrimonio', naturaleza: 'acreedora' },
            
            // Gastos
            { codigo: '60', nombre: 'COMPRAS', tipo: 'gasto', naturaleza: 'deudora' },
            { codigo: '601', nombre: 'Mercaderías', tipo: 'gasto', naturaleza: 'deudora' },
            { codigo: '63', nombre: 'GASTOS SERVICIOS', tipo: 'gasto', naturaleza: 'deudora' },
            { codigo: '631', nombre: 'Servicios públicos', tipo: 'gasto', naturaleza: 'deudora' },
            { codigo: '65', nombre: 'OTROS GASTOS', tipo: 'gasto', naturaleza: 'deudora' },
            { codigo: '651', nombre: 'Pérdidas', tipo: 'gasto', naturaleza: 'deudora' },
            
            // Ingresos
            { codigo: '70', nombre: 'VENTAS', tipo: 'ingreso', naturaleza: 'acreedora' },
            { codigo: '701', nombre: 'Mercaderías', tipo: 'ingreso', naturaleza: 'acreedora' }
        ];

        // Asientos contables de ejemplo
        this.asientos = [
            {
                id: 1,
                numero: '001',
                fecha: '2024-01-25',
                glosa: 'Venta de medicamentos - Ticket 1234',
                debe: [
                    { cuenta: '101', descripcion: 'Caja', debe: 47.06, haber: 0 }
                ],
                haber: [
                    { cuenta: '701', descripcion: 'Ventas', debe: 0, haber: 35.00 },
                    { cuenta: '501', descripcion: 'IGV', debe: 0, haber: 12.06 }
                ],
                usuario: 'Dra. Lopez',
                estado: 'aprobado'
            },
            {
                id: 2,
                numero: '002',
                fecha: '2024-01-25',
                glosa: 'Compra de medicamentos - Factura 001',
                debe: [
                    { cuenta: '201', descripcion: 'Mercaderías', debe: 75.00, haber: 0 },
                    { cuenta: '501', descripcion: 'IGV', debe: 13.50, haber: 0 }
                ],
                haber: [
                    { cuenta: '401', descripcion: 'Cuentas por pagar', debe: 0, haber: 88.50 }
                ],
                usuario: 'Dr. Martinez',
                estado: 'aprobado'
            },
            {
                id: 3,
                numero: '003',
                fecha: '2024-01-25',
                glosa: 'Pago de servicios públicos',
                debe: [
                    { cuenta: '631', descripcion: 'Servicios públicos', debe: 150.00, haber: 0 }
                ],
                haber: [
                    { cuenta: '101', descripcion: 'Caja', debe: 0, haber: 150.00 }
                ],
                usuario: 'Admin',
                estado: 'aprobado'
            }
        ];

        // Cargar libros con los datos
        this.generarLibros();
    }

    generarLibros() {
        // Generar Libro Diario
        this.libros.diario = this.asientos.map(asiento => ({
            fecha: asiento.fecha,
            numero: asiento.numero,
            glosa: asiento.glosa,
            movimientos: [
                ...asiento.debe.map(m => ({ ...m, tipo: 'DEBE' })),
                ...asiento.haber.map(m => ({ ...m, tipo: 'HABER' }))
            ],
            totalDebe: asiento.debe.reduce((sum, m) => sum + m.debe, 0),
            totalHaber: asiento.haber.reduce((sum, m) => sum + m.haber, 0),
            usuario: asiento.usuario,
            estado: asiento.estado
        }));

        // Generar Libro Mayor
        this.libros.mayor = this.generarLibroMayor();
        
        // Generar Balance de Comprobación
        this.libros.balance = this.generarBalanceComprobacion();
        
        // Generar Estados Financieros
        this.libros.estadoResultados = this.generarEstadoResultados();
        this.libros.balanceGeneral = this.generarBalanceGeneral();
    }

    generarLibroMayor() {
        const cuentasMayor = {};
        
        this.asientos.forEach(asiento => {
            [...asiento.debe, ...asiento.haber].forEach(movimiento => {
                if (!cuentasMayor[movimiento.cuenta]) {
                    cuentasMayor[movimiento.cuenta] = {
                        codigo: movimiento.cuenta,
                        nombre: this.obtenerNombreCuenta(movimiento.cuenta),
                        movimientos: [],
                        saldoDebe: 0,
                        saldoHaber: 0
                    };
                }
                
                const movimientoLibro = {
                    fecha: asiento.fecha,
                    numeroAsiento: asiento.numero,
                    glosa: asiento.glosa,
                    debe: movimiento.debe,
                    haber: movimiento.haber
                };
                
                cuentasMayor[movimiento.cuenta].movimientos.push(movimientoLibro);
            });
        });

        // Calcular saldos
        Object.values(cuentasMayor).forEach(cuenta => {
            cuenta.movimientos.forEach(mov => {
                cuenta.saldoDebe += mov.debe;
                cuenta.saldoHaber += mov.haber;
            });
        });

        return Object.values(cuentasMayor);
    }

    generarBalanceComprobacion() {
        return this.libros.mayor.map(cuenta => {
            const saldoDeudor = cuenta.saldoDebe > cuenta.saldoHaber ? 
                cuenta.saldoDebe - cuenta.saldoHaber : 0;
            const saldoAcreedor = cuenta.saldoHaber > cuenta.saldoDebe ? 
                cuenta.saldoHaber - cuenta.saldoDebe : 0;
                
            return {
                codigo: cuenta.codigo,
                nombre: cuenta.nombre,
                saldoDeudor,
                saldoAcreedor,
                movimientos: cuenta.movimientos.length
            };
        }).sort((a, b) => a.codigo.localeCompare(b.codigo));
    }

    generarEstadoResultados() {
        const ingresos = this.libros.mayor.filter(c => 
            this.obtenerTipoCuenta(c.codigo) === 'ingreso'
        );
        
        const gastos = this.libros.mayor.filter(c => 
            this.obtenerTipoCuenta(c.codigo) === 'gasto'
        );

        const totalIngresos = ingresos.reduce((sum, c) => sum + c.saldoHaber - c.saldoDebe, 0);
        const totalGastos = gastos.reduce((sum, c) => sum + c.saldoDebe - c.saldoHaber, 0);

        return {
            ingresos: ingresos.map(c => ({
                codigo: c.codigo,
                nombre: c.nombre,
                monto: c.saldoHaber - c.saldoDebe
            })),
            gastos: gastos.map(c => ({
                codigo: c.codigo,
                nombre: c.nombre,
                monto: c.saldoDebe - c.saldoHaber
            })),
            totalIngresos,
            totalGastos,
            resultadoNeto: totalIngresos - totalGastos
        };
    }

    generarBalanceGeneral() {
        const activos = this.libros.mayor.filter(c => 
            this.obtenerTipoCuenta(c.codigo) === 'activo'
        );
        
        const pasivos = this.libros.mayor.filter(c => 
            this.obtenerTipoCuenta(c.codigo) === 'pasivo'
        );
        
        const patrimonio = this.libros.mayor.filter(c => 
            this.obtenerTipoCuenta(c.codigo) === 'patrimonio'
        );

        const totalActivos = activos.reduce((sum, c) => sum + c.saldoDebe - c.saldoHaber, 0);
        const totalPasivos = pasivos.reduce((sum, c) => sum + c.saldoHaber - c.saldoDebe, 0);
        const totalPatrimonio = patrimonio.reduce((sum, c) => sum + c.saldoHaber - c.saldoDebe, 0);

        return {
            activos: activos.map(c => ({
                codigo: c.codigo,
                nombre: c.nombre,
                monto: c.saldoDebe - c.saldoHaber
            })),
            pasivos: pasivos.map(c => ({
                codigo: c.codigo,
                nombre: c.nombre,
                monto: c.saldoHaber - c.saldoDebe
            })),
            patrimonio: patrimonio.map(c => ({
                codigo: c.codigo,
                nombre: c.nombre,
                monto: c.saldoHaber - c.saldoDebe
            })),
            totalActivos,
            totalPasivos,
            totalPatrimonio,
            totalPasivosPatrimonio: totalPasivos + totalPatrimonio
        };
    }

    obtenerNombreCuenta(codigo) {
        const cuenta = this.cuentas.find(c => c.codigo === codigo);
        return cuenta ? cuenta.nombre : `Cuenta ${codigo}`;
    }

    obtenerTipoCuenta(codigo) {
        const cuenta = this.cuentas.find(c => c.codigo === codigo);
        return cuenta ? cuenta.tipo : 'sin_categoria';
    }

    inicializarEventos() {
        // Navegación entre libros
        document.querySelectorAll('.nav-link[data-libro]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const libro = e.target.dataset.libro;
                this.mostrarLibro(libro);
                this.actualizarNavegacionActiva(libro);
            });
        });

        // Botones de acción
        document.getElementById('btn-nuevo-asiento')?.addEventListener('click', () => {
            this.mostrarFormularioAsiento();
        });

        document.getElementById('btn-exportar-libro')?.addEventListener('click', () => {
            this.exportarLibroActual();
        });

        document.getElementById('btn-imprimir-libro')?.addEventListener('click', () => {
            this.imprimirLibroActual();
        });

        document.getElementById('btn-generar-balances')?.addEventListener('click', () => {
            this.generarBalances();
        });

        document.getElementById('btn-validar-libros')?.addEventListener('click', () => {
            this.validarLibros();
        });

        // Filtros
        document.getElementById('filtro-fecha-desde')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        document.getElementById('filtro-fecha-hasta')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        document.getElementById('filtro-cuenta')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });
    }

    mostrarLibro(tipoLibro) {
        // Ocultar todas las secciones
        document.querySelectorAll('.libro-section').forEach(section => {
            section.style.display = 'none';
        });

        // Mostrar la sección correspondiente
        const seccion = document.getElementById(`seccion-${tipoLibro}`);
        if (seccion) {
            seccion.style.display = 'block';
        }

        // Cargar datos del libro
        switch (tipoLibro) {
            case 'diario':
                this.cargarLibroDiario();
                break;
            case 'mayor':
                this.cargarLibroMayor();
                break;
            case 'balance':
                this.cargarBalanceComprobacion();
                break;
            case 'estado-resultados':
                this.cargarEstadoResultados();
                break;
            case 'balance-general':
                this.cargarBalanceGeneral();
                break;
        }
    }

    actualizarNavegacionActiva(libroActivo) {
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        const linkActivo = document.querySelector(`[data-libro="${libroActivo}"]`);
        if (linkActivo) {
            linkActivo.classList.add('active');
        }
    }

    cargarLibroDiario() {
        const tbody = document.querySelector('#tabla-libro-diario tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        this.libros.diario.forEach(asiento => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${asiento.fecha}</td>
                <td>${asiento.numero}</td>
                <td>${asiento.glosa}</td>
                <td class="text-end">S/ ${asiento.totalDebe.toFixed(2)}</td>
                <td class="text-end">S/ ${asiento.totalHaber.toFixed(2)}</td>
                <td>
                    <span class="badge bg-${asiento.estado === 'aprobado' ? 'success' : 'warning'}">
                        ${asiento.estado}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="librosElectronicos.verAsiento(${asiento.numero})" title="Ver">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="librosElectronicos.editarAsiento(${asiento.numero})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(fila);
        });

        // Actualizar totales
        const totalDebe = this.libros.diario.reduce((sum, a) => sum + a.totalDebe, 0);
        const totalHaber = this.libros.diario.reduce((sum, a) => sum + a.totalHaber, 0);

        document.getElementById('total-debe-diario').textContent = `S/ ${totalDebe.toFixed(2)}`;
        document.getElementById('total-haber-diario').textContent = `S/ ${totalHaber.toFixed(2)}`;
        document.getElementById('diferencia-diario').textContent = `S/ ${Math.abs(totalDebe - totalHaber).toFixed(2)}`;
    }

    cargarLibroMayor() {
        const tbody = document.querySelector('#tabla-libro-mayor tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        this.libros.mayor.forEach(cuenta => {
            const saldoActual = cuenta.saldoDebe - cuenta.saldoHaber;
            const naturalezaCuenta = this.obtenerNaturalezaCuenta(cuenta.codigo);
            const saldoNaturaleza = naturalezaCuenta === 'deudora' ? 
                Math.max(saldoActual, 0) : Math.max(-saldoActual, 0);

            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td><strong>${cuenta.codigo}</strong></td>
                <td>${cuenta.nombre}</td>
                <td class="text-end">S/ ${cuenta.saldoDebe.toFixed(2)}</td>
                <td class="text-end">S/ ${cuenta.saldoHaber.toFixed(2)}</td>
                <td class="text-end">
                    <span class="badge bg-${naturalezaCuenta === 'deudora' ? 'info' : 'warning'}">
                        S/ ${saldoNaturaleza.toFixed(2)}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="librosElectronicos.verMayorCuenta('${cuenta.codigo}')" title="Ver Movimientos">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(fila);
        });

        // Inicializar DataTables si está disponible
        if ($.fn.DataTable) {
            $('#tabla-libro-mayor').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                }
            });
        }
    }

    cargarBalanceComprobacion() {
        const tbody = document.querySelector('#tabla-balance tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        let totalDebe = 0;
        let totalHaber = 0;

        this.libros.balance.forEach(cuenta => {
            totalDebe += cuenta.saldoDeudor;
            totalHaber += cuenta.saldoAcreedor;

            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td><strong>${cuenta.codigo}</strong></td>
                <td>${cuenta.nombre}</td>
                <td class="text-end">S/ ${cuenta.saldoDeudor.toFixed(2)}</td>
                <td class="text-end">S/ ${cuenta.saldoAcreedor.toFixed(2)}</td>
                <td class="text-center">${cuenta.movimientos}</td>
            `;
            tbody.appendChild(fila);
        });

        // Agregar fila de totales
        const filaTotales = document.createElement('tr');
        filaTotales.className = 'table-info';
        filaTotales.innerHTML = `
            <td colspan="2"><strong>TOTALES</strong></td>
            <td class="text-end"><strong>S/ ${totalDebe.toFixed(2)}</strong></td>
            <td class="text-end"><strong>S/ ${totalHaber.toFixed(2)}</strong></td>
            <td class="text-center"><strong>${this.libros.balance.reduce((sum, c) => sum + c.movimientos, 0)}</strong></td>
        `;
        tbody.appendChild(filaTotales);

        // Verificar equilibrio
        const diferencia = Math.abs(totalDebe - totalHaber);
        const equilibrioEl = document.getElementById('equilibrio-balance');
        if (equilibrioEl) {
            equilibrioEl.innerHTML = diferencia < 0.01 ? 
                '<div class="alert alert-success">Los libros están equilibrados</div>' :
                `<div class="alert alert-danger">Diferencia: S/ ${diferencia.toFixed(2)}</div>`;
        }
    }

    cargarEstadoResultados() {
        const datos = this.libros.estadoResultados;
        
        // Llenar tabla de ingresos
        const tbodyIngresos = document.querySelector('#tabla-ingresos tbody');
        if (tbodyIngresos) {
            tbodyIngresos.innerHTML = '';
            datos.ingresos.forEach(ingreso => {
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td><strong>${ingreso.codigo}</strong></td>
                    <td>${ingreso.nombre}</td>
                    <td class="text-end">S/ ${ingreso.monto.toFixed(2)}</td>
                `;
                tbodyIngresos.appendChild(fila);
            });
        }

        // Llenar tabla de gastos
        const tbodyGastos = document.querySelector('#tabla-gastos tbody');
        if (tbodyGastos) {
            tbodyGastos.innerHTML = '';
            datos.gastos.forEach(gasto => {
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td><strong>${gasto.codigo}</strong></td>
                    <td>${gasto.nombre}</td>
                    <td class="text-end">S/ ${gasto.monto.toFixed(2)}</td>
                `;
                tbodyGastos.appendChild(fila);
            });
        }

        // Mostrar totales
        document.getElementById('total-ingresos').textContent = `S/ ${datos.totalIngresos.toFixed(2)}`;
        document.getElementById('total-gastos').textContent = `S/ ${datos.totalGastos.toFixed(2)}`;
        document.getElementById('resultado-neto').textContent = `S/ ${datos.resultadoNeto.toFixed(2)}`;
        
        const resultadoEl = document.getElementById('resultado-neto');
        if (resultadoEl) {
            resultadoEl.className = `h4 mb-0 ${datos.resultadoNeto >= 0 ? 'text-success' : 'text-danger'}`;
        }
    }

    cargarBalanceGeneral() {
        const datos = this.libros.balanceGeneral;
        
        // Llenar activos
        const tbodyActivos = document.querySelector('#tabla-activos tbody');
        if (tbodyActivos) {
            tbodyActivos.innerHTML = '';
            datos.activos.forEach(activo => {
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td><strong>${activo.codigo}</strong></td>
                    <td>${activo.nombre}</td>
                    <td class="text-end">S/ ${activo.monto.toFixed(2)}</td>
                `;
                tbodyActivos.appendChild(fila);
            });
        }

        // Llenar pasivos
        const tbodyPasivos = document.querySelector('#tabla-pasivos tbody');
        if (tbodyPasivos) {
            tbodyPasivos.innerHTML = '';
            datos.pasivos.forEach(pasivo => {
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td><strong>${pasivo.codigo}</strong></td>
                    <td>${pasivo.nombre}</td>
                    <td class="text-end">S/ ${pasivo.monto.toFixed(2)}</td>
                `;
                tbodyPasivos.appendChild(fila);
            });
        }

        // Llenar patrimonio
        const tbodyPatrimonio = document.querySelector('#tabla-patrimonio tbody');
        if (tbodyPatrimonio) {
            tbodyPatrimonio.innerHTML = '';
            datos.patrimonio.forEach(patrimonio => {
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td><strong>${patrimonio.codigo}</strong></td>
                    <td>${patrimonio.nombre}</td>
                    <td class="text-end">S/ ${patrimonio.monto.toFixed(2)}</td>
                `;
                tbodyPatrimonio.appendChild(fila);
            });
        }

        // Mostrar totales
        document.getElementById('total-activos').textContent = `S/ ${datos.totalActivos.toFixed(2)}`;
        document.getElementById('total-pasivos').textContent = `S/ ${datos.totalPasivos.toFixed(2)}`;
        document.getElementById('total-patrimonio').textContent = `S/ ${datos.totalPatrimonio.toFixed(2)}`;
        document.getElementById('total-pasivos-patrimonio').textContent = `S/ ${datos.totalPasivosPatrimonio.toFixed(2)}`;

        // Verificar equilibrio
        const diferencia = Math.abs(datos.totalActivos - datos.totalPasivosPatrimonio);
        const equilibrioEl = document.getElementById('equilibrio-balance-general');
        if (equilibrioEl) {
            equilibrioEl.innerHTML = diferencia < 0.01 ? 
                '<div class="alert alert-success">El balance está equilibrado (Activo = Pasivo + Patrimonio)</div>' :
                `<div class="alert alert-danger">Diferencia: S/ ${diferencia.toFixed(2)}</div>`;
        }
    }

    obtenerNaturalezaCuenta(codigo) {
        const cuenta = this.cuentas.find(c => c.codigo === codigo);
        return cuenta ? cuenta.naturaleza : 'deudora';
    }

    verAsiento(numeroAsiento) {
        const asiento = this.asientos.find(a => a.numero === numeroAsiento);
        if (!asiento) return;

        Swal.fire({
            title: `Asiento Contable N° ${asiento.numero}`,
            html: `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Fecha:</strong> ${asiento.fecha}</p>
                        <p><strong>Glosa:</strong> ${asiento.glosa}</p>
                        <p><strong>Usuario:</strong> ${asiento.usuario}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Estado:</strong> 
                            <span class="badge bg-${asiento.estado === 'aprobado' ? 'success' : 'warning'}">
                                ${asiento.estado}
                            </span>
                        </p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6>DEBE</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${asiento.debe.map(mov => `
                                    <tr>
                                        <td><strong>${mov.cuenta}</strong></td>
                                        <td>${mov.descripcion}</td>
                                        <td class="text-end">S/ ${mov.debe.toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                        <p class="text-end"><strong>Total DEBE: S/ ${asiento.debe.reduce((sum, m) => sum + m.debe, 0).toFixed(2)}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <h6>HABER</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${asiento.haber.map(mov => `
                                    <tr>
                                        <td><strong>${mov.cuenta}</strong></td>
                                        <td>${mov.descripcion}</td>
                                        <td class="text-end">S/ ${mov.haber.toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                        <p class="text-end"><strong>Total HABER: S/ ${asiento.haber.reduce((sum, m) => sum + m.haber, 0).toFixed(2)}</strong></p>
                    </div>
                </div>
            `,
            width: '800px',
            confirmButtonText: 'Cerrar'
        });
    }

    verMayorCuenta(codigoCuenta) {
        const cuenta = this.libros.mayor.find(c => c.codigo === codigoCuenta);
        if (!cuenta) return;

        Swal.fire({
            title: `Libro Mayor - Cuenta ${codigoCuenta}`,
            html: `
                <div class="mb-3">
                    <p><strong>${cuenta.nombre}</strong></p>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Asiento</th>
                                <th>Glosa</th>
                                <th class="text-end">DEBE</th>
                                <th class="text-end">HABER</th>
                                <th class="text-end">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${cuenta.movimientos.map(mov => {
                                const saldo = mov.debe - mov.haber;
                                return `
                                    <tr>
                                        <td>${mov.fecha}</td>
                                        <td>${mov.numeroAsiento}</td>
                                        <td>${mov.glosa}</td>
                                        <td class="text-end">S/ ${mov.debe.toFixed(2)}</td>
                                        <td class="text-end">S/ ${mov.haber.toFixed(2)}</td>
                                        <td class="text-end">S/ ${saldo.toFixed(2)}</td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="3"><strong>TOTALES</strong></td>
                                <td class="text-end"><strong>S/ ${cuenta.saldoDebe.toFixed(2)}</strong></td>
                                <td class="text-end"><strong>S/ ${cuenta.saldoHaber.toFixed(2)}</strong></td>
                                <td class="text-end"><strong>S/ ${(cuenta.saldoDebe - cuenta.saldoHaber).toFixed(2)}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `,
            width: '1000px',
            confirmButtonText: 'Cerrar'
        });
    }

    mostrarFormularioAsiento() {
        const proximoNumero = String(this.asientos.length + 1).padStart(3, '0');
        
        Swal.fire({
            title: 'Nuevo Asiento Contable',
            html: `
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Número</label>
                        <input type="text" id="asiento-numero" class="form-control" value="${proximoNumero}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha</label>
                        <input type="date" id="asiento-fecha" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Glosa</label>
                        <input type="text" id="asiento-glosa" class="form-control" placeholder="Descripción del asiento">
                    </div>
                </div>
                <div class="mb-3">
                    <h6>MOVIMIENTOS CONTABLES</h6>
                    <div id="movimientos-asiento">
                        <!-- Se agregarán dinámicamente -->
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="librosElectronicos.agregarMovimiento()">
                        <i class="fas fa-plus"></i> Agregar Movimiento
                    </button>
                </div>
            `,
            width: '800px',
            showCancelButton: true,
            confirmButtonText: 'Guardar Asiento',
            cancelButtonText: 'Cancelar',
            preShow: () => {
                this.inicializarFormularioAsiento();
            },
            preConfirm: () => {
                return this.validarAsiento();
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.guardarAsiento(result.value);
            }
        });
    }

    inicializarFormularioAsiento() {
        const container = document.getElementById('movimientos-asiento');
        if (container) {
            container.innerHTML = this.generarFilaMovimiento(0);
        }
    }

    generarFilaMovimiento(indice) {
        const cuentasOptions = this.cuentas.map(cuenta => 
            `<option value="${cuenta.codigo}">${cuenta.codigo} - ${cuenta.nombre}</option>`
        ).join('');

        return `
            <div class="row mb-2 movimiento-row" data-indice="${indice}">
                <div class="col-md-3">
                    <select class="form-select form-select-sm cuenta-select">
                        <option value="">Seleccionar cuenta</option>
                        ${cuentasOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm descripcion-input" placeholder="Descripción">
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control form-control-sm debe-input" placeholder="DEBE" step="0.01" min="0">
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control form-control-sm haber-input" placeholder="HABER" step="0.01" min="0">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="librosElectronicos.eliminarMovimiento(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }

    agregarMovimiento() {
        const container = document.getElementById('movimientos-asiento');
        const indice = container.children.length;
        
        const nuevaFila = document.createElement('div');
        nuevaFila.innerHTML = this.generarFilaMovimiento(indice);
        container.appendChild(nuevaFila.firstElementChild);
    }

    eliminarMovimiento(boton) {
        boton.closest('.movimiento-row').remove();
        this.recalcularTotalesAsiento();
    }

    validarAsiento() {
        const movimientos = document.querySelectorAll('.movimiento-row');
        const debe = [];
        const haber = [];

        movimientos.forEach((row, index) => {
            const cuenta = row.querySelector('.cuenta-select').value;
            const descripcion = row.querySelector('.descripcion-input').value;
            const valorDebe = parseFloat(row.querySelector('.debe-input').value) || 0;
            const valorHaber = parseFloat(row.querySelector('.haber-input').value) || 0;

            if (!cuenta) {
                Swal.showValidationMessage(`Fila ${index + 1}: Debe seleccionar una cuenta`);
                return false;
            }

            if (valorDebe === 0 && valorHaber === 0) {
                Swal.showValidationMessage(`Fila ${index + 1}: Debe ingresar un valor en DEBE o HABER`);
                return false;
            }

            if (valorDebe > 0 && valorHaber > 0) {
                Swal.showValidationMessage(`Fila ${index + 1}: No puede tener valores en ambas columnas`);
                return false;
            }

            if (valorDebe > 0) {
                debe.push({ cuenta, descripcion, valor: valorDebe });
            } else {
                haber.push({ cuenta, descripcion, valor: valorHaber });
            }
        });

        const totalDebe = debe.reduce((sum, m) => sum + m.valor, 0);
        const totalHaber = haber.reduce((sum, m) => sum + m.valor, 0);

        if (Math.abs(totalDebe - totalHaber) > 0.01) {
            Swal.showValidationMessage(`El asiento no está equilibrado. Diferencia: S/ ${Math.abs(totalDebe - totalHaber).toFixed(2)}`);
            return false;
        }

        return {
            numero: document.getElementById('asiento-numero').value,
            fecha: document.getElementById('asiento-fecha').value,
            glosa: document.getElementById('asiento-glosa').value,
            debe,
            haber
        };
    }

    guardarAsiento(datosAsiento) {
        const nuevoAsiento = {
            id: this.asientos.length + 1,
            numero: datosAsiento.numero,
            fecha: datosAsiento.fecha,
            glosa: datosAsiento.glosa,
            debe: datosAsiento.debe.map(m => ({
                cuenta: m.cuenta,
                descripcion: m.descripcion || this.obtenerNombreCuenta(m.cuenta),
                debe: m.valor,
                haber: 0
            })),
            haber: datosAsiento.haber.map(m => ({
                cuenta: m.cuenta,
                descripcion: m.descripcion || this.obtenerNombreCuenta(m.cuenta),
                debe: 0,
                haber: m.valor
            })),
            usuario: 'Usuario Actual',
            estado: 'borrador'
        };

        this.asientos.push(nuevoAsiento);
        this.generarLibros();
        this.cargarLibroDiario();

        Swal.fire({
            title: '¡Asiento Guardado!',
            text: 'El asiento contable ha sido registrado exitosamente.',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }

    aplicarFiltros() {
        // TODO: Implementar filtrado de datos
        this.cargarLibroDiario();
    }

    exportarLibroActual() {
        // Determinar qué libro está visible
        const libroVisible = document.querySelector('.libro-section[style*="block"]');
        if (!libroVisible) return;

        const tipoLibro = libroVisible.id.replace('seccion-', '');
        
        // Generar contenido del libro
        let contenido = this.generarContenidoLibro(tipoLibro);
        
        // Crear y descargar archivo
        const blob = new Blob([contenido], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `libro-${tipoLibro}-${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);

        Swal.fire({
            title: '¡Libro Exportado!',
            text: `El libro ${tipoLibro} ha sido exportado exitosamente.`,
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }

    generarContenidoLibro(tipoLibro) {
        switch (tipoLibro) {
            case 'diario':
                return this.generarCSVLibroDiario();
            case 'mayor':
                return this.generarCSVLibroMayor();
            case 'balance':
                return this.generarCSVBalance();
            default:
                return 'Libro no soportado para exportación';
        }
    }

    generarCSVLibroDiario() {
        let csv = 'Fecha,Número,Glosa,DEBE,HABER\n';
        this.libros.diario.forEach(asiento => {
            csv += `${asiento.fecha},${asiento.numero},"${asiento.glosa}",${asiento.totalDebe.toFixed(2)},${asiento.totalHaber.toFixed(2)}\n`;
        });
        return csv;
    }

    generarCSVLibroMayor() {
        let csv = 'Código,Nombre,DEBE,HABER,Saldo\n';
        this.libros.mayor.forEach(cuenta => {
            const saldo = cuenta.saldoDebe - cuenta.saldoHaber;
            csv += `${cuenta.codigo},"${cuenta.nombre}",${cuenta.saldoDebe.toFixed(2)},${cuenta.saldoHaber.toFixed(2)},${saldo.toFixed(2)}\n`;
        });
        return csv;
    }

    generarCSVBalance() {
        let csv = 'Código,Nombre,Saldo Deudor,Saldo Acreedor\n';
        this.libros.balance.forEach(cuenta => {
            csv += `${cuenta.codigo},"${cuenta.nombre}",${cuenta.saldoDeudor.toFixed(2)},${cuenta.saldoAcreedor.toFixed(2)}\n`;
        });
        return csv;
    }

    imprimirLibroActual() {
        // Determinar qué libro está visible
        const libroVisible = document.querySelector('.libro-section[style*="block"]');
        if (!libroVisible) return;

        // Crear ventana de impresión
        const ventanaImpresion = window.open('', '_blank', 'width=800,height=600');
        ventanaImpresion.document.write(`
            <html>
                <head>
                    <title>Libro Contable</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px; }
                        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                        th { background-color: #f0f0f0; font-weight: bold; }
                        .text-end { text-align: right; }
                        @media print { body { margin: 0; } }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>FARMACIA SIFANO</h1>
                        <h2>LIBRO CONTABLE</h2>
                        <p>Fecha de impresión: ${new Date().toLocaleDateString()}</p>
                    </div>
                    ${libroVisible.innerHTML}
                </body>
            </html>
        `);
        ventanaImpresion.document.close();
        ventanaImpresion.print();
    }

    generarBalances() {
        this.generarLibros();
        this.cargarBalanceComprobacion();
        this.cargarEstadoResultados();
        this.cargarBalanceGeneral();

        Swal.fire({
            title: '¡Balances Generados!',
            text: 'Todos los estados financieros han sido actualizados.',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }

    validarLibros() {
        const errores = [];

        // Verificar equilibrio del libro diario
        const totalDebeDiario = this.libros.diario.reduce((sum, a) => sum + a.totalDebe, 0);
        const totalHaberDiario = this.libros.diario.reduce((sum, a) => sum + a.totalHaber, 0);
        
        if (Math.abs(totalDebeDiario - totalHaberDiario) > 0.01) {
            errores.push(`Libro Diario no está equilibrado. Diferencia: S/ ${Math.abs(totalDebeDiario - totalHaberDiario).toFixed(2)}`);
        }

        // Verificar balance de comprobación
        const totalDebeBalance = this.libros.balance.reduce((sum, c) => sum + c.saldoDeudor, 0);
        const totalHaberBalance = this.libros.balance.reduce((sum, c) => sum + c.saldoAcreedor, 0);
        
        if (Math.abs(totalDebeBalance - totalHaberBalance) > 0.01) {
            errores.push(`Balance de Comprobación no está equilibrado. Diferencia: S/ ${Math.abs(totalDebeBalance - totalHaberBalance).toFixed(2)}`);
        }

        // Verificar balance general
        const datosBG = this.libros.balanceGeneral;
        const diferenciaBG = Math.abs(datosBG.totalActivos - datosBG.totalPasivosPatrimonio);
        
        if (diferenciaBG > 0.01) {
            errores.push(`Balance General no está equilibrado. Diferencia: S/ ${diferenciaBG.toFixed(2)}`);
        }

        if (errores.length === 0) {
            Swal.fire({
                title: 'Validación Exitosa',
                text: 'Todos los libros están correctamente equilibrados.',
                icon: 'success',
                confirmButtonText: 'Excelente'
            });
        } else {
            Swal.fire({
                title: 'Errores de Validación',
                html: errores.map(error => `<div class="alert alert-danger">${error}</div>`).join(''),
                icon: 'error',
                width: '600px',
                confirmButtonText: 'Corregir Errores'
            });
        }
    }

    configurarValidaciones() {
        // Configurar validaciones en tiempo real para los formularios
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('debe-input') || e.target.classList.contains('haber-input')) {
                this.validarMovimiento(e.target);
            }
        });
    }

    validarMovimiento(input) {
        const row = input.closest('.movimiento-row');
        const debeInput = row.querySelector('.debe-input');
        const haberInput = row.querySelector('.haber-input');

        if (input === debeInput && input.value > 0) {
            haberInput.value = '';
            haberInput.disabled = true;
        } else if (input === haberInput && input.value > 0) {
            debeInput.value = '';
            debeInput.disabled = true;
        } else {
            debeInput.disabled = false;
            haberInput.disabled = false;
        }
    }
}

// Inicializar el sistema de libros electrónicos
let librosElectronicos;

document.addEventListener('DOMContentLoaded', function() {
    librosElectronicos = new LibrosElectronicos();
});

// Exportar para uso global
window.LibrosElectronicos = LibrosElectronicos;