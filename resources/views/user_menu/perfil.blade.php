@extends('layouts.app')
@section('title', 'Mi Perfil')
@section('page-title', 'Mi Perfil')
@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Mi Perfil</li>
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

    .profile-container {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }

    /* Header con gradiente */
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 3rem 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        animation: fadeIn 0.5s ease;
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .profile-header-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .profile-info {
        flex: 1;
        color: white;
    }

    .profile-name {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
    }

    .profile-role {
        display: inline-block;
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .profile-stats {
        display: flex;
        gap: 2rem;
        margin-top: 1.5rem;
    }

    .profile-stat {
        text-align: center;
    }

    .profile-stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        display: block;
    }

    .profile-stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Contenedor de tabs */
    .profile-tabs {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid var(--border);
    }

    .nav-tabs {
        border: none;
        gap: 1rem;
    }

    .nav-tabs .nav-link {
        border: none;
        background: transparent;
        color: #64748b;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        background: #f8fafc;
        color: var(--primary);
    }

    .nav-tabs .nav-link.active {
        background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    /* Cards de contenido */
    .info-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid var(--border);
        margin-bottom: 1.5rem;
        animation: fadeIn 0.6s ease;
    }

    .info-card-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .info-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .info-card-icon.primary {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: var(--primary);
    }

    .info-card-icon.success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: var(--success);
    }

    .info-card-icon.warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: var(--warning);
    }

    .info-card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f8fafc;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-value {
        font-weight: 600;
        color: var(--dark);
    }

    /* Botones mejorados */
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

    .btn-outline-custom {
        background: white;
        color: var(--dark);
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        border: 2px solid var(--border);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-outline-custom:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: var(--dark);
    }

    /* Form mejorado */
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

    .form-control-custom {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-control-custom:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    /* Activity timeline */
    .activity-timeline {
        position: relative;
        padding-left: 2rem;
    }

    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(180deg, var(--primary) 0%, transparent 100%);
    }

    .activity-item {
        position: relative;
        padding-bottom: 2rem;
    }

    .activity-dot {
        position: absolute;
        left: -1.55rem;
        top: 0.25rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--primary);
        border: 3px solid white;
        box-shadow: 0 0 0 2px var(--primary);
    }

    .activity-content {
        background: #f8fafc;
        padding: 1rem;
        border-radius: 10px;
        border-left: 3px solid var(--primary);
    }

    .activity-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.25rem;
    }

    .activity-time {
        font-size: 0.75rem;
        color: #64748b;
    }

    .is-invalid {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }
    .alert {
        padding: 0.75rem 1rem;
        border-radius: 10px;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    @media (max-width: 768px) {
        .profile-header-content {
            flex-direction: column;
            text-align: center;
        }

        .profile-stats {
            justify-content: center;
        }

        .info-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
</style>
@endpush
@livewireStyles
@section('content')
<div class="profile-container">
    <div class="container-fluid">
        <!-- Header del Perfil -->
        <div class="profile-header">
            <div class="profile-header-content">
                
                @livewire('profile-avatar')
                
                <div class="profile-info">
                    <h1 class="profile-name">{{ ucfirst(strtolower($user->usuario)) }}</h1>
                    <span class="profile-role">
                        <i class="fas fa-user-tie me-2"></i>
                        {{ ucfirst(strtolower($user->tipousuario)) }}
                    </span>
                    
                    <div class="profile-stats">
                        <div class="profile-stat">
                            <span class="profile-stat-value">156</span>
                            <span class="profile-stat-label">Ventas</span>
                        </div>
                        <div class="profile-stat">
                            <span class="profile-stat-value">24</span>
                            <span class="profile-stat-label">Reportes</span>
                        </div>
                        <div class="profile-stat">
                            <span class="profile-stat-value">98%</span>
                            <span class="profile-stat-label">Eficiencia</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs de navegación -->
        <div class="profile-tabs">
            <ul class="nav nav-tabs" id="profileTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">
                        <i class="fas fa-user me-2"></i>Información Personal
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button">
                        <i class="fas fa-lock me-2"></i>Seguridad
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button">
                        <i class="fas fa-history me-2"></i>Actividad Reciente
                    </button>
                </li>
            </ul>
        </div>

        <!-- Contenido de los tabs -->
        <div class="tab-content" id="profileTabContent">
            <!-- Tab de Información Personal -->
            <div class="tab-pane fade show active" id="info" role="tabpanel">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="info-card">
                            <div class="info-card-header">
                                <div class="info-card-icon primary">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h3 class="info-card-title">Datos Personales</h3>
                            </div>

                            <form>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Nombre Completo</label>
                                            <input type="text" class="form-control-custom" value="{{ ucfirst(strtolower($user->usuario)) }}" placeholder="Nombre completo">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Correo Electrónico</label>
                                            <input type="email" class="form-control-custom" value="{{ $user->usuario }}@sedimcorp.com" placeholder="correo@ejemplo.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Teléfono</label>
                                            <input type="tel" class="form-control-custom" value="{{ $empleado->telefono_formateado }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">DNI</label>
                                            <input type="text" class="form-control-custom" value="{{ $empleado->Documento }}" placeholder="12345678">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Dirección</label>
                                            <input type="text" class="form-control-custom" value="{{ $empleado->Direccion }}" placeholder="Dirección completa">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-3 justify-content-end mt-4">
                                    <button type="button" class="btn-outline-custom">
                                        <i class="fas fa-times"></i>
                                        Cancelar
                                    </button>
                                    <button type="submit" class="btn-primary-gradient">
                                        <i class="fas fa-save"></i>
                                        Guardar Cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="info-card">
                            <div class="info-card-header">
                                <div class="info-card-icon success">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <h3 class="info-card-title">Información Laboral</h3>
                            </div>

                            <div class="info-row">
                                <span class="info-label">
                                    <i class="fas fa-id-badge text-primary"></i>
                                    Cargo
                                </span>
                                <span class="info-value"><i>{{ ucfirst(strtolower($user->tipousuario)) }}</i></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">
                                    <i class="fas fa-calendar-alt text-success"></i>
                                    Fecha de Creacion de Usuario
                                </span>
                                <span class="info-value"><i>{{ $user->created_at }}</i></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">
                                    <i class="fas fa-building text-info"></i>
                                    Departamento
                                </span>
                                <span class="info-value">Contabilidad</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">
                                    <i class="fas fa-clock text-warning"></i>
                                    Horario
                                </span>
                                <span class="info-value">8:00 AM - 6:00 PM</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab de Seguridad -->
            <div class="tab-pane fade" id="security" role="tabpanel">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="info-card">
                            <div class="info-card-header">
                                <div class="info-card-icon warning">
                                    <i class="fas fa-key"></i>
                                </div>
                                <h3 class="info-card-title">Cambiar Contraseña</h3>
                            </div>

                            <!-- Mensajes de éxito/error -->
                            @if (session('success'))
                                <div class="alert alert-success mb-3">
                                    {{ session('success') }}
                                </div>
                            @endif
                            @if ($errors->has('current_password'))
                                <div class="alert alert-danger mb-3">
                                    {{ $errors->first('current_password') }}
                                </div>
                            @endif

                            <form action="{{ route('perfil.password.update') }}" method="POST">
                                @csrf

                                <div class="form-group-custom">
                                    <label class="form-label-custom">Contraseña Actual</label>
                                    <input type="password" name="current_password" class="form-control-custom @error('current_password') is-invalid @enderror" placeholder="••••••••" required>
                                    @error('current_password')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group-custom">
                                    <label class="form-label-custom">Nueva Contraseña</label>
                                    <input type="password" name="password" class="form-control-custom @error('password') is-invalid @enderror" placeholder="••••••••" required>
                                    @error('password')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group-custom">
                                    <label class="form-label-custom">Confirmar Nueva Contraseña</label>
                                    <input type="password" name="password_confirmation" class="form-control-custom" placeholder="••••••••" required>
                                </div>

                                <button type="submit" class="btn-primary-gradient w-100">
                                    <i class="fas fa-shield-alt"></i>
                                    Actualizar Contraseña
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="info-card">
                            <div class="info-card-header">
                                <div class="info-card-icon primary">
                                    <i class="fas fa-laptop"></i>
                                </div>
                                <h3 class="info-card-title">Sesiones Activas</h3>
                            </div>

                            <div class="info-row">
                                <div>
                                    <div class="info-label">
                                        <i class="fas fa-desktop text-success"></i>
                                        Windows - Chrome
                                    </div>
                                    <small class="text-muted">Trujillo, Perú • Ahora</small>
                                </div>
                                <span class="badge bg-success">Actual</span>
                            </div>
                            <div class="info-row">
                                <div>
                                    <div class="info-label">
                                        <i class="fas fa-mobile-alt text-info"></i>
                                        Android - App Móvil
                                    </div>
                                    <small class="text-muted">Lima, Perú • Hace 2 horas</small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger">Cerrar</button>
                            </div>
                        </div>

                        <div class="info-card mt-3">
                            <div class="info-card-header">
                                <div class="info-card-icon success">
                                    <i class="fas fa-shield-check"></i>
                                </div>
                                <h3 class="info-card-title">Autenticación en Dos Pasos</h3>
                            </div>
                            <p class="text-muted mb-3">Agrega una capa extra de seguridad a tu cuenta.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab de Actividad Reciente -->
            <div class="tab-pane fade" id="activity" role="tabpanel">
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-card-icon primary">
                            <i class="fas fa-history"></i>
                        </div>
                        <h3 class="info-card-title">Actividad Reciente</h3>
                    </div>

                    <div class="activity-timeline">
                        <div class="activity-item">
                            <div class="activity-dot"></div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <i class="fas fa-box text-primary me-2"></i>
                                    Actualizó el producto "Aspirina 100mg"
                                </div>
                                <div class="activity-time">Hace 2 horas</div>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-dot"></div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <i class="fas fa-file-invoice text-success me-2"></i>
                                    Generó reporte de ventas mensual
                                </div>
                                <div class="activity-time">Hace 5 horas</div>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-dot"></div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <i class="fas fa-sign-in-alt text-info me-2"></i>
                                    Inicio de sesión desde Windows
                                </div>
                                <div class="activity-time">Hace 8 horas</div>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-dot"></div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <i class="fas fa-users text-warning me-2"></i>
                                    Actualizó información de cliente "Botica Santa Rosa"
                                </div>
                                <div class="activity-time">Ayer a las 4:30 PM</div>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-dot"></div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <i class="fas fa-chart-line text-success me-2"></i>
                                    Revisó dashboard de ventas
                                </div>
                                <div class="activity-time">Ayer a las 2:15 PM</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const activeTab = "{{ session('tab') ?? 'info' }}";
        const tab = new bootstrap.Tab(document.querySelector(`#${activeTab}-tab`));
        tab.show();
    });
</script>
@endpush

@endsection