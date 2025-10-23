// public/js/pharma-config.js
const PharmaConfig = {
    app: {
        name: 'PharmaAdmin',
        version: '1.0.0',
        language: 'es',
        currency: 'PEN',
        timezone: 'America/Lima'
    },

    api: {
        baseUrl: '/api',
        timeout: 10000,
        endpoints: {
            dashboard: '/dashboard/stats',
            products: '/products',
            sales: '/sales',
            inventory: '/inventory',
            clients: '/clients',
            reports: '/reports',
            prescriptions: '/prescriptions',
            suppliers: '/suppliers'
        }
    },

    ui: {
        sidebarWidth: 280,
        sidebarCollapsedWidth: 70,
        topbarHeight: 70,
        animationDuration: 300,
        refreshInterval: 30000 // 30 segundos
    },

    productCategories: [
        { id: 1, name: 'Analgésicos', color: '#2563eb' },
        { id: 2, name: 'Antiinflamatorios', color: '#10b981' },
        { id: 3, name: 'Antibióticos', color: '#f59e0b' },
        { id: 4, name: 'Antihistamínicos', color: '#ef4444' },
        { id: 5, name: 'Otros', color: '#6b7280' }
    ],

    productStatuses: {
        active: { label: 'Activo', class: 'success', color: '#10b981' },
        low_stock: { label: 'Stock Bajo', class: 'warning', color: '#f59e0b' },
        critical: { label: 'Crítico', class: 'danger', color: '#ef4444' },
        expired: { label: 'Vencido', class: 'danger', color: '#ef4444' },
        inactive: { label: 'Inactivo', class: 'secondary', color: '#6b7280' }
    },

    salesStatuses: {
        pending: { label: 'Pendiente', class: 'warning', color: '#f59e0b' },
        processing: { label: 'Procesando', class: 'info', color: '#3b82f6' },
        shipped: { label: 'Enviado', class: 'primary', color: '#8b5cf6' },
        delivered: { label: 'Entregado', class: 'success', color: '#10b981' },
        cancelled: { label: 'Cancelado', class: 'danger', color: '#ef4444' }
    }
};

const PharmaUtils = {
    formatCurrency(amount) {
        return new Intl.NumberFormat('es-PE', {
            style: 'currency',
            currency: 'PEN',
            minimumFractionDigits: 2
        }).format(amount);
    },

    formatDate(date, format = 'short') {
        const options = format === 'long' ? 
            { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' } :
            { year: 'numeric', month: '2-digit', day: '2-digit' };
        
        return new Date(date).toLocaleDateString('es-PE', options);
    },

    formatNumber(number) {
        return new Intl.NumberFormat('es-PE').format(number);
    },

    getStatusBadge(status, type = 'product') {
        const statusConfig = type === 'product' ? 
            PharmaConfig.productStatuses : 
            PharmaConfig.salesStatuses;
        
        const config = statusConfig[status] || statusConfig.active;
        return `<span class="badge badge-${config.class}">${config.label}</span>`;
    },

    calculatePercentage(current, total) {
        return total > 0 ? Math.round((current / total) * 100) : 0;
    },

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    showAlert(message, type = 'info', duration = 5000) {
        const alertContainer = document.getElementById('alertContainer') || this.createAlertContainer();
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        alertContainer.appendChild(alert);
        
        setTimeout(() => {
            if (alert.parentElement) {
                alert.remove();
            }
        }, duration);
    },

    createAlertContainer() {
        const container = document.createElement('div');
        container.id = 'alertContainer';
        container.className = 'position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }
};


const PharmaAPI = {
    async request(endpoint, options = {}) {
        const url = PharmaConfig.api.baseUrl + endpoint;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            timeout: PharmaConfig.api.timeout
        };

        try {
            const response = await fetch(url, { ...defaultOptions, ...options });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            PharmaUtils.showAlert(`Error en la comunicación con el servidor: ${error.message}`, 'danger');
            throw error;
        }
    },

    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    },

    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
};


if (typeof module !== 'undefined' && module.exports) {
    module.exports = { PharmaConfig, PharmaUtils, PharmaAPI };
}