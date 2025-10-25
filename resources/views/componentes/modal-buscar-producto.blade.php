<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Producto - Modal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
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
            background: var(--success-gradient);
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
            border-color: #4facfe;
            box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
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

        .category-filters {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .category-chip {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .category-chip::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--success-gradient);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .category-chip:hover::before {
            left: 0;
        }

        .category-chip:hover {
            color: white;
            border-color: #4facfe;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
        }

        .category-chip.active {
            background: var(--success-gradient);
            color: white;
            border-color: #4facfe;
        }

        .product-table-container {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .product-table {
            margin: 0;
        }

        .product-table thead th {
            background: var(--success-gradient);
            color: white;
            border: none;
            font-weight: 600;
            padding: 1.2rem;
            font-size: 0.95rem;
        }

        .product-table tbody td {
            padding: 1.2rem;
            border-color: rgba(0, 0, 0, 0.05);
            vertical-align: middle;
        }

        .product-row {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-row:hover {
            background: linear-gradient(135deg, rgba(79, 172, 254, 0.05) 0%, rgba(0, 242, 254, 0.05) 100%);
            transform: scale(1.01);
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            background: var(--success-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 1rem;
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
        }

        .product-info h6 {
            margin: 0;
            font-weight: 600;
            color: #2c3e50;
        }

        .product-info small {
            color: #6c757d;
        }

        .stock-badge {
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .stock-alto {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .stock-medio {
            background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            color: #2d3436;
        }

        .stock-bajo {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .stock-agotado {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
        }

        .price-highlight {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .discount-badge {
            background: var(--danger-gradient);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 0.5rem;
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
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--success-gradient);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(79, 172, 254, 0.2);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: var(--success-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
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
            z-index: 1000;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4facfe;
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

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .shape {
            position: absolute;
            background: rgba(79, 172, 254, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 80%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .quick-search-tags {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .search-tag {
            background: rgba(79, 172, 254, 0.1);
            border: 1px solid rgba(79, 172, 254, 0.3);
            border-radius: 20px;
            padding: 0.3rem 0.8rem;
            font-size: 0.8rem;
            color: #4facfe;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-tag:hover {
            background: var(--success-gradient);
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Modal -->
    <div class="modal fade" id="buscarProductoModal" tabindex="-1" aria-labelledby="buscarProductoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content position-relative">
                <!-- Elementos flotantes decorativos -->
                <div class="floating-shapes">
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                </div>

                <div class="modal-header">
                    <h5 class="modal-title" id="buscarProductoModalLabel">
                        <i class="fas fa-boxes me-2"></i>
                        Buscar Producto
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
                            <div class="stat-number">2,847</div>
                            <div class="stat-label">Productos Disponibles</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">1,234</div>
                            <div class="stat-label">Stock Bajo</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">567</div>
                            <div class="stat-label">Con Descuento</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">S/ 1.2M</div>
                            <div class="stat-label">Valor Inventario</div>
                        </div>
                    </div>

                    <!-- Búsqueda -->
                    <div class="search-card">
                        <div class="search-input-group">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control search-input" id="productSearch" placeholder="Buscar por nombre, código, laboratorio, principio activo...">
                        </div>
                        
                        <div class="category-filters">
                            <div class="category-chip active" data-category="todos">
                                <i class="fas fa-th me-1"></i> Todos
                            </div>
                            <div class="category-chip" data-category="medicamentos">
                                <i class="fas fa-pills me-1"></i> Medicamentos
                            </div>
                            <div class="category-chip" data-category="suplementos">
                                <i class="fas fa-heartbeat me-1"></i> Suplementos
                            </div>
                            <div class="category-chip" data-category="cuidado-personal">
                                <i class="fas fa-spa me-1"></i> Cuidado Personal
                            </div>
                            <div class="category-chip" data-category="bebes">
                                <i class="fas fa-baby me-1"></i> Bebés
                            </div>
                            <div class="category-chip" data-category="dispositivos">
                                <i class="fas fa-thermometer-half me-1"></i> Dispositivos
                            </div>
                        </div>

                        <div class="quick-search-tags">
                            <span class="search-tag">Paracetamol</span>
                            <span class="search-tag">Ibuprofeno</span>
                            <span class="search-tag">Omeprazol</span>
                            <span class="search-tag">Vitamina C</span>
                            <span class="search-tag">Aspirina</span>
                            <span class="search-tag">Antibióticos</span>
                        </div>
                    </div>

                    <!-- Tabla de Productos -->
                    <div class="product-table-container">
                        <table class="table table-hover product-table" id="productsTable">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-box me-2"></i>Producto</th>
                                    <th><i class="fas fa-barcode me-2"></i>Código</th>
                                    <th><i class="fas fa-flask me-2"></i>Laboratorio</th>
                                    <th><i class="fas fa-thermometer-half me-2"></i>Stock</th>
                                    <th><i class="fas fa-dollar-sign me-2"></i>Precio</th>
                                    <th><i class="fas fa-tags me-2"></i>Categoría</th>
                                    <th><i class="fas fa-cogs me-2"></i>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="product-row" data-product-id="1">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="product-image">
                                                <i class="fas fa-pills"></i>
                                            </div>
                                            <div class="product-info">
                                                <h6>Paracetamol 500mg</h6>
                                                <small>Caja x 20 comprimidos</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>PARA50020</strong><br>
                                        <small class="text-muted">EAN: 7894561234567</small>
                                    </td>
                                    <td>
                                        <strong>Laboratorios InkaFarma</strong><br>
                                        <small class="text-muted">Fabricación: 2024</small>
                                    </td>
                                    <td>
                                        <span class="stock-badge stock-alto">150 unidades</span>
                                    </td>
                                    <td>
                                        <div class="price-highlight">
                                            S/ 8.50
                                            <span class="discount-badge">-15%</span>
                                        </div>
                                        <small class="text-muted">Precio anterior: S/ 10.00</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">Medicamentos</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-success" onclick="seleccionarProducto(1)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="verDetallesProducto(1)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="agregarCarrito(1)">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="product-row" data-product-id="2">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="product-image" style="background: var(--warning-gradient);">
                                                <i class="fas fa-capsules"></i>
                                            </div>
                                            <div class="product-info">
                                                <h6>Ibuprofeno 400mg</h6>
                                                <small>Sobre x 10 cápsulas</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>IBU40010</strong><br>
                                        <small class="text-muted">EAN: 7894561234568</small>
                                    </td>
                                    <td>
                                        <strong>PharmaCorp Perú</strong><br>
                                        <small class="text-muted">Fabricación: 2024</small>
                                    </td>
                                    <td>
                                        <span class="stock-badge stock-medio">25 unidades</span>
                                    </td>
                                    <td>
                                        <div class="price-highlight">
                                            S/ 12.80
                                        </div>
                                        <small class="text-muted">Precio público: S/ 15.00</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">Medicamentos</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-success" onclick="seleccionarProducto(2)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="verDetallesProducto(2)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="agregarCarrito(2)">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="product-row" data-product-id="3">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="product-image" style="background: var(--danger-gradient);">
                                                <i class="fas fa-heartbeat"></i>
                                            </div>
                                            <div class="product-info">
                                                <h6>Omeprazol 20mg</h6>
                                                <small>Caja x 14 cápsulas</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>OME20</strong><br>
                                        <small class="text-muted">EAN: 7894561234569</small>
                                    </td>
                                    <td>
                                        <strong>MediLife S.A.C.</strong><br>
                                        <small class="text-muted">Fabricación: 2023</small>
                                    </td>
                                    <td>
                                        <span class="stock-badge stock-bajo">8 unidades</span>
                                    </td>
                                    <td>
                                        <div class="price-highlight">
                                            S/ 18.50
                                            <span class="discount-badge">-20%</span>
                                        </div>
                                        <small class="text-muted">Precio anterior: S/ 23.00</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">Medicamentos</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-success" onclick="seleccionarProducto(3)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="verDetallesProducto(3)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="agregarCarrito(3)">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="product-row" data-product-id="4">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="product-image" style="background: var(--info-gradient); color: #2c3e50;">
                                                <i class="fas fa-seedling"></i>
                                            </div>
                                            <div class="product-info">
                                                <h6>Vitamina C 1000mg</h6>
                                                <small>Frasco x 60 comprimidos</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>VITC1000</strong><br>
                                        <small class="text-muted">EAN: 7894561234570</small>
                                    </td>
                                    <td>
                                        <strong>NaturalHealth</strong><br>
                                        <small class="text-muted">Fabricación: 2024</small>
                                    </td>
                                    <td>
                                        <span class="stock-badge stock-alto">85 unidades</span>
                                    </td>
                                    <td>
                                        <div class="price-highlight">
                                            S/ 35.90
                                        </div>
                                        <small class="text-muted">Precio público: S/ 42.00</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Suplementos</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-success" onclick="seleccionarProducto(4)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="verDetallesProducto(4)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="agregarCarrito(4)">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="product-row" data-product-id="5">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="product-image" style="background: var(--secondary-gradient);">
                                                <i class="fas fa-thermometer-half"></i>
                                            </div>
                                            <div class="product-info">
                                                <h6>Termómetro Digital</h6>
                                                <small>Unidad - Material: Plástico ABS</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>TERM001</strong><br>
                                        <small class="text-muted">EAN: 7894561234571</small>
                                    </td>
                                    <td>
                                        <strong>MedDevice Corp.</strong><br>
                                        <small class="text-muted">Fabricación: 2024</small>
                                    </td>
                                    <td>
                                        <span class="stock-badge stock-agotado">0 unidades</span>
                                    </td>
                                    <td>
                                        <div class="price-highlight">
                                            S/ 45.00
                                        </div>
                                        <small class="text-muted">Sin descuentos</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Dispositivos</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-success" onclick="seleccionarProducto(5)" disabled>
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="verDetallesProducto(5)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="agregarCarrito(5)" disabled>
                                                <i class="fas fa-shopping-cart"></i>
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
                    <button type="button" class="btn btn-success-custom btn-lg" onclick="crearNuevoProducto()">
                        <i class="fas fa-plus me-2"></i>Crear Nuevo Producto
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

    <style>
        .btn-success-custom {
            background: var(--success-gradient);
            border: none;
            border-radius: 50px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-success-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
            color: white;
        }
    </style>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            let productsTable = $('#productsTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 10,
                responsive: true,
                order: [[4, 'asc']], // Ordenar por precio
                columnDefs: [
                    { orderable: false, targets: [6] }, // No ordenar columna de acciones
                    { className: "dt-center", targets: [6] }
                ]
            });

            // Búsqueda en tiempo real
            $('#productSearch').on('keyup', function() {
                productsTable.search(this.value).draw();
                
                // Simular carga
                $('#loadingOverlay').show();
                setTimeout(() => {
                    $('#loadingOverlay').hide();
                }, 500);
            });

            // Filtros de categoría
            $('.category-chip').on('click', function() {
                const category = $(this).data('category');
                
                // Remover clase active de todos los filtros
                $('.category-chip').removeClass('active');
                
                // Agregar clase active al filtro seleccionado
                $(this).addClass('active');
                
                // Aplicar filtro
                applyCategoryFilter(category);
            });

            // Tags de búsqueda rápida
            $('.search-tag').on('click', function() {
                const tag = $(this).text();
                $('#productSearch').val(tag).trigger('keyup');
            });

            // Selección de producto al hacer clic en la fila
            $('.product-row').on('click', function() {
                const productId = $(this).data('product-id');
                const stockBadge = $(this).find('.stock-badge');
                
                // Verificar si el producto tiene stock
                if (stockBadge.hasClass('stock-agotado')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sin Stock',
                        text: 'Este producto no está disponible en este momento',
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    return;
                }
                
                seleccionarProducto(productId);
            });

            // Animación de entrada del modal
            $('#buscarProductoModal').on('shown.bs.modal', function () {
                $('#productSearch').focus();
                $('.stat-card').addClass('pulse');
            });
        });

        // Función para aplicar filtro de categoría
        function applyCategoryFilter(category) {
            const table = $('#productsTable').DataTable();
            
            $('#loadingOverlay').show();
            
            setTimeout(() => {
                if (category === 'todos') {
                    table.column(5).search('').draw();
                } else {
                    // Mapear categorías a los badges
                    const categoryMap = {
                        'medicamentos': 'Medicamentos',
                        'suplementos': 'Suplementos',
                        'cuidado-personal': 'Cuidado Personal',
                        'bebes': 'Bebés',
                        'dispositivos': 'Dispositivos'
                    };
                    
                    const searchTerm = categoryMap[category] || '';
                    table.column(5).search(searchTerm).draw();
                }
                $('#loadingOverlay').hide();
                
                // Mostrar notificación
                Swal.fire({
                    icon: 'success',
                    title: 'Filtro aplicado',
                    text: `Mostrando productos de: ${category === 'todos' ? 'todas las categorías' : category}`,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }, 800);
        }

        // Función para seleccionar producto
        function seleccionarProducto(productId) {
            // Obtener datos del producto de la tabla
            const row = $(`tr[data-product-id="${productId}"]`);
            const productoNombre = row.find('h6').text();
            const productoCodigo = row.find('td:nth-child(2) strong').text();
            const productoPrecio = row.find('.price-highlight').text().trim();

            // Simular selección
            Swal.fire({
                icon: 'success',
                title: 'Producto seleccionado',
                html: `<strong>${productoNombre}</strong><br>Código: ${productoCodigo}<br>Precio: ${productoPrecio}`,
                timer: 2500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });

            // Cerrar modal después de un breve delay
            setTimeout(() => {
                $('#buscarProductoModal').modal('hide');
            }, 2000);
        }

        // Función para ver detalles del producto
        function verDetallesProducto(productId) {
            Swal.fire({
                icon: 'info',
                title: 'Ver Detalles',
                text: `Abriendo detalles del producto ID: ${productId}`,
                showConfirmButton: true,
                confirmButtonText: 'Cerrar'
            });
        }

        // Función para agregar al carrito
        function agregarCarrito(productId) {
            const row = $(`tr[data-product-id="${productId}"]`);
            const productoNombre = row.find('h6').text();

            Swal.fire({
                icon: 'success',
                title: 'Agregado al carrito',
                text: `${productoNombre} ha sido agregado al carrito`,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }

        // Función para crear nuevo producto
        function crearNuevoProducto() {
            Swal.fire({
                icon: 'question',
                title: 'Nuevo Producto',
                text: '¿Desea crear un nuevo producto?',
                showCancelButton: true,
                confirmButtonText: 'Sí, crear',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Cerrar modal y abrir formulario de nuevo producto
                    $('#buscarProductoModal').modal('hide');
                    
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Nuevo Producto',
                            text: 'Abriendo formulario de nuevo producto...',
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

            // Efectos de hover en imágenes de productos
            const productImages = document.querySelectorAll('.product-image');
            productImages.forEach(image => {
                image.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.1) rotate(5deg)';
                    this.style.boxShadow = '0 15px 30px rgba(79, 172, 254, 0.4)';
                });
                
                image.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1) rotate(0deg)';
                    this.style.boxShadow = '0 5px 15px rgba(79, 172, 254, 0.3)';
                });
            });
        });
    </script>
</body>
</html>