// public/js/dashboard-admin.js
class PharmaAdminDashboard {
    constructor(dashboardData = {}) {
        this.sidebar = document.getElementById('sidebar');
        this.mainContent = document.querySelector('.main-content');
        this.sidebarToggle = document.getElementById('sidebarToggle');
        this.mobileToggle = document.getElementById('mobileToggle');
        this.navLinks = document.querySelectorAll('.nav-link');
        this.contentSections = document.querySelectorAll('.content-section');
        this.pageTitle = document.querySelector('.page-title');
        this.dashboardData = dashboardData;
        
        this.init();
    }

    init() {
        this.setupEventList
        s();
        this.initializeCharts();
        this.setupAutoRefresh();
        this.updateDateTime();
        

        setInterval(() => this.updateDateTime(), 60000);
        
        console.log('Dashboard data loaded:', this.dashboardData);
    }

    setupEventListeners() {

        if (this.sidebarToggle) {
            this.sidebarToggle.addEventListener('click', () => this.toggleSidebar());
        }


        if (this.mobileToggle) {
            this.mobileToggle.addEventListener('click', () => this.toggleMobileSidebar());
        }

 
        this.navLinks.forEach(link => {
            link.addEventListener('click', (e) => this.handleNavigation(e));
        });

   
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && 
                !this.sidebar.contains(e.target) && 
                !this.mobileToggle.contains(e.target)) {
                this.sidebar.classList.remove('mobile-open');
            }
        });


        window.addEventListener('resize', () => this.handleResize());


        const chartFilters = document.querySelectorAll('.chart-filter');
        chartFilters.forEach(filter => {
            filter.addEventListener('change', (e) => this.updateChartData(e));
        });
    }

    toggleSidebar() {
        this.sidebar.classList.toggle('collapsed');
        this.mainContent.classList.toggle('expanded');
        
      
        localStorage.setItem('sidebarCollapsed', this.sidebar.classList.contains('collapsed'));
    }

    toggleMobileSidebar() {
        this.sidebar.classList.toggle('mobile-open');
    }

    handleNavigation(e) {
        e.preventDefault();
        
        const clickedLink = e.currentTarget;
        const targetSection = clickedLink.getAttribute('data-section');
        
 
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
        });
        clickedLink.parentElement.classList.add('active');
        
   
        this.showSection(targetSection);
        

        const sectionTitle = clickedLink.querySelector('span').textContent;
        this.pageTitle.textContent = sectionTitle;

        if (window.innerWidth <= 768) {
            this.sidebar.classList.remove('mobile-open');
        }
    }

    showSection(sectionId) {

        this.contentSections.forEach(section => {
            section.classList.remove('active');
        });
        
   
        const targetSection = document.getElementById(`${sectionId}-section`);
        if (targetSection) {
            targetSection.classList.add('active');
        }
    }

    handleResize() {
        if (window.innerWidth > 768) {
            this.sidebar.classList.remove('mobile-open');
        }
    }


    initializeCharts() {
        this.initSalesChart();
        this.initTopProductsChart();
    }

    initSalesChart() {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;

        const firstData = this.dashboardData.graficosPorMes?.[0] || { labels: [], data: [] };

        this.salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: firstData.labels,
                datasets: [{
                    label: 'Ventas (S/)',
                    data: firstData.data,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: function(value){ return 'S/ ' + value.toLocaleString(); } },
                        grid: { borderDash: [5,5] }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        const monthSelector = document.getElementById('monthSelector');
        if (monthSelector) {
            monthSelector.addEventListener('change', (e) => {
                const selected = e.target.value;
                if (selected === 'all') {
                    const allData = this.dashboardData.graficosPorMes || [];
                    let labels = [], data = [];
                    allData.forEach(m => { labels = labels.concat(m.labels); data = data.concat(m.data); });
                    this.salesChart.data.labels = labels;
                    this.salesChart.data.datasets[0].data = data;
                } else {
                    const monthData = this.dashboardData.graficosPorMes[selected];
                    this.salesChart.data.labels = monthData.labels;
                    this.salesChart.data.datasets[0].data = monthData.data;
                }
                this.salesChart.update();
            });
        }
    }


    filterSalesCharts(selectedValue) {
        if (!this.salesCharts) return;
        this.salesCharts.forEach((chart, index) => {
            const chartDiv = document.querySelector(`.monthly-chart[data-index="${index}"]`);
            if (!chartDiv) return;

            if (selectedValue === 'all' || selectedValue == index) {
                chartDiv.style.display = 'block';
                chart.update();
            } else {
                chartDiv.style.display = 'none';
            }
        });
    }


    initTopProductsChart() {
        const ctx = document.getElementById('topProductsChart');
        if (!ctx) return;

      
        const productData = this.dashboardData.topProductos || {
            labels: ['Sin datos', 'Sin datos', 'Sin datos', 'Sin datos', 'Sin datos'],
            data: [1, 1, 1, 1, 1],
            colors: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#6b7280']
        };

        const topProductsChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: productData.labels,
                datasets: [{
                    data: productData.data,
                    backgroundColor: productData.colors,
                    borderWidth: 0,
                    hoverBorderWidth: 2,
                    hoverBorderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        this.topProductsChart = topProductsChart;
    }

    // Auto-refresh data every 5 minutes
    setupAutoRefresh() {
        setInterval(async () => {
            try {
                const response = await fetch('/dashboard/stats', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.updateStatsDisplay(data.data);
                    this.updateChartsWithData(data.data); // <-- Nuevo método
                    console.log('Stats updated:', data.data);
                }
                if (this.salesChart && data.ventasMensuales) {
                    this.salesChart.data.labels = data.ventasMensuales.labels;
                    this.salesChart.data.datasets[0].data = data.ventasMensuales.data;
                    this.salesChart.update('active');
                }

                // Actualizar gráfico de productos
                if (this.topProductsChart && data.topProductos) {
                    this.topProductsChart.data.labels = data.topProductos.labels;
                    this.topProductsChart.data.datasets[0].data = data.topProductos.data;
                    this.topProductsChart.update('active');
                }
            } catch (error) {
                console.error('Error updating stats:', error);
            }
        }, 300000); // 5 minutes
    }

    updateStatsDisplay(data) {
        // Actualizar las tarjetas de estadísticas
        const totalSales = document.getElementById('totalSales');
        if (totalSales) {
            totalSales.textContent = 'S/ ' + new Intl.NumberFormat('es-PE').format(data.ventasMes || 0);
        }

        const totalProducts = document.getElementById('totalProducts');
        if (totalProducts) {
            totalProducts.textContent = new Intl.NumberFormat('es-PE').format(data.totalProductos || 0);
        }

        const lowStock = document.getElementById('lowStock');
        if (lowStock) {
            lowStock.textContent = data.stockBajo || 0;
        }

        const prescriptions = document.getElementById('prescriptions');
        if (prescriptions) {
            prescriptions.textContent = data.recetasProcesadas || 0;
        }

        // Actualizar el badge de notificaciones
        const notificationCount = document.getElementById('notificationCount');
        if (notificationCount) {
            const alertCount = (data.stockBajo || 0) + (data.stockCritico || 0);
            notificationCount.textContent = alertCount;
        }
    }

    updateDateTime() {
        const now = new Date();
        const dateTime = now.toLocaleString('es-PE', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Update datetime display if exists
        const dateTimeElement = document.getElementById('currentDateTime');
        if (dateTimeElement) {
            dateTimeElement.textContent = dateTime;
        }
    }

    updateChartData(e) {
        const filterValue = e.target.value;
        const chartContainer = e.target.closest('.chart-card');
        const chartCanvas = chartContainer.querySelector('canvas');
        
        // Show loading state
        chartContainer.classList.add('loading');
        
        // Simulate API call delay
        setTimeout(() => {
            if (chartCanvas.id === 'salesChart') {
                this.updateSalesChartData(filterValue);
            }
            chartContainer.classList.remove('loading');
        }, 500);
    }

    updateSalesChartData(months) {
        if (!this.salesChart) return;

        // Por ahora usar los mismos datos, más adelante puedes hacer una llamada AJAX
        // para obtener datos específicos del período seleccionado
        let labels, data;
        
        if (months === '12') {
            labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            data = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // Llenar con datos reales
        } else {
            const chartData = this.dashboardData.ventasMensuales || {
                labels: ['Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep'],
                data: [0, 0, 0, 0, 0, 0]
            };
            labels = chartData.labels;
            data = chartData.data;
        }

        this.salesChart.data.labels = labels;
        this.salesChart.data.datasets[0].data = data;
        this.salesChart.update('active');
    }

    // Public methods
    refreshDashboard() {
        this.setupAutoRefresh();
        if (this.salesChart) this.salesChart.update();
        if (this.topProductsChart) this.topProductsChart.update();
    }
}

// Función global para inicializar el dashboard
function initializeDashboard(dashboardData) {
    window.pharmaAdmin = new PharmaAdminDashboard(dashboardData);
    
    // Load saved preferences
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        document.getElementById('sidebar').classList.add('collapsed');
        document.querySelector('.main-content').classList.add('expanded');
    }
}
