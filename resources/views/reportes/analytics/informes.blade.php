@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar text-primary"></i> Informes Personalizados
        </h1>
        <div>
            <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Reportes
            </a>
            <button class="btn btn-outline-primary" onclick="crearNuevoInforme()">
                <i class="fas fa-plus"></i> Nuevo Informe
            </button>
        </div>
    </div>

    <!-- Filtros y Configuración -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Configuración del Informe</h6>
        </div>
        <div class="card-body">
            <form id="informeForm" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipo de Informe</label>
                            <select class="form-control" name="tipo_informe" id="tipoInforme">
                                <option value="ventas" selected>Análisis de Ventas</option>
                                <option value="clientes">Análisis de Clientes</option>
                                <option value="productos">Análisis de Productos</option>
                                <option value="rentabilidad">Análisis de Rentabilidad</option>
                                <option value="comparativo">Informe Comparativo</option>
                                <option value="prediccion">Informe Predictivo</option>
                                <option value="personalizado">Personalizado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Período de Análisis</label>
                            <select class="form-control" name="periodo" id="periodo">
                                <option value="hoy">Hoy</option>
                                <option value="semana">Esta Semana</option>
                                <option value="mes" selected>Este Mes</option>
                                <option value="trimestre">Este Trimestre</option>
                                <option value="semestre">Este Semestre</option>
                                <option value="año">Este Año</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Dimensiones</label>
                            <select class="form-control" name="dimensiones" multiple id="dimensiones">
                                <option value="fecha" selected>Fecha</option>
                                <option value="cliente" selected>Cliente</option>
                                <option value="producto" selected>Producto</option>
                                <option value="categoria">Categoría</option>
                                <option value="region">Región</option>
                                <option value="vendedor">Vendedor</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Métricas</label>
                            <select class="form-control" name="metricas" multiple id="metricas">
                                <option value="ventas" selected>Ventas</option>
                                <option value="cantidad" selected>Cantidad</option>
                                <option value="margen">Margen</option>
                                <option value="rentabilidad">Rentabilidad</option>
                                <option value="descuentos">Descuentos</option>
                                <option value="costo">Costo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary" onclick="generarInforme()">
                            <i class="fas fa-chart-line"></i> Generar Informe
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="vistaPrevia()">
                            <i class="fas fa-eye"></i> Vista Previa
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="guardarInforme()">
                            <i class="fas fa-save"></i> Guardar Configuración
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Plantillas de Informes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Plantillas de Informes Disponibles</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-left-primary h-100">
                        <div class="card-body">
                            <div class="text-center">
                                <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                                <h6>Análisis de Ventas</h6>
                                <p class="text-muted small">Informe detallado de tendencias de ventas y performance</p>
                                <button class="btn btn-sm btn-primary" onclick="usarPlantilla('ventas')">
                                    Usar Plantilla
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-left-success h-100">
                        <div class="card-body">
                            <div class="text-center">
                                <i class="fas fa-users fa-3x text-success mb-3"></i>
                                <h6>Análisis de Clientes</h6>
                                <p class="text-muted small">Segmentación y comportamiento de clientes</p>
                                <button class="btn btn-sm btn-success" onclick="usarPlantilla('clientes')">
                                    Usar Plantilla
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-left-info h-100">
                        <div class="card-body">
                            <div class="text-center">
                                <i class="fas fa-boxes fa-3x text-info mb-3"></i>
                                <h6>Análisis de Productos</h6>
                                <p class="text-muted small">Performance de productos y categorías</p>
                                <button class="btn btn-sm btn-info" onclick="usarPlantilla('productos')">
                                    Usar Plantilla
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-left-warning h-100">
                        <div class="card-body">
                            <div class="text-center">
                                <i class="fas fa-percentage fa-3x text-warning mb-3"></i>
                                <h6>Análisis de Rentabilidad</h6>
                                <p class="text-muted small">Márgenes y rentabilidad por línea de negocio</p>
                                <button class="btn btn-sm btn-warning" onclick="usarPlantilla('rentabilidad')">
                                    Usar Plantilla
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informe Generado -->
    <div class="card shadow" id="informeGenerado" style="display: none;">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary" id="tituloInforme">Informe Generado</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Opciones:</div>
                    <a class="dropdown-item" href="#" onclick="exportarExcel()">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportarPDF()">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportarPPT()">
                        <i class="fas fa-file-powerpoint"></i> Exportar PowerPoint
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="programarEnvio()">
                        <i class="fas fa-clock"></i> Programar Envío
                    </a>
                    <a class="dropdown-item" href="#" onclick="compartirInforme()">
                        <i class="fas fa-share"></i> Compartir
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Resumen Ejecutivo -->
            <div class="alert alert-info">
                <h6><i class="fas fa-lightbulb"></i> Resumen Ejecutivo</h6>
                <p id="resumenEjecutivo">
                    El análisis muestra un crecimiento sostenido en las ventas del 12.5% respecto al mes anterior. 
                    Los medicamentos representan el 42.3% del total de ventas, seguido por dispositivos médicos con 28.7%. 
                    Se identifica una oportunidad de mejora en la categoría de suplementos que muestra menor crecimiento.
                </p>
            </div>

            <!-- Gráficos del Informe -->
            <div class="row mb-4">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Tendencia de Ventas</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="informeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Distribución</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-pie">
                                <canvas id="distribucionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla Detallada -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="informeTable" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Período</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Ventas</th>
                            <th>Margen</th>
                            <th>% Crecimiento</th>
                        </tr>
                    </thead>
                    <tbody id="tablaInforme">
                        <tr>
                            <td>Ene 2024</td>
                            <td>Hospital Central S.A.</td>
                            <td>Amoxicilina 500mg</td>
                            <td class="text-right">450</td>
                            <td class="text-right">S/ 7,200</td>
                            <td class="text-right">S/ 2,160</td>
                            <td class="text-success">+15.2%</td>
                        </tr>
                        <tr>
                            <td>Ene 2024</td>
                            <td>Farmacia Bienestar</td>
                            <td>Termómetro Digital</td>
                            <td class="text-right">234</td>
                            <td class="text-right">S/ 5,850</td>
                            <td class="text-right">S/ 1,755</td>
                            <td class="text-success">+8.7%</td>
                        </tr>
                        <tr>
                            <td>Ene 2024</td>
                            <td>Clínica San José</td>
                            <td>Vitamina C 1000mg</td>
                            <td class="text-right">567</td>
                            <td class="text-right">S/ 5,670</td>
                            <td class="text-right">S/ 1,701</td>
                            <td class="text-warning">+2.1%</td>
                        </tr>
                        <tr>
                            <td>Dic 2023</td>
                            <td>Laboratorio Médico</td>
                            <td>Jeringas 5ml</td>
                            <td class="text-right">1,234</td>
                            <td class="text-right">S/ 3,702</td>
                            <td class="text-right">S/ 1,111</td>
                            <td class="text-danger">-5.3%</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Conclusiones y Recomendaciones -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-success">Conclusiones Principales</h6>
                        </div>
                        <div class="card-body">
                            <ul>
                                <li>Crecimiento sostenido del 12.5% en ventas totales</li>
                                <li>Medicamentos mantienen liderazgo con 42.3% del mercado</li>
                                <li>Cliente VIP Hospital Central representa el 10.1% de las ventas</li>
                                <li>Margen promedio mejorado a 31.4% (+1.2%)</li>
                                <li>Productividad por vendedor incrementada 8.3%</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-warning">Recomendaciones</h6>
                        </div>
                        <div class="card-body">
                            <ul>
                                <li>Incrementar stock de medicamentos de alta rotación</li>
                                <li>Desarrollar estrategias promocionales para suplementos</li>
                                <li>Implementar programa de fidelización para clientes VIP</li>
                                <li>Optimizar procesos de ventas para mejorar márgenes</li>
                                <li>Ampliar presencia en regiones con menor penetración</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informes Guardados -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informes Guardados</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Análisis Mensual de Ventas</h6>
                            <p class="text-muted small">Generado: 25/01/2024</p>
                            <p class="small">Informe completo del comportamiento mensual de ventas con análisis de tendencias.</p>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="cargarInforme('mensual')">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="editarInforme('mensual')">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="exportarInforme('mensual')">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Reporte de Clientes VIP</h6>
                            <p class="text-muted small">Generado: 24/01/2024</p>
                            <p class="small">Análisis detallado del comportamiento y valor de los clientes más importantes.</p>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="cargarInforme('vip')">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="editarInforme('vip')">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="exportarInforme('vip')">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Proyección de Rentabilidad</h6>
                            <p class="text-muted small">Generado: 23/01/2024</p>
                            <p class="small">Informe predictivo de rentabilidad basado en tendencias históricas.</p>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="cargarInforme('rentabilidad')">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="editarInforme('rentabilidad')">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="exportarInforme('rentabilidad')">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function generarInforme() {
    const tipoInforme = document.getElementById('tipoInforme').value;
    
    Swal.fire({
        title: 'Generando Informe...',
        text: `Procesando informe de ${tipoInforme}`,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        // Mostrar el informe generado
        document.getElementById('informeGenerado').style.display = 'block';
        document.getElementById('tituloInforme').textContent = `Informe de ${tipoInforme.charAt(0).toUpperCase() + tipoInforme.slice(1)}`;
        document.getElementById('informeGenerado').scrollIntoView({ behavior: 'smooth' });
        
        // Inicializar gráficos del informe
        setTimeout(initializeInformeCharts, 500);
        
        Swal.fire({
            title: '¡Informe Generado!',
            text: 'El informe ha sido procesado exitosamente.',
            icon: 'success'
        });
    });
}

