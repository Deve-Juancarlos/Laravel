// Sistema de Reportes SUNAT - Contabilidad SIFANO
// Generación de reportes fiscales y tributarios para SUNAT

class ReportesSUNAT {
    constructor() {
        this.reportes = {
            PDT: [],
            Planilla: [],
            DJAnuales: [],
            LibrosElectronicos: [],
           Formato8: [],
            Formato14: [],
            Formato17: [],
            Formato351: [],
            Formato710: []
        };
        this.configuracion = {};
        this.periodos = [];
        this.validaciones = [];
        this.init();
    }

    init() {
        this.cargarDatosDemo();
        this.inicializarEventos();
        this.cargarDashboardSUNAT();
        this.cargarReportesGenerados();
        this.configurarCalendarioTributario();
    }

    cargarDatosDemo() {
        // Configuración de la empresa
        this.configuracion = {
            ruc: '20123456789',
            razonSocial: 'Farmacia SIFANO S.A.C.',
            direccion: 'Av. Salud 789, Lima',
            actividadPrincipal: '4721 - Venta al por menor de productos farmacéuticos',
            regimenTributario: 'Régimen General',
            condicionIIBB: 'AgentRetenedor',
            agenteRetencion: 'SI',
            agentePercepcion: 'NO',
            EMUA: 'SI',
            mucoa: 'NO',
            agenteRetencion2PorCiento: 'SI'
        };

        // Períodos tributarios
        this.periodos = [
            { 
                periodo: '202401', 
                año: 2024, 
                mes: 1, 
                nombre: 'Enero 2024', 
                fechaVencimiento: '2024-02-15', 
                estado: 'presentado',
                fechaPresentacion: '2024-02-10'
            },
            { 
                periodo: '202402', 
                año: 2024, 
                mes: 2, 
                nombre: 'Febrero 2024', 
                fechaVencimiento: '2024-03-15', 
                estado: 'presentado',
                fechaPresentacion: '2024-03-12'
            },
            { 
                periodo: '202403', 
                año: 2024, 
                mes: 3, 
                nombre: 'Marzo 2024', 
                fechaVencimiento: '2024-04-15', 
                estado: 'pendiente',
                fechaPresentacion: null
            }
        ];

        // Generar reportes de ejemplo
        this.generarReportesDemo();
    }

    generarReportesDemo() {
        // Generar datos para cada tipo de reporte
        this.generarPDT();
        this.generarPlanilla();
        this.generarLibrosElectronicos();
        this.generarFormatos();
    }

    generarPDT() {
        // PDT 621 - Declaracion Jurada informativa
        this.reportes.PDT.push({
            tipo: 'PDT621',
            periodo: '202403',
            nombre: 'Declaración Jurada Informativa',
            descripcion: 'PDT - Formulario N° 621 - Declaración Jurada Informativa Mensual',
            estado: 'generado',
            fechaGeneracion: '2024-03-01',
            fechaVencimiento: '2024-04-15',
            estadoPresentacion: 'pendiente',
            archivos: [
                { nombre: 'PDT621_202403.txt', tipo: 'archivo', tamaño: '2.5 KB' },
                { nombre: 'PDT621_202403.pdf', tipo: 'reporte', tamaño: '125 KB' }
            ],
            datos: {
                totalIngresos: 125000.00,
                totalEgresos: 89000.00,
                igv: 28350.00,
                retenciones: 2100.00,
                numeroOperaciones: 1250
            }
        });

        // PDT 1673 - Formato Virtual
        this.reportes.PDT.push({
            tipo: 'PDT1673',
            periodo: '202403',
            nombre: 'Formulario Virtual - IGV',
            descripcion: 'PDT - Formulario N° 1673 - Declaración mensual del IGV',
            estado: 'generado',
            fechaGeneracion: '2024-03-01',
            fechaVencimiento: '2024-04-15',
            estadoPresentacion: 'pendiente',
            archivos: [
                { nombre: 'PDT1673_202403.txt', tipo: 'archivo', tamaño: '3.1 KB' },
                { nombre: 'PDT1673_202403.pdf', tipo: 'reporte', tamaño: '95 KB' }
            ],
            datos: {
                igvVentas: 22500.00,
                igvCompras: 15840.00,
                igvNeto: 6660.00,
                creditoFiscal: 15840.00
            }
        });
    }

    generarPlanilla() {
        // Planilla Electrónica
        this.reportes.Planilla.push({
            tipo: 'PLE',
            periodo: '202403',
            nombre: 'Planilla Electrónica',
            descripcion: 'Planilla Electrónica - Formato 5.1',
            estado: 'generado',
            fechaGeneracion: '2024-03-01',
            fechaVencimiento: '2024-03-31',
            estadoPresentacion: 'pendiente',
            archivos: [
                { nombre: 'PLE_5.1_202403.txt', tipo: 'archivo', tamaño: '4.2 KB' }
            ],
            datos: {
                numeroTrabajadores: 8,
                totalRemuneraciones: 28000.00,
                totalDescuentos: 8400.00,
                totalAportes: 8400.00,
                netoPagar: 19600.00
            }
        });
    }

