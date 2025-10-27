@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('contador.estado-resultados.index') }}">Estado de Resultados</a></li>
            <li class="breadcrumb-item active"><i class="fas fa-download"></i> Exportar</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary">
                <i class="fas fa-download me-2"></i>
                Exportar Estado de Resultados
            </h2>
            <p class="text-muted mb-0">Generar reportes en diferentes formatos</p>
        </div>
        <div class="text-end">
            <a href="{{ route('contador.estado-resultados.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Filtros de Exportación -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>
                Configuración de Exportación
            </h5>
        </div>
        <div class="card-body">
            <form id="formExportacion" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Período</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" 
                                       value="{{ \Carbon\Carbon::now()->startOfYear()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control" 
                                       value="{{ \Carbon\Carbon::now()->endOfYear()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Opciones</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="incluir_detalle" id="incluir_detalle" checked>
                                    <label class="form-check-label" for="incluir_detalle">
                                        Incluir detalle de cuentas
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="incluir_graficos" id="incluir_graficos" checked>
                                    <label class="form-check-label" for="incluir_graficos">
                                        Incluir gráficos y análisis
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="incluir_comparacion" id="incluir_comparacion">
                                    <label class="form-check-label" for="incluir_comparacion">
                                        Incluir comparación período anterior
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Opciones de Exportación -->
    <div class="row">
        <!-- Excel/CSV -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-excel me-2"></i>
                        Exportar a Excel/CSV
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Formatos Disponibles:</h6>
                        <div class="list-group">
                            <button type="button" class="list-group-item list-group-item-action" onclick="exportarExcel('completo')">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas fa-table text-success me-2"></i>Estado Completo</h6>
                                    <small class="text-muted">.xlsx</small>
                                </div>
                                <p class="mb-1">Estado de resultados completo con todas las secciones</p>
                                <small class="text-muted">Recomendado para análisis detallado</small>
                            </button>
                            
                            <button type="button" class="list-group-item list-group-item-action" onclick="exportarExcel('resumen')">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas fa-chart-pie text-info me-2"></i>Resumen Ejecutivo</h6>
                                    <small class="text-muted">.xlsx</small>
                                </div>
                                <p class="mb-1">Solo datos principales y resúmenes</p>
                                <small class="text-muted">Ideal para presentaciones</small>
                            </button>
                            
                            <button type="button" class="list-group-item list-group-item-action" onclick="exportarCSV()">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas fa-file-csv text-warning me-2"></i>Datos CSV</h6>
                                    <small class="text-muted">.csv</small>
                                </div>
                                <p class="mb-1">Datos en formato plano para importación</p>
                                <small class="text-muted">Compatible con otros sistemas</small>
                            </button>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Ventajas del formato Excel:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Mantiene formato y fórmulas</li>
                            <li>Fácil de editar y personalizar</li>
                            <li>Incluye múltiples hojas</li>
                            <li>Compatible con Power BI</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- PDF -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-pdf me-2"></i>
                        Exportar a PDF
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Plantillas PDF:</h6>
                        <div class="list-group">
                            <button type="button" class="list-group-item list-group-item-action" onclick="exportarPDF('oficial')">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas fa-stamp text-danger me-2"></i>Formato Oficial SUNAT</h6>
                                    <small class="text-muted">.pdf</small>
                                </div>
                                <p class="mb-1">Cumple con normativas contables peruanas</p>
                                <small class="text-muted">Para presentación oficial</small>
                            </button>
                            
                            <button type="button" class="list-group-item list-group-item-action" onclick="exportarPDF('ejecutivo')">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas fa-chart-line text-primary me-2"></i>Reporte Ejecutivo</h6>
                                    <small class="text-muted">.pdf</small>
                                </div>
                                <p class="mb-1">Análisis completo con gráficos</p>
                                <small class="text-muted">Para gerencia y dirección</small>
                            </button>
                            
                            <button type="button" class="list-group-item list-group-item-action" onclick="exportarPDF('detallado')">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas fa-list-alt text-success me-2"></i>Análisis Detallado</h6>
                                    <small class="text-muted">.pdf</small>
                                </div>
                                <p class="mb-1">Máximo detalle con comentarios</p>
                                <small class="text-muted">Para auditoría interna</small>
                            </button>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Características PDF:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Formato profesional y fijo</li>
                            <li>Ideal para impresión</li>
                            <li>No editable (mayor seguridad)</li>
                            <li>Compartir fácilmente</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exportaciones Especializadas -->
    <div class="row">
        <!-- Análisis Farmacéutico -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-pills me-2"></i>
                        Reportes Farmacéuticos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Reportes Específicos SIFANO:</h6>
                        <div class="list-group">
                            <button type="button" class="list-group-item list-group-item-action" onclick="exportarFarmaceutico('rentabilidad')">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas fa-percentage text-info me-2"></i>Análisis de Rentabilidad</h6>
                                    <small class="text-muted">.xlsx</small>
                                </div>
                                <p class="mb-1">Rentabilidad por producto y línea</p>
                                <small class="text-muted">Para decisiones comerciales</small>
                            </button>
                            
                            <button type="button" class="list-group-item list-group-item-action" onclick="exportarFarmaceutico('costos')">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas fa-dollar-sign text-danger me-2"></i>Control de Costos</h6>
                                    <small class="text-muted">.pdf</small>
                                </div>
                                <p class="mb-1">Análisis de costos por categoría</p>
                                <small class="text-muted">Para optimización de inventarios</small>
                            </button>
                            
                            <button type="button" class="list-group-item list-group-item-action" onclick="exportarFarmaceutico('ventas')">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas fa-chart-bar text-success me-2"></i>Desempeño de Ventas</h6>
                                    <small class="text-muted">.xlsx</small>
                                </div>
                                <p class="mb-1">Análisis de ventas por producto</p>
                                <small class="text-muted">Para estrategias de marketing</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparaciones y Tendencias -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-comparison me-2"></i>
                        Análisis Comparativo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Reportes Comparativos:</h6>
                        <div class="list-group">
                            <button type="button" class="list-group-item list-group-item-action" onclick="exportarComparativo('periodos')">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas fa-calendar-alt text-primary me-2"></i>Comparación Períodos</h6>
                                    <small class="text-muted">.pdf</small>
                                </div>
                                <p class="mb-1">Análisis de tendencias mensuales/anuales</p>
                                <small class="text-muted">Para planificación estratégica</small>
                            </button>
                            
                            <button type="button" class="list-group-item list-group-item-action" onclick="exportarComparativo('variaciones')">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas fa-chart-line text-success me-2"></i>Análisis de Variaciones</h6>
                                    <small class="text-muted">.xlsx</small>
                                </div>
                                <p class="mb-1">Detalle de variaciones por cuenta</p>
                                <small class="text-muted">Para control de gestión</small>
                            </button>
                            
                            <button type="button" class="list-group-item list-group-item-action" onclick="exportarComparativo('proyecciones')">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas fa-crystal-ball text-info me-2"></i>Proyecciones</h6>
                                    <small class="text-muted">.pdf</small>
                                </div>
                                <p class="mb-1">Proyecciones basadas en tendencias</p>
                                <small class="text-muted">Para planificación futura</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progreso de Exportación -->
    <div class="card mb-4" id="cardProgreso" style="display: none;">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-spinner fa-spin me-2"></i>
                Generando Reporte...
            </h5>
        </div>
        <div class="card-body">
            <div class="progress mb-3" style="height: 25px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: 0%" id="barraProgreso">0%</div>
            </div>
            <p class="mb-0" id="mensajeProgreso">Preparando datos...</p>
        </div>
    </div>

    <!-- Historial de Exportaciones -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="fas fa-history me-2"></i>
                Historial de Exportaciones
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Formato</th>
                            <th>Período</th>
                            <th>Tamaño</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="historialExportaciones">
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                <i class="fas fa-info-circle"></i>
                                No hay exportaciones previas
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarProgreso(mensaje) {
    const cardProgreso = document.getElementById('cardProgreso');
    const barraProgreso = document.getElementById('barraProgreso');
    const mensajeProgreso = document.getElementById('mensajeProgreso');
    
    cardProgreso.style.display = 'block';
    mensajeProgreso.textContent = mensaje;
    
    // Simular progreso
    let progreso = 0;
    const intervalo = setInterval(() => {
        progreso += Math.random() * 15;
        if (progreso > 100) progreso = 100;
        
        barraProgreso.style.width = progreso + '%';
        barraProgreso.textContent = Math.round(progreso) + '%';
        
        if (progreso >= 100) {
            clearInterval(intervalo);
            setTimeout(() => {
                cardProgreso.style.display = 'none';
                alert('Exportación completada exitosamente');
                agregarAlHistorial('Excel Completo', 'xlsx', '01/01/2024 - 31/12/2024', '2.5 MB');
            }, 500);
        }
    }, 200);
}