function initializeInformeCharts() {
    // Gráfico de tendencias del informe
    const ctxInforme = document.getElementById('informeChart').getContext('2d');
    new Chart(ctxInforme, {
        type: 'line',
        data: {
            labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
            datasets: [{
                label: 'Ventas',
                data: [125000, 134000, 142000, 156000],
                borderColor: 'rgb(78, 115, 223)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.1
            }, {
                label: 'Margen',
                data: [39000, 42000, 45000, 49000],
                borderColor: 'rgb(72, 187, 120)',
                backgroundColor: 'rgba(72, 187, 120, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Evolución Semanal'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + (value/1000).toFixed(0) + 'K';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de distribución
    const ctxDistribucion = document.getElementById('distribucionChart').getContext('2d');
    new Chart(ctxDistribucion, {
        type: 'doughnut',
        data: {
            labels: ['Medicamentos', 'Dispositivos', 'Suplementos', 'Otros'],
            datasets: [{
                data: [42.3, 28.7, 18.2, 10.8],
                backgroundColor: [
                    'rgb(78, 115, 223)',
                    'rgb(72, 187, 120)',
                    'rgb(246, 194, 62)',
                    'rgb(231, 74, 59)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            }
        }
    });
}

function vistaPrevia() {
    Swal.fire({
        title: 'Generando Vista Previa...',
        text: 'Creando vista previa del informe',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Vista Previa',
            html: `
                <div class="text-left">
                    <h6>Configuración del Informe:</h6>
                    <ul>
                        <li><strong>Tipo:</strong> Análisis de Ventas</li>
                        <li><strong>Período:</strong> Este Mes</li>
                        <li><strong>Dimensiones:</strong> Fecha, Cliente, Producto</li>
                        <li><strong>Métricas:</strong> Ventas, Cantidad, Margen</li>
                    </ul>
                </div>
            `,
            icon: 'info'
        });
    });
}

function guardarInforme() {
    Swal.fire({
        title: 'Guardar Configuración',
        text: 'Ingrese un nombre para esta configuración:',
        input: 'text',
        inputPlaceholder: 'Nombre del informe',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                title: '¡Guardado!',
                text: `La configuración "${result.value}" ha sido guardada.`,
                icon: 'success'
            });
        }
    });
}

function crearNuevoInforme() {
    // Resetear formulario
    document.getElementById('informeForm').reset();
    
    // Ocultar informe generado
    document.getElementById('informeGenerado').style.display = 'none';
    
    // Scroll hacia arriba
    window.scrollTo(0, 0);
}

function usarPlantilla(tipo) {
    Swal.fire({
        title: 'Cargando Plantilla...',
        text: `Aplicando plantilla de ${tipo}`,
        timer: 1500,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        // Configurar según plantilla
        document.getElementById('tipoInforme').value = tipo;
        document.getElementById('periodo').value = 'mes';
        
        Swal.fire({
            title: '¡Plantilla Aplicada!',
            text: `Se ha aplicado la plantilla de ${tipo}. Configure los parámetros específicos.`,
            icon: 'success'
        });
    });
}

function exportarExcel() {
    Swal.fire({
        title: 'Exportando a Excel...',
        text: 'Generando archivo Excel del informe',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Exportado!', 'El archivo Excel ha sido generado.', 'success');
    });
}

function exportarPDF() {
    Swal.fire({
        title: 'Exportando a PDF...',
        text: 'Generando archivo PDF del informe',
        timer: 2500,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Exportado!', 'El archivo PDF ha sido generado.', 'success');
    });
}

function exportarPPT() {
    Swal.fire({
        title: 'Exportando a PowerPoint...',
        text: 'Generando presentación del informe',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Exportado!', 'La presentación PowerPoint ha sido generada.', 'success');
    });
}

function programarEnvio() {
    Swal.fire({
        title: 'Programar Envío',
        text: 'Configure cuándo desea enviar este informe:',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Programar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('¡Programado!', 'El envío del informe ha sido programado.', 'success');
        }
    });
}

function compartirInforme() {
    Swal.fire({
        title: 'Compartir Informe',
        text: 'Seleccione cómo desea compartir:',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Por Email',
        cancelButtonText: 'Por Link'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('¡Enviado!', 'El informe ha sido enviado por email.', 'success');
        } else {
            Swal.fire({
                title: 'Link de Compartición',
                html: `
                    <div class="text-left">
                        <p><strong>URL del Informe:</strong></p>
                        <code>https://sifano.com/reports/analytics/informe-123456</code>
                        <p class="mt-2 small text-muted">El link será válido por 30 días</p>
                    </div>
                `,
                icon: 'info'
            });
        }
    });
}

function cargarInforme(id) {
    Swal.fire({
        title: 'Cargando Informe...',
        text: `Cargando informe ${id}`,
        timer: 1500,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        // Mostrar informe generado con datos del informe guardado
        document.getElementById('informeGenerado').style.display = 'block';
        document.getElementById('tituloInforme').textContent = `Informe Cargado: ${id}`;
        document.getElementById('informeGenerado').scrollIntoView({ behavior: 'smooth' });
        
        setTimeout(initializeInformeCharts, 500);
    });
}

function editarInforme(id) {
    Swal.fire({
        title: 'Editando Informe...',
        text: `Abriendo editor para ${id}`,
        timer: 1500,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('Editor', 'Funcionalidad de edición en desarrollo', 'info');
    });
}

function exportarInforme(id) {
    Swal.fire({
        title: 'Exportando Informe...',
        text: `Generando archivo para ${id}`,
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Exportado!', 'El informe ha sido exportado exitosamente.', 'success');
    });
}

// Inicializar DataTable cuando sea necesario
function initializeInformeTable() {
    $('#informeTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        columnDefs: [
            { targets: [3, 4, 5], className: 'text-right' },
            { targets: [6], className: 'text-center' }
        ]
    });
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    initializeInformeTable();
});
</script>
@endsection