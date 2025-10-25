// Sistema de Búsqueda de Clientes - Ventas SIFANO
// Búsqueda avanzada y gestión de clientes para farmacia

class BuscadorClientes {
    constructor() {
        this.clientes = [];
        this.filtros = {
            tipo: '',
            estado: '',
            ciudad: '',
            descuentos: false
        };
        this.resultados = [];
        this.clienteSeleccionado = null;
        this.init();
    }

    init() {
        this.cargarDatosDemo();
        this.inicializarEventos();
        this.configurarTablas();
        this.cargarFiltros();
    }

    cargarDatosDemo() {
        // Clientes del sistema
        this.clientes = [
            {
                id: 1,
                nombre: 'Juan Carlos Pérez',
                apellidos: 'Pérez García',
                dni: '12345678',
                ruc: '',
                telefono: '+51-987-654-321',
                email: 'juan.perez@email.com',
                fechaNacimiento: '1985-03-15',
                direccion: 'Av. Principal 123, San Isidro',
                distrito: 'San Isidro',
                ciudad: 'Lima',
                tipo: 'persona_natural',
                estado: 'activo',
                fechaRegistro: '2023-01-15',
                ultimaCompra: '2024-01-25',
                totalCompras: 15,
                montoTotal: 2450.75,
                descuentos: [5, 10], // 5% cliente frecuente, 10% cliente VIP
                categoria: 'frecuente',
                observaciones: 'Cliente muy puntual con pagos',
                medicamentosRecurrentes: ['Paracetamol 500mg', 'Ibuprofeno 400mg'],
                alergias: [],
                historialMedico: []
            },
            {
                id: 2,
                nombre: 'María Elena García',
                apellidos: 'García López',
                dni: '87654321',
                ruc: '',
                telefono: '+51-912-345-678',
                email: 'maria.garcia@email.com',
                fechaNacimiento: '1990-07-22',
                direccion: 'Jr. Salud 456, Miraflores',
                distrito: 'Miraflores',
                ciudad: 'Lima',
                tipo: 'persona_natural',
                estado: 'activo',
                fechaRegistro: '2023-03-20',
                ultimaCompra: '2024-01-24',
                totalCompras: 8,
                montoTotal: 1230.50,
                descuentos: [5], // 5% cliente frecuente
                categoria: 'frecuente',
                observaciones: 'Prefiere medicamentos genéricos',
                medicamentosRecurrentes: ['Omeprazol 20mg'],
                alergias: ['Penicilina'],
                historialMedico: ['Gastritis crónica']
            },
            {
                id: 3,
                nombre: 'Carlos Alberto Rodríguez',
                apellidos: 'Rodríguez Sánchez',
                dni: '11223344',
                ruc: '',
                telefono: '+51-923-456-789',
                email: 'carlos.rodriguez@email.com',
                fechaNacimiento: '1978-11-08',
                direccion: 'Calle Los Olivos 789, La Molina',
                distrito: 'La Molina',
                ciudad: 'Lima',
                tipo: 'persona_natural',
                estado: 'activo',
                fechaRegistro: '2022-11-10',
                ultimaCompra: '2024-01-23',
                totalCompras: 25,
                montoTotal: 3200.25,
                descuentos: [10, 15], // 10% VIP, 15% promoción especial
                categoria: 'vip',
                observaciones: 'Cliente VIP, médico, compras grandes',
                medicamentosRecurrentes: ['Amoxicilina 250mg', 'Loratadina 10mg'],
                alergias: [],
                historialMedico: []
            },
            {
                id: 4,
                nombre: 'Ana Lucia Martínez',
                apellidos: 'Martínez Flores',
                dni: '55667788',
                ruc: '',
                telefono: '+51-934-567-890',
                email: 'ana.martinez@email.com',
                fechaNacimiento: '1992-05-30',
                direccion: 'Av. Universitaria 321, Surco',
                distrito: 'Santiago de Surco',
                ciudad: 'Lima',
                tipo: 'persona_natural',
                estado: 'inactivo',
                fechaRegistro: '2023-06-15',
                ultimaCompra: '2023-12-15',
                totalCompras: 3,
                montoTotal: 180.75,
                descuentos: [],
                categoria: 'ocasional',
                observaciones: 'Cliente ocasional, poca frecuencia',
                medicamentosRecurrentes: [],
                alergias: ['Aspirina'],
                historialMedico: ['Migrña ocasional']
            },
            {
                id: 5,
                nombre: 'Roberto Carlos',
                apellidos: 'López Mendez',
                dni: '99887766',
                ruc: '',
                telefono: '+51-945-678-901',
                email: 'roberto.lopez@email.com',
                fechaNacimiento: '1980-09-12',
                direccion: 'Pasaje Los Jardines 654, San Juan de Lurigancho',
                distrito: 'San Juan de Lurigancho',
                ciudad: 'Lima',
                tipo: 'persona_natural',
                estado: 'activo',
                fechaRegistro: '2023-08-01',
                ultimaCompra: '2024-01-22',
                totalCompras: 12,
                montoTotal: 890.40,
                descuentos: [5], // 5% cliente frecuente
                categoria: 'frecuente',
                observaciones: 'Prefiere medicamentos naturales',
                medicamentosRecurrentes: ['Paracetamol 500mg'],
                alergias: [],
                historialMedico: []
            },
            {
                id: 6,
                nombre: 'Farmacia Integral SAC',
                apellidos: '',
                dni: '',
                ruc: '20123456789',
                telefono: '+51-1-456-7890',
                email: 'compras@farmaciaintegral.com',
                fechaNacimiento: '',
                direccion: 'Av. Industriales 999, Lurín',
                distrito: 'Lurín',
                ciudad: 'Lima',
                tipo: 'persona_juridica',
                estado: 'activo',
                fechaRegistro: '2022-01-20',
                ultimaCompra: '2024-01-20',
                totalCompras: 45,
                montoTotal: 15670.80,
                descuentos: [20], // 20% por compra al por mayor
                categoria: 'mayorista',
                observaciones: 'Cliente mayorista, pagos a 30 días',
                medicamentosRecurrentes: [],
                alergias: [],
                historialMedico: [],
                contacto: 'Juan Pérez - Gerente de Compras'
            }
        ];
    }