function exportarExcel(tipo) {
    const formData = new FormData(document.getElementById('formExportacion'));
    formData.append('formato', 'excel');
    formData.append('tipo', tipo);
    
    mostrarProgreso(`Generando ${tipo === 'completo' ? 'reporte completo' : 'resumen ejecutivo'} en Excel...`);
    
    // Simular llamada al servidor
    setTimeout(() => {
        // Aquí iría la llamada real al endpoint
        console.log('Exportando:', Object.fromEntries(formData));
    }, 1000);
}

function exportarCSV() {
    mostrarProgreso('Generando archivo CSV...');
    setTimeout(() => {
        alert('CSV exportado exitosamente');
        agregarAlHistorial('Datos CSV', 'csv', '01/01/2024 - 31/12/2024', '1.2 MB');
    }, 1500);
}

function exportarPDF(tipo) {
    const mensaje = {
        'oficial': 'Generando reporte oficial SUNAT en PDF...',
        'ejecutivo': 'Generando reporte ejecutivo en PDF...',
        'detallado': 'Generando análisis detallado en PDF...'
    };
    
    mostrarProgreso(mensaje[tipo]);
    setTimeout(() => {
        alert(`PDF ${tipo} generado exitosamente`);
        agregarAlHistorial(`PDF ${tipo}`, 'pdf', '01/01/2024 - 31/12/2024', '3.8 MB');
    }, 2000);
}

function exportarFarmaceutico(tipo) {
    const mensaje = {
        'rentabilidad': 'Analizando rentabilidad farmacéutica...',
        'costos': 'Generando reporte de costos...',
        'ventas': 'Analizando desempeño de ventas...'
    };
    
    mostrarProgreso(mensaje[tipo]);
    setTimeout(() => {
        alert(`Reporte farmacéutico ${tipo} generado exitosamente`);
        agregarAlHistorial(`Farmacéutico ${tipo}`, 'xlsx', '01/01/2024 - 31/12/2024', '4.2 MB');
    }, 2500);
}

function exportarComparativo(tipo) {
    const mensaje = {
        'periodos': 'Generando comparación de períodos...',
        'variaciones': 'Analizando variaciones...',
        'proyecciones': 'Calculando proyecciones...'
    };
    
    mostrarProgreso(mensaje[tipo]);
    setTimeout(() => {
        alert(`Análisis comparativo ${tipo} generado exitosamente`);
        agregarAlHistorial(`Comparativo ${tipo}`, 'pdf', '01/01/2024 - 31/12/2024', '5.1 MB');
    }, 3000);
}

function agregarAlHistorial(tipo, formato, periodo, tamaño) {
    const tbody = document.getElementById('historialExportaciones');
    
    // Limpiar mensaje de "no hay datos"
    if (tbody.children.length === 1 && tbody.children[0].children.length === 1) {
        tbody.innerHTML = '';
    }
    
    const ahora = new Date();
    const fecha = ahora.toLocaleDateString('es-PE') + ' ' + ahora.toLocaleTimeString('es-PE');
    
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${fecha}</td>
        <td><span class="badge bg-primary">${tipo}</span></td>
        <td>${formato.toUpperCase()}</td>
        <td>${periodo}</td>
        <td>${tamaño}</td>
        <td>
            <button class="btn btn-sm btn-outline-primary me-1" onclick="descargar('${tipo}')">
                <i class="fas fa-download"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger me-1" onclick="eliminar(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

function descargar(tipo) {
    alert(`Descargando archivo: ${tipo}`);
}

function eliminar(boton) {
    if (confirm('¿Está seguro de eliminar este registro?')) {
        const row = boton.closest('tr');
        row.remove();
        
        // Si no hay más filas, mostrar mensaje de "no hay datos"
        const tbody = document.getElementById('historialExportaciones');
        if (tbody.children.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        <i class="fas fa-info-circle"></i>
                        No hay exportaciones previas
                    </td>
                </tr>
            `;
        }
    }
}
</script>
@endsection