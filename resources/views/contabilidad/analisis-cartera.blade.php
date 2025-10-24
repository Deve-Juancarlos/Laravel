<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Cartera - Sistema Contable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .cartera-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .alerta-card {
            border-radius: 10px;
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .alerta-danger { border-left: 4px solid #dc3545; background: #fff5f5; }
        .alerta-warning { border-left: 4px solid #ffc107; background: #fff8e1; }
        .alerta-success { border-left: 4px solid #28a745; background: #f1fff1; }
        .cliente-row {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .cliente-row:hover {
            background-color: #f8f9fa !important;
            transform: translateX(5px);
        }
        .vencida-amount { color: #dc3545; font-weight: bold; }
        .por-vencer-amount { color: #28a745; font-weight: bold; }
        .normal-amount { color: #007bff; font-weight: bold; }
        .btn-danger { background: linear-gradient(45deg, #dc3545, #c82333); border: none; }
        .btn-warning { background: linear-gradient(45deg, #ffc107, #e0a800); border: none; }
        .btn-success { background: linear-gradient(45deg, #28a745, #1e7e34); border: none; }
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="cartera-header text-center">
            <h1 class="h2 mb-2">
                <i class="fas fa-hand-holding-usd me-2"></i>
                ANÁLISIS DE CARTERA
            </h1>
            <p class="mb-0">Distribuidora Farmacéutica</p>
            <p class="mb-0">Al {{ date('d/m/Y H:i') }}</p>
        </div>

        <!-- Resumen de Cartera -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center text-white" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                    <div class="card-body">
                        <i class="fas fa-exclamation-triangle fa-3x mb-2"></i>
                        <h3>{{ number_format($resumen['total_vencido'] ?? 85000.00, 2) }}</h3>
                        <p class="mb-0">Total Vencido</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-center text-white" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
                    <div class="card-body">
                        <i class="fas fa-clock fa-3x mb-2"></i>
                        <h3>{{ number_format($resumen['por_vencer'] ?? 65000.00, 2) }}</h3>
                        <p class="mb-0">Por Vencer</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-center text-white" style="background: linear-gradient(135deg, #28a745, #1e7e34);">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-3x mb-2"></i>
                        <h3>{{ number_format($resumen['total_cartera'] ?? 150000.00, 2) }}</h3>
                        <p class="mb-0">Total Cartera</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-center text-white" style="background: linear-gradient(135deg, #007bff, #0056b3);">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x mb-2"></i>
                        <h3>{{ $resumen['clientes_morosos'] ?? 8 }}</h3>
                        <p class="mb-0">Clientes Morosos</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2"></i>Evolución de la Cartera
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="evolucionCarteraChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Distribución por Antigüedad
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="antiguedadChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="mb-3">
                    <i class="fas fa-bell me-2"></i>Alertas de Cobranza
                </h5>
                
                <!-- Alerta de Clientes con más de 90 días vencidos -->
                <div class="alerta-card alerta-danger">
                    <div class="p-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">🚨 Clientes Críticos (Más de 90 días vencidos)</h6>
                                <p class="mb-0">{{ $clientes_criticos ?? 3 }} clientes con deudas vencidas superiores a S/ 15,000 requieren atención inmediata.</p>
                            </div>
                            <button class="btn btn-danger btn-sm" onclick="verClientesCriticos()">
                                Ver Detalle
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Alerta de cartera vencida -->
                <div class="alerta-card alerta-warning">
                    <div class="p-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock fa-2x me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">⚠️ Cartera Vencida Elevada</h6>
                                <p class="mb-0">El {{ number_format(($resumen['total_vencido'] ?? 85000) / ($resumen['total_cartera'] ?? 150000) * 100, 1) }}% de la cartera está vencida. Recomendado implementar estrategias de cobranza.</p>
                            </div>
                            <button class="btn btn-warning btn-sm" onclick="generarPlanCobranza()">
                                Crear Plan
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Alerta de tendencia -->
                <div class="alerta-card alerta-success">
                    <div class="p-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-trending-up fa-2x me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">📈 Mejora en Cobranzas</h6>
                                <p class="mb-0">Se han recuperado S/ {{ number_format($recuperacion_mes ?? 25000, 2) }} en el último mes. Continuar con las estrategias actuales.</p>
                            </div>
                            <button class="btn btn-success btn-sm" onclick="verReporteCobranzas()">
                                Ver Reporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Clientes Morosos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>Top 10 - Clientes con Mayor Deuda
                        </h5>
                        <button class="btn btn-primary" onclick="exportarLista()">
                            <i class="fas fa-download me-2"></i>Exportar
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>DNI/RUC</th>
                                        <th class="text-end">Deuda Total</th>
                                        <th class="text-center">Días Vencido</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $clientesEjemplo = [
                                            (object)['nombre' => 'Farmacia San Pedro', 'dni_ruc' => '20-12345678-9', 'deuda_total' => 25000.00, 'dias_vencido' => 120, 'estado' => 'crítico'],
                                            (object)['nombre' => 'Botica La Salud', 'dni_ruc' => '20-87654321-0', 'deuda_total' => 18500.00, 'dias_vencido' => 95, 'estado' => 'crítico'],
                                            (object)['nombre' => 'Farmacia Moderna', 'dni_ruc' => '20-11223344-5', 'deuda_total' => 15000.00, 'dias_vencido' => 60, 'estado' => 'alerta'],
                                            (object)['nombre' => 'Botica Central', 'dni_ruc' => '20-99887766-1', 'deuda_total' => 12800.00, 'dias_vencido' => 45, 'estado' => 'alerta'],
                                            (object)['nombre' => 'Farmacia Universal', 'dni_ruc' => '20-55443322-7', 'deuda_total' => 9500.00, 'dias_vencido' => 30, 'estado' => 'normal'],
                                        ];
                                    @endphp
                                    
                                    @foreach($clientesEjemplo as $index => $cliente)
                                        <tr class="cliente-row" onclick="verCliente('{{ $cliente->dni_ruc }}')">
                                            <td>
                                                <strong>{{ $cliente->nombre }}</strong>
                                                <br><small class="text-muted">#{{ $index + 1 }}</small>
                                            </td>
                                            <td>
                                                <code>{{ $cliente->dni_ruc }}</code>
                                            </td>
                                            <td class="text-end">
                                                <span class="{{ 
                                                    $cliente->estado == 'crítico' ? 'vencida-amount' : 
                                                    ($cliente->estado == 'alerta' ? 'por-vencer-amount' : 'normal-amount')
                                                }}">
                                                    S/ {{ number_format($cliente->deuda_total, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($cliente->dias_vencido > 90)
                                                    <span class="badge bg-danger">{{ $cliente->dias_vencido }}</span>
                                                @elseif($cliente->dias_vencido > 30)
                                                    <span class="badge bg-warning">{{ $cliente->dias_vencido }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ $cliente->dias_vencido }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($cliente->estado == 'crítico')
                                                    <span class="badge bg-danger">Crítico</span>
                                                @elseif($cliente->estado == 'alerta')
                                                    <span class="badge bg-warning">Alerta</span>
                                                @else
                                                    <span class="badge bg-success">Normal</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); enviarRecordatorio('{{ $cliente->dni_ruc }}')">
                                                        <i class="fas fa-bell"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success" onclick="event.stopPropagation(); generarPlanPago('{{ $cliente->dni_ruc }}')">
                                                        <i class="fas fa-calendar"></i>
                                                    </button>
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
                                <button class="btn btn-primary w-100" onclick="generarReporteCompleto()">
                                    <i class="fas fa-file-alt me-2"></i>Reporte Completo
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-success w-100" onclick="programarLlamadas()">
                                    <i class="fas fa-phone me-2"></i>Programar Llamadas
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-info w-100" onclick="enviarCartasCobranza()">
                                    <i class="fas fa-envelope me-2"></i>Cartas de Cobranza
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-warning w-100" onclick="gestionarProvisiones()">
                                    <i class="fas fa-shield-alt me-2"></i>Provisiones
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
        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });

        function initializeCharts() {
            // Evolución de Cartera
            const evolucionCtx = document.getElementById('evolucionCarteraChart').getContext('2d');
            new Chart(evolucionCtx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Vencido',
                            data: [120000, 95000, 85000, 78000, 82000, 85000],
                            borderColor: 'rgb(220, 53, 69)',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Por Vencer',
                            data: [30000, 45000, 55000, 60000, 62000, 65000],
                            borderColor: 'rgb(255, 193, 7)',
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'S/ ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Antigüedad
            const antiguedadCtx = document.getElementById('antiguedadChart').getContext('2d');
            new Chart(antiguedadCtx, {
                type: 'doughnut',
                data: {
                    labels: ['0-30 días', '31-60 días', '61-90 días', '90+ días'],
                    datasets: [{
                        data: [45000, 20000, 15000, 5000],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(255, 152, 0, 0.8)',
                            'rgba(220, 53, 69, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Action Functions
        function verCliente(ruc) {
            alert('Ver cliente: ' + ruc);
        }

        function enviarRecordatorio(ruc) {
            if (confirm('¿Enviar recordatorio de pago al cliente ' + ruc + '?')) {
                // Implementar envío de recordatorio
                alert('Recordatorio enviado a: ' + ruc);
            }
        }

        function generarPlanPago(ruc) {
            alert('Generar plan de pago para: ' + ruc);
        }

        function verClientesCriticos() {
            window.location.href = '/contabilidad/analisis/cartera/clientes-criticos';
        }

        function generarPlanCobranza() {
            window.location.href = '/contabilidad/analisis/cartera/plan-cobranza';
        }

        function verReporteCobranzas() {
            window.location.href = '/contabilidad/reportes/cobranzas';
        }

        function exportarLista() {
            window.open('/contabilidad/analisis/cartera/exportar', '_blank');
        }

        function generarReporteCompleto() {
            window.open('/contabilidad/analisis/cartera/reporte-completo', '_blank');
        }

        function programarLlamadas() {
            window.open('/contabilidad/analisis/cartera/programar-llamadas', '_blank');
        }

        function enviarCartasCobranza() {
            window.open('/contabilidad/analisis/cartera/enviar-cartas', '_blank');
        }

        function gestionarProvisiones() {
            window.open('/contabilidad/analisis/cartera/provisiones', '_blank');
        }
    </script>
</body>
</html>