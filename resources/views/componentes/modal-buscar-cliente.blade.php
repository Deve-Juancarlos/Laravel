<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Cliente - Modal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --shadow-elegant: 0 20px 40px rgba(0, 0, 0, 0.1);
            --border-radius: 15px;
        }

        body {
            background: var(--primary-gradient);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .modal-content {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-elegant);
            backdrop-filter: blur(20px);
            background: var(--glass-bg);
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            border: none;
            padding: 2rem;
        }

        .modal-header h5 {
            font-weight: 600;
            font-size: 1.5rem;
            margin: 0;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .modal-body {
            padding: 2rem;
        }

        .search-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .search-input-group {
            position: relative;
        }

        .search-input {
            border-radius: 50px;
            border: 2px solid #e9ecef;
            padding: 1rem 1.5rem 1rem 3rem;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            transform: translateY(-2px);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.2rem;
            z-index: 10;
        }

        .quick-filters {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-chip {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .filter-chip:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .client-table-container {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .client-table {
            margin: 0;
        }

        .client-table thead th {
            background: var(--primary-gradient);
            color: white;
            border: none;
            font-weight: 600;
            padding: 1.2rem;
            font-size: 0.95rem;
        }

        .client-table tbody td {
            padding: 1.2rem;
            border-color: rgba(0, 0, 0, 0.05);
            vertical-align: middle;
        }

        .client-row {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .client-row:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            transform: scale(1.01);
        }

        .client-status {
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-activo {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .status-inactivo {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .status-vip {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .client-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 1rem;
        }

        .client-info h6 {
            margin: 0;
            font-weight: 600;
            color: #2c3e50;
        }

        .client-info small {
            color: #6c757d;
        }

        .credit-badge {
            background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            color: #2d3436;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .btn-primary-custom {
            background: var(--primary-gradient);
            border: none;
            border-radius: 50px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .modal-footer {
            border: none;
            padding: 2rem;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }

        .stats-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            flex: 1;
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            border-radius: var(--border-radius);
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <!-- Modal -->
    <div class="modal fade" id="buscarClienteModal" tabindex="-1" aria-labelledby="buscarClienteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="buscarClienteModalLabel">
                        <i class="fas fa-users me-2"></i>
                        Buscar Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body position-relative">
                    <!-- Loading Overlay -->
                    <div class="loading-overlay" id="loadingOverlay">
                        <div class="loading-spinner"></div>
                    </div>

                    <!-- Estadísticas Rápidas -->
                    <div class="stats-row">
                        <div class="stat-card">
                            <div class="stat-number text-primary">1,247</div>
                            <div class="stat-label">Clientes Activos</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number text-success">856</div>
                            <div class="stat-label">Clientes VIP</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number text-warning">234</div>
                            <div class="stat-label">Nuevos Este Mes</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number text-info">$45,280</div>
                            <div class="stat-label">Crédito Total</div>
                        </div>
                    </div>

                    <!-- Búsqueda -->
                    <div class="search-card">
                        <div class="search-input-group">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control search-input" id="clientSearch" placeholder="Buscar por nombre, documento, teléfono o email...">
                        </div>
                        
                        <div class="quick-filters">
                            <button class="filter-chip" data-filter="activos">
                                <i class="fas fa-user-check me-1"></i> Activos
                            </button>
                            <button class="filter-chip" data-filter="vip">
                                <i class="fas fa-crown me-1"></i> VIP
                            </button>
                            <button class="filter-chip" data-filter="nuevos">
                                <i class="fas fa-user-plus me-1"></i> Nuevos
                            </button>
                            <button class="filter-chip" data-filter="credito">
                                <i class="fas fa-credit-card me-1"></i> Con Crédito
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de Clientes -->
                    <div class="client-table-container">
                        <table class="table table-hover client-table" id="clientsTable">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-user me-2"></i>Cliente</th>
                                    <th><i class="fas fa-id-card me-2"></i>Documento</th>
                                    <th><i class="fas fa-phone me-2"></i>Contacto</th>
                                    <th><i class="fas fa-calendar me-2"></i>Última Compra</th>
                                    <th><i class="fas fa-dollar-sign me-2"></i>Total Compras</th>
                                    <th><i class="fas fa-info-circle me-2"></i>Estado</th>
                                    <th><i class="fas fa-cogs me-2"></i>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="client-row" data-client-id="1">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="client-avatar">MG</div>
                                            <div class="client-info">
                                                <h6>María García López</h6>
                                                <small>Cliente desde 2022</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>45896231</strong><br>
                                        <small class="text-muted">DNI</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone text-success me-1"></i> 987654321<br>
                                        <i class="fas fa-envelope text-primary me-1"></i> maria.garcia@email.com
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Hace 2 días</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">S/ 15,420.50</strong>
                                    </td>
                                    <td>
                                        <span class="client-status status-vip">VIP</span>
                                        <br>
                                        <small class="credit-badge">S/ 5,000.00</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="seleccionarCliente(1)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="verDetalles(1)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="editarCliente(1)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="client-row" data-client-id="2">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="client-avatar bg-gradient-secondary">CR</div>
                                            <div class="client-info">
                                                <h6>Carlos Rodríguez</h6>
                                                <small>Cliente desde 2023</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>78945612</strong><br>
                                        <small class="text-muted">DNI</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone text-success me-1"></i> 912345678<br>
                                        <i class="fas fa-envelope text-primary me-1"></i> carlos.rodriguez@email.com
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Hace 1 semana</span>
                                    </td>
                                    <td>
                                        <strong class="text-primary">S/ 8,750.25</strong>
                                    </td>
                                    <td>
                                        <span class="client-status status-activo">Activo</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="seleccionarCliente(2)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="verDetalles(2)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="editarCliente(2)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="client-row" data-client-id="3">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="client-avatar bg-gradient-warning">LP</div>
                                            <div class="client-info">
                                                <h6>Lucía Pérez Santos</h6>
                                                <small>Cliente desde 2021</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>32165498</strong><br>
                                        <small class="text-muted">DNI</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone text-success me-1"></i> 998877665<br>
                                        <i class="fas fa-envelope text-primary me-1"></i> lucia.perez@email.com
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">Hace 3 semanas</span>
                                    </td>
                                    <td>
                                        <strong class="text-muted">S/ 2,180.75</strong>
                                    </td>
                                    <td>
                                        <span class="client-status status-inactivo">Inactivo</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="seleccionarCliente(3)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="verDetalles(3)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="editarCliente(3)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="client-row" data-client-id="4">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="client-avatar bg-gradient-success">JM</div>
                                            <div class="client-info">
                                                <h6>José Martínez</h6>
                                                <small>Cliente desde 2024</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>65432109</strong><br>
                                        <small class="text-muted">DNI</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone text-success me-1"></i> 976543210<br>
                                        <i class="fas fa-envelope text-primary me-1"></i> jose.martinez@email.com
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Ayer</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">S/ 12,890.30</strong>
                                    </td>
                                    <td>
                                        <span class="client-status status-vip">VIP</span>
                                        <br>
                                        <small class="credit-badge">S/ 8,000.00</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="seleccionarCliente(4)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="verDetalles(4)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="editarCliente(4)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary-custom btn-lg" onclick="crearNuevoCliente()">
                        <i class="fas fa-plus me-2"></i>Crear Nuevo Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            let clientsTable = $('#clientsTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 10,
                responsive: true,
                order: [[4, 'desc']], // Ordenar por total de compras
                columnDefs: [
                    { orderable: false, targets: [6] }, // No ordenar columna de acciones
                    { className: "dt-center", targets: [6] }
                ]
            });

            // Búsqueda en tiempo real
            $('#clientSearch').on('keyup', function() {
                clientsTable.search(this.value).draw();
                
                // Simular carga
                $('#loadingOverlay').show();
                setTimeout(() => {
                    $('#loadingOverlay').hide();
                }, 500);
            });

            // Filtros rápidos
            $('.filter-chip').on('click', function() {
                const filter = $(this).data('filter');
                
                // Remover clase active de todos los filtros
                $('.filter-chip').removeClass('active');
                
                // Agregar clase active al filtro seleccionado
                $(this).addClass('active');
                
                // Aplicar filtro
                applyQuickFilter(filter);
            });

            // Selección de cliente al hacer clic en la fila
            $('.client-row').on('click', function() {
                const clientId = $(this).data('client-id');
                seleccionarCliente(clientId);
            });

            // Animación de entrada del modal
            $('#buscarClienteModal').on('shown.bs.modal', function () {
                $('#clientSearch').focus();
                $('.stat-card').addClass('pulse');
            });
        });

        // Función para aplicar filtros rápidos
        function applyQuickFilter(filter) {
            const table = $('#clientsTable').DataTable();
            
            $('#loadingOverlay').show();
            
            setTimeout(() => {
                switch(filter) {
                    case 'activos':
                        // Filtrar por estado activo (excepto inactivos)
                        table.column(5).search('').draw();
                        break;
                    case 'vip':
                        // Filtrar por clientes VIP
                        table.column(5).search('VIP').draw();
                        break;
                    case 'nuevos':
                        // Filtrar por clientes nuevos (último año)
                        table.column(0).search('2024').draw();
                        break;
                    case 'credito':
                        // Filtrar por clientes con crédito
                        table.column(5).search('S/').draw();
                        break;
                }
                $('#loadingOverlay').hide();
                
                // Mostrar notificación
                Swal.fire({
                    icon: 'success',
                    title: 'Filtro aplicado',
                    text: 'Los resultados han sido filtrados correctamente',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }, 800);
        }

        // Función para seleccionar cliente
        function seleccionarCliente(clientId) {
            // Obtener datos del cliente de la tabla
            const row = $(`tr[data-client-id="${clientId}"]`);
            const clienteNombre = row.find('h6').text();
            const clienteDoc = row.find('td:nth-child(2) strong').text();

            // Simular selección
            Swal.fire({
                icon: 'success',
                title: 'Cliente seleccionado',
                html: `<strong>${clienteNombre}</strong><br>Doc: ${clienteDoc}`,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });

            // Cerrar modal después de un breve delay
            setTimeout(() => {
                $('#buscarClienteModal').modal('hide');
            }, 1500);
        }

        // Función para ver detalles del cliente
        function verDetalles(clientId) {
            Swal.fire({
                icon: 'info',
                title: 'Ver Detalles',
                text: `Abriendo detalles del cliente ID: ${clientId}`,
                showConfirmButton: true,
                confirmButtonText: 'Cerrar'
            });
        }

        // Función para editar cliente
        function editarCliente(clientId) {
            Swal.fire({
                icon: 'warning',
                title: 'Editar Cliente',
                text: `Abriendo editor para cliente ID: ${clientId}`,
                showConfirmButton: true,
                confirmButtonText: 'Cerrar'
            });
        }

        // Función para crear nuevo cliente
        function crearNuevoCliente() {
            Swal.fire({
                icon: 'question',
                title: 'Nuevo Cliente',
                text: '¿Desea crear un nuevo cliente?',
                showCancelButton: true,
                confirmButtonText: 'Sí, crear',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Cerrar modal y abrir formulario de nuevo cliente
                    $('#buscarClienteModal').modal('hide');
                    
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Nuevo Cliente',
                            text: 'Abriendo formulario de nuevo cliente...',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }, 500);
                }
            });
        }

        // Animaciones CSS adicionales
        document.addEventListener('DOMContentLoaded', function() {
            // Añadir efectos de hover mejorados
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>
</html>