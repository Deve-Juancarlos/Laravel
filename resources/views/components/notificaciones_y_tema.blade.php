<div class="d-flex align-items-center ms-auto">
    <!-- NOTIFICACIONES DROPDOWN -->
    <div class="dropdown notification-dropdown">
        <button class="btn btn-icon notification-bell" id="notificationButton" data-bs-toggle="dropdown" aria-expanded="false" title="Notificaciones">
            <i class="far fa-bell"></i>
            <span class="notification-badge" id="notificationBadge" style="display: none;"></span>
        </button>
        
        <div class="dropdown-menu dropdown-menu-end notification-menu" aria-labelledby="notificationButton">
            <div class="notification-header">
                <h6 class="mb-0">Notificaciones</h6>
                <button class="btn btn-sm btn-link text-primary p-0" id="markAllReadBtn" title="Marcar todas como leídas">
                    <i class="fas fa-check-double me-1"></i> Marcar todas
                </button>
            </div>

            <div class="notification-list-wrapper">
                <div class="notification-list" id="notificationList">
                    <!-- Loading state -->
                    <div class="notification-placeholder">
                        <div class="placeholder-icon"></div>
                        <div class="placeholder-content">
                            <div class="placeholder-line w-75"></div>
                            <div class="placeholder-line w-100"></div>
                            <div class="placeholder-line w-50"></div>
                        </div>
                    </div>
                    <div class="notification-placeholder">
                        <div class="placeholder-icon"></div>
                        <div class="placeholder-content">
                            <div class="placeholder-line w-75"></div>
                            <div class="placeholder-line w-100"></div>
                            <div class="placeholder-line w-50"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="notification-footer">
                <button class="btn btn-sm btn-outline-danger w-100" id="clearReadBtn">
                    <i class="fas fa-trash-alt me-1"></i> Limpiar Leídas
                </button>
            </div>
        </div>
    </div>

    <!-- BOTÓN DE TEMA -->
    <button class="btn btn-icon theme-toggle" id="themeToggle" title="Cambiar tema">
        <i class="fas fa-moon theme-icon-dark"></i>
        <i class="fas fa-sun theme-icon-light" style="display: none;"></i>
    </button>
</div>

@push('styles')
<style>
/* === VARIABLES CSS === */
:root {
    --bg-primary: #ffffff;
    --bg-secondary: #f8f9fa;
    --bg-tertiary: #e9ecef;
    --text-primary: #212529;
    --text-secondary: #6c757d;
    --border-color: #dee2e6;
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.12);
    --primary-color: #667eea;
    --success-color: #48bb78;
    --danger-color: #f56565;
    --warning-color: #ed8936;
    --info-color: #4299e1;
}

[data-theme="dark"] {
    --bg-primary: #1a202c;
    --bg-secondary: #2d3748;
    --bg-tertiary: #4a5568;
    --text-primary: #f7fafc;
    --text-secondary: #cbd5e0;
    --border-color: #4a5568;
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.3);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.4);
}

/* === BOTONES DE ICONOS === */
.btn-icon {
    position: relative;
    width: 42px;
    height: 42px;
    border-radius: 12px;
    border: none;
    background-color: var(--bg-secondary);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 10px;
}

.btn-icon:hover {
    background-color: var(--bg-tertiary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.btn-icon i {
    font-size: 18px;
}

/* === BADGE DE NOTIFICACIONES === */
.notification-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    padding: 0 6px;
    box-shadow: 0 2px 6px rgba(102, 126, 234, 0.4);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* === ANIMACIÓN DE CAMPANA === */
.notification-bell.has-notifications i {
    color: var(--primary-color);
    animation: ring 1.5s ease-in-out;
}

@keyframes ring {
    0%, 100% { transform: rotate(0); }
    10% { transform: rotate(25deg); }
    20% { transform: rotate(-20deg); }
    30% { transform: rotate(15deg); }
    40% { transform: rotate(-10deg); }
    50% { transform: rotate(5deg); }
    60% { transform: rotate(0); }
}

/* === MENÚ DROPDOWN === */
.notification-menu {
    width: 380px;
    max-width: 95vw;
    border: 1px solid var(--border-color);
    border-radius: 16px;
    box-shadow: var(--shadow-md);
    background-color: var(--bg-primary);
    padding: 0;
    margin-top: 10px;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* === HEADER === */
.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
}

.notification-header h6 {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 16px;
}

/* === LISTA DE NOTIFICACIONES === */
.notification-list-wrapper {
    max-height: 420px;
    overflow-y: auto;
}

.notification-list-wrapper::-webkit-scrollbar {
    width: 6px;
}

.notification-list-wrapper::-webkit-scrollbar-track {
    background: var(--bg-secondary);
}

.notification-list-wrapper::-webkit-scrollbar-thumb {
    background: var(--bg-tertiary);
    border-radius: 10px;
}

.notification-item {
    display: flex;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
}

.notification-item:hover {
    background-color: var(--bg-secondary);
}

.notification-item::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(128, 128, 128, 0.1), transparent);
    transition: left 0.5s ease;
}

