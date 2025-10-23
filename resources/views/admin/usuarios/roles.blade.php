@extends('layouts.admin')

@push('styles')
<style>
    /* Main Container */
    .roles-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .roles-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        overflow: hidden;
    }

    .roles-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }

    .roles-header h4 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }

    .roles-header p {
        margin: 0.5rem 0 0 0;
        opacity: 0.95;
        font-size: 1rem;
    }

    .roles-body {
        padding: 2.5rem;
    }

    /* User Info Section */
    .user-info-section {
        background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2.5rem;
        border: 2px solid #e0e7ff;
    }

    .user-info-label {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .user-info-value {
        font-size: 1.5rem;
        color: #1e293b;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar-large {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.25rem;
        text-transform: uppercase;
    }

    /* Role Selection */
    .role-selection-title {
        font-size: 1.25rem;
        color: #1e293b;
        font-weight: 700;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .role-cards-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    /* Role Card */
    .role-card {
        border: 3px solid #e2e8f0;
        border-radius: 16px;
        padding: 2rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        position: relative;
    }

    .role-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        transform: translateY(-4px);
    }

    .role-card.selected {
        border-color: #667eea;
        background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.25);
        transform: translateY(-4px);
    }

    .role-card-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .role-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: white;
    }

    .role-icon.admin {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .role-icon.user {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .role-title {
        flex: 1;
    }

    .role-title h5 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
    }

    .role-title .role-badge-mini {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 0.25rem;
    }

    .role-badge-mini.admin {
        background: #fef2f2;
        color: #dc2626;
    }

    .role-badge-mini.user {
        background: #eff6ff;
        color: #2563eb;
    }

    .role-description {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 1.25rem;
        line-height: 1.6;
    }

    .role-permissions {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .role-permissions li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0;
        color: #475569;
        font-size: 0.9rem;
    }

    .role-permissions li i {
        color: #10b981;
        font-size: 0.875rem;
    }

    /* Radio Input (hidden) */
    .role-card input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    /* Selected Indicator */
    .selected-indicator {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: none;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
    }

    .role-card.selected .selected-indicator {
        display: flex;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
    }

    .btn-save-role {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1rem 2.5rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }

    .btn-save-role:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-cancel-role {
        background: white;
        color: #64748b;
        border: 2px solid #e2e8f0;
        padding: 1rem 2.5rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }

    .btn-cancel-role:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .role-card {
        animation: fadeInUp 0.5s ease;
    }

    .role-card:nth-child(2) {
        animation-delay: 0.1s;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="roles-container">
        <div class="roles-card">
            <!-- Header -->
            <div class="roles-header">
                <h4>
                    <i class="fas fa-user-tag"></i>
                    Asignar Rol de Usuario
                </h4>
                <p>Selecciona el nivel de acceso para este usuario</p>
            </div>

            <!-- Body -->
            <div class="roles-body">
                <!-- User Info -->
                <div class="user-info-section">
                    <div class="user-info-label">Usuario Seleccionado</div>
                    <div class="user-info-value">
                        <div class="user-avatar-large">
                            {{ strtoupper(substr($user->usuario, 0, 2)) }}
                        </div>
                        <span>{{ $user->usuario }}</span>
                    </div>
                </div>

                <form action="{{ route('admin.usuarios.updateRol', $user->usuario) }}" method="POST" id="roleForm">
                    @csrf
                    @method('PUT')

                    <div class="role-selection-title">
                        <i class="fas fa-shield-alt"></i> Elige el Rol
                    </div>

                    <!-- Role Cards -->
                    <div class="role-cards-container">
                        <!-- Admin Role -->
                        <div class="role-card {{ $user->tipousuario === 'ADMIN' ? 'selected' : '' }}" data-role="ADMIN">
                            <div class="selected-indicator">
                                <i class="fas fa-check"></i>
                            </div>
                            <input type="radio" name="tipousuario" value="ADMIN" id="rolAdmin"
                                   {{ $user->tipousuario === 'ADMIN' ? 'checked' : '' }}>
                            
                            <div class="role-card-header">
                                <div class="role-icon admin">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div class="role-title">
                                    <h5>Administrador</h5>
                                    <span class="role-badge-mini admin">Admin</span>
                                </div>
                            </div>

                            <p class="role-description">
                                Acceso completo al sistema con permisos para gestionar usuarios, 
                                configuraciones y todos los módulos.
                            </p>

                            <ul class="role-permissions">
                                <li><i class="fas fa-check-circle"></i> Gestión total del sistema</li>
                                <li><i class="fas fa-check-circle"></i> Eliminar y editar planillas</li>
                                <li><i class="fas fa-check-circle"></i> Administrar usuarios</li>
                                <li><i class="fas fa-check-circle"></i> Acceso a todos los reportes</li>
                                <li><i class="fas fa-check-circle"></i> Configuración avanzada</li>
                            </ul>
                        </div>

                        <!-- User Role -->
                        <div class="role-card {{ $user->tipousuario === 'USER' ? 'selected' : '' }}" data-role="USER">
                            <div class="selected-indicator">
                                <i class="fas fa-check"></i>
                            </div>
                            <input type="radio" name="tipousuario" value="USER" id="rolUser"
                                   {{ $user->tipousuario === 'USER' ? 'checked' : '' }}>
                            
                            <div class="role-card-header">
                                <div class="role-icon user">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="role-title">
                                    <h5>Usuario Regular</h5>
                                    <span class="role-badge-mini user">User</span>
                                </div>
                            </div>

                            <p class="role-description">
                                Acceso limitado a funciones esenciales para el trabajo diario, 
                                sin permisos administrativos.
                            </p>

                            <ul class="role-permissions">
                                <li><i class="fas fa-check-circle"></i> Ver planillas y documentos</li>
                                <li><i class="fas fa-check-circle"></i> Consultar cuentas corrientes</li>
                                <li><i class="fas fa-check-circle"></i> Generar reportes básicos</li>
                                <li><i class="fas fa-check-circle"></i> Visualizar estadísticas</li>
                                <li><i class="fas fa-times-circle" style="color: #dc2626;"></i> Sin acceso administrativo</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button type="submit" class="btn-save-role">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <a href="{{ route('admin.usuarios.index') }}" class="btn-cancel-role">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleCards = document.querySelectorAll('.role-card');
    
    roleCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove selected class from all cards
            roleCards.forEach(c => c.classList.remove('selected'));
            
            // Add selected class to clicked card
            this.classList.add('selected');
            
            // Check the corresponding radio button
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });

    // Prevent form submission without selection
    const form = document.getElementById('roleForm');
    form.addEventListener('submit', function(e) {
        const selectedRole = document.querySelector('input[name="tipousuario"]:checked');
        if (!selectedRole) {
            e.preventDefault();
            alert('Por favor, selecciona un rol antes de guardar.');
        }
    });
});
</script>
@endpush