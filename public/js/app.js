
class LayoutManager {
    constructor() {
        this.activeDropdown = null;
        this.sidebarOpen = false;
        this.init();
    }

    init() {
        this.setupSidebarToggle();
        this.setupDropdownMenus();
        this.setupSubmenuToggle();
        this.setupClickOutside();
        this.setupKeyboardNavigation();
        this.setupMobileMenu();
        this.setupAccessibility();
        
        console.log('Layout Manager inicializado correctamente');
    }

    /**
     * Maneja el toggle del sidebar
     */
    setupSidebarToggle() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (!sidebarToggle || !sidebar) return;

        sidebarToggle.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleSidebar();
        });

        // Cerrar sidebar al hacer clic en el overlay (solo móvil)
        if (overlay) {
            overlay.addEventListener('click', () => {
                this.closeSidebar();
            });
        }

        // Cerrar sidebar al cambiar tamaño de ventana
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) {
                this.closeSidebar();
            }
        });
    }

    /**
     * Maneja los menús desplegables de manera exclusiva
     */
    setupDropdownMenus() {
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        
        dropdownToggles.forEach(toggle => {
            const dropdown = toggle.closest('.dropdown');
            const menu = dropdown?.querySelector('.dropdown-menu');
            
            if (!dropdown || !menu) return;

            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Si ya está activo, lo cerramos
                if (this.activeDropdown === dropdown) {
                    this.closeDropdown(dropdown);
                    return;
                }
                
                // Cerramos cualquier dropdown activo
                if (this.activeDropdown) {
                    this.closeDropdown(this.activeDropdown);
                }
                
                // Abrimos el nuevo dropdown
                this.openDropdown(dropdown);
            });

            // Accesibilidad por teclado
            toggle.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggle.click();
                }
                
                if (e.key === 'Escape') {
                    this.closeDropdown(dropdown);
                    toggle.focus();
                }
            });

            // Navegación dentro del dropdown
            const items = menu.querySelectorAll('.dropdown-item');
            items.forEach((item, index) => {
                item.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        const nextItem = items[index + 1] || items[0];
                        nextItem.focus();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        const prevItem = items[index - 1] || items[items.length - 1];
                        prevItem.focus();
                    } else if (e.key === 'Escape') {
                        this.closeDropdown(dropdown);
                        toggle.focus();
                    }
                });
            });
        });
    }

    /**
     * Maneja los submenús del sidebar
     */
    setupSubmenuToggle() {
        const submenuToggles = document.querySelectorAll('.has-submenu > .nav-link');
        
        submenuToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const parent = toggle.parentElement;
                
                // En móvil, siempre permite toggle
                // En desktop, solo si está en modo compacto
                if (window.innerWidth > 1024) {
                    const sidebar = document.querySelector('.sidebar');
                    if (sidebar.classList.contains('sidebar-compact')) {
                        this.toggleSubmenu(parent);
                    }
                } else {
                    this.toggleSubmenu(parent);
                }
            });

            // Accesibilidad
            toggle.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggle.click();
                }
            });
        });
    }

    /**
     * Cierra menús al hacer clic fuera
     */
    setupClickOutside() {
        document.addEventListener('click', (e) => {
            // Cerrar dropdowns si el clic no es dentro de ellos
            if (!e.target.closest('.dropdown')) {
                this.closeAllDropdowns();
            }
            
            // Cerrar sidebar en móvil si el clic no es dentro del sidebar
            if (this.sidebarOpen && 
                window.innerWidth <= 1024 && 
                !e.target.closest('.sidebar') && 
                !e.target.closest('#sidebarToggle')) {
                this.closeSidebar();
            }
        });
    }

    /**
     * Navegación por teclado
     */
    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // ESC cierra menús
            if (e.key === 'Escape') {
                this.closeAllDropdowns();
                this.closeSidebar();
            }
            
            // Alt + M abre/cierra sidebar (móvil)
            if (e.altKey && e.key === 'm' && window.innerWidth <= 1024) {
                e.preventDefault();
                if (this.sidebarOpen) {
                    this.closeSidebar();
                } else {
                    this.openSidebar();
                }
            }
        });
    }

    /**
     * Configuración específica para móvil
     */
    setupMobileMenu() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (!sidebar || !overlay) return;

        // Añadir overlay si no existe
        if (!overlay) {
            const newOverlay = document.createElement('div');
            newOverlay.className = 'sidebar-overlay';
            document.body.appendChild(newOverlay);
        }
    }

    /**
     * Mejoras de accesibilidad
     */
    setupAccessibility() {
        // Añadir atributos ARIA
        this.updateAriaAttributes();
        
        // Manejar cambios de foco
        const focusableElements = document.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        focusableElements.forEach(element => {
            if (!element.hasAttribute('tabindex')) {
                element.setAttribute('tabindex', '0');
            }
        });
    }

    /**
     * Abre un dropdown específico
     */
    openDropdown(dropdown) {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (!toggle || !menu) return;

        menu.classList.add('show');
        toggle.setAttribute('aria-expanded', 'true');
        dropdown.classList.add('dropdown-open');
        this.activeDropdown = dropdown;

        // Animación suave
        requestAnimationFrame(() => {
            menu.style.opacity = '1';
            menu.style.transform = 'translateY(0)';
        });
    }

    /**
     * Cierra un dropdown específico
     */
    closeDropdown(dropdown) {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (!toggle || !menu) return;

        menu.classList.remove('show');
        toggle.setAttribute('aria-expanded', 'false');
        dropdown.classList.remove('dropdown-open');

        if (this.activeDropdown === dropdown) {
            this.activeDropdown = null;
        }

        // Animación de salida
        menu.style.opacity = '0';
        menu.style.transform = 'translateY(-10px)';
    }

    /**
     * Cierra todos los dropdowns
     */
    closeAllDropdowns() {
        const openDropdowns = document.querySelectorAll('.dropdown.dropdown-open');
        openDropdowns.forEach(dropdown => {
            this.closeDropdown(dropdown);
        });
    }

    /**
     * Toggle de submenú
     */
    toggleSubmenu(submenuItem) {
        const isExpanded = submenuItem.classList.contains('expanded');
        
        if (isExpanded) {
            this.collapseSubmenu(submenuItem);
        } else {
            this.expandSubmenu(submenuItem);
        }
    }

    /**
     * Expande un submenú
     */
    expandSubmenu(submenuItem) {
        const link = submenuItem.querySelector('.nav-link');
        const submenu = submenuItem.querySelector('.nav-submenu');
        
        if (!link || !submenu) return;

        submenuItem.classList.add('expanded');
        link.setAttribute('aria-expanded', 'true');
        
        // Animación suave
        submenu.style.maxHeight = submenu.scrollHeight + 'px';
        
        // Cerrar otros submenús del mismo nivel
        const siblings = submenuItem.parentElement.querySelectorAll('.has-submenu.expanded');
        siblings.forEach(sibling => {
            if (sibling !== submenuItem) {
                this.collapseSubmenu(sibling);
            }
        });
    }

    /**
     * Colapsa un submenú
     */
    collapseSubmenu(submenuItem) {
        const link = submenuItem.querySelector('.nav-link');
        const submenu = submenuItem.querySelector('.nav-submenu');
        
        if (!link || !submenu) return;

        submenuItem.classList.remove('expanded');
        link.setAttribute('aria-expanded', 'false');
        submenu.style.maxHeight = '0';
    }

    /**
     * Abre el sidebar
     */
    openSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (!sidebar) return;

        sidebar.classList.add('show');
        if (overlay) overlay.classList.add('show');
        
        document.body.classList.add('sidebar-open');
        this.sidebarOpen = true;

        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
    }

    /**
     * Cierra el sidebar
     */
    closeSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (!sidebar) return;

        sidebar.classList.remove('show');
        if (overlay) overlay.classList.remove('show');
        
        document.body.classList.remove('sidebar-open');
        this.sidebarOpen = false;

        // Restaurar scroll del body
        document.body.style.overflow = '';
    }

    /**
     * Toggle del sidebar
     */
    toggleSidebar() {
        if (this.sidebarOpen) {
            this.closeSidebar();
        } else {
            this.openSidebar();
        }
    }

    /**
     * Actualiza atributos ARIA para accesibilidad
     */
    updateAriaAttributes() {
        // Dropdowns
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        dropdownToggles.forEach(toggle => {
            if (!toggle.hasAttribute('aria-expanded')) {
                toggle.setAttribute('aria-expanded', 'false');
            }
            if (!toggle.hasAttribute('aria-haspopup')) {
                toggle.setAttribute('aria-haspopup', 'true');
            }
        });

        const dropdownMenus = document.querySelectorAll('.dropdown-menu');
        dropdownMenus.forEach(menu => {
            if (!menu.hasAttribute('role')) {
                menu.setAttribute('role', 'menu');
            }
        });

        // Submenús
        const submenuLinks = document.querySelectorAll('.has-submenu > .nav-link');
        submenuLinks.forEach(link => {
            if (!link.hasAttribute('aria-expanded')) {
                link.setAttribute('aria-expanded', 'false');
            }
            if (!link.hasAttribute('aria-haspopup')) {
                link.setAttribute('aria-haspopup', 'true');
            }
        });

        const submenus = document.querySelectorAll('.nav-submenu');
        submenus.forEach(submenu => {
            if (!submenu.hasAttribute('role')) {
                submenu.setAttribute('role', 'menu');
            }
        });
    }

    /**
     * Obtiene el estado actual del layout
     */
    getState() {
        return {
            activeDropdown: this.activeDropdown,
            sidebarOpen: this.sidebarOpen,
            isMobile: window.innerWidth <= 768
        };
    }

    /**
     * Destruye la instancia y limpia eventos
     */
    destroy() {
        // Limpiar eventos
        document.removeEventListener('click', this.handleClickOutside);
        document.removeEventListener('keydown', this.handleKeyboardNavigation);
        window.removeEventListener('resize', this.handleResize);
        
        console.log('Layout Manager destruido');
    }
}

