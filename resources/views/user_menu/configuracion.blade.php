@use('Illuminate\Support\Str')
@extends('layouts.app')
@section('title', 'Configuración')
@section('page-title', 'Configuración')
@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Configuración</li>
@endsection

@push('styles')
<style>
    :root {
        --primary: #2563eb;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --info: #06b6d4;
        --dark: #1e293b;
        --border: #e2e8f0;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .settings-container {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .settings-header {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid var(--border);
        animation: fadeIn 0.5s ease;
    }

    .settings-header h5 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .settings-header p {
        color: #64748b;
        margin: 0.25rem 0 0 0;
        font-size: 0.875rem;
    }

    .settings-sidebar {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid var(--border);
        animation: fadeIn 0.6s ease;
        position: sticky;
        top: 2rem;
    }

    .settings-nav {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .settings-nav-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        color: #64748b;
        font-weight: 600;
        border: 2px solid transparent;
    }

    .settings-nav-item:hover {
        background: #f8fafc;
        color: var(--primary);
    }

    .settings-nav-item.active {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: var(--primary);
        border-color: var(--primary);
    }

    .settings-nav-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        background: #f8fafc;
        transition: all 0.3s ease;
    }

    .settings-nav-item.active .settings-nav-icon {
        background: var(--primary);
        color: white;
    }

    .settings-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid var(--border);
        margin-bottom: 1.5rem;
        animation: fadeIn 0.7s ease;
    }

    .settings-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .settings-card-title {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .settings-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .settings-card-icon.primary {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: var(--primary);
    }

    .settings-card-icon.success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: var(--success);
    }

    .settings-card-icon.warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: var(--warning);
    }

    .settings-card-icon.info {
        background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%);
        color: var(--info);
    }

    .settings-card h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .setting-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 0;
        border-bottom: 1px solid #f8fafc;
    }

    .setting-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .setting-info h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--dark);
        margin: 0 0 0.25rem 0;
    }

    .setting-info p {
        font-size: 0.875rem;
        color: #64748b;
        margin: 0;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 32px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #cbd5e1;
        transition: 0.4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 24px;
        width: 24px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    input:checked + .slider {
        background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
    }

    input:checked + .slider:before {
        transform: translateX(28px);
    }

    .form-group-custom {
        margin-bottom: 1.5rem;
    }

    .form-label-custom {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
        display: block;
        font-size: 0.875rem;
    }

    .form-control-custom, .form-select-custom {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-control-custom:focus, .form-select-custom:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    .btn-primary-gradient {
        background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        border: none;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        color: white;
    }

    .btn-danger-gradient {
        background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        border: none;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-danger-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        color: white;
    }

    .danger-zone {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border: 2px solid #fca5a5;
        border-radius: 16px;
        padding: 2rem;
        margin-top: 2rem;
    }

    .danger-zone h4 {
        color: var(--danger);
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .danger-zone p {
        color: #991b1b;
        margin-bottom: 1rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.875rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #059669;
    }

    .status-badge.warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #d97706;
    }

    @media (max-width: 992px) {
        .settings-sidebar {
            position: static;
            margin-bottom: 2rem;
        }

        .settings-nav {
            flex-direction: row;
            overflow-x: auto;
        }

        .settings-nav-item {
            min-width: 200px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.settings-nav-item');
    const sections = {
        'general': document.getElementById('general-section'),
        'notifications': document.getElementById('notifications-section'),
        'appearance': document.getElementById('appearance-section'),
        'reports': document.getElementById('reports-section'),
        'integrations': document.getElementById('integrations-section')
    };

    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            
            const section = this.getAttribute('data-section');
            
            Object.values(sections).forEach(s => s.style.display = 'none');
            if (sections[section]) {
                sections[section].style.display = 'block';
            }
        });
    });
});
</script>
@endpush

