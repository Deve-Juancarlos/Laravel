<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Contador - Distribuidora Farmacéutica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .alerta-card {
            border-left: 4px solid;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .alerta-warning {
            border-left-color: #ffc107;
            background-color: #fff3cd;
        }
        
        .alerta-danger {
            border-left-color: #dc3545;
            background-color: #f8d7da;
        }
        
        .alerta-info {
            border-left-color: #17a2b8;
            background-color: #d1ecf1;
        }
        
        .alerta-success {
            border-left-color: #28a745;
            background-color: #d4edda;
        }
        
        .metric-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .metric-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            min-height: 100vh;
            padding: 2rem 1rem;
        }
        
        .menu-item {
            color: white;
            text-decoration: none;
            padding: 0.75rem 1rem;
            margin: 0.25rem 0;
            border-radius: 8px;
            display: block;
            transition: background 0.3s ease;
        }
        
        .menu-item:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        .menu-item.active {
            background-color: rgba(255,255,255,0.2);
        }
        
        .error-alert {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .quick-action-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 1rem;
            border-radius: 10px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40,167,69,0.3);
            color: white;
        }
        
        .pharmacy-icon {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- SIDEBAR DEL CONTADOR -->
            <div class="col-md-3 col-lg-2 sidebar">
                <h4 class="mb-4">
                    <i class="fas fa-calculator me-2"></i>
                    Contabilidad
                </h4>
                
                <nav>
                    <a href="#resumen" class="menu-item active">
                        <i class="fas fa-chart-pie me-2"></i>
                        Resumen General
                    </a>
                    
                    <a href="#libro-mayor" class="menu-item">
                        <i class="fas fa-book me-2"></i>
                        Libro Mayor
                    </a>
                    
                    <a href="#balance" class="menu-item">
                        <i class="fas fa-balance-scale me-2"></i>
                        Balance General
                    </a>
                    
                    <a href="#resultados" class="menu-item">
                        <i class="fas fa-chart-line me-2"></i>
                        Estado de Resultados
                    </a>
                    
                    <a href="#cartera" class="menu-item">
                        <i class="fas fa-hand-holding-usd me-2"></i>
                        Análisis de Cartera
                    </a>
                    
                    <a href="#farmacia" class="menu-item">
                        <i class="fas fa-pills me-2"></i>
                        Control Farmacéutico
                    </a>
                    
                    <a href="#tributario" class="menu-item">
                        <i class="fas fa-file-invoice me-2"></i>
                        Reportes Tributarios
                    </a>
                </nav>
                
                <div class="mt-4 pt-4 border-top border-light">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Última actualización: {{ now()->format('d/m/Y H:i') }}
                    </small>
                </div>
            </div>
            
            <!-- CONTENIDO PRINCIPAL -->
            <div class="col-md-9 col-lg-10 p-4">
                
                <!-- ENCABEZADO -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h3 mb-1">
                                    <i class="fas fa-calculator me-2 text-primary"></i>
                                    Dashboard Contable
                                </h1>
                                <p class="text-muted mb-0">
                                    Distribuidora Farmacéutica - {{ now()->format('d \d\e F \d\e Y') }}
                                </p>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-primary fs-6">
                                    <i class="fas fa-user me-1"></i>
                                    {{ Auth::user()->name ?? 'Contador' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ERROR DE CONEXIÓN -->
                @isset($error)
                    <div class="error-alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ $error }}
                        <button class="btn btn-sm btn-outline-danger ms-2" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Reintentar
                        </button>
                    </div>
                @endisset

                <!-- MÉTRICAS PRINCIPALES -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <div class="pharmacy-icon rounded-circle p-3 me-3">
                                    <i class="fas fa-pills text-white fa-2x"></i>
                                </div>
                                <div>
                                    <div class="metric-number">{{ number_format($resumen_general['total_activos'] ?? 0, 2) }}</div>
                                    <div class="metric-label">Total Activos</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <div class="bg-success rounded-circle p-3 me-3">
                                    <i class="fas fa-shopping-cart text-white fa-2x"></i>
                                </div>
                                <div>
                                    <div class="metric-number">{{ number_format($resumen_general['ventas_mes'] ?? 0, 2) }}</div>
                                    <div class="metric-label">Ventas del Mes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <div class="bg-warning rounded-circle p-3 me-3">
                                    <i class="fas fa-exclamation-triangle text-white fa-2x"></i>
                                </div>
                                <div>
                                    <div class="metric-number">{{ number_format($resumen_general['cartera_vencida'] ?? 0, 2) }}</div>
                                    <div class="metric-label">Cartera Vencida</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <div class="bg-info rounded-circle p-3 me-3">
                                    <i class="fas fa-calendar-times text-white fa-2x"></i>
                                </div>
                                <div>
                                    <div class="metric-number">{{ $resumen_general['productos_vencer'] ?? 0 }}</div>
                                    <div class="metric-label">Productos por Vencer</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACCIONES RÁPIDAS -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3">
                            <i class="fas fa-bolt me-2"></i>
                            Acciones Rápidas
                        </h5>
                    </div>
                    
                    <div class="col-md-3">
                        <button class="btn quick-action-btn w-100 mb-2" onclick="abrirLibroMayor()">
                            <i class="fas fa-book me-2"></i>
                            Ver Libro Mayor
                        </button>
                    </div>
                    
                    <div class="col-md-3">
                        <button class="btn quick-action-btn w-100 mb-2" onclick="generarBalance()">
                            <i class="fas fa-balance-scale me-2"></i>
                            Balance General
                        </button>
                    </div>
                    
                    <div class="col-md-3">
                        <button class="btn quick-action-btn w-100 mb-2" onclick="analizarCartera()">
                            <i class="fas fa-hand-holding-usd me-2"></i>
                            Análisis Cartera
                        </button>
                    </div>
                    
                    <div class="col-md-3">
                        <button class="btn quick-action-btn w-100 mb-2" onclick="controlFarmaceutico()">
                            <i class="fas fa-pills me-2"></i>
                            Control Farmacia
                        </button>
                    </div>
                </div>

                <!-- ALERTAS CONTABLES -->
                @if(!empty($alertas_contables))
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">
                                <i class="fas fa-bell me-2 text-warning"></i>
                                Alertas Contables
                            </h5>
                            
                            @foreach($alertas_contables as $alerta)
                                <div class="alerta-card alerta-{{ $alerta['tipo'] }}">
                                    <div class="d-flex align-items-center">
                                        <i class="{{ $alerta['icono'] }} me-3 fa-2x"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $alerta['titulo'] }}</h6>
                                            <p class="mb-0">{{ $alerta['mensaje'] }}</p>
                                        </div>
                                        <a href="{{ $alerta['url'] }}" class="btn btn-sm btn-outline-primary">
                                            Ver Detalle
                                            <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- CUENTAS PRINCIPALES -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-list me-2"></i>
                                    Principales Cuentas Contables
                                </h5>
                            </div>
                            <div class="card-body">
                                @if(!empty($cuentas_principales))
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Cuenta</th>
                                                    <th>Descripción</th>
                                                    <th class="text-end">Saldo</th>
                                                    <th>Naturaleza</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($cuentas_principales as $cuenta)
                                                    <tr style="cursor: pointer;" onclick="verCuenta('{{ $cuenta->cuenta }}')">
                                                        <td>
                                                            <strong>{{ $cuenta->cuenta }}</strong>
                                                        </td>
                                                        <td>{{ $cuenta->descripcion }}</td>
                                                        <td class="text-end">
                                                            <span class="fw-bold {{ $cuenta->saldo >= 0 ? 'text-success' : 'text-danger' }}">
                                                                {{ number_format($cuenta->saldo, 2) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $cuenta->naturaleza == 'A' ? 'primary' : 'secondary' }}">
                                                                {{ $cuenta->naturaleza }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No se encontraron cuentas contables</p>
                                        <small class="text-muted">Verifique la conexión con la base de datos</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- ÚLTIMOS MOVIMIENTOS -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    Últimos Movimientos
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                @if(!empty($ultimos_movimientos))
                                    @foreach($ultimos_movimientos as $movimiento)
                                        <div class="border-bottom p-2">
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">{{ $movimiento->comprobante ?? 'N/A' }}</small>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($movimiento->fecha ?? now())->format('d/m') }}</small>
                                            </div>
                                            <div class="small">{{ $movimiento->glosa ?? 'Sin descripción' }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="p-3 text-center text-muted">
                                        <small>Sin movimientos recientes</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARTERA VENCIDA -->
                @if(!empty($cartera_vencida))
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                                        Top 5 - Clientes con Mayor Deuda Vencida
                                    </h5>
                                    <a href="{{ route('contabilidad.analisis-cartera') }}" class="btn btn-sm btn-outline-primary">
                                        Ver Análisis Completo
                                    </a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Cliente</th>
                                                    <th class="text-end">Total Vencido</th>
                                                    <th class="text-end">Días Máximo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($cartera_vencida as $deudor)
                                                    <tr onclick="verCliente('{{ $deudor->cliente }}')">
                                                        <td>
                                                            <strong>{{ $deudor->cliente }}</strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="text-danger fw-bold">
                                                                S/ {{ number_format($deudor->total_vencido, 2) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="badge bg-{{ $deudor->dias_maximo > 90 ? 'danger' : 'warning' }}">
                                                                {{ $deudor->dias_maximo }} días
                                                            </span>
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
                @endif

            </div>
        </div>
    </div>

    <!-- Modal Libro Mayor -->
    <div class="modal fade" id="modalLibroMayor" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-book me-2"></i>
                        Libro Mayor
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formLibroMayor">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Cuenta Contable</label>
                                <select class="form-select" id="cuentaSelect" required>
                                    <option value="">Seleccionar cuenta...</option>
                                    @foreach($cuentas_principales ?? [] as $cuenta)
                                        <option value="{{ $cuenta->cuenta }}">{{ $cuenta->cuenta }} - {{ $cuenta->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fechaInicio" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fechaFin" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>
                                    Consultar
                                </button>
                            </div>
                        </div>
                    </form>
                    <div id="resultadoLibroMayor" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configurar fechas por defecto
        document.getElementById('fechaInicio').value = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
        document.getElementById('fechaFin').value = new Date().toISOString().split('T')[0];

        // Funciones para las acciones rápidas
        function abrirLibroMayor() {
            new bootstrap.Modal(document.getElementById('modalLibroMayor')).show();
        }

        function generarBalance() {
            window.location.href = "{{ route('contabilidad.balance-general') }}";
        }

        function analizarCartera() {
            window.location.href = "{{ route('contabilidad.analisis-cartera') }}";
        }

        function controlFarmaceutico() {
            window.location.href = "{{ route('contabilidad.control-farmaceutico') }}";
        }

        function verCuenta(cuenta) {
            document.getElementById('cuentaSelect').value = cuenta;
            abrirLibroMayor();
        }

        // Manejar formulario del libro mayor
        document.getElementById('formLibroMayor').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const cuenta = document.getElementById('cuentaSelect').value;
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            
            if (!cuenta || !fechaInicio || !fechaFin) {
                alert('Por favor complete todos los campos');
                return;
            }
            
            // Redirigir a la vista del libro mayor
            const url = `{{ route('contabilidad.libro-mayor') }}?cuenta=${cuenta}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
            window.location.href = url;
        });

        // Actualizar la página cada 5 minutos
        setTimeout(function() {
            location.reload();
        }, 300000);

        // Efectos visuales
        document.addEventListener('DOMContentLoaded', function() {
            // Animar las cards
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });
        });
    </script>
</body>
</html>