/**
 * Funciones de utilidad
 */
class LayoutUtils {
    /**
     * Debounce function para optimizar eventos de resize
     */
    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Detecta si el dispositivo es móvil
     */
    static isMobile() {
        return window.innerWidth <= 768;
    }

    /**
     * Detecta si el dispositivo es tablet
     */
    static isTablet() {
        return window.innerWidth > 768 && window.innerWidth <= 1024;
    }

    /**
     * Obtiene información del viewport
     */
    static getViewportInfo() {
        return {
            width: window.innerWidth,
            height: window.innerHeight,
            isMobile: this.isMobile(),
            isTablet: this.isTablet(),
            isDesktop: window.innerWidth > 1024
        };
    }
}

/**
 * Inicialización cuando el DOM está listo
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Layout Manager
    window.layoutManager = new LayoutManager();
    
    // Configurar optimizaciones de rendimiento
    const debouncedResize = LayoutUtils.debounce(() => {
        window.layoutManager.updateAriaAttributes();
    }, 250);
    
    window.addEventListener('resize', debouncedResize);

    // Manejar visibilidad de la página
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            window.layoutManager.closeAllDropdowns();
            window.layoutManager.closeSidebar();
        }
    });

    console.log('Layout inicializado correctamente');
});

/**
 * Exposer para uso global
 */
window.LayoutManager = LayoutManager;
window.LayoutUtils = LayoutUtils;