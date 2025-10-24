<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Farmacéutico - Sistema Contable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .farmacia-header {
            background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .control-card {
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid;
        }
        .control-danger { border-left-color: #dc3545; background: #fff5f5; }
        .control-warning { border-left-color: #ffc107; background: #fff8e1; }
        .control-success { border-left-color: #28a745; background: #f1fff1; }
        .control-info { border-left-color: #17a2b8; background: #f0f9ff; }
        .vencimiento-badge {
            background: linear-gradient(45deg, #ff5722, #e64a19);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.75rem;
        }
        .stock-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.75rem;
        }
        .stock-critico { background: #dc3545; color: white; }
        .stock-bajo { background: #ffc107; color: #212529; }
        .stock-normal { background: #28a745; color: white; }
        .stock-alto { background: #17a2b8; color: white; }
        .chart-container {
            position: relative;
            height: 350px;
            margin: 20px 0;
        }
        .nav-pills .nav-link {
            border-radius: 25px;
            margin: 0 5px;
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="farmacia-header text-center">
            <h1 class="h2 mb-2">
                <i class="fas fa-pills me-2"></i>
                CONTROL FARMACÉUTICO
            </h1>
            <p class="mb-0">Distribuidora Farmacéutica - Gestión Integral</p>
            <p class="mb-0">{{ date('d/m/Y H:i') }}</p>
        </div>

        <!-- Tabs Navigation -->
        <div class="row mb-4">
            <div class="col-12">
                <ul class="nav nav-pills nav-justified">
                    <li class="nav-item">
                        <a class="nav-link active" href="#inventario" data-bs-toggle="pill">
                            <i class="fas fa-boxes me-2"></i>Inventario
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#vencimientos" data-bs-toggle="pill">
                            <i class="fas fa-clock me-2"></i>Vencimientos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#lotes" data-bs-toggle="pill">
                            <i class="fas fa-barcode me-2"></i>Control de Lotes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#cumplimiento" data-bs-toggle="pill">
                            <i class="fas fa-shield-alt me-2"></i>Cumplimiento
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Tab Inventario -->
            <div class="tab-pane fade show active" id="inventario">
                <!-- Métricas de Inventario -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center text-white" style="background: linear-gradient(135deg, #28a745, #1e7e34);">
                            <div class="card-body">
                                <i class="fas fa-boxes fa-3x mb-2"></i>
                                <h3>{{ number_format($inventario['total_productos'] ?? 1250, 0) }}</h3>
                                <p class="mb-0">Total Productos</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-center text-white" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
                            <div class="card-body">
                                <i class="fas fa-exclamation-triangle fa-3x mb-2"></i>
                                <h3>{{ $inventario['stock_critico'] ?? 23 }}</h3>
                                <p class="mb-0">Stock Crítico</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-center text-white" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                            <div class="card-body">
                                <i class="fas fa-dollar-sign fa-3x mb-2"></i>
                                <h3>{{ number_format($inventario['valor_inventario'] ?? 450000.00, 2) }}</h3>
                                <p class="mb-0">Valor Inventario</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-center text-white" style="background: linear-gradient(135deg, #6f42c1, #5a32a3);">
                            <div class="card-body">
                                <i class="fas fa-chart-line fa-3x mb-2"></i>
                                <h3>{{ $inventario['rotacion_mes'] ?? 8.5 }}</h3>
                                <p class="mb-0">Rotación/Mes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos de Inventario -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-area me-2"></i>Evolución del Inventario
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="inventarioChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>Distribución por Categoría
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height: 250px;">
                                    <canvas id="categoriaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertas de Stock -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-circle me-2"></i>Alertas de Stock
                                </h5>
                                <button class="btn btn-primary" onclick="actualizarInventario()">
                                    <i class="fas fa-sync-alt me-2"></i>Actualizar
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Producto</th>
                                                <th>Stock Actual</th>
                                                <th>Stock Mínimo</th>
                                                <th>Estado</th>
                                                <th>Última Venta</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $productosEjemplo = [
                                                    (object)['nombre' => 'Paracetamol 500mg', 'stock_actual' => 15, 'stock_minimo' => 50, 'estado' => 'crítico', 'ultima_venta' => '2025-01-20'],
                                                    (object)['nombre' => 'Ibuprofeno 400mg', 'stock_actual' => 35, 'stock_minimo' => 30, 'estado' => 'bajo', 'ultima_venta' => '2025-01-22'],
                                                    (object)['nombre' => 'Amoxicilina 500mg', 'stock_actual' => 150, 'stock_minimo' => 40, 'estado' => 'normal', 'ultima_venta' => '2025-01-23'],
                                                    (object)['nombre' => 'Loratadina 10mg', 'stock_actual' => 200, 'stock_minimo' => 25, 'estado' => 'alto', 'ultima_venta' => '2025-01-21'],
                                                    (object)['nombre' => 'Aspirina 100mg', 'stock_actual' => 8, 'stock_minimo' => 20, 'estado' => 'crítico', 'ultima_venta' => '2025-01-19'],
                                                ];
                                            @endphp
                                            
                                            @foreach($productosEjemplo as $producto)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $producto->nombre }}</strong>
                                                    </td>
                                                    <td>
                                                        {{ $producto->stock_actual }}
                                                    </td>
                                                    <td>
                                                        {{ $producto->stock_minimo }}
                                                    </td>
                                                    <td>
                                                        @if($producto->estado == 'crítico')
                                                            <span class="stock-badge stock-critico">Crítico</span>
                                                        @elseif($producto->estado == 'bajo')
                                                            <span class="stock-badge stock-bajo">Bajo</span>
                                                        @elseif($producto->estado == 'alto')
                                                            <span class="stock-badge stock-alto">Alto</span>
                                                        @else
                                                            <span class="stock-badge stock-normal">Normal</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ \Carbon\Carbon::parse($producto->ultima_venta)->format('d/m/Y') }}
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-success" onclick="generarOrdenCompra('{{ $producto->nombre }}')">
                                                                <i class="fas fa-shopping-cart"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-primary" onclick="verHistorial('{{ $producto->nombre }}')">
                                                                <i class="fas fa-history"></i>
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
            </div>

            <!-- Tab Vencimientos -->
            <div class="tab-pane fade" id="vencimientos">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center text-white" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                            <div class="card-body">
                                <i class="fas fa-exclamation-triangle fa-3x mb-2"></i>
                                <h3>{{ $vencimientos['por_vencer_30'] ?? 45 }}</h3>
                                <p class="mb-0">Por Vencer (30 días)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-center text-white" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
                            <div class="card-body">
                                <i class="fas fa-clock fa-3x mb-2"></i>
                                <h3>{{ $vencimientos['vencidos'] ?? 12 }}</h3>
                                <p class="mb-0">Productos Vencidos</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-center text-white" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                            <div class="card-body">
                                <i class="fas fa-dollar-sign fa-3x mb-2"></i>
                                <h3>{{ number_format($vencimientos['valor_en_riesgo'] ?? 25000.00, 2) }}</h3>
                                <p class="mb-0">Valor en Riesgo</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Productos por Vencer -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-times me-2"></i>Productos por Vencer
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Lote</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Días Restantes</th>
                                        <th>Cantidad</th>
                                        <th>Valor</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $productosVencimiento = [
                                            (object)['nombre' => 'Paracetamol Jarabe', 'lote' => 'L2024-001', 'fecha_vencimiento' => '2025-02-15', 'cantidad' => 120, 'valor' => 1800.00],
                                            (object)['nombre' => 'Vitamina C', 'lote' => 'L2024-012', 'fecha_vencimiento' => '2025-02-20', 'cantidad' => 85, 'valor' => 2550.00],
                                            (object)['nombre' => 'Jarabe Tos', 'lote' => 'L2024-008', 'fecha_vencimiento' => '2025-01-30', 'cantidad' => 45, 'valor' => 900.00],
                                            (object)['nombre' => 'Pomada Solar', 'lote' => 'L2024-015', 'fecha_vencimiento' => '2025-03-01', 'cantidad' => 200, 'valor' => 4000.00],
                                        ];
                                    @endphp
                                    
                                    @foreach($productosVencimiento as $producto)
                                        @php
                                            $fechaVenc = \Carbon\Carbon::parse($producto->fecha_vencimiento);
                                            $diasRestantes = now()->diffInDays($fechaVenc, false);
                                            $estado = $diasRestantes < 0 ? 'vencido' : ($diasRestantes <= 30 ? 'por_vencer' : 'normal');
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $producto->nombre }}</strong>
                                            </td>
                                            <td>
                                                <code>{{ $producto->lote }}</code>
                                            </td>
                                            <td>
                                                {{ $fechaVenc->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                @if($diasRestantes < 0)
                                                    <span class="badge bg-danger">{{ abs($diasRestantes) }} días vencido</span>
                                                @elseif($diasRestantes <= 7)
                                                    <span class="badge bg-danger">{{ $diasRestantes }} días</span>
                                                @elseif($diasRestantes <= 30)
                                                    <span class="badge bg-warning">{{ $diasRestantes }} días</span>
                                                @else
                                                    <span class="badge bg-success">{{ $diasRestantes }} días</span>
                                                @endif
                                            </td>
                                            <td>{{ $producto->cantidad }}</td>
                                            <td>S/ {{ number_format($producto->valor, 2) }}</td>
                                            <td>
                                                @if($estado == 'vencido')
                                                    <span class="vencimiento-badge">VENCIDO</span>
                                                @elseif($estado == 'por_vencer')
                                                    <span class="badge bg-warning">PRÓXIMO</span>
                                                @else
                                                    <span class="badge bg-success">NORMAL</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Lotes -->
            <div class="tab-pane fade" id="lotes">
                <!-- Control de Lotes -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-barcode me-2"></i>Control de Lotes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Sistema de Trazabilidad:</strong> Cada lote tiene un identificador único que permite rastrear el producto desde el proveedor hasta el cliente final.
                        </div>
                        
                        <!-- Información de Lotes -->
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Últimos Lotes Ingresados</h6>
                                <div class="list-group">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Paracetamol 500mg</strong><br>
                                            <small class="text-muted">L2025-003 - {{ \Carbon\Carbon::parse('2025-01-20')->format('d/m/Y') }}</small>
                                        </div>
                                        <span class="badge bg-success">500 cajas</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Ibuprofeno 400mg</strong><br>
                                            <small class="text-muted">L2025-004 - {{ \Carbon\Carbon::parse('2025-01-21')->format('d/m/Y') }}</small>
                                        </div>
                                        <span class="badge bg-success">300 cajas</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Amoxicilina 500mg</strong><br>
                                            <small class="text-muted">L2025-005 - {{ \Carbon\Carbon::parse('2025-01-22')->format('d/m/Y') }}</small>
                                        </div>
                                        <span class="badge bg-success">150 cajas</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Lotes con Mayor Movimiento</h6>
                                <div class="list-group">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Paracetamol 500mg</strong><br>
                                            <small class="text-muted">L2024-012</small>
                                        </div>
                                        <span class="badge bg-primary">450 vendidos</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Ibuprofeno 400mg</strong><br>
                                            <small class="text-muted">L2024-015</small>
                                        </div>
                                        <span class="badge bg-primary">320 vendidos</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Loratadina 10mg</strong><br>
                                            <small class="text-muted">L2024-018</small>
                                        </div>
                                        <span class="badge bg-primary">280 vendidos</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Cumplimiento -->
            <div class="tab-pane fade" id="cumplimiento">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center text-white" style="background: linear-gradient(135deg, #28a745, #1e7e34);">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-3x mb-2"></i>
                                <h3>{{ $cumplimiento['inspecciones_aprobadas'] ?? 12 }}</h3>
                                <p class="mb-0">Inspecciones Aprobadas</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-center text-white" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
                            <div class="card-body">
                                <i class="fas fa-exclamation-triangle fa-3x mb-2"></i>
                                <h3>{{ $cumplimiento['observaciones'] ?? 3 }}</h3>
                                <p class="mb-0">Observaciones</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-center text-white" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                            <div class="card-body">
                                <i class="fas fa-calendar-check fa-3x mb-2"></i>
                                <h3>{{ $cumplimiento['dias_vencimiento'] ?? 15 }}</h3>
                                <p class="mb-0">Próximo Vencimiento</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertas de Cumplimiento -->
                <div class="row">
                    <div class="col-12">
                        <div class="control-card control-success">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">✅ Todas las Licencias Vigentes</h6>
                                    <p class="mb-0">Registro Sanitario, Autorización de Funcionamiento y Certificado ISO vigentes hasta 2025.</p>
                                </div>
                                <button class="btn btn-success btn-sm" onclick="verLicencias()">
                                    Ver Detalle
                                </button>
                            </div>
                        </div>
                        
                        <div class="control-card control-warning">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock fa-2x me-3 text-warning"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">⚠️ Inspecciones Programadas</h6>
                                    <p class="mb-0">Inspección DIGEMID programada para el {{ \Carbon\Carbon::parse('2025-02-15')->format('d/m/Y') }}.</p>
                                </div>
                                <button class="btn btn-warning btn-sm" onclick="verInspecciones()">
                                    Preparar
                                </button>
                            </div>
                        </div>
                        
                        <div class="control-card control-info">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt fa-2x me-3 text-info"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">🔒 Sistema de Farmacovigilancia Activo</h6>
                                    <p class="mb-0">Reportes automáticos funcionando correctamente. Último reporte enviado: {{ \Carbon\Carbon::parse('2025-01-20')->format('d/m/Y') }}.</p>
                                </div>
                                <button class="btn btn-info btn-sm" onclick="verFarmacovigilancia()">
                                    Ver Reportes
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
            // Inventario Chart
            const inventarioCtx = document.getElementById('inventarioChart').getContext('2d');
            new Chart(inventarioCtx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Valor Inventario',
                        data: [380000, 420000, 450000, 430000, 460000, 450000],
                        borderColor: 'rgb(102, 126, 234)',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: {
                                callback: function(value) {
                                    return 'S/ ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Categoría Chart
            const categoriaCtx = document.getElementById('categoriaChart').getContext('2d');
            new Chart(categoriaCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Analgésicos', 'Antibióticos', 'Vitaminas', 'Otros'],
                    datasets: [{
                        data: [35, 25, 20, 20],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 205, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)'
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
        function actualizarInventario() {
            alert('Actualizando inventario...');
        }

        function generarOrdenCompra(producto) {
            if (confirm('¿Generar orden de compra para ' + producto + '?')) {
                alert('Orden de compra generada para: ' + producto);
            }
        }

        function verHistorial(producto) {
            window.open('/contabilidad/control-farmaceutico/historial/' + encodeURIComponent(producto), '_blank');
        }

        function verLicencias() {
            window.open('/contabilidad/control-farmaceutico/licencias', '_blank');
        }

        function verInspecciones() {
            window.open('/contabilidad/control-farmaceutico/inspecciones', '_blank');
        }

        function verFarmacovigilancia() {
            window.open('/contabilidad/control-farmaceutico/farmacovigilancia', '_blank');
        }
    </script>
</body>
</html>