.notification-item:hover::after {
    left: 100%;
}

.notification-item.unread {
    background-color: rgba(102, 126, 234, 0.05);
    border-left: 3px solid var(--primary-color);
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
    font-size: 18px;
}

.notification-icon.tipo-info {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
}

.notification-icon.tipo-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
}

.notification-icon.tipo-warning {
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
    color: white;
}

.notification-icon.tipo-error {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
    color: white;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 600;
    font-size: 14px;
    color: var(--text-primary);
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notification-message {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notification-time {
    font-size: 11px;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 4px;
}

.notification-empty {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-secondary);
}

.notification-empty i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.3;
}

/* === FOOTER === */
.notification-footer {
    padding: 12px 20px;
    border-top: 1px solid var(--border-color);
}

/* === LOADING STATE === */
.notification-placeholder {
    display: flex;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border-color);
}

.placeholder-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(90deg, var(--bg-tertiary) 0%, var(--bg-secondary) 50%, var(--bg-tertiary) 100%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    margin-right: 12px;
    flex-shrink: 0;
}

.placeholder-content {
    flex-grow: 1;
}

.placeholder-line {
    height: 10px;
    background: linear-gradient(90deg, var(--bg-tertiary) 0%, var(--bg-secondary) 50%, var(--bg-tertiary) 100%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 5px;
    margin-bottom: 8px;
}

.placeholder-line:last-child {
    margin-bottom: 0;
}

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.w-50 { width: 50%; }
.w-75 { width: 75%; }
.w-100 { width: 100%; }

/* === TEMA === */
.theme-toggle {
    position: relative;
}

.theme-icon-dark,
.theme-icon-light {
    transition: all 0.3s ease;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let notificaciones = [];
    
    const notificationList = document.getElementById('notificationList');
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationBell = document.getElementById('notificationButton');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const clearReadBtn = document.getElementById('clearReadBtn');
    const loadingHTML = notificationList.innerHTML;

    // === FUNCIONES PRINCIPALES ===
    
    function cargarNotificaciones() {
        if (notificaciones.length === 0) {
            showLoadingState();
        }
        
        fetch('{{ route("admin.notificaciones.index") }}')
            .then(response => {
                if (!response.ok) throw new Error('Error de red');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    notificaciones = data.notificaciones;
                    renderizarNotificaciones();
                    actualizarBadge();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                notificationList.innerHTML = `
                    <div class="notification-empty">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        <p class="mb-1">Error al cargar notificaciones</p>
                        <small class="text-muted">Intente recargar la página.</small>
                    </div>
                `;
            });
    }

    function renderizarNotificaciones() {
        if (notificaciones.length === 0) {
            notificationList.innerHTML = `
                <div class="notification-empty">
                    <i class="far fa-bell-slash"></i>
                    <p class="mb-0">No tienes notificaciones</p>
                    <small class="text-muted">Cuando recibas una, aparecerá aquí</small>
                </div>
            `;
            return;
        }

        notificationList.innerHTML = notificaciones.map(notif => {
            const iconClass = getIconClass(notif.tipo);
            const iconName = getIconName(notif.tipo);
            const timeAgo = calcularTiempoTranscurrido(notif.created_at);
            const unreadClass = !notif.leida ? 'unread' : '';
            
            // Si tiene URL, crear enlace clickeable
            if (notif.url) {
                return `
                    <div class="notification-item ${unreadClass}" data-id="${notif.id}" data-url="${notif.url}">
                        <div class="notification-icon ${iconClass}">
                            <i class="${iconName}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">${notif.titulo}</div>
                            <div class="notification-message">${notif.mensaje}</div>
                            <div class="notification-time">
                                <i class="far fa-clock"></i> ${timeAgo}
                                ${!notif.leida ? '<span class="ms-2 badge bg-primary" style="font-size: 9px;">NUEVA</span>' : ''}
                            </div>
                        </div>
                        ${!notif.leida ? '<i class="fas fa-circle text-primary" style="font-size: 8px; margin-left: 8px;"></i>' : ''}
                    </div>
                `;
            } else {
                // Sin URL, solo marcar como leída al hacer clic
                return `
                    <div class="notification-item ${unreadClass}" data-id="${notif.id}">
                        <div class="notification-icon ${iconClass}">
                            <i class="${iconName}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">${notif.titulo}</div>
                            <div class="notification-message">${notif.mensaje}</div>
                            <div class="notification-time">
                                <i class="far fa-clock"></i> ${timeAgo}
                                ${!notif.leida ? '<span class="ms-2 badge bg-primary" style="font-size: 9px;">NUEVA</span>' : ''}
                            </div>
                        </div>
                        ${!notif.leida ? '<i class="fas fa-circle text-primary" style="font-size: 8px; margin-left: 8px;"></i>' : ''}
                    </div>
                `;
            }
        }).join('');

        // Agregar event listeners a las notificaciones
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.dataset.id;
                const url = this.dataset.url;
                
                // Marcar como leída
                marcarComoLeida(id, () => {
                    // Si tiene URL, redirigir después de marcar como leída
                    if (url) {
                        window.location.href = url;
                    }
                });
            });
        });
    }

    function actualizarBadge() {
        const noLeidas = notificaciones.filter(n => !n.leida).length;
        
        if (noLeidas > 0) {
            notificationBadge.textContent = noLeidas > 9 ? '9+' : noLeidas;
            notificationBadge.style.display = 'block';
            notificationBell.classList.add('has-notifications');
        } else {
            notificationBadge.style.display = 'none';
            notificationBell.classList.remove('has-notifications');
        }
    }

    function marcarComoLeida(id, callback) {
        fetch(`{{ url('admin/notificaciones') }}/${id}/marcar-leida`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notif = notificaciones.find(n => n.id == id);
                if (notif) notif.leida = true;
                renderizarNotificaciones();
                actualizarBadge();
                if (callback) callback();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function showLoadingState() {
        notificationList.innerHTML = loadingHTML;
    }

    function getIconClass(tipo) {
        const tipos = {
            'success': 'tipo-success',
            'error': 'tipo-error',
            'warning': 'tipo-warning',
            'info': 'tipo-info'
        };
        return tipos[tipo] || 'tipo-info';
    }

    function getIconName(tipo) {
        const iconos = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        };
        return iconos[tipo] || 'fas fa-bell';
    }

    function calcularTiempoTranscurrido(fecha) {
        const ahora = new Date();
        const creacion = new Date(fecha);
        const diff = Math.floor((ahora - creacion) / 1000);

        if (diff < 60) return 'Hace un momento';
        if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`;
        if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} h`;
        if (diff < 604800) return `Hace ${Math.floor(diff / 86400)} días`;
        return creacion.toLocaleDateString();
    }

    // === EVENT LISTENERS ===

    markAllReadBtn.addEventListener('click', function() {
        fetch('{{ route("admin.notificaciones.limpiar") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notificaciones.forEach(n => n.leida = true);
                renderizarNotificaciones();
                actualizarBadge();
            }
        });
    });

    clearReadBtn.addEventListener('click', function() {
        fetch('{{ route("admin.notificaciones.limpiar") }}', {
            method: 'DELETE',  // ✅ Cambiar de POST a DELETE
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notificaciones = notificaciones.filter(n => !n.leida);
                renderizarNotificaciones();
                actualizarBadge();
            }
        });
    });


    // === TEMA ===
    const themeToggle = document.getElementById('themeToggle');
    const themeDarkIcon = document.querySelector('.theme-icon-dark');
    const themeLightIcon = document.querySelector('.theme-icon-light');

    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    if (currentTheme === 'dark') {
        themeDarkIcon.style.display = 'none';
        themeLightIcon.style.display = 'block';
    }

    themeToggle.addEventListener('click', function() {
        const theme = document.documentElement.getAttribute('data-theme');
        const newTheme = theme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        if (newTheme === 'dark') {
            themeDarkIcon.style.display = 'none';
            themeLightIcon.style.display = 'block';
        } else {
            themeDarkIcon.style.display = 'block';
            themeLightIcon.style.display = 'none';
        }
    });

    // === INICIALIZACIÓN ===
    cargarNotificaciones();
    setInterval(cargarNotificaciones, 30000); // Actualizar cada 30 segundos
});
</script>
@endpush