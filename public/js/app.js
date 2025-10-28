/**
 * APP.JS - Sistema SIFANO
 * JavaScript principal para funcionalidad del dashboard
 */

(function() {
    'use strict';

    // ============================================
    // CONFIGURACIÓN GLOBAL
    // ============================================
    const CONFIG = {
        animationDuration: 300,
        sidebarBreakpoint: 1024
    };

    // ============================================
    // INICIALIZACIÓN AL CARGAR EL DOM
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        initSidebar();
        initDropdowns();
        initSubmenuToggles();
        initAlerts();
        initLoadingOverlay();
        initResponsiveTables();
        console.log('✅ Sistema SIFANO inicializado correctamente');
    });

    // ============================================
    // SIDEBAR: Toggle y manejo responsive
    // ============================================
    function initSidebar() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mainContent = document.querySelector('.main-content');
        
        if (!sidebar || !sidebarToggle) return;

        // Crear overlay para móviles
        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }

        // Toggle del sidebar
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSidebar();
        });

        // Cerrar sidebar al hacer clic en el overlay
        overlay.addEventListener('click', function() {
            closeSidebar();
        });

        // Cerrar sidebar con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });

        // Ajustar sidebar en resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth > CONFIG.sidebarBreakpoint) {
                    closeSidebar();
                }
            }, 250);
        });

        function toggleSidebar() {
            const isOpen = sidebar.classList.contains('show');
            if (isOpen) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }

        function openSidebar() {
            sidebar.classList.add('show');
            overlay.classList.add('show');
            document.body.classList.add('sidebar-open');
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
    }

    // ============================================
    // SUBMENÚS: Toggle de menús desplegables
    // ============================================
    function initSubmenuToggles() {
        const submenuLinks = document.querySelectorAll('.has-submenu > .nav-link');
        
        submenuLinks.forEach(link => {
            // Remover event listeners duplicados
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);
            
            newLink.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const parent = this.parentElement;
                const isExpanded = parent.classList.contains('expanded');
                const submenu = parent.querySelector('.nav-submenu');
                
                // Cerrar otros submenús del mismo nivel
                const siblings = parent.parentElement.querySelectorAll('.has-submenu');
                siblings.forEach(sibling => {
                    if (sibling !== parent && sibling.classList.contains('expanded')) {
                        sibling.classList.remove('expanded');
                        const siblingSubmenu = sibling.querySelector('.nav-submenu');
                        if (siblingSubmenu) {
                            siblingSubmenu.style.maxHeight = '0px';
                        }
                    }
                });
                
                // Toggle del submenú actual
                if (isExpanded) {
                    parent.classList.remove('expanded');
                    submenu.style.maxHeight = '0px';
                } else {
                    parent.classList.add('expanded');
                    // Calcular altura real del contenido
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                }
                
                console.log('📂 Submenú toggled:', this.textContent.trim());
            });
        });

        // Expandir submenú activo al cargar la página
        const activeSubmenuItem = document.querySelector('.nav-submenu .nav-link.active');
        if (activeSubmenuItem) {
            const parent = activeSubmenuItem.closest('.has-submenu');
            if (parent) {
                parent.classList.add('expanded');
                const submenu = parent.querySelector('.nav-submenu');
                if (submenu) {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                }
            }
        }
    }

    // ============================================
    // DROPDOWNS: Menú de usuario y notificaciones
    // ============================================
    function initDropdowns() {
        const dropdownToggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const menu = this.nextElementSibling;
                if (!menu || !menu.classList.contains('dropdown-menu')) return;
                
                // Cerrar otros dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(openMenu => {
                    if (openMenu !== menu) {
                        openMenu.classList.remove('show');
                    }
                });
                
                // Toggle del dropdown actual
                menu.classList.toggle('show');
            });
        });

        // Cerrar dropdowns al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!e.target.closest('[data-bs-toggle="dropdown"]')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });

        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    }

    // ============================================
    // ALERTAS: Auto-dismiss con animación
    // ============================================
    function initAlerts() {
        const alerts = document.querySelectorAll('.alert');
        
        alerts.forEach(alert => {
            // Auto-dismiss después de 5 segundos
            setTimeout(() => {
                dismissAlert(alert);
            }, 5000);
            
            // Botón de cerrar
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    dismissAlert(alert);
                });
            }
        });

        function dismissAlert(alert) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }
    }

    // ============================================
    // LOADING OVERLAY
    // ============================================
    function initLoadingOverlay() {
        window.showLoading = function() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.classList.add('show');
                overlay.style.display = 'flex';
            }
        };

        window.hideLoading = function() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.classList.remove('show');
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 300);
            }
        };
    }

    // ============================================
    // TABLAS RESPONSIVE
    // ============================================
    function initResponsiveTables() {
        const tables = document.querySelectorAll('table:not(.table-responsive table)');
        
        tables.forEach(table => {
            if (!table.parentElement.classList.contains('table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    }

    // ============================================
    // UTILIDADES GLOBALES
    // ============================================

    // Formatear números como moneda
    window.formatCurrency = function(amount) {
        return new Intl.NumberFormat('es-PE', {
            style: 'currency',
            currency: 'PEN',
            minimumFractionDigits: 2
        }).format(amount);
    };

    // Formatear fechas
    window.formatDate = function(date) {
        return new Intl.DateTimeFormat('es-PE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        }).format(new Date(date));
    };

    // Mostrar toast notification
    window.showToast = function(message, type = 'info') {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            Toast.fire({
                icon: type,
                title: message
            });
        }
    };

    // Confirmar acción
    window.confirmAction = function(title, text, confirmText = 'Sí, continuar') {
        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancelar'
            });
        }
        return Promise.resolve({ isConfirmed: confirm(text) });
    };

    // Debounce para búsquedas
    window.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    // ============================================
    // MANEJO DE FORMULARIOS
    // ============================================
    
    // Prevenir doble submit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
                
                // Re-habilitar después de 3 segundos por seguridad
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = submitBtn.dataset.originalText || 'Enviar';
                }, 3000);
            }
        });
    });

    // ============================================
    // BÚSQUEDA EN TIEMPO REAL
    // ============================================
    const searchInput = document.querySelector('.topbar-search input');
    if (searchInput) {
        const debouncedSearch = debounce(function(value) {
            console.log('🔍 Buscando:', value);
            // Aquí puedes agregar la lógica de búsqueda
        }, 500);

        searchInput.addEventListener('input', function(e) {
            const value = e.target.value.trim();
            if (value.length >= 3) {
                debouncedSearch(value);
            }
        });
    }

    // ============================================
    // ANIMACIONES DE ENTRADA
    // ============================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observar tarjetas para animación
    document.querySelectorAll('.card').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `all 0.5s ease ${index * 0.1}s`;
        observer.observe(card);
    });

    // ============================================
    // ACTUALIZAR DATOS AUTOMÁTICAMENTE
    // ============================================
    window.autoRefreshData = function(callback, interval = 60000) {
        setInterval(callback, interval);
        console.log(`🔄 Auto-refresh activado cada ${interval / 1000}s`);
    };

    // ============================================
    // MANEJO DE ERRORES GLOBAL
    // ============================================
    window.addEventListener('error', function(e) {
        console.error('❌ Error capturado:', e.error);
    });

    window.addEventListener('unhandledrejection', function(e) {
        console.error('❌ Promesa rechazada:', e.reason);
    });

    // ============================================
    // HELPERS PARA CHARTS.JS
    // ============================================
    window.chartColors = {
        primary: '#2563eb',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#06b6d4',
        secondary: '#6b7280'
    };

    window.createChartGradient = function(ctx, color1, color2) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        return gradient;
    };


    if (typeof Swal !== 'undefined') {
        window.Swal = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-primary mx-2',
                cancelButton: 'btn btn-outline-secondary mx-2'
            },
            buttonsStyling: false
        });
    }

    window.log = {
        info: (msg, ...args) => console.log('ℹ️', msg, ...args),
        success: (msg, ...args) => console.log('✅', msg, ...args),
        warning: (msg, ...args) => console.warn('⚠️', msg, ...args),
        error: (msg, ...args) => console.error('❌', msg, ...args),
        debug: (msg, ...args) => console.debug('🐛', msg, ...args)
    };

    console.log('%c SIFANO Sistema Inicializado', 'color: #2563eb; font-size: 16px; font-weight: bold;');

})();