    generarLibrosElectronicos() {
        // Libros Electrónicos
        this.reportes.LibrosElectronicos.push({
            tipo: 'Diario',
            periodo: '202403',
            nombre: 'Libro Diario Electrónico',
            descripcion: 'Formato 3.1 - Libro Diario Electrónico',
            estado: 'generado',
            fechaGeneracion: '2024-03-01',
            fechaVencimiento: '2024-04-15',
            estadoPresentacion: 'pendiente',
            archivos: [
                { nombre: 'LE_Diario_3.1_202403.txt', tipo: 'archivo', tamaño: '15.7 KB' }
            ],
            datos: {
                totalAsientos: 1250,
                totalMovimientos: 2500,
                fechaInicio: '2024-03-01',
                fechaFin: '2024-03-31'
            }
        });

        this.reportes.LibrosElectronicos.push({
            tipo: 'Mayor',
            periodo: '202403',
            nombre: 'Libro Mayor Electrónico',
            descripcion: 'Formato 3.2 - Libro Mayor Electrónico',
            estado: 'generado',
            fechaGeneracion: '2024-03-01',
            fechaVencimiento: '2024-04-15',
            estadoPresentacion: 'pendiente',
            archivos: [
                { nombre: 'LE_Mayor_3.2_202403.txt', tipo: 'archivo', tamaño: '8.9 KB' }
            ],
            datos: {
                totalCuentas: 45,
                fechaInicio: '2024-03-01',
                fechaFin: '2024-03-31'
            }
        });

        this.reportes.LibrosElectronicos.push({
            tipo: 'Inventarios',
            periodo: '202403',
            nombre: 'Libro de Inventarios Electrónico',
            descripcion: 'Formato 3.3 - Libro de Inventarios Electrónico',
            estado: 'generado',
            fechaGeneracion: '2024-03-01',
            fechaVencimiento: '2024-04-15',
            estadoPresentacion: 'pendiente',
            archivos: [
                { nombre: 'LE_Inventarios_3.3_202403.txt', tipo: 'archivo', tamaño: '12.3 KB' }
            ],
            datos: {
                totalProductos: 150,
                valorInventario: 45000.00,
                fechaInventario: '2024-03-31'
            }
        });
    }

    generarFormatos() {
        // Formato 8 - Registro de Bienes
        this.reportes.Formato8.push({
            tipo: 'Formato8',
            periodo: '202403',
            nombre: 'Registro de Bienes',
            descripcion: 'Formato 8 - Registro de Bienes de Activo Fijo',
            estado: 'generado',
            fechaGeneracion: '2024-03-01',
            fechaVencimiento: '2024-04-15',
            estadoPresentacion: 'pendiente',
            archivos: [
                { nombre: 'F8_202403.txt', tipo: 'archivo', tamaño: '5.1 KB' }
            ],
            datos: {
                totalActivos: 15,
                valorTotalActivos: 75000.00,
                depreciacionMes: 1250.00
            }
        });

        // Formato 14 - Honorarios
        this.reportes.Formato14.push({
            tipo: 'Formato14',
            periodo: '202403',
            nombre: 'Honorarios',
            descripcion: 'Formato 14 - Registro de Retenciones de Renta de Cuarta Categoría',
            estado: 'generado',
            fechaGeneracion: '2024-03-01',
            fechaVencimiento: '2024-04-15',
            estadoPresentacion: 'pendiente',
            archivos: [
                { nombre: 'F14_202403.txt', tipo: 'archivo', tamaño: '2.8 KB' }
            ],
            datos: {
                totalHonorarios: 8500.00,
                totalRetenciones: 850.00,
                numeroProveedores: 3
            }
        });

        // Formato 17 - Casas de Cambio
        this.reportes.Formato17.push({
            tipo: 'Formato17',
            periodo: '202403',
            nombre: 'Operaciones en Moneda Extranjera',
            descripcion: 'Formato 17 - Declaración de Operaciones en Moneda Extranjera',
            estado: 'no_aplicado',
            fechaGeneracion: null,
            fechaVencimiento: '2024-04-15',
            estadoPresentacion: 'no_aplicado',
            archivos: [],
            datos: {
                mensaje: 'No se realizaron operaciones en moneda extranjera'
            }
        });

        // Formato 351 - Comprobantes de Pago
        this.reportes.Formato351.push({
            tipo: 'Formato351',
            periodo: '202403',
            nombre: 'Comprobantes de Pago',
            descripcion: 'Formato 351 - Reporte de Comprobantes de Pago Emitidos',
            estado: 'generado',
            fechaGeneracion: '2024-03-01',
            fechaVencimiento: '2024-04-15',
            estadoPresentacion: 'pendiente',
            archivos: [
                { nombre: 'F351_202403.txt', tipo: 'archivo', tamaño: '28.5 KB' }
            ],
            datos: {
                totalComprobantes: 1250,
                totalVentas: 125000.00,
                totalIGV: 22500.00
            }
        });

        // Formato 710 - Empresas del Sistema Financiero
        this.reportes.Formato710.push({
            tipo: 'Formato710',
            periodo: '202403',
            nombre: 'Operaciones Financieras',
            descripcion: 'Formato 710 - Empresas del Sistema Financiero',
            estado: 'generado',
            fechaGeneracion: '2024-03-01',
            fechaVencimiento: '2024-04-15',
            estadoPresentacion: 'pendiente',
            archivos: [
                { nombre: 'F710_202403.txt', tipo: 'archivo', tamaño: '1.7 KB' }
            ],
            datos: {
                numeroCuentas: 2,
                saldoCaja: 25000.00,
                saldoBancos: 150000.00
            }
        });
    }