@section('content')
<div class="settings-container">
    <div class="container-fluid">
        <div class="settings-header">
            <h5>Configuración del Sistema</h5>
            <p>Personaliza tu experiencia y ajusta las preferencias</p>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <div class="settings-sidebar">
                    <nav class="settings-nav">
                        <a href="#general" class="settings-nav-item active" data-section="general">
                            <div class="settings-nav-icon">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <span>General</span>
                        </a>
                        <a href="#notifications" class="settings-nav-item" data-section="notifications">
                            <div class="settings-nav-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <span>Notificaciones</span>
                        </a>
                        <a href="#appearance" class="settings-nav-item" data-section="appearance">
                            <div class="settings-nav-icon">
                                <i class="fas fa-palette"></i>
                            </div>
                            <span>Apariencia</span>
                        </a>
                        <a href="#reports" class="settings-nav-item" data-section="reports">
                            <div class="settings-nav-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <span>Reportes</span>
                        </a>
                        <a href="#integrations" class="settings-nav-item" data-section="integrations">
                            <div class="settings-nav-icon">
                                <i class="fas fa-plug"></i>
                            </div>
                            <span>Integraciones</span>
                        </a>
                    </nav>
                </div>
            </div>

            <div class="col-lg-9">
                <div id="general-section">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-card-title">
                                <div class="settings-card-icon primary">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <h3>Configuración General</h3>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Idioma del Sistema</h4>
                                <p>Selecciona el idioma de la interfaz</p>
                            </div>
                            <select class="form-select-custom" style="width: 200px;">
                                <option selected>Español (Perú)</option>
                                <option>Inglés (US)</option>
                                <option>Portugués (BR)</option>
                            </select>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Zona Horaria</h4>
                                <p>Define tu zona horaria local</p>
                            </div>
                            <select class="form-select-custom" style="width: 250px;">
                                <option selected>América/Lima (GMT-5)</option>
                                <option>América/Bogotá (GMT-5)</option>
                                <option>América/Mexico_City (GMT-6)</option>
                            </select>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Formato de Fecha</h4>
                                <p>Cómo se mostrarán las fechas</p>
                            </div>
                            <select class="form-select-custom" style="width: 200px;">
                                <option selected>DD/MM/YYYY</option>
                                <option>MM/DD/YYYY</option>
                                <option>YYYY-MM-DD</option>
                            </select>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Moneda Predeterminada</h4>
                                <p>Moneda para transacciones y reportes</p>
                            </div>
                            <select class="form-select-custom" style="width: 200px;">
                                <option selected>PEN (S/)</option>
                                <option>USD ($)</option>
                                <option>EUR (€)</option>
                            </select>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-card-title">
                                <div class="settings-card-icon success">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h3>Seguridad y Privacidad</h3>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Autenticación de Dos Factores (2FA)</h4>
                                <p>Agrega una capa extra de seguridad</p>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="status-badge warning">Desactivado</span>
                                <button class="btn-primary-gradient btn-sm">
                                    <i class="fas fa-lock"></i>
                                    Activar
                                </button>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Tiempo de Sesión</h4>
                                <p>Cierre automático de sesión por inactividad</p>
                            </div>
                            <select class="form-select-custom" style="width: 200px;">
                                <option>15 minutos</option>
                                <option selected>30 minutos</option>
                                <option>1 hora</option>
                                <option>Nunca</option>
                            </select>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Registro de Actividad</h4>
                                <p>Mantener historial de acciones</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="notifications-section" style="display: none;">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-card-title">
                                <div class="settings-card-icon warning">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <h3>Preferencias de Notificaciones</h3>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Notificaciones por Email</h4>
                                <p>Recibir alertas importantes por correo</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Notificaciones Push</h4>
                                <p>Alertas en tiempo real en el navegador</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Alertas de Stock Bajo</h4>
                                <p>Notificar cuando productos estén por agotarse</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Alertas de Vencimiento</h4>
                                <p>Notificar productos próximos a vencer</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Resumen Diario</h4>
                                <p>Reporte diario de ventas y movimientos</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Alertas de Facturas Vencidas</h4>
                                <p>Notificar facturas pendientes de pago</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="appearance-section" style="display: none;">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-card-title">
                                <div class="settings-card-icon info">
                                    <i class="fas fa-palette"></i>
                                </div>
                                <h3>Personalización Visual</h3>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Tema del Sistema</h4>
                                <p>Modo claro u oscuro</p>
                            </div>
                            <select class="form-select-custom" style="width: 200px;">
                                <option selected>Modo Claro</option>
                                <option>Modo Oscuro</option>
                                <option>Automático</option>
                            </select>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Color de Acento</h4>
                                <p>Color principal de la interfaz</p>
                            </div>
                            <div class="d-flex gap-2">
                                <div style="width: 40px; height: 40px; background: #2563eb; border-radius: 10px; cursor: pointer; border: 3px solid #2563eb;"></div>
                                <div style="width: 40px; height: 40px; background: #10b981; border-radius: 10px; cursor: pointer; border: 2px solid #e2e8f0;"></div>
                                <div style="width: 40px; height: 40px; background: #f59e0b; border-radius: 10px; cursor: pointer; border: 2px solid #e2e8f0;"></div>
                                <div style="width: 40px; height: 40px; background: #8b5cf6; border-radius: 10px; cursor: pointer; border: 2px solid #e2e8f0;"></div>
                                <div style="width: 40px; height: 40px; background: #ef4444; border-radius: 10px; cursor: pointer; border: 2px solid #e2e8f0;"></div>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Densidad de la UI</h4>
                                <p>Espaciado entre elementos</p>
                            </div>
                            <select class="form-select-custom" style="width: 200px;">
                                <option>Compacta</option>
                                <option selected>Normal</option>
                                <option>Espaciada</option>
                            </select>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Animaciones</h4>
                                <p>Efectos de transición y movimiento</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="reports-section" style="display: none;">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-card-title">
                                <div class="settings-card-icon success">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h3>Configuración de Reportes</h3>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Formato de Exportación</h4>
                                <p>Formato predeterminado para exportar</p>
                            </div>
                            <select class="form-select-custom" style="width: 200px;">
                                <option selected>Excel (.xlsx)</option>
                                <option>PDF</option>
                                <option>CSV</option>
                            </select>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Incluir Logo en Reportes</h4>
                                <p>Agregar logo de la empresa en PDFs</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Reportes Automáticos</h4>
                                <p>Generar reportes programados</p>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked>
                                    <label class="form-check-label">Ventas Diarias</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked>
                                    <label class="form-check-label">Stock Semanal</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox">
                                    <label class="form-check-label">Cuentas por Cobrar Mensual</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="integrations-section" style="display: none;">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-card-title">
                                <div class="settings-card-icon primary">
                                    <i class="fas fa-plug"></i>
                                </div>
                                <h3>Integraciones Externas</h3>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>SUNAT API</h4>
                                <p>Conexión con servicios de SUNAT</p>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="status-badge success">Conectado</span>
                                <button class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-sync"></i>
                                    Reconfigurar
                                </button>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Facturación Electrónica</h4>
                                <p>Sistema de emisión de comprobantes</p>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="status-badge success">Activo</span>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>API Key</h4>
                                <p>Clave para integraciones de terceros</p>
                            </div>
                            <button class="btn-primary-gradient btn-sm">
                                <i class="fas fa-key"></i>
                                Generar Nueva Key
                            </button>
                        </div>
                    </div>
                </div>

                <div class="danger-zone">
                    <h4>
                        <i class="fas fa-exclamation-triangle"></i>
                        Zona de Peligro
                    </h4>
                    <p>Las siguientes acciones son irreversibles y pueden afectar permanentemente tus datos.</p>
                    
                    <div class="d-flex gap-3 flex-wrap">
                        <button class="btn btn-outline-danger">
                            <i class="fas fa-trash"></i>
                            Limpiar Caché
                        </button>
                        <button class="btn btn-outline-danger">
                            <i class="fas fa-undo"></i>
                            Restaurar Configuración
                        </button>
@endsection