document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Cerrar sidebar al hacer click fuera (móvil)
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Submenu toggle
        document.querySelectorAll('.nav-link.has-submenu').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
                const submenu = this.nextElementSibling;
                if (submenu) {
                    submenu.classList.toggle('active');
                }
            });
        });

        // Loading Functions
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('active');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('active');
        }

        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Confirmación de logout
        document.getElementById('logout-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Cerrar sesión?',
                text: "¿Está seguro que desea salir del sistema?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1e40af',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });