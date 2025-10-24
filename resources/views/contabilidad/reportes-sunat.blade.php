<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes SUNAT - Sistema Contable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .sunat-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .report-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid;
        }
        .report-primary { border-left-color: #007bff; }
        .report-success { border-left-color: #28a745; }
        .report-warning { border-left-color: #ffc107; }
        .report-danger { border-left-color: #dc3545; }
        .sunat-icon {
            background: linear-gradient(45deg, #2c3e50, #34495e);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .btn-sunat {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
        }
        .btn-sunat:hover {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            color: white;
        }
        .status-pending { color: #ffc107; }
        .status-processing { color: #17a2b8; }
        .status-completed { color: #28a745; }
        .status-error { color: #dc3545; }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .download-icon {
            transition: transform 0.3s ease;
        }
        .download-icon:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="sunat-header text-center">
            <h1 class="h2 mb-2">
                <i class="fas fa-university me-2"></i>
                REPORTES SUNAT
            </h1>
            <p class="mb-0">Sistema de Reportes Tributarios y Fiscales</p>
            <p class="mb-0">Distribuidora Farmacéutica - {{ date('d/m/Y H:i') }}</p>
        </div>

        <!-- Estado de Envío -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="sunat-icon">
                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                    <h6>Estado Envío</h6>
                    <span class="badge bg-success">Conectado</span>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="sunat-icon">
                    <i class="fas fa-file-invoice fa-2x mb-2"></i>
                    <h6>Último Envío</h6>
                    <small>{{ date('d/m/Y H:i', strtotime('-2 hours')) }}</small>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="sunat-icon">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h6>Documentos Enviados</h6>
                    <h4 class="mb-0">{{ $estadisticas['documentos_enviados'] ?? 1247 }}</h4>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="sunat-icon">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h6>Errores Pendientes</h6>
                    <h4 class="mb-0 text-warning">{{ $estadisticas['errores_pendientes'] ?? 3 }}</h4>
                </div>
            </div>
        </div>

        <!-- Tipos de Reportes -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-3">
                    <i class="fas fa-file-alt me-2"></i>Tipos de Reportes SUNAT
                </h4>
            </div>
            
            <!-- Reporte IGV -->
            <div class="col-lg-6">
                <div class="report-card report-primary">
                    <div class="row">
                        <div class="col-8">
                            <h5>
                                <i class="fas fa-calculator me-2"></i>
                                Declaración de IGV
                            </h5>
                            <p class="text-muted">Reporte mensual del Impuesto General a las Ventas</p>
                            <div class="d-flex align-items-center mb-2">
                                <small class="text-muted me-3">Último envío:</small>
                                <span class="badge bg-secondary">{{ date('m/Y', strtotime('-1 month')) }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-3">Estado:</small>
                                <span class="status-completed"><i class="fas fa-check-circle me-1"></i>Enviado</span>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="mb-3">
                                <h3 class="text-primary">{{ number_format($reportes['igv_mes'] ?? 85000.00, 2) }}</h3>
                                <small class="text-muted">IGV a Pagar</small>
                            </div>
                            <button class="btn btn-primary w-100" onclick="generarIGV()">
                                <i class="fas fa-download me-2"></i>Generar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reporte Compras -->
            <div class="col-lg-6">
                <div class="report-card report-success">
                    <div class="row">
                        <div class="col-8">
                            <h5>
                                <i class="fas fa-shopping-cart me-2"></i>
                                Registro de Compras
                            </h5>
                            <p class="text-muted">Detalle mensual de todas las compras realizadas</p>
                            <div class="d-flex align-items-center mb-2">
                                <small class="text-muted me-3">Último envío:</small>
                                <span class="badge bg-secondary">{{ date('m/Y', strtotime('-1 month')) }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-3">Estado:</small>
                                <span class="status-completed"><i class="fas fa-check-circle me-1"></i>Enviado</span>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="mb-3">
                                <h3 class="text-success">{{ $reportes['compras_count'] ?? 156 }}</h3>
                                <small class="text-muted">Facturas</small>
                            </div>
                            <button class="btn btn-success w-100" onclick="generarCompras()">
                                <i class="fas fa-download me-2"></i>Generar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reporte Ventas -->
            <div class="col-lg-6">
                <div class="report-card report-warning">
                    <div class="row">
                        <div class="col-8">
                            <h5>
                                <i class="fas fa-cash-register me-2"></i>
                                Registro de Ventas
                            </h5>
                            <p class="text-muted">Detalle mensual de todas las ventas realizadas</p>
                            <div class="d-flex align-items-center mb-2">
                                <small class="text-muted me-3">Último envío:</small>
                                <span class="badge bg-secondary">{{ date('m/Y', strtotime('-1 month')) }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-3">Estado:</small>
                                <span class="status-completed"><i class="fas fa-check-circle me-1"></i>Enviado</span>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="mb-3">
                                <h3 class="text-warning">{{ $reportes['ventas_count'] ?? 203 }}</h3>
                                <small class="text-muted">Facturas</small>
                            </div>
                            <button class="btn btn-warning w-100" onclick="generarVentas()">
                                <i class="fas fa-download me-2"></i>Generar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- PDT 621 -->
            <div class="col-lg-6">
                <div class="report-card report-danger">
                    <div class="row">
                        <div class="col-8">
                            <h5>
                                <i class="fas fa-file-invoice me-2"></i>
                                PDT 621 - RR.HH.
                            </h5>
                            <p class="text-muted">Planilla de pagos de retenciones por rentas de 5ta categoría</p>
                            <div class="d-flex align-items-center mb-2">
                                <small class="text-muted me-3">Último envío:</small>
                                <span class="badge bg-secondary">{{ date('m/Y', strtotime('-2 months')) }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-3">Estado:</small>
                                <span class="status-completed"><i class="fas fa-check-circle me-1"></i>Enviado</span>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="mb-3">
                                <h3 class="text-danger">{{ $reportes['empleados'] ?? 12 }}</h3>
                                <small class="text-muted">Empleados</small>
                            </div>
                            <button class="btn btn-danger w-100" onclick="generarPDT621()">
                                <i class="fas fa-download me-2"></i>Generar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generador de Reportes -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cogs me-2"></i>Generador de Reportes
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="formReporte">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Tipo de Reporte</label>
                                    <select class="form-select" name="tipo_reporte" id="tipoReporte" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="igv">IGV - Formulario 621</option>
                                        <option value="compras">Registro de Compras</option>
                                        <option value="ventas">Registro de Ventas</option>
                                        <option value=" PDT621">PDT 621 - RRHH</option>
                                        <option value="pdt">PDT 617 - Asesoría</option>
                                        <option value="estados_financieros">Estados Financieros</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Año</label>
                                    <select class="form-select" name="año" required>
                                        <option value="2025" selected>2025</option>
                                        <option value="2024">2024</option>
                                        <option value="2023">2023</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Mes</label>
                                    <select class="form-select" name="mes" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="01">Enero</option>
                                        <option value="02">Febrero</option>
                                        <option value="03">Marzo</option>
                                        <option value="04">Abril</option>
                                        <option value="05">Mayo</option>
                                        <option value="06">Junio</option>
                                        <option value="07">Julio</option>
                                        <option value="08">Agosto</option>
                                        <option value="09">Septiembre</option>
                                        <option value="10">Octubre</option>
                                        <option value="11">Noviembre</option>
                                        <option value="12">Diciembre</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-sunat">
                                            <i class="fas fa-cog me-1"></i>
                                            Generar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>Próximos Vencimientos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1">IGV {{ date('m/Y') }}</h6>
                                    <small class="text-muted">Formulario 621</small>
                                </div>
                                <span class="badge bg-danger">{{ date('15/m') }}</span>
                            </div>
                            
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1">PDT 621</h6>
                                    <small class="text-muted">Retenciones RR.HH.</small>
                                </div>
                                <span class="badge bg-warning">{{ date('15/m', strtotime('+1 month')) }}</span>
                            </div>
                            
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1">Estado Financiero</h6>
                                    <small class="text-muted">Reporte Anual</small>
                                </div>
                                <span class="badge bg-info">Marzo</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de Reportes -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>Historial de Reportes Enviados
                        </h5>
                        <button class="btn btn-outline-primary" onclick="refrescarHistorial()">
                            <i class="fas fa-sync-alt me-2"></i>Actualizar
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha Generación</th>
                                        <th>Tipo de Reporte</th>
                                        <th>Período</th>
                                        <th>Estado</th>
                                        <th>Fecha Envío</th>
                                        <th>Archivo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $reportesEjemplo = [
                                            (object)['fecha_generacion' => '2025-01-15 10:30:00', 'tipo' => 'IGV - Formulario 621', 'periodo' => '12/2024', 'estado' => 'enviado', 'fecha_envio' => '2025-01-15 11:45:00'],
                                            (object)['fecha_generacion' => '2025-01-15 09:15:00', 'tipo' => 'Registro de Ventas', 'periodo' => '12/2024', 'estado' => 'enviado', 'fecha_envio' => '2025-01-15 10:30:00'],
                                            (object)['fecha_generacion' => '2025-01-14 16:20:00', 'tipo' => 'Registro de Compras', 'periodo' => '12/2024', 'estado' => 'enviado', 'fecha_envio' => '2025-01-14 17:10:00'],
                                            (object)['fecha_generacion' => '2025-01-10 14:00:00', 'tipo' => 'PDT 621 - RRHH', 'periodo' => '12/2024', 'estado' => 'error', 'fecha_envio' => null],
                                            (object)['fecha_generacion' => '2025-01-08 11:45:00', 'tipo' => 'IGV - Formulario 621', 'periodo' => '11/2024', 'estado' => 'enviado', 'fecha_envio' => '2025-01-08 12:30:00'],
                                        ];
                                    @endphp
                                    
                                    @foreach($reportesEjemplo as $reporte)
                                        <tr>
                                            <td>
                                                {{ \Carbon\Carbon::parse($reporte->fecha_generacion)->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                <strong>{{ $reporte->tipo }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $reporte->periodo }}</span>
                                            </td>
                                            <td>
                                                @if($reporte->estado == 'enviado')
                                                    <span class="status-completed">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Enviado
                                                    </span>
                                                @elseif($reporte->estado == 'pendiente')
                                                    <span class="status-pending">
                                                        <i class="fas fa-clock me-1"></i>
                                                        Pendiente
                                                    </span>
                                                @elseif($reporte->estado == 'procesando')
                                                    <span class="status-processing">
                                                        <i class="fas fa-spinner fa-spin me-1"></i>
                                                        Procesando
                                                    </span>
                                                @else
                                                    <span class="status-error">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Error
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($reporte->fecha_envio)
                                                    {{ \Carbon\Carbon::parse($reporte->fecha_envio)->format('d/m/Y H:i') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($reporte->estado == 'enviado')
                                                    <a href="#" class="text-decoration-none download-icon" onclick="descargarReporte('{{ $reporte->periodo }}')">
                                                        <i class="fas fa-download text-success me-2"></i>
                                                        Descargar
                                                    </a>
                                                @else
                                                    <span class="text-muted">No disponible</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-info" onclick="verDetalle('{{ $reporte->periodo }}')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @if($reporte->estado == 'enviado')
                                                        <button class="btn btn-sm btn-outline-success" onclick="descargarReporte('{{ $reporte->periodo }}')">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>Acciones Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <button class="btn btn-primary w-100" onclick="enviarTodosReportes()">
                                    <i class="fas fa-paper-plane me-2"></i>Enviar Todos
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-info w-100" onclick="verEstadoEnvio()">
                                    <i class="fas fa-server me-2"></i>Estado Servidor
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-warning w-100" onclick="configurarNotificaciones()">
                                    <i class="fas fa-bell me-2"></i>Notificaciones
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-success w-100" onclick="generarBackup()">
                                    <i class="fas fa-archive me-2"></i>Backup SUNAT
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form handler
        document.getElementById('formReporte').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const tipoReporte = document.getElementById('tipoReporte').value;
            const año = this.querySelector('select[name="año"]').value;
            const mes = this.querySelector('select[name="mes"]').value;
            
            if (!tipoReporte || !mes) {
                alert('Por favor complete todos los campos');
                return;
            }
            
            // Simular generación de reporte
            generarReporte(tipoReporte, año, mes);
        });

        // Action Functions
        function generarIGV() {
            generarReporte('igv', 2025, '01');
        }

        function generarCompras() {
            generarReporte('compras', 2025, '01');
        }

        function generarVentas() {
            generarReporte('ventas', 2025, '01');
        }

        function generarPDT621() {
            generarReporte('pdt621', 2025, '01');
        }

        function generarReporte(tipo, año, mes) {
            alert(`Generando reporte ${tipo} para ${mes}/${año}...`);
            // Aquí se implementaría la lógica real de generación
            setTimeout(() => {
                alert('Reporte generado exitosamente');
                refrescarHistorial();
            }, 2000);
        }

        function descargarReporte(periodo) {
            alert(`Descargando reporte del período ${periodo}...`);
        }

        function verDetalle(periodo) {
            alert(`Ver detalle del reporte ${periodo}`);
        }

        function refrescarHistorial() {
            location.reload();
        }

        function enviarTodosReportes() {
            if (confirm('¿Enviar todos los reportes pendientes a SUNAT?')) {
                alert('Enviando reportes a SUNAT...');
            }
        }

        function verEstadoEnvio() {
            window.open('/contabilidad/reportes/sunat/estado-servidor', '_blank');
        }

        function configurarNotificaciones() {
            alert('Configurando notificaciones de vencimientos...');
        }

        function generarBackup() {
            alert('Generando backup de reportes SUNAT...');
        }

        // Auto-refresh cada 5 minutos
        setInterval(() => {
            refrescarHistorial();
        }, 300000);
    </script>
</body>
</html>