    inicializarEventos() {
        // Navegación entre tipos de reportes
        document.querySelectorAll('.nav-link[data-reporte]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tipo = e.target.dataset.reporte;
                this.mostrarTipoReporte(tipo);
                this.actualizarNavegacionActiva(tipo);
            });
        });

        // Botones de acción
        document.getElementById('btn-nuevo-reporte')?.addEventListener('click', () => {
            this.mostrarFormularioReporte();
        });

        document.getElementById('btn-generar-pdt')?.addEventListener('click', () => {
            this.generarPDT();
        });

        document.getElementById('btn-generar-libros')?.addEventListener('click', () => {
            this.generarLibrosElectronicos();
        });

        document.getElementById('btn-validar-datos')?.addEventListener('click', () => {
            this.validarDatosSUNAT();
        });

        document.getElementById('btn-calendario-tributario')?.addEventListener('click', () => {
            this.mostrarCalendarioTributario();
        });

        document.getElementById('btn-exportar-periodo')?.addEventListener('click', () => {
            this.exportarPeriodoActual();
        });

        document.getElementById('btn-presentar-renta')?.addEventListener('click', () => {
            this.presentarDeclaracionRenta();
        });

        // Filtros
        document.getElementById('filtro-periodo-sunat')?.addEventListener('change', () => {
            this.aplicarFiltrosReporte();
        });

        document.getElementById('filtro-estado-sunat')?.addEventListener('change', () => {
            this.aplicarFiltrosReporte();
        });

        document.getElementById('filtro-tipo-sunat')?.addEventListener('change', () => {
            this.aplicarFiltrosReporte();
        });
    }

    cargarDashboardSUNAT() {
        // Actualizar estadísticas del dashboard
        const reportesPendientes = this.contarReportesPorEstado('pendiente');
        const reportesGenerados = this.contarReportesPorEstado('generado');
        const reportesPresentados = this.contarReportesPorEstado('presentado');
        const totalArchivos = this.contarTotalArchivos();

        document.getElementById('reportes-pendientes').textContent = reportesPendientes;
        document.getElementById('reportes-generados').textContent = reportesGenerados;
        document.getElementById('reportes-presentados').textContent = reportesPresentados;
        document.getElementById('total-archivos').textContent = totalArchivos;

        // Actualizar gráfico de estado de reportes
        this.inicializarGraficoEstadoReportes();
    }

    cargarReportesGenerados() {
        this.mostrarTipoReporte('PDT');
    }

    contarReportesPorEstado(estado) {
        let total = 0;
        Object.values(this.reportes).forEach(categoria => {
            total += categoria.filter(r => r.estado === estado || r.estadoPresentacion === estado).length;
        });
        return total;
    }

    contarTotalArchivos() {
        let total = 0;
        Object.values(this.reportes).forEach(categoria => {
            categoria.forEach(reporte => {
                total += reporte.archivos.length;
            });
        });
        return total;
    }

    mostrarTipoReporte(tipoReporte) {
        // Ocultar todas las secciones
        document.querySelectorAll('.reporte-section').forEach(section => {
            section.style.display = 'none';
        });

        // Mostrar la sección correspondiente
        const seccion = document.getElementById(`seccion-${tipoReporte.toLowerCase()}`);
        if (seccion) {
            seccion.style.display = 'block';
        }

        // Cargar datos del reporte
        this.cargarTablaReportes(tipoReporte);
    }

    actualizarNavegacionActiva(tipoActivo) {
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        const linkActivo = document.querySelector(`[data-reporte="${tipoActivo}"]`);
        if (linkActivo) {
            linkActivo.classList.add('active');
        }
    }

    cargarTablaReportes(tipoReporte) {
        const reportes = this.reportes[tipoReporte] || [];
        const tbody = document.querySelector(`#tabla-reportes-${tipoReporte.toLowerCase()} tbody`);
        
        if (!tbody) return;

        tbody.innerHTML = '';

        reportes.forEach((reporte, index) => {
            const fila = document.createElement('tr');
            const estadoColor = this.getEstadoColor(reporte.estadoPresentacion || reporte.estado);
            
            fila.innerHTML = `
                <td>
                    <strong>${reporte.nombre}</strong><br>
                    <small class="text-muted">${reporte.descripcion}</small>
                </td>
                <td>
                    <span class="badge bg-primary">${reporte.periodo}</span>
                </td>
                <td>
                    <span class="badge bg-${estadoColor}">
                        ${this.getEstadoTexto(reporte.estadoPresentacion || reporte.estado)}
                    </span>
                </td>
                <td>${reporte.fechaVencimiento}</td>
                <td>
                    ${reporte.archivos.map(archivo => `
                        <div class="d-flex align-items-center mb-1">
                            <i class="fas fa-file-${this.getIconoArchivo(archivo.tipo)} me-2"></i>
                            <span class="text-truncate" style="max-width: 150px;" title="${archivo.nombre}">
                                ${archivo.nombre}
                            </span>
                            <small class="text-muted ms-2">${archivo.tamaño}</small>
                        </div>
                    `).join('')}
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="reportesSUNAT.verReporte('${tipoReporte}', ${index})" title="Ver">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="reportesSUNAT.descargarArchivo('${tipoReporte}', ${index})" title="Descargar">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="reportesSUNAT.presentarReporte('${tipoReporte}', ${index})" title="Presentar">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(fila);
        });
    }

    getEstadoColor(estado) {
        const colores = {
            'presentado': 'success',
            'generado': 'info',
            'pendiente': 'warning',
            'no_aplicado': 'secondary',
            'error': 'danger'
        };
        return colores[estado] || 'secondary';
    }

    getEstadoTexto(estado) {
        const textos = {
            'presentado': 'Presentado',
            'generado': 'Generado',
            'pendiente': 'Pendiente',
            'no_aplicado': 'No Aplica',
            'error': 'Error'
        };
        return textos[estado] || estado;
    }

    getIconoArchivo(tipo) {
        const iconos = {
            'archivo': 'text',
            'reporte': 'pdf',
            'validacion': 'check'
        };
        return iconos[tipo] || 'text';
    }

    inicializarGraficoEstadoReportes() {
        const ctx = document.getElementById('grafico-estado-reportes');
        if (!ctx) return;

        const pendientes = this.contarReportesPorEstado('pendiente');
        const generados = this.contarReportesPorEstado('generado');
        const presentados = this.contarReportesPorEstado('presentado');

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pendientes', 'Generados', 'Presentados'],
                datasets: [{
                    data: [pendientes, generados, presentados],
                    backgroundColor: ['#ffc107', '#17a2b8', '#28a745']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Estado de Reportes SUNAT'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    verReporte(tipoReporte, indice) {
        const reportes = this.reportes[tipoReporte] || [];
        const reporte = reportes[indice];
        
        if (!reporte) return;

        let datosHTML = '';
        if (reporte.datos) {
            datosHTML = Object.entries(reporte.datos)
                .map(([key, value]) => `<p><strong>${this.formatKeyName(key)}:</strong> ${value}</p>`)
                .join('');
        }

        Swal.fire({
            title: reporte.nombre,
            html: `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Tipo:</strong> ${reporte.tipo}</p>
                        <p><strong>Período:</strong> ${reporte.periodo}</p>
                        <p><strong>Estado:</strong> 
                            <span class="badge bg-${this.getEstadoColor(reporte.estadoPresentacion || reporte.estado)}">
                                ${this.getEstadoTexto(reporte.estadoPresentacion || reporte.estado)}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fecha de Generación:</strong> ${reporte.fechaGeneracion || 'No generada'}</p>
                        <p><strong>Fecha de Vencimiento:</strong> ${reporte.fechaVencimiento}</p>
                        <p><strong>Archivos:</strong> ${reporte.archivos.length}</p>
                    </div>
                </div>
                ${datosHTML ? `<hr><h6>Datos del Reporte:</h6>${datosHTML}` : ''}
                ${reporte.archivos.length > 0 ? `
                    <hr>
                    <h6>Archivos Generados:</h6>
                    ${reporte.archivos.map(archivo => `
                        <div class="alert alert-info">
                            <i class="fas fa-file-${this.getIconoArchivo(archivo.tipo)} me-2"></i>
                            <strong>${archivo.nombre}</strong> (${archivo.tamaño})
                        </div>
                    `).join('')}
                ` : ''}
            `,
            width: '800px',
            confirmButtonText: 'Cerrar'
        });
    }

    formatKeyName(key) {
        const nombres = {
            'totalIngresos': 'Total Ingresos',
            'totalEgresos': 'Total Egresos',
            'igv': 'IGV',
            'igvVentas': 'IGV Ventas',
            'igvCompras': 'IGV Compras',
            'igvNeto': 'IGV Neto',
            'creditoFiscal': 'Crédito Fiscal',
            'retenciones': 'Retenciones',
            'numeroOperaciones': 'Número de Operaciones',
            'numeroTrabajadores': 'Número de Trabajadores',
            'totalRemuneraciones': 'Total Remuneraciones',
            'totalDescuentos': 'Total Descuentos',
            'totalAportes': 'Total Aportes',
            'netoPagar': 'Neto a Pagar',
            'totalAsientos': 'Total Asientos',
            'totalMovimientos': 'Total Movimientos',
            'totalCuentas': 'Total Cuentas',
            'totalProductos': 'Total Productos',
            'valorInventario': 'Valor del Inventario',
            'totalActivos': 'Total Activos',
            'valorTotalActivos': 'Valor Total Activos',
            'depreciacionMes': 'Depreciación del Mes',
            'totalHonorarios': 'Total Honorarios',
            'totalRetenciones': 'Total Retenciones',
            'numeroProveedores': 'Número de Proveedores',
            'totalComprobantes': 'Total Comprobantes',
            'totalVentas': 'Total Ventas',
            'numeroCuentas': 'Número de Cuentas',
            'saldoCaja': 'Saldo en Caja',
            'saldoBancos': 'Saldo en Bancos'
        };
        return nombres[key] || key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase());
    }

    descargarArchivo(tipoReporte, indice) {
        const reportes = this.reportes[tipoReporte] || [];
        const reporte = reportes[indice];
        
        if (!reporte || reporte.archivos.length === 0) return;

        // Simular descarga de archivo
        const archivo = reporte.archivos[0]; // Descargar el primer archivo
        const contenido = this.generarContenidoArchivo(tipoReporte, indice, archivo);
        
        const blob = new Blob([contenido], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = archivo.nombre;
        a.click();
        window.URL.revokeObjectURL(url);

        Swal.fire({
            title: '¡Archivo Descargado!',
            text: `${archivo.nombre} ha sido descargado exitosamente.`,
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }

    generarContenidoArchivo(tipoReporte, indice, archivo) {
        // Generar contenido simulado según el tipo de archivo
        const reportes = this.reportes[tipoReporte] || [];
        const reporte = reportes[indice];
        
        let contenido = '';
        
        if (archivo.tipo === 'archivo') {
            contenido = this.generarContenidoArchivosSUNAT(tipoReporte, reporte);
        } else if (archivo.tipo === 'reporte') {
            contenido = this.generarContenidoReportePDF(tipoReporte, reporte);
        }
        
        return contenido;
    }

    generarContenidoArchivosSUNAT(tipoReporte, reporte) {
        // Generar contenido específico para archivos SUNAT
        const timestamp = new Date().toISOString();
        
        switch (tipoReporte) {
            case 'PDT':
                return this.generarContenidoPDT(reporte);
            case 'Formato351':
                return this.generarContenidoFormato351(reporte);
            case 'LibrosElectronicos':
                return this.generarContenidoLibroElectronico(reporte);
            default:
                return `CONTENIDO DE ARCHIVO SUNAT - ${tipoReporte}\nPeríodo: ${reporte.periodo}\nGenerado: ${timestamp}\n\nEste es un archivo simulado para demostración.`;
        }
    }

    generarContenidoPDT(reporte) {
        return `PDT_${reporte.tipo}_${reporte.periodo}
1|20123456789|FARMACIA SIFANO S.A.C.|2024-03-31|
2|${reporte.datos.totalIngresos}|${reporte.datos.totalEgresos}|${reporte.datos.igv}|${reporte.datos.retenciones}
3|${reporte.datos.numeroOperaciones}|0|0|0
999|${timestamp}`;
    }

    generarContenidoFormato351(reporte) {
        return `FORMATO_351_${reporte.periodo}
RUC: 20123456789
Razón Social: FARMACIA SIFANO S.A.C.
Período: ${reporte.periodo}
Total Comprobantes: ${reporte.datos.totalComprobantes}
Total Ventas: ${reporte.datos.totalVentas}
Total IGV: ${reporte.datos.totalIGV}`;
    }

    generarContenidoLibroElectronico(reporte) {
        return `LIBRO_ELECTRONICO_${reporte.tipo}_${reporte.periodo}
Campo 1: RUC - 20123456789
Campo 2: Razón Social - FARMACIA SIFANO S.A.C.
Campo 3: Período - ${reporte.periodo}
Campo 4: Fecha Inicio - ${reporte.datos.fechaInicio}
Campo 5: Fecha Fin - ${reporte.datos.fechaFin}
${reporte.tipo === 'Diario' ? `
Campo 6: Total Asientos - ${reporte.datos.totalAsientos}
Campo 7: Total Movimientos - ${reporte.datos.totalMovimientos}
` : ''}`;
    }

    generarContenidoReportePDF(tipoReporte, reporte) {
        return `REPORTE PDF - ${reporte.nombre}
Período: ${reporte.periodo}
Generado: ${new Date().toLocaleString()}

DATOS DEL REPORTE:
${Object.entries(reporte.datos).map(([key, value]) => `${this.formatKeyName(key)}: ${value}`).join('\n')}

Este es un reporte simulado para demostración.`;
    }

    presentarReporte(tipoReporte, indice) {
        const reportes = this.reportes[tipoReporte] || [];
        const reporte = reportes[indice];
        
        if (!reporte) return;

        // Simular presentación a SUNAT
        Swal.fire({
            title: 'Presentar a SUNAT',
            html: `
                <div class="text-center">
                    <i class="fas fa-paper-plane text-primary" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">¿Confirmar presentación?</h4>
                    <p>Está a punto de presentar el siguiente reporte a SUNAT:</p>
                    <p><strong>${reporte.nombre}</strong></p>
                    <p>Período: ${reporte.periodo}</p>
                    <p>Vencimiento: ${reporte.fechaVencimiento}</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Esta acción enviará los datos a SUNAT de forma definitiva.
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Presentar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                this.procesarPresentacion(tipoReporte, indice);
            }
        });
    }

    procesarPresentacion(tipoReporte, indice) {
        // Simular procesamiento de presentación
        const delay = new Promise(resolve => setTimeout(resolve, 2000));
        
        Swal.fire({
            title: 'Procesando presentación...',
            html: '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        delay.then(() => {
            // Actualizar estado del reporte
            const reportes = this.reportes[tipoReporte] || [];
            if (reportes[indice]) {
                reportes[indice].estadoPresentacion = 'presentado';
                reportes[indice].fechaPresentacion = new Date().toISOString().split('T')[0];
            }

            // Recargar tabla
            this.cargarTablaReportes(tipoReporte);
            this.cargarDashboardSUNAT();

            Swal.fire({
                title: '¡Presentación Exitosa!',
                text: `El reporte ${reportes[indice].nombre} ha sido presentado a SUNAT exitosamente.`,
                icon: 'success',
                confirmButtonText: 'Excelente'
            });
        });
    }

    generarPDT() {
        const periodoSeleccionado = document.getElementById('filtro-periodo-sunat')?.value || '202403';
        
        // Verificar si ya existe un PDT para este período
        const pdtExistente = this.reportes.PDT.find(p => p.periodo === periodoSeleccionado);
        if (pdtExistente) {
            Swal.fire({
                title: 'PDT ya existe',
                text: `Ya existe un PDT generado para el período ${periodoSeleccionado}.`,
                icon: 'info',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Simular generación de PDT
        Swal.fire({
            title: 'Generando PDT...',
            html: '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        setTimeout(() => {
            this.generarNuevoPDT(periodoSeleccionado);
            
            Swal.fire({
                title: '¡PDT Generado!',
                text: 'El PDT ha sido generado exitosamente.',
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });

            this.cargarTablaReportes('PDT');
            this.cargarDashboardSUNAT();
        }, 3000);
    }

    generarNuevoPDT(periodo) {
        // Simular datos para el nuevo PDT
        const nuevoPDT = {
            tipo: 'PDT621',
            periodo: periodo,
            nombre: 'Declaración Jurada Informativa',
            descripcion: 'PDT - Formulario N° 621 - Declaración Jurada Informativa Mensual',
            estado: 'generado',
            fechaGeneracion: new Date().toISOString().split('T')[0],
            fechaVencimiento: this.calcularFechaVencimiento(periodo),
            estadoPresentacion: 'pendiente',
            archivos: [
                { nombre: `PDT621_${periodo}.txt`, tipo: 'archivo', tamaño: '2.5 KB' },
                { nombre: `PDT621_${periodo}.pdf`, tipo: 'reporte', tamaño: '125 KB' }
            ],
            datos: {
                totalIngresos: Math.random() * 150000 + 50000,
                totalEgresos: Math.random() * 100000 + 30000,
                igv: Math.random() * 25000 + 15000,
                retenciones: Math.random() * 3000 + 500,
                numeroOperaciones: Math.floor(Math.random() * 500) + 1000
            }
        };

        this.reportes.PDT.push(nuevoPDT);
    }

    calcularFechaVencimiento(periodo) {
        const año = parseInt(periodo.substring(0, 4));
        const mes = parseInt(periodo.substring(4, 6));
        
        // El vencimiento es el 15 del mes siguiente
        const fechaVencimiento = new Date(año, mes, 15);
        return fechaVencimiento.toISOString().split('T')[0];
    }

    generarLibrosElectronicos() {
        const periodoSeleccionado = document.getElementById('filtro-periodo-sunat')?.value || '202403';

        Swal.fire({
            title: 'Generando Libros Electrónicos...',
            html: '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        setTimeout(() => {
            // Simular generación de libros electrónicos
            ['Diario', 'Mayor', 'Inventarios'].forEach(tipo => {
                const libroExistente = this.reportes.LibrosElectronicos.find(l => 
                    l.tipo === tipo && l.periodo === periodoSeleccionado
                );
                
                if (!libroExistente) {
                    this.reportes.LibrosElectronicos.push({
                        tipo: tipo,
                        periodo: periodoSeleccionado,
                        nombre: `Libro ${tipo} Electrónico`,
                        descripcion: `Formato 3.${tipo === 'Diario' ? '1' : tipo === 'Mayor' ? '2' : '3'} - Libro ${tipo} Electrónico`,
                        estado: 'generado',
                        fechaGeneracion: new Date().toISOString().split('T')[0],
                        fechaVencimiento: this.calcularFechaVencimiento(periodoSeleccionado),
                        estadoPresentacion: 'pendiente',
                        archivos: [
                            { nombre: `LE_${tipo}_3.${tipo === 'Diario' ? '1' : tipo === 'Mayor' ? '2' : '3'}_${periodoSeleccionado}.txt`, tipo: 'archivo', tamaño: `${(Math.random() * 20 + 5).toFixed(1)} KB` }
                        ],
                        datos: this.generarDatosLibroElectronico(tipo)
                    });
                }
            });

            Swal.fire({
                title: '¡Libros Generados!',
                text: 'Los libros electrónicos han sido generados exitosamente.',
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });

            this.cargarTablaReportes('LibrosElectronicos');
            this.cargarDashboardSUNAT();
        }, 4000);
    }

    generarDatosLibroElectronico(tipo) {
        switch (tipo) {
            case 'Diario':
                return {
                    totalAsientos: Math.floor(Math.random() * 500) + 1000,
                    totalMovimientos: Math.floor(Math.random() * 1000) + 2000,
                    fechaInicio: '2024-03-01',
                    fechaFin: '2024-03-31'
                };
            case 'Mayor':
                return {
                    totalCuentas: Math.floor(Math.random() * 20) + 40,
                    fechaInicio: '2024-03-01',
                    fechaFin: '2024-03-31'
                };
            case 'Inventarios':
                return {
                    totalProductos: Math.floor(Math.random() * 50) + 100,
                    valorInventario: Math.random() * 30000 + 30000,
                    fechaInventario: '2024-03-31'
                };
            default:
                return {};
        }
    }

    validarDatosSUNAT() {
        Swal.fire({
            title: 'Validando Datos SUNAT...',
            html: '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        setTimeout(() => {
            const errores = [];
            const warnings = [];

            // Simular validaciones
            if (Math.random() > 0.8) {
                errores.push('El PDT presenta inconsistencias en los totales');
            }

            if (Math.random() > 0.7) {
                warnings.push('Algunos comprobantes no tienen información completa');
            }

            if (errores.length === 0 && warnings.length === 0) {
                Swal.fire({
                    title: 'Validación Exitosa',
                    text: 'Todos los datos han sido validados correctamente.',
                    icon: 'success',
                    confirmButtonText: 'Excelente'
                });
            } else {
                let mensajeHTML = '';
                if (errores.length > 0) {
                    mensajeHTML += '<h6 class="text-danger">Errores encontrados:</h6>';
                    errores.forEach(error => {
                        mensajeHTML += `<div class="alert alert-danger">${error}</div>`;
                    });
                }
                if (warnings.length > 0) {
                    mensajeHTML += '<h6 class="text-warning">Advertencias:</h6>';
                    warnings.forEach(warning => {
                        mensajeHTML += `<div class="alert alert-warning">${warning}</div>`;
                    });
                }

                Swal.fire({
                    title: 'Validación Completada',
                    html: mensajeHTML,
                    icon: errores.length > 0 ? 'error' : 'warning',
                    width: '600px',
                    confirmButtonText: 'Entendido'
                });
            }
        }, 3000);
    }

    mostrarCalendarioTributario() {
        let eventosHTML = this.periodos.map(periodo => {
            const diasRestantes = this.calcularDiasRestantes(periodo.fechaVencimiento);
            const color = diasRestantes < 0 ? 'danger' : diasRestantes <= 7 ? 'warning' : 'success';
            
            return `
                <div class="alert alert-${color}">
                    <strong>${periodo.nombre}</strong><br>
                    <small>Vence: ${periodo.fechaVencimiento}</small><br>
                    <small>Días restantes: ${diasRestantes}</small><br>
                    <small>Estado: ${this.getEstadoTexto(periodo.estado)}</small>
                </div>
            `;
        }).join('');

        Swal.fire({
            title: 'Calendario Tributario',
            html: `
                <div class="row">
                    <div class="col-md-12">
                        <h6>Próximos Vencimientos:</h6>
                        ${eventosHTML}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <h6>Obligaciones Mensuales:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> PDT 1673 - IGV</li>
                            <li><i class="fas fa-check text-success"></i> PDT 621 - Declaración Informativa</li>
                            <li><i class="fas fa-check text-success"></i> Planilla Electrónica</li>
                            <li><i class="fas fa-check text-success"></i> Libros Electrónicos</li>
                            <li><i class="fas fa-check text-success"></i> Formato 351</li>
                        </ul>
                    </div>
                </div>
            `,
            width: '600px',
            confirmButtonText: 'Cerrar'
        });
    }

    calcularDiasRestantes(fechaVencimiento) {
        const hoy = new Date();
        const vencimiento = new Date(fechaVencimiento);
        const diferencia = vencimiento - hoy;
        return Math.ceil(diferencia / (1000 * 60 * 60 * 24));
    }

    configurarCalendarioTributario() {
        // Configurar colores según proximidad del vencimiento
        this.actualizarIndicadoresVencimiento();
    }

    actualizarIndicadoresVencimiento() {
        // Actualizar indicadores visuales en el dashboard
        document.querySelectorAll('.vencimiento-indicator').forEach(indicator => {
            const fechaVencimiento = indicator.dataset.fecha;
            const diasRestantes = this.calcularDiasRestantes(fechaVencimiento);
            
            if (diasRestantes < 0) {
                indicator.className = 'badge bg-danger';
                indicator.textContent = 'Vencido';
            } else if (diasRestantes <= 7) {
                indicator.className = 'badge bg-warning';
                indicator.textContent = `${diasRestantes}d restantes`;
            } else {
                indicator.className = 'badge bg-success';
                indicator.textContent = `${diasRestantes}d restantes`;
            }
        });
    }

    aplicarFiltrosReporte() {
        const periodo = document.getElementById('filtro-periodo-sunat')?.value || '';
        const estado = document.getElementById('filtro-estado-sunat')?.value || '';
        const tipo = document.getElementById('filtro-tipo-sunat')?.value || '';

        // TODO: Implementar filtrado real
        this.cargarDashboardSUNAT();
    }

    exportarPeriodoActual() {
        const periodo = document.getElementById('filtro-periodo-sunat')?.value || '202403';
        
        // Recopilar todos los reportes del período
        const reportesPeriodo = {};
        Object.keys(this.reportes).forEach(categoria => {
            reportesPeriodo[categoria] = this.reportes[categoria].filter(r => r.periodo === periodo);
        });

        // Generar reporte consolidado
        const reporteConsolidado = this.generarReporteConsolidado(periodo, reportesPeriodo);
        
        // Descargar archivo
        const blob = new Blob([reporteConsolidado], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `reporte-sunat-${periodo}.txt`;
        a.click();
        window.URL.revokeObjectURL(url);

        Swal.fire({
            title: '¡Período Exportado!',
            text: `Los reportes del período ${periodo} han sido exportados exitosamente.`,
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }

    generarReporteConsolidado(periodo, reportesPeriodo) {
        let contenido = `REPORTE CONSOLIDADO SUNAT - FARMACIA SIFANO S.A.C.\n`;
        contenido += `RUC: ${this.configuracion.ruc}\n`;
        contenido += `Período: ${periodo}\n`;
        contenido += `Generado: ${new Date().toLocaleString()}\n\n`;

        Object.keys(reportesPeriodo).forEach(categoria => {
            if (reportesPeriodo[categoria].length > 0) {
                contenido += `=== ${categoria.toUpperCase()} ===\n`;
                reportesPeriodo[categoria].forEach(reporte => {
                    contenido += `- ${reporte.nombre}: ${this.getEstadoTexto(reporte.estadoPresentacion || reporte.estado)}\n`;
                });
                contenido += '\n';
            }
        });

        return contenido;
    }

    presentarDeclaracionRenta() {
        Swal.fire({
            title: 'Declaración Anual de Renta',
            html: `
                <div class="text-center">
                    <i class="fas fa-file-invoice-dollar text-primary" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Generar Declaración de Renta Anual</h4>
                    <p>Esta funcionalidad generará automáticamente:</p>
                    <ul class="text-start">
                        <li>Formulario Virtual 710</li>
                        <li>Estados Financieros</li>
                        <li>Declaración Jurada Anual</li>
                    </ul>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Se generarán todos los archivos necesarios para la declaración anual.
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Generar Declaración',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.procesarDeclaracionRenta();
            }
        });
    }

    procesarDeclaracionRenta() {
        Swal.fire({
            title: 'Procesando Declaración...',
            html: '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        setTimeout(() => {
            // Simular generación de declaración anual
            const nuevaDeclaracion = {
                tipo: 'DeclaracionAnual',
                año: 2023,
                nombre: 'Declaración de Renta Anual 2023',
                descripcion: 'Declaración anual del Impuesto a la Renta',
                estado: 'generado',
                fechaGeneracion: new Date().toISOString().split('T')[0],
                fechaVencimiento: '2024-03-31',
                estadoPresentacion: 'pendiente',
                archivos: [
                    { nombre: 'Formulario710_2023.txt', tipo: 'archivo', tamaño: '5.2 KB' },
                    { nombre: 'EstadosFinancieros_2023.pdf', tipo: 'reporte', tamaño: '250 KB' },
                    { nombre: 'DeclaracionJurada_2023.txt', tipo: 'archivo', tamaño: '3.1 KB' }
                ],
                datos: {
                    utilidadNeta: 125000.00,
                    impuestoRenta: 37500.00,
                    activosTotales: 250000.00,
                    patrimonio: 180000.00
                }
            };

            this.reportes.DJAnuales.push(nuevaDeclaracion);

            Swal.fire({
                title: '¡Declaración Generada!',
                text: 'La declaración anual de renta ha sido generada exitosamente.',
                icon: 'success',
                timer: 5000,
                showConfirmButton: false
            });
        }, 4000);
    }
}

// Inicializar el sistema de reportes SUNAT
let reportesSUNAT;

document.addEventListener('DOMContentLoaded', function() {
    reportesSUNAT = new ReportesSUNAT();
});

// Exportar para uso global
window.ReportesSUNAT = ReportesSUNAT;