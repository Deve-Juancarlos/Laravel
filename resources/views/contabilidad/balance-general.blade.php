<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance General - Sistema Contable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .balance-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .balance-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .balance-title {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        .balance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .balance-row:hover {
            background-color: #f8f9fa;
        }
        .balance-total {
            font-weight: bold;
            font-size: 1.1em;
            border-top: 2px solid #3498db;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }
        .balance-amount {
            font-family: 'Courier New', monospace;
            text-align: right;
        }
        .activo-amount { color: #28a745; }
        .pasivo-amount { color: #dc3545; }
        .patrimonio-amount { color: #007bff; }
        .total-amount { color: #6f42c1; font-weight: bold; }
        .btn-export {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="balance-header text-center">
            <h1 class="h2 mb-2">
                <i class="fas fa-balance-scale me-2"></i>
                BALANCE GENERAL
            </h1>
            <p class="mb-0">Distribuidora Farmacéutica</p>
            <p class="mb-0">Al {{ \Carbon\Carbon::parse($fecha_balance ?? now())->format('d \d\e F \d\e Y') }}</p>
        </div>

        <!-- Controls -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="balance-section">
                    <form class="row g-3" method="GET" action="{{ route('contabilidad.balance-general') }}">
                        <div class="col-md-4">
                            <label class="form-label">Fecha del Balance</label>
                            <input type="date" class="form-control" name="fecha" value="{{ request('fecha', now()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>
                                Consultar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-4">
                <div class="balance-section text-center">
                    <h6 class="text-muted mb-3">Exportar Balance</h6>
                    <button class="btn btn-success me-2" onclick="exportarPDF()">
                        <i class="fas fa-file-pdf me-2"></i>PDF
                    </button>
                    <button class="btn btn-info" onclick="exportarExcel()">
                        <i class="fas fa-file-excel me-2"></i>Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Balance General -->
        <div class="row">
            <!-- ACTIVOS -->
            <div class="col-md-6">
                <div class="balance-section">
                    <h4 class="balance-title">
                        <i class="fas fa-wallet me-2"></i>ACTIVOS
                    </h4>
                    
                    <!-- Activos Corrientes -->
                    <div class="mb-3">
                        <h6 class="text-primary mb-2">
                            <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                            ACTIVOS CORRIENTES
                        </h6>
                        
                        <div class="balance-row">
                            <span>Efectivo y Equivalentes</span>
                            <span class="balance-amount activo-amount">
                                {{ number_format($activos['efectivo'] ?? 25000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row">
                            <span>Cuentas por Cobrar</span>
                            <span class="balance-amount activo-amount">
                                {{ number_format($activos['cuentas_cobrar'] ?? 150000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row">
                            <span>Inventarios</span>
                            <span class="balance-amount activo-amount">
                                {{ number_format($activos['inventarios'] ?? 80000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row">
                            <span>Gastos Pagados por Adelantado</span>
                            <span class="balance-amount activo-amount">
                                {{ number_format($activos['gastos_adelantados'] ?? 5000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row balance-total">
                            <span><strong>TOTAL ACTIVOS CORRIENTES</strong></span>
                            <span class="balance-amount activo-amount">
                                <strong>{{ number_format(($activos['efectivo'] ?? 25000) + ($activos['cuentas_cobrar'] ?? 150000) + ($activos['inventarios'] ?? 80000) + ($activos['gastos_adelantados'] ?? 5000), 2) }}</strong>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Activos No Corrientes -->
                    <div class="mb-3">
                        <h6 class="text-primary mb-2">
                            <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                            ACTIVOS NO CORRIENTES
                        </h6>
                        
                        <div class="balance-row">
                            <span>Propiedad, Planta y Equipo</span>
                            <span class="balance-amount activo-amount">
                                {{ number_format($activos['ppe'] ?? 200000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row">
                            <span>Depreciación Acumulada</span>
                            <span class="balance-amount activo-amount">
                                ({{ number_format($activos['depreciacion'] ?? 45000.00, 2) }})
                            </span>
                        </div>
                        
                        <div class="balance-row">
                            <span>Intangibles</span>
                            <span class="balance-amount activo-amount">
                                {{ number_format($activos['intangibles'] ?? 15000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row balance-total">
                            <span><strong>TOTAL ACTIVOS NO CORRIENTES</strong></span>
                            <span class="balance-amount activo-amount">
                                <strong>{{ number_format(($activos['ppe'] ?? 200000) - ($activos['depreciacion'] ?? 45000) + ($activos['intangibles'] ?? 15000), 2) }}</strong>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Total Activos -->
                    <div class="balance-row balance-total">
                        <span><strong>TOTAL ACTIVOS</strong></span>
                        <span class="balance-amount total-amount">
                            <strong>{{ number_format(($activos['efectivo'] ?? 25000) + ($activos['cuentas_cobrar'] ?? 150000) + ($activos['inventarios'] ?? 80000) + ($activos['gastos_adelantados'] ?? 5000) + ($activos['ppe'] ?? 200000) - ($activos['depreciacion'] ?? 45000) + ($activos['intangibles'] ?? 15000), 2) }}</strong>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- PASIVOS Y PATRIMONIO -->
            <div class="col-md-6">
                <div class="balance-section">
                    <h4 class="balance-title">
                        <i class="fas fa-credit-card me-2"></i>PASIVOS Y PATRIMONIO
                    </h4>
                    
                    <!-- Pasivos Corrientes -->
                    <div class="mb-3">
                        <h6 class="text-danger mb-2">
                            <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                            PASIVOS CORRIENTES
                        </h6>
                        
                        <div class="balance-row">
                            <span>Cuentas por Pagar</span>
                            <span class="balance-amount pasivo-amount">
                                {{ number_format($pasivos['cuentas_pagar'] ?? 45000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row">
                            <span>Obligaciones Financieras</span>
                            <span class="balance-amount pasivo-amount">
                                {{ number_format($pasivos['obligaciones'] ?? 30000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row">
                            <span>Provisiones</span>
                            <span class="balance-amount pasivo-amount">
                                {{ number_format($pasivos['provisiones'] ?? 8000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row">
                            <span>Ingresos Diferidos</span>
                            <span class="balance-amount pasivo-amount">
                                {{ number_format($pasivos['ingresos_diferidos'] ?? 2000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row balance-total">
                            <span><strong>TOTAL PASIVOS CORRIENTES</strong></span>
                            <span class="balance-amount pasivo-amount">
                                <strong>{{ number_format(($pasivos['cuentas_pagar'] ?? 45000) + ($pasivos['obligaciones'] ?? 30000) + ($pasivos['provisiones'] ?? 8000) + ($pasivos['ingresos_diferidos'] ?? 2000), 2) }}</strong>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Pasivos No Corrientes -->
                    <div class="mb-3">
                        <h6 class="text-danger mb-2">
                            <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                            PASIVOS NO CORRIENTES
                        </h6>
                        
                        <div class="balance-row">
                            <span>Deudas a Largo Plazo</span>
                            <span class="balance-amount pasivo-amount">
                                {{ number_format($pasivos['largo_plazo'] ?? 60000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row">
                            <span>Provisiones a Largo Plazo</span>
                            <span class="balance-amount pasivo-amount">
                                {{ number_format($pasivos['provisiones_largo'] ?? 12000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row balance-total">
                            <span><strong>TOTAL PASIVOS NO CORRIENTES</strong></span>
                            <span class="balance-amount pasivo-amount">
                                <strong>{{ number_format(($pasivos['largo_plazo'] ?? 60000) + ($pasivos['provisiones_largo'] ?? 12000), 2) }}</strong>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Total Pasivos -->
                    <div class="balance-row balance-total">
                        <span><strong>TOTAL PASIVOS</strong></span>
                        <span class="balance-amount total-amount">
                            <strong>{{ number_format(($pasivos['cuentas_pagar'] ?? 45000) + ($pasivos['obligaciones'] ?? 30000) + ($pasivos['provisiones'] ?? 8000) + ($pasivos['ingresos_diferidos'] ?? 2000) + ($pasivos['largo_plazo'] ?? 60000) + ($pasivos['provisiones_largo'] ?? 12000), 2) }}</strong>
                        </span>
                    </div>
                    
                    <!-- Patrimonio -->
                    <div class="mb-3">
                        <h6 class="text-info mb-2">
                            <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                            PATRIMONIO
                        </h6>
                        
                        <div class="balance-row">
                            <span>Capital Social</span>
                            <span class="balance-amount patrimonio-amount">
                                {{ number_format($patrimonio['capital'] ?? 180000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row">
                            <span>Reservas</span>
                            <span class="balance-amount patrimonio-amount">
                                {{ number_format($patrimonio['reservas'] ?? 25000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row">
                            <span>Utilidades Acumuladas</span>
                            <span class="balance-amount patrimonio-amount">
                                {{ number_format($patrimonio['utilidades'] ?? 52000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row">
                            <span>Utilidad del Ejercicio</span>
                            <span class="balance-amount patrimonio-amount">
                                {{ number_format($patrimonio['utilidad_ejercicio'] ?? 35000.00, 2) }}
                            </span>
                        </div>
                        
                        <div class="balance-row balance-total">
                            <span><strong>TOTAL PATRIMONIO</strong></span>
                            <span class="balance-amount patrimonio-amount">
                                <strong>{{ number_format(($patrimonio['capital'] ?? 180000) + ($patrimonio['reservas'] ?? 25000) + ($patrimonio['utilidades'] ?? 52000) + ($patrimonio['utilidad_ejercicio'] ?? 35000), 2) }}</strong>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Total Pasivos y Patrimonio -->
                    <div class="balance-row balance-total">
                        <span><strong>TOTAL PASIVOS Y PATRIMONIO</strong></span>
                        <span class="balance-amount total-amount">
                            <strong>{{ number_format(
                                (($pasivos['cuentas_pagar'] ?? 45000) + ($pasivos['obligaciones'] ?? 30000) + ($pasivos['provisiones'] ?? 8000) + ($pasivos['ingresos_diferidos'] ?? 2000) + ($pasivos['largo_plazo'] ?? 60000) + ($pasivos['provisiones_largo'] ?? 12000)) + 
                                (($patrimonio['capital'] ?? 180000) + ($patrimonio['reservas'] ?? 25000) + ($patrimonio['utilidades'] ?? 52000) + ($patrimonio['utilidad_ejercicio'] ?? 35000)), 2) }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Análisis Financiero -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="balance-section">
                    <h5 class="balance-title">
                        <i class="fas fa-chart-line me-2"></i>Ratios Financieros
                    </h5>
                    @php
                        $activos_total = ($activos['efectivo'] ?? 25000) + ($activos['cuentas_cobrar'] ?? 150000) + ($activos['inventarios'] ?? 80000) + ($activos['gastos_adelantados'] ?? 5000) + ($activos['ppe'] ?? 200000) - ($activos['depreciacion'] ?? 45000) + ($activos['intangibles'] ?? 15000);
                        $pasivos_total = ($pasivos['cuentas_pagar'] ?? 45000) + ($pasivos['obligaciones'] ?? 30000) + ($pasivos['provisiones'] ?? 8000) + ($pasivos['ingresos_diferidos'] ?? 2000) + ($pasivos['largo_plazo'] ?? 60000) + ($pasivos['provisiones_largo'] ?? 12000);
                        $ratio_liquidez = (($activos['efectivo'] ?? 25000) + ($activos['cuentas_cobrar'] ?? 150000)) / ($pasivos['cuentas_pagar'] ?? 45000);
                        $ratio_endeudamiento = $pasivos_total / $activos_total * 100;
                        $ratio_patrimonio = ($patrimonio['capital'] ?? 180000) / $activos_total * 100;
                    @endphp
                    
                    <div class="balance-row">
                        <span>Razón Corriente</span>
                        <span class="balance-amount">{{ number_format($ratio_liquidez, 2) }}</span>
                    </div>
                    
                    <div class="balance-row">
                        <span>Ratio de Endeudamiento</span>
                        <span class="balance-amount">{{ number_format($ratio_endeudamiento, 1) }}%</span>
                    </div>
                    
                    <div class="balance-row">
                        <span>Ratio de Patrimonio</span>
                        <span class="balance-amount">{{ number_format($ratio_patrimonio, 1) }}%</span>
                    </div>
                    
                    <div class="balance-row">
                        <span>Leverage (Deuda/Patrimonio)</span>
                        <span class="balance-amount">{{ number_format($pasivos_total / (($patrimonio['capital'] ?? 180000) + ($patrimonio['reservas'] ?? 25000) + ($patrimonio['utilidades'] ?? 52000) + ($patrimonio['utilidad_ejercicio'] ?? 35000)), 2) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="balance-section">
                    <h5 class="balance-title">
                        <i class="fas fa-check-circle me-2"></i>Verificación
                    </h5>
                    
                    <div class="balance-row">
                        <span>Total Activos</span>
                        <span class="balance-amount">{{ number_format($activos_total, 2) }}</span>
                    </div>
                    
                    <div class="balance-row">
                        <span>Total Pasivos + Patrimonio</span>
                        <span class="balance-amount">{{ number_format($pasivos_total + (($patrimonio['capital'] ?? 180000) + ($patrimonio['reservas'] ?? 25000) + ($patrimonio['utilidades'] ?? 52000) + ($patrimonio['utilidad_ejercicio'] ?? 35000)), 2) }}</span>
                    </div>
                    
                    <div class="balance-row">
                        <span>Diferencia</span>
                        <span class="balance-amount {{ abs($activos_total - ($pasivos_total + (($patrimonio['capital'] ?? 180000) + ($patrimonio['reservas'] ?? 25000) + ($patrimonio['utilidades'] ?? 52000) + ($patrimonio['utilidad_ejercicio'] ?? 35000)))) < 0.01 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($activos_total - ($pasivos_total + (($patrimonio['capital'] ?? 180000) + ($patrimonio['reservas'] ?? 25000) + ($patrimonio['utilidades'] ?? 52000) + ($patrimonio['utilidad_ejercicio'] ?? 35000))), 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="text-center">
                    <a href="/dashboard/contador" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                    </a>
                    <a href="/contabilidad/reportes/estado-resultados" class="btn btn-outline-primary">
                        <i class="fas fa-chart-line me-2"></i>Ver Estado de Resultados
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportarPDF() {
            window.open('/dashboard/contador/balance-general/exportar?formato=pdf&fecha=' + document.querySelector('input[name="fecha"]').value, '_blank');
        }

        function exportarExcel() {
            window.open('/dashboard/contador/balance-general/exportar?formato=excel&fecha=' + document.querySelector('input[name="fecha"]').value, '_blank');
        }
    </script>
</body>
</html>