@extends('layouts.app')

@section('title', 'SIFANO - Área Farmacéutica')

@push('styles')
<style>
    /* Estilos específicos para Farmacia */
    .farmacia-sidebar {
        background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
    }

    .farmacia-brand {
        background: rgba(255,255,255,0.15);
        border-bottom: 1px solid rgba(255,255,255,0.25);
    }

    .farmacia-nav .nav-link:hover,
    .farmacia-nav .nav-link.active {
        background: rgba(255,255,255,0.15);
        border-left-color: #fbbf24;
    }

    .farmacia-card {
        border-left: 4px solid #7c3aed;
    }

    .alert-critical {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: white;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .alert-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .alert-info {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        color: white;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .pharmacy-metric {
        background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
        color: white;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    .inventory-card {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
    }

    .controlled-card {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    }

    .expiration-card {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .temperature-card {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-normal {
        background: #dcfce7;
        color: #166534;
    }

    .status-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .status-critical {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-expired {
        background: #1f2937;
        color: white;
    }

    .controlled-badge {
        background: #dc2626;
        color: white;
        padding: 0.125rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.625rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .temperature-gauge {
        position: relative;
        width: 100px;
        height: 100px;
        margin: 0 auto;
    }

    .temperature-reading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.25rem;
        font-weight: 700;
        text-align: center;
    }

    .medication-form {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .pharmacy-toolbar {
        background: white;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .inventory-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .summary-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        text-align: center;
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 1rem auto;
    }

    .icon-blue {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
    }

    .icon-red {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
    }

    .icon-yellow {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .icon-green {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .control-alert {
        border-left: 4px solid #dc2626;
        background: #fef2f2;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }

    .temperature-monitor {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .stock-alert {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }

    .critical-alert {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-pills text-primary me-2"></i>
            Área Farmacéutica
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                @yield('breadcrumb')
                <li class="breadcrumb-item active">Farmacia</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @hasrole('Administrador|Farmaceutico')
        <button class="btn btn-outline-primary" onclick="exportInventoryReport()">
            <i class="fas fa-download me-2"></i>
            Exportar Inventario
        </button>
        <button class="btn btn-outline-warning" onclick="checkExpirations()">
            <i class="fas fa-clock me-2"></i>
            Verificar Vencimientos
        </button>
        <button class="btn btn-primary" onclick="openControlledMedicationModal()">
            <i class="fas fa-shield-alt me-2"></i>
            Medicamento Controlado
        </button>
        @endhasrole
    </div>
</div>

<!-- Alertas Farmacéuticas Críticas -->
<div class="critical-alert" id="criticalAlert" style="display: none;">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle me-3"></i>
        <div>
            <strong>Alerta Crítica:</strong> <span id="criticalAlertText">Se han detectado medicamentos vencidos o medicamentos controlados sin registro.</span>
        </div>
    </div>
</div>

<div class="alert-warning" id="warningAlert" style="display: none;">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-circle me-3"></i>
        <div>
            <strong>Advertencia:</strong> <span id="warningAlertText">Algunos medicamentos están próximos a vencer o el stock está bajo.</span>
        </div>
    </div>
</div>

@hasrole('Administrador|Farmaceutico')
<!-- Métricas Farmacéuticas -->
<div class="inventory-summary">
    <div class="summary-card">
        <div class="summary-icon icon-green">
            <i class="fas fa-boxes"></i>
        </div>
        <h6 class="text-muted mb-1">Total Productos</h6>
        <h4 class="mb-0">{{ $totalProductos ?? 0 }}</h4>
        <small class="text-muted">
            <i class="fas fa-check me-1"></i>
            {{ $productosDisponibles ?? 0 }} disponibles
        </small>
    </div>
    
    <div class="summary-card">
        <div class="summary-icon icon-red">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h6 class="text-muted mb-1">Medicamentos Controlados</h6>
        <h4 class="mb-0">{{ $medicamentosControlados ?? 0 }}</h4>
        <small class="text-muted">
            <i class="fas fa-clock me-1"></i>
            {{ $ventasControladasHoy ?? 0 }} vendidas hoy
        </small>
    </div>
    
    <div class="summary-card">
        <div class="summary-icon icon-yellow">
            <i class="fas fa-clock"></i>
        </div>
        <h6 class="text-muted mb-1">Próximos a Vencer</h6>
        <h4 class="mb-0">{{ $productosVencer30Dias ?? 0 }}</h4>
        <small class="text-muted">
            <i class="fas fa-calendar me-1"></i>
            Próximos 30 días
        </small>
    </div>
    
    <div class="summary-card">
        <div class="summary-icon icon-blue">
            <i class="fas fa-thermometer-half"></i>
        </div>
        <h6 class="text-muted mb-1">Temperatura Promedio</h6>
        <h4 class="mb-0">{{ number_format($temperaturaPromedio ?? 0, 1) }}°C</h4>
        <small class="{{ (($temperaturaPromedio ?? 0) >= 15 && ($temperaturaPromedio ?? 0) <= 25) ? 'text-success' : 'text-danger' }}">
            <i class="fas fa-thermometer me-1"></i>
            {{ (($temperaturaPromedio ?? 0) >= 15 && ($temperaturaPromedio ?? 0) <= 25) ? 'Rango Normal' : 'Fuera de Rango' }}
        </small>
    </div>
</div>

<!-- Control de Temperaturas -->
<div class="temperature-monitor">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="fas fa-thermometer-half me-2"></i>
            Control de Temperaturas
        </h5>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary" onclick="refreshTemperatureData()">
                <i class="fas fa-sync me-1"></i>
                Actualizar
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="calibrateSensors()">
                <i class="fas fa-cog me-1"></i>
                Calibrar Sensores
            </button>
        </div>
    </div>
    
    <div class="row" id="temperatureSensors">
        <!-- Los sensores de temperatura se cargarán dinámicamente -->
        @foreach($sensoresTemperatura ?? [] as $sensor)
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title">{{ $sensor->nombre }}</h6>
                    <div class="temperature-gauge">
                        <canvas id="tempGauge{{ $sensor->id }}"></canvas>
                        <div class="temperature-reading">
                            {{ number_format($sensor->temperatura_actual, 1) }}°C
                        </div>
                    </div>
                    <span class="status-badge {{ $sensor->estado_css }}">
                        {{ $sensor->estado }}
                    </span>
                    <small class="text-muted d-block mt-2">
                        Última actualización: {{ $sensor->ultima_lectura }}
                    </small>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Alertas de Stock Bajo -->
@if(($productosStockBajo ?? 0) > 0)
<div class="stock-alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-circle me-3"></i>
        <div>
            <strong>Stock Bajo:</strong> {{ $productosStockBajo ?? 0 }} productos necesitan reposición. 
            <a href="{{ route('productos.stock-bajo') }}" class="text-white fw-bold">Ver lista completa</a>
        </div>
    </div>
</div>
@endif
@endhasrole

@yield('farmacia-content')
@endsection

@section('scripts')
<script>
    // Funcionalidades específicas del farmacéutico
    function exportInventoryReport() {
        showLoading();
        
        const reportType = prompt('Tipo de reporte a exportar:\n1. Inventario Completo\n2. Medicamentos Controlados\n3. Productos por Vencer\n4. Stock Bajo\n5. Trazabilidad');
        
        if (reportType) {
            window.open(`/reportes/farmacia/exportar?tipo=${reportType}&formato=excel`, '_blank');
        }
        
        hideLoading();
    }

    function checkExpirations() {
        showLoading();
        
        fetch('/api/farmacia/verificar-vencimientos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                Swal.fire({
                    title: 'Verificación de Vencimientos',
                    html: `
                        <div class="text-start">
                            <p><strong>Vencidos:</strong> ${data.vencidos} productos</p>
                            <p><strong>Próximos 7 días:</strong> ${data.proximos_7_dias}</p>
                            <p><strong>Próximos 30 días:</strong> ${data.proximos_30_dias}</p>
                        </div>
                    `,
                    icon: data.vencidos > 0 ? 'warning' : 'success',
                    confirmButtonText: 'Ver Detalles'
                }).then(() => {
                    window.location.href = '/productos/vencimientos';
                });
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            Swal.fire('Error', 'Error verificando vencimientos', 'error');
        });
    }

    function openControlledMedicationModal() {
        // Modal para registro de medicamentos controlados
        Swal.fire({
            title: 'Medicamento Controlado',
            html: `
                <div class="text-start">
                    <div class="mb-3">
                        <label class="form-label">Producto</label>
                        <select class="form-control" id="controlledProduct">
                            <option value="">Seleccionar producto...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="controlledQuantity" min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">DNI del Cliente</label>
                        <input type="text" class="form-control" id="clientDni" maxlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Número de Receta</label>
                        <input type="text" class="form-control" id="recipeNumber">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Registrar Venta',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const product = document.getElementById('controlledProduct').value;
                const quantity = document.getElementById('controlledQuantity').value;
                const dni = document.getElementById('clientDni').value;
                const recipe = document.getElementById('recipeNumber').value;
                
                if (!product || !quantity || !dni || !recipe) {
                    Swal.showValidationMessage('Todos los campos son obligatorios');
                    return false;
                }
                
                return { product, quantity, dni, recipe };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                registerControlledSale(result.value);
            }
        });
    }

    function registerControlledSale(data) {
        showLoading();
        
        fetch('/api/farmacia/medicamento-controlado', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                Swal.fire('Éxito', 'Venta de medicamento controlado registrada correctamente', 'success');
            } else {
                Swal.fire('Error', data.message || 'Error registrando la venta', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            Swal.fire('Error', 'Error de conexión', 'error');
        });
    }

    function refreshTemperatureData() {
        showLoading();
        
        fetch('/api/farmacia/temperaturas/refresh')
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    updateTemperatureSensors(data.sensores);
                    checkTemperatureAlerts(data.alertas);
                } else {
                    Swal.fire('Error', 'Error actualizando temperaturas', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                Swal.fire('Error', 'Error de conexión', 'error');
            });
    }

    function updateTemperatureSensors(sensores) {
        sensores.forEach(sensor => {
            const canvas = document.getElementById(`tempGauge${sensor.id}`);
            if (canvas) {
                // Actualizar medidor de temperatura
                const ctx = canvas.getContext('2d');
                const temperature = sensor.temperatura;
                const percentage = Math.max(0, Math.min(100, ((temperature - 10) / 20) * 100));
                
                // Dibujar medidor circular
                canvas.width = 100;
                canvas.height = 100;
                
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.beginPath();
                ctx.arc(50, 50, 40, Math.PI, 2 * Math.PI);
                ctx.lineWidth = 8;
                ctx.strokeStyle = '#e5e7eb';
                ctx.stroke();
                
                ctx.beginPath();
                ctx.arc(50, 50, 40, Math.PI, Math.PI + (percentage / 100) * Math.PI);
                ctx.lineWidth = 8;
                ctx.strokeStyle = sensor.temperatura >= 15 && sensor.temperatura <= 25 ? '#10b981' : '#dc2626';
                ctx.stroke();
            }
        });
    }

    function checkTemperatureAlerts(alertas) {
        let criticalCount = 0;
        let warningCount = 0;
        
        alertas.forEach(alerta => {
            if (alerta.nivel === 'critico') {
                criticalCount++;
            } else if (alerta.nivel === 'advertencia') {
                warningCount++;
            }
        });
        
        if (criticalCount > 0) {
            document.getElementById('criticalAlert').style.display = 'block';
            document.getElementById('criticalAlertText').textContent = `${criticalCount} sensores detectaron temperaturas críticas`;
        }
        
        if (warningCount > 0) {
            document.getElementById('warningAlert').style.display = 'block';
            document.getElementById('warningAlertText').textContent = `${warningCount} sensores requieren atención`;
        }
    }

    function calibrateSensors() {
        Swal.fire({
            title: 'Calibración de Sensores',
            text: 'Esta acción puede tomar varios minutos. ¿Deseas continuar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#7c3aed',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, calibrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                
                fetch('/api/farmacia/sensores/calibrar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        Swal.fire('Éxito', 'Calibración completada exitosamente', 'success');
                        refreshTemperatureData();
                    } else {
                        Swal.fire('Error', data.message || 'Error en la calibración', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión durante la calibración', 'error');
                });
            }
        });
    }

    // Load controlled medications for dropdown
    function loadControlledMedications() {
        fetch('/api/farmacia/medicamentos-controlados')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('controlledProduct');
                    select.innerHTML = '<option value="">Seleccionar producto...</option>';
                    
                    data.medicamentos.forEach(med => {
                        const option = document.createElement('option');
                        option.value = med.id;
                        option.textContent = `${med.codigo} - ${med.nombre}`;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading controlled medications:', error);
            });
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        loadControlledMedications();
        
        // Refresh temperature data every 30 seconds
        setInterval(refreshTemperatureData, 30000);
        
        // Auto-refresh critical alerts
        setTimeout(checkForCriticalAlerts, 5000);
    });

    function checkForCriticalAlerts() {
        fetch('/api/farmacia/alertas')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    checkTemperatureAlerts(data.temperaturas || []);
                    
                    // Check expiration alerts
                    if (data.vencimientos_criticos > 0) {
                        document.getElementById('criticalAlert').style.display = 'block';
                        document.getElementById('criticalAlertText').textContent = 
                            `${data.vencimientos_criticos} medicamentos vencidos detectados`;
                    }
                }
            })
            .catch(error => {
                console.error('Error checking alerts:', error);
            });
    }
</script>
@endsection