    inicializarEventos() {
        // Campo de búsqueda principal
        const inputBusqueda = document.getElementById('busqueda-cliente');
        if (inputBusqueda) {
            inputBusqueda.addEventListener('input', (e) => {
                this.buscarClientes(e.target.value);
            });

            inputBusqueda.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.ejecutarBusqueda();
                }
            });
        }

        // Filtros
        document.getElementById('filtro-tipo-cliente')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        document.getElementById('filtro-estado-cliente')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        document.getElementById('filtro-categoria-cliente')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        document.getElementById('filtro-descuentos')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        // Botones de acción
        document.getElementById('btn-buscar-avanzada')?.addEventListener('click', () => {
            this.mostrarBusquedaAvanzada();
        });

        document.getElementById('btn-nuevo-cliente')?.addEventListener('click', () => {
            this.mostrarFormularioCliente();
        });

        document.getElementById('btn-exportar-clientes')?.addEventListener('click', () => {
            this.exportarClientes();
        });

        document.getElementById('btn-importar-clientes')?.addEventListener('click', () => {
            this.importarClientes();
        });

        document.getElementById('btn-reportes-clientes')?.addEventListener('click', () => {
            this.generarReportes();
        });

        // Búsqueda rápida
        document.getElementById('busqueda-dni')?.addEventListener('input', (e) => {
            this.buscarPorDNI(e.target.value);
        });

        document.getElementById('busqueda-telefono')?.addEventListener('input', (e) => {
            this.buscarPorTelefono(e.target.value);
        });

        document.getElementById('busqueda-email')?.addEventListener('input', (e) => {
            this.buscarPorEmail(e.target.value);
        });
    }

    configurarTablas() {
        // Tabla principal de resultados
        if ($.fn.DataTable) {
            $('#tabla-clientes-resultados').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                columns: [
                    { data: 'nombre' },
                    { data: 'dni_ruc' },
                    { data: 'telefono' },
                    { data: 'email' },
                    { data: 'categoria' },
                    { data: 'totalCompras' },
                    { data: 'montoTotal' },
                    { data: 'estado' },
                    { data: 'acciones', orderable: false }
                ],
                order: [[5, 'desc']]
            });
        }
    }

    cargarFiltros() {
        // Llenar selects de filtros
        const selectTipo = document.getElementById('filtro-tipo-cliente');
        if (selectTipo) {
            selectTipo.innerHTML = '<option value="">Todos los tipos</option>' +
                '<option value="persona_natural">Persona Natural</option>' +
                '<option value="persona_juridica">Persona Jurídica</option>';
        }

        const selectEstado = document.getElementById('filtro-estado-cliente');
        if (selectEstado) {
            selectEstado.innerHTML = '<option value="">Todos los estados</option>' +
                '<option value="activo">Activo</option>' +
                '<option value="inactivo">Inactivo</option>';
        }

        const selectCategoria = document.getElementById('filtro-categoria-cliente');
        if (selectCategoria) {
            selectCategoria.innerHTML = '<option value="">Todas las categorías</option>' +
                '<option value="vip">VIP</option>' +
                '<option value="frecuente">Frecuente</option>' +
                '<option value="ocasional">Ocasional</option>' +
                '<option value="mayorista">Mayorista</option>';
        }
    }

    buscarClientes(termino) {
        if (!termino || termino.length < 2) {
            this.resultados = [];
            this.actualizarTablaResultados();
            return;
        }

        const terminoLower = termino.toLowerCase();
        this.resultados = this.clientes.filter(cliente => 
            cliente.nombre.toLowerCase().includes(terminoLower) ||
            cliente.apellidos.toLowerCase().includes(terminoLower) ||
            (cliente.dni && cliente.dni.includes(termino)) ||
            (cliente.ruc && cliente.ruc.includes(termino)) ||
            cliente.email.toLowerCase().includes(terminoLower) ||
            cliente.telefono.includes(termino) ||
            cliente.direccion.toLowerCase().includes(terminoLower)
        );

        this.actualizarTablaResultados();
    }

    buscarPorDNI(dni) {
        if (!dni || dni.length < 6) return;

        const cliente = this.clientes.find(c => c.dni === dni);
        if (cliente) {
            this.mostrarInfoCliente(cliente);
        } else {
            Swal.fire({
                title: 'Cliente No Encontrado',
                text: `No se encontró ningún cliente con DNI: ${dni}`,
                icon: 'info',
                confirmButtonText: 'Crear Cliente',
                showCancelButton: true,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.mostrarFormularioCliente({ dni: dni });
                }
            });
        }
    }

    buscarPorTelefono(telefono) {
        if (!telefono || telefono.length < 6) return;

        const cliente = this.clientes.find(c => c.telefono.includes(telefono));
        if (cliente) {
            this.mostrarInfoCliente(cliente);
        }
    }

    buscarPorEmail(email) {
        if (!email || email.length < 5) return;

        const cliente = this.clientes.find(c => c.email.toLowerCase() === email.toLowerCase());
        if (cliente) {
            this.mostrarInfoCliente(cliente);
        }
    }

    aplicarFiltros() {
        this.filtros.tipo = document.getElementById('filtro-tipo-cliente')?.value || '';
        this.filtros.estado = document.getElementById('filtro-estado-cliente')?.value || '';
        this.filtros.categoria = document.getElementById('filtro-categoria-cliente')?.value || '';
        this.filtros.descuentos = document.getElementById('filtro-descuentos')?.checked || false;

        let clientesFiltrados = [...this.clientes];

        // Aplicar filtros
        if (this.filtros.tipo) {
            clientesFiltrados = clientesFiltrados.filter(c => c.tipo === this.filtros.tipo);
        }

        if (this.filtros.estado) {
            clientesFiltrados = clientesFiltrados.filter(c => c.estado === this.filtros.estado);
        }

        if (this.filtros.categoria) {
            clientesFiltrados = clientesFiltrados.filter(c => c.categoria === this.filtros.categoria);
        }

        if (this.filtros.descuentos) {
            clientesFiltrados = clientesFiltrados.filter(c => c.descuentos.length > 0);
        }

        this.resultados = clientesFiltrados;
        this.actualizarTablaResultados();
    }

    actualizarTablaResultados() {
        const tbody = document.querySelector('#tabla-clientes-resultados tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        this.resultados.forEach(cliente => {
            const fila = document.createElement('tr');
            const dniRuc = cliente.dni || cliente.ruc;
            const categoriaColor = this.getCategoriaColor(cliente.categoria);
            const estadoColor = this.getEstadoColor(cliente.estado);

            fila.innerHTML = `
                <td>
                    <div>
                        <strong>${cliente.nombre} ${cliente.apellidos}</strong><br>
                        <small class="text-muted">${cliente.tipo === 'persona_juridica' ? 'Razón Social' : 'Cliente'}</small>
                    </div>
                </td>
                <td>
                    <div>
                        <strong>${dniRuc}</strong><br>
                        <small class="text-muted">${cliente.distrito}</small>
                    </div>
                </td>
                <td>${cliente.telefono}</td>
                <td>
                    <div>
                        ${cliente.email}<br>
                        <small class="text-muted">Última compra: ${cliente.ultimaCompra}</small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-${categoriaColor}">
                        ${this.getCategoriaTexto(cliente.categoria)}
                    </span>
                    ${cliente.descuentos.length > 0 ? `<br><small class="text-success">${Math.max(...cliente.descuentos)}% descuento</small>` : ''}
                </td>
                <td>
                    <strong>${cliente.totalCompras}</strong><br>
                    <small class="text-muted">compras</small>
                </td>
                <td>
                    <strong>S/ ${cliente.montoTotal.toFixed(2)}</strong>
                </td>
                <td>
                    <span class="badge bg-${estadoColor}">
                        ${this.getEstadoTexto(cliente.estado)}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="buscadorClientes.verCliente(${cliente.id})" title="Ver">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="buscadorClientes.seleccionarCliente(${cliente.id})" title="Seleccionar">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="buscadorClientes.editarCliente(${cliente.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-info" onclick="buscadorClientes.historialCliente(${cliente.id})" title="Historial">
                            <i class="fas fa-history"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(fila);
        });

        // Actualizar contador de resultados
        const contador = document.getElementById('contador-resultados');
        if (contador) {
            contador.textContent = `Se encontraron ${this.resultados.length} cliente(s)`;
        }
    }

    getCategoriaColor(categoria) {
        const colores = {
            'vip': 'warning',
            'frecuente': 'success',
            'ocasional': 'info',
            'mayorista': 'primary'
        };
        return colores[categoria] || 'secondary';
    }

    getCategoriaTexto(categoria) {
        const textos = {
            'vip': 'VIP',
            'frecuente': 'Frecuente',
            'ocasional': 'Ocasional',
            'mayorista': 'Mayorista'
        };
        return textos[categoria] || categoria;
    }

    getEstadoColor(estado) {
        return estado === 'activo' ? 'success' : 'secondary';
    }

    getEstadoTexto(estado) {
        return estado === 'activo' ? 'Activo' : 'Inactivo';
    }

    ejecutarBusqueda() {
        const termino = document.getElementById('busqueda-cliente').value;
        if (termino && termino.length >= 2) {
            this.buscarClientes(termino);
        }
    }

    mostrarBusquedaAvanzada() {
        Swal.fire({
            title: 'Búsqueda Avanzada',
            html: `
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Nombre/Apellidos</label>
                        <input type="text" id="adv-nombre" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">DNI/RUC</label>
                        <input type="text" id="adv-dni" class="form-control">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" id="adv-telefono" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="text" id="adv-email" class="form-control">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Ciudad</label>
                        <select id="adv-ciudad" class="form-select">
                            <option value="">Todas</option>
                            <option value="Lima">Lima</option>
                            <option value="Arequipa">Arequipa</option>
                            <option value="Trujillo">Trujillo</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Categoría</label>
                        <select id="adv-categoria" class="form-select">
                            <option value="">Todas</option>
                            <option value="vip">VIP</option>
                            <option value="frecuente">Frecuente</option>
                            <option value="ocasional">Ocasional</option>
                            <option value="mayorista">Mayorista</option>
                        </select>
                    </div>
                </div>
            `,
            width: '600px',
            showCancelButton: true,
            confirmButtonText: 'Buscar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const filtros = {
                    nombre: document.getElementById('adv-nombre').value,
                    dni: document.getElementById('adv-dni').value,
                    telefono: document.getElementById('adv-telefono').value,
                    email: document.getElementById('adv-email').value,
                    ciudad: document.getElementById('adv-ciudad').value,
                    categoria: document.getElementById('adv-categoria').value
                };
                return filtros;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.busquedaAvanzada(result.value);
            }
        });
    }

    busquedaAvanzada(filtros) {
        this.resultados = this.clientes.filter(cliente => {
            let coincide = true;

            if (filtros.nombre) {
                const nombreCompleto = `${cliente.nombre} ${cliente.apellidos}`.toLowerCase();
                coincide = coincide && nombreCompleto.includes(filtros.nombre.toLowerCase());
            }

            if (filtros.dni) {
                coincide = coincide && (
                    (cliente.dni && cliente.dni.includes(filtros.dni)) ||
                    (cliente.ruc && cliente.ruc.includes(filtros.dni))
                );
            }

            if (filtros.telefono) {
                coincide = coincide && cliente.telefono.includes(filtros.telefono);
            }

            if (filtros.email) {
                coincide = coincide && cliente.email.toLowerCase().includes(filtros.email.toLowerCase());
            }

            if (filtros.ciudad) {
                coincide = coincide && cliente.ciudad === filtros.ciudad;
            }

            if (filtros.categoria) {
                coincide = coincide && cliente.categoria === filtros.categoria;
            }

            return coincide;
        });

        this.actualizarTablaResultados();

        Swal.fire({
            title: 'Búsqueda Completada',
            text: `Se encontraron ${this.resultados.length} cliente(s) que coinciden con los criterios.`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }

    verCliente(clienteId) {
        const cliente = this.clientes.find(c => c.id === clienteId);
        if (!cliente) return;

        Swal.fire({
            title: `Información del Cliente`,
            html: `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nombre:</strong> ${cliente.nombre} ${cliente.apellidos}</p>
                        <p><strong>DNI/RUC:</strong> ${cliente.dni || cliente.ruc}</p>
                        <p><strong>Teléfono:</strong> ${cliente.telefono}</p>
                        <p><strong>Email:</strong> ${cliente.email}</p>
                        <p><strong>Fecha Nacimiento:</strong> ${cliente.fechaNacimiento || 'No especificada'}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Dirección:</strong> ${cliente.direccion}</p>
                        <p><strong>Distrito:</strong> ${cliente.distrito}</p>
                        <p><strong>Ciudad:</strong> ${cliente.ciudad}</p>
                        <p><strong>Categoría:</strong> ${this.getCategoriaTexto(cliente.categoria)}</p>
                        <p><strong>Estado:</strong> ${this.getEstadoTexto(cliente.estado)}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Total Compras:</strong> ${cliente.totalCompras}</p>
                        <p><strong>Monto Total:</strong> S/ ${cliente.montoTotal.toFixed(2)}</p>
                        <p><strong>Última Compra:</strong> ${cliente.ultimaCompra}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Descuentos:</strong> ${cliente.descuentos.length > 0 ? cliente.descuentos.join(', ') + '%' : 'Ninguno'}</p>
                        <p><strong>Medicamentos Recurrentes:</strong> ${cliente.medicamentosRecurrentes.join(', ') || 'Ninguno'}</p>
                        <p><strong>Alergias:</strong> ${cliente.alergias.join(', ') || 'Ninguna'}</p>
                    </div>
                </div>
                ${cliente.observaciones ? `<hr><p><strong>Observaciones:</strong> ${cliente.observaciones}</p>` : ''}
                ${cliente.historialMedico.length > 0 ? `<p><strong>Historial Médico:</strong> ${cliente.historialMedico.join(', ')}</p>` : ''}
            `,
            width: '800px',
            confirmButtonText: 'Cerrar'
        });
    }

    seleccionarCliente(clienteId) {
        const cliente = this.clientes.find(c => c.id === clienteId);
        if (!cliente) return;

        this.clienteSeleccionado = cliente;

        // Mostrar confirmación
        Swal.fire({
            title: 'Cliente Seleccionado',
            html: `
                <div class="text-center">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">${cliente.nombre} ${cliente.apellidos}</h4>
                    <p class="text-muted">DNI/RUC: ${cliente.dni || cliente.ruc}</p>
                    ${cliente.descuentos.length > 0 ? `<p class="text-success"><strong>Descuento disponible: ${Math.max(...cliente.descuentos)}%</strong></p>` : ''}
                </div>
            `,
            icon: 'success',
            confirmButtonText: 'Usar Cliente',
            showCancelButton: true,
            cancelButtonText: 'Seleccionar Otro'
        }).then((result) => {
            if (result.isConfirmed) {
                // Disparar evento personalizado para que otros módulos puedan usar el cliente seleccionado
                document.dispatchEvent(new CustomEvent('clienteSeleccionado', {
                    detail: { cliente: this.clienteSeleccionado }
                }));

                Swal.fire({
                    title: 'Cliente Confirmado',
                    text: `${cliente.nombre} ${cliente.apellidos} será utilizado en la venta.`,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                this.clienteSeleccionado = null;
            }
        });
    }

    editarCliente(clienteId) {
        const cliente = this.clientes.find(c => c.id === clienteId);
        if (!cliente) return;

        this.mostrarFormularioCliente(cliente);
    }

    mostrarFormularioCliente(cliente = null) {
        const isEdit = cliente !== null;
        const titulo = isEdit ? 'Editar Cliente' : 'Nuevo Cliente';

        Swal.fire({
            title: titulo,
            html: `
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Nombre *</label>
                        <input type="text" id="form-nombre" class="form-control" value="${cliente?.nombre || ''}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellidos</label>
                        <input type="text" id="form-apellidos" class="form-control" value="${cliente?.apellidos || ''}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">DNI</label>
                        <input type="text" id="form-dni" class="form-control" value="${cliente?.dni || ''}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">RUC</label>
                        <input type="text" id="form-ruc" class="form-control" value="${cliente?.ruc || ''}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Teléfono *</label>
                        <input type="text" id="form-telefono" class="form-control" value="${cliente?.telefono || ''}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="text" id="form-email" class="form-control" value="${cliente?.email || ''}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="form-label">Dirección</label>
                        <input type="text" id="form-direccion" class="form-control" value="${cliente?.direccion || ''}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label class="form-label">Tipo</label>
                        <select id="form-tipo" class="form-select">
                            <option value="persona_natural" ${cliente?.tipo === 'persona_natural' ? 'selected' : ''}>Persona Natural</option>
                            <option value="persona_juridica" ${cliente?.tipo === 'persona_juridica' ? 'selected' : ''}>Persona Jurídica</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Categoría</label>
                        <select id="form-categoria" class="form-select">
                            <option value="ocasional" ${cliente?.categoria === 'ocasional' ? 'selected' : ''}>Ocasional</option>
                            <option value="frecuente" ${cliente?.categoria === 'frecuente' ? 'selected' : ''}>Frecuente</option>
                            <option value="vip" ${cliente?.categoria === 'vip' ? 'selected' : ''}>VIP</option>
                            <option value="mayorista" ${cliente?.categoria === 'mayorista' ? 'selected' : ''}>Mayorista</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select id="form-estado" class="form-select">
                            <option value="activo" ${cliente?.estado === 'activo' ? 'selected' : ''}>Activo</option>
                            <option value="inactivo" ${cliente?.estado === 'inactivo' ? 'selected' : ''}>Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="form-label">Observaciones</label>
                        <textarea id="form-observaciones" class="form-control" rows="2">${cliente?.observaciones || ''}</textarea>
                    </div>
                </div>
            `,
            width: '700px',
            showCancelButton: true,
            confirmButtonText: isEdit ? 'Actualizar' : 'Crear',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const datos = {
                    nombre: document.getElementById('form-nombre').value,
                    apellidos: document.getElementById('form-apellidos').value,
                    dni: document.getElementById('form-dni').value,
                    ruc: document.getElementById('form-ruc').value,
                    telefono: document.getElementById('form-telefono').value,
                    email: document.getElementById('form-email').value,
                    direccion: document.getElementById('form-direccion').value,
                    tipo: document.getElementById('form-tipo').value,
                    categoria: document.getElementById('form-categoria').value,
                    estado: document.getElementById('form-estado').value,
                    observaciones: document.getElementById('form-observaciones').value
                };

                if (!datos.nombre || !datos.telefono || !datos.email) {
                    Swal.showValidationMessage('Los campos marcados con * son obligatorios');
                    return false;
                }

                return datos;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (isEdit) {
                    this.actualizarCliente(cliente.id, result.value);
                } else {
                    this.crearCliente(result.value);
                }
            }
        });
    }

    crearCliente(datos) {
        const nuevoCliente = {
            id: this.clientes.length + 1,
            ...datos,
            fechaRegistro: new Date().toISOString().split('T')[0],
            ultimaCompra: '',
            totalCompras: 0,
            montoTotal: 0,
            descuentos: [],
            medicamentosRecurrentes: [],
            alergias: [],
            historialMedico: []
        };

        this.clientes.push(nuevoCliente);

        Swal.fire({
            title: '¡Cliente Creado!',
            text: 'El cliente ha sido registrado exitosamente.',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });

        this.actualizarTablaResultados();
    }

    actualizarCliente(clienteId, datos) {
        const cliente = this.clientes.find(c => c.id === clienteId);
        if (!cliente) return;

        Object.assign(cliente, datos);

        Swal.fire({
            title: '¡Cliente Actualizado!',
            text: 'La información del cliente ha sido actualizada.',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });

        this.actualizarTablaResultados();
    }

    historialCliente(clienteId) {
        const cliente = this.clientes.find(c => c.id === clienteId);
        if (!cliente) return;

        // Simular historial de compras
        const historial = [
            { fecha: '2024-01-25', items: 'Paracetamol x3, Ibuprofeno x2', total: 205.00, estado: 'Completada' },
            { fecha: '2024-01-20', items: 'Amoxicilina x1', total: 80.00, estado: 'Completada' },
            { fecha: '2024-01-15', items: 'Omeprazol x2, Loratadina x1', total: 285.00, estado: 'Completada' },
            { fecha: '2024-01-10', items: 'Paracetamol x5', total: 175.00, estado: 'Completada' }
        ];

        Swal.fire({
            title: `Historial de Compras - ${cliente.nombre}`,
            html: `
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${historial.map(item => `
                                <tr>
                                    <td>${item.fecha}</td>
                                    <td>${item.items}</td>
                                    <td>S/ ${item.total.toFixed(2)}</td>
                                    <td><span class="badge bg-success">${item.estado}</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <p><strong>Total de compras:</strong> ${cliente.totalCompras}</p>
                    <p><strong>Monto total:</strong> S/ ${cliente.montoTotal.toFixed(2)}</p>
                </div>
            `,
            width: '800px',
            confirmButtonText: 'Cerrar'
        });
    }

    exportarClientes() {
        // Generar CSV de clientes
        let csv = 'Nombre,DNI/RUC,Teléfono,Email,Categoría,Estado,Total Compras,Monto Total\n';
        
        this.resultados.forEach(cliente => {
            const dniRuc = cliente.dni || cliente.ruc;
            csv += `${cliente.nombre} ${cliente.apellidos},${dniRuc},${cliente.telefono},${cliente.email},${cliente.categoria},${cliente.estado},${cliente.totalCompras},${cliente.montoTotal}\n`;
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `clientes-${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
        
        Swal.fire({
            title: '¡Exportado!',
            text: 'La lista de clientes ha sido exportada exitosamente.',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }

    importarClientes() {
        Swal.fire({
            title: 'Importar Clientes',
            text: 'Funcionalidad en desarrollo',
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
    }

    generarReportes() {
        const totalClientes = this.clientes.length;
        const clientesActivos = this.clientes.filter(c => c.estado === 'activo').length;
        const clientesVIP = this.clientes.filter(c => c.categoria === 'vip').length;
        const clientesFrecuentes = this.clientes.filter(c => c.categoria === 'frecuente').length;
        const montoTotal = this.clientes.reduce((sum, c) => sum + c.montoTotal, 0);

        // Crear ventana para mostrar reporte
        const ventanaReporte = window.open('', '_blank', 'width=800,height=600');
        ventanaReporte.document.write(`
            <html>
                <head>
                    <title>Reporte de Clientes</title>
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
                        <h1>Reporte de Clientes</h1>
                        <p><strong>Fecha:</strong> ${new Date().toLocaleString()}</p>
                    </div>
                    
                    <div class="stats">
                        <h3>Resumen Ejecutivo</h3>
                        <p><strong>Total de Clientes:</strong> ${totalClientes}</p>
                        <p><strong>Clientes Activos:</strong> ${clientesActivos}</p>
                        <p><strong>Clientes VIP:</strong> ${clientesVIP}</p>
                        <p><strong>Clientes Frecuentes:</strong> ${clientesFrecuentes}</p>
                        <p><strong>Monto Total de Ventas:</strong> S/ ${montoTotal.toFixed(2)}</p>
                    </div>
                    
                    <h3>Clientes por Categoría</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Cantidad</th>
                                <th>Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>VIP</td><td>${clientesVIP}</td><td>${((clientesVIP/totalClientes)*100).toFixed(1)}%</td></tr>
                            <tr><td>Frecuentes</td><td>${clientesFrecuentes}</td><td>${((clientesFrecuentes/totalClientes)*100).toFixed(1)}%</td></tr>
                            <tr><td>Ocasionales</td><td>${this.clientes.filter(c => c.categoria === 'ocasional').length}</td><td>${((this.clientes.filter(c => c.categoria === 'ocasional').length/totalClientes)*100).toFixed(1)}%</td></tr>
                            <tr><td>Mayoristas</td><td>${this.clientes.filter(c => c.categoria === 'mayorista').length}</td><td>${((this.clientes.filter(c => c.categoria === 'mayorista').length/totalClientes)*100).toFixed(1)}%</td></tr>
                        </tbody>
                    </table>
                </body>
            </html>
        `);
        ventanaReporte.document.close();
    }
}

// Inicializar el buscador de clientes
let buscadorClientes;

document.addEventListener('DOMContentLoaded', function() {
    buscadorClientes = new BuscadorClientes();
});

// Exportar para uso global
window.BuscadorClientes = BuscadorClientes;