@extends('layouts.app')

@section('title', 'Búsqueda Avanzada de Clientes')
@section('page-title', 'Búsqueda Avanzada de Clientes')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('contador.clientes.index') }}" class="text-decoration-none">
            <i class="fas fa-users"></i> Clientes
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Búsqueda Avanzada</li>
@endsection

@push('styles')
<style>
    .search-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }
    
    .filter-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #667eea;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #667eea;
    }
    
    .client-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        margin-bottom: 1rem;
        border-left: 4px solid #667eea;
    }
    
    .client-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    
    .client-avatar {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .client-info h6 {
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }
    
    .client-info small {
        color: #6c757d;
    }
    
    .client-stats {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .client-stat {
        text-align: center;
        padding: 0.5rem 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .client-stat-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
    }
    
    .client-stat-value {
        font-size: 1rem;
        font-weight: 700;
        color: #2c3e50;
    }
    
    .quick-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .view-toggle {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.5rem;
        display: inline-flex;
    }
    
    .view-toggle button {
        border: none;
        background: transparent;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    .view-toggle button.active {
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .saved-search-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .saved-search-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    }
</style>
@endpush

@section('content')

<!-- Header de Búsqueda -->
<div class="search-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3><i class="fas fa-search me-2"></i>Búsqueda Avanzada de Clientes</h3>
            <p class="mb-0 opacity-75">Encuentre clientes usando múltiples criterios de búsqueda</p>
        </div>
        <div>
            <button class="btn btn-light" onclick="limpiarBusqueda()">
                <i class="fas fa-eraser me-1"></i>Limpiar Filtros
            </button>
        </div>
    </div>
</div>

<!-- Búsqueda Rápida -->
<div class="filter-card">
    <form id="busquedaForm" method="GET">
        <div class="row align-items-end mb-3">
            <div class="col-md-8">
                <label class="form-label fw-bold">
                    <i class="fas fa-bolt text-warning me-1"></i>Búsqueda Rápida
                </label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Nombre, RUC, DNI, email, teléfono...">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Buscar
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="busquedaAvanzada" 
                           {{ request('avanzada') ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="busquedaAvanzada">
                        <i class="fas fa-sliders-h me-1"></i>Activar Filtros Avanzados
                    </label>
                </div>
            </div>
        </div>

        <!-- Filtros Avanzados -->
        <div id="filtrosAvanzados" style="{{ request('avanzada') ? '' : 'display: none;' }}">
            <hr class="my-4">
            
            <div class="row">
                <!-- Información Básica -->
                <div class="col-md-4">
                    <h6 class="filter-section-title">Información Básica</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipo de Cliente</label>
                        <select class="form-select" name="tipo">
                            <option value="">Todos</option>
                            <option value="1" {{ request('tipo') == '1' ? 'selected' : '' }}>Regular</option>
                            <option value="2" {{ request('tipo') == '2' ? 'selected' : '' }}>Premium</option>
                            <option value="3" {{ request('tipo') == '3' ? 'selected' : '' }}>VIP</option>
                            <option value="4" {{ request('tipo') == '4' ? 'selected' : '' }}>Corporativo</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado">
                            <option value="">Todos</option>
                            <option value="1" {{ request('estado') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ request('estado') == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>

                <!-- Ubicación -->
                <div class="col-md-4">
                    <h6 class="filter-section-title">Ubicación</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Zona</label>
                        <select class="form-select" name="zona">
                            <option value="">Todas</option>
                            <option value="001" {{ request('zona') == '001' ? 'selected' : '' }}>Zona 001</option>
                            <option value="002" {{ request('zona') == '002' ? 'selected' : '' }}>Zona 002</option>
                            <option value="003" {{ request('zona') == '003' ? 'selected' : '' }}>Zona 003</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Búsqueda por Dirección</label>
                        <input type="text" class="form-control" name="direccion" 
                               value="{{ request('direccion') }}" 
                               placeholder="Av., Jr., Distrito...">
                    </div>
                </div>

                <!-- Información Comercial -->
                <div class="col-md-4">
                    <h6 class="filter-section-title">Información Comercial</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Límite de Crédito</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="number" class="form-control" name="limite_min" 
                                       placeholder="Mín" value="{{ request('limite_min') }}">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control" name="limite_max" 
                                       placeholder="Máx" value="{{ request('limite_max') }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="con_deuda" value="1" 
                                   id="conDeuda" {{ request('con_deuda') ? 'checked' : '' }}>
                            <label class="form-check-label" for="conDeuda">
                                Solo clientes con deuda pendiente
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="d-flex justify-content-between mt-4">
                <div>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-search me-1"></i>Buscar con Filtros
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg" onclick="guardarBusqueda()">
                        <i class="fas fa-bookmark me-1"></i>Guardar Búsqueda
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-success" onclick="exportarResultados()">
                        <i class="fas fa-file-excel me-1"></i>Exportar
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Resultados -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-0">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0 fw-bold">
                    Resultados de Búsqueda 
                    <span class="badge bg-primary ms-2">{{ $resultados ?? '2,847' }}</span>
                </h6>
            </div>
            <div class="view-toggle">
                <button onclick="cambiarVista('tarjetas')" id="btnTarjetas" class="active">
                    <i class="fas fa-th"></i>
                </button>
                <button onclick="cambiarVista('tabla')" id="btnTabla">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Vista de Tarjetas -->
        <div id="vistaTarjetas">
            <div class="row">
                @for($i = 1; $i <= 6; $i++)
                <div class="col-lg-6 mb-3">
                    <div class="client-card">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="client-avatar me-3">
                                    {{ substr(['H', 'F', 'C', 'L', 'D', 'F'][$i-1], 0, 1) }}
                                </div>
                                <div class="client-info flex-grow-1">
                                    <h6>
                                        @if($i == 1) Hospital Central S.A.
                                        @elseif($i == 2) Farmacia Bienestar
                                        @elseif($i == 3) Clínica San José
                                        @elseif($i == 4) Laboratorio Médico Plus
                                        @elseif($i == 5) Dr. Roberto Silva
                                        @else Farmacia Salud Total @endif
                                    </h6>
                                    <small>
                                        <i class="fas fa-id-card me-1"></i>{{ 20000000000 + $i * 1000000 }}
                                        <i class="fas fa-phone ms-2 me-1"></i>+51 999 888 777
                                    </small>
                                    <div class="mt-2">
                                        <span class="badge {{ $i <= 2 ? 'bg-warning' : ($i <= 4 ? 'bg-success' : 'bg-secondary') }}">
                                            {{ $i <= 2 ? 'VIP' : ($i <= 4 ? 'Premium' : 'Regular') }}
                                        </span>
                                        <span class="badge bg-success ms-1">Activo</span>
                                    </div>
                                </div>
                            </div>
                            <div class="quick-actions">
                                <a href="{{ route('contador.clientes.show', $i) }}" 
                                   class="btn btn-sm btn-outline-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('contador.clientes.editar', $i) }}" 
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                        <div class="client-stats">
                            <div class="client-stat flex-grow-1">
                                <div class="client-stat-label">Compras</div>
                                <div class="client-stat-value">{{ 10 + $i * 5 }}</div>
                            </div>
                            <div class="client-stat flex-grow-1">
                                <div class="client-stat-label">Deuda</div>
                                <div class="client-stat-value text-danger">S/ {{ number_format(1000 * $i, 2) }}</div>
                            </div>
                            <div class="client-stat flex-grow-1">
                                <div class="client-stat-label">Última Compra</div>
                                <div class="client-stat-value">{{ $i }}d</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>

        <!-- Vista de Tabla -->
        <div id="vistaTabla" style="display: none;">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Cliente</th>
                            <th>Documento</th>
                            <th>Contacto</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th class="text-end">Deuda</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 1; $i <= 6; $i++)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="client-avatar me-3" style="width: 40px; height: 40px; font-size: 1rem;">
                                        {{ substr(['H', 'F', 'C', 'L', 'D', 'F'][$i-1], 0, 1) }}
                                    </div>
                                    <strong>
                                        @if($i == 1) Hospital Central S.A.
                                        @elseif($i == 2) Farmacia Bienestar
                                        @elseif($i == 3) Clínica San José
                                        @elseif($i == 4) Laboratorio Médico Plus
                                        @elseif($i == 5) Dr. Roberto Silva
                                        @else Farmacia Salud Total @endif
                                    </strong>
                                </div>
                            </td>
                            <td>{{ 20000000000 + $i * 1000000 }}</td>
                            <td>
                                <small>
                                    <i class="fas fa-phone me-1"></i>+51 999 888 777<br>
                                    <i class="fas fa-envelope me-1"></i>cliente{{ $i }}@email.com
                                </small>
                            </td>
                            <td>
                                <span class="badge {{ $i <= 2 ? 'bg-warning' : ($i <= 4 ? 'bg-success' : 'bg-secondary') }}">
                                    {{ $i <= 2 ? 'VIP' : ($i <= 4 ? 'Premium' : 'Regular') }}
                                </span>
                            </td>
                            <td><span class="badge bg-success">Activo</span></td>
                            <td class="text-end fw-bold text-danger">S/ {{ number_format(1000 * $i, 2) }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('contador.clientes.show', $i) }}" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('contador.clientes.editar', $i) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación -->
        <div class="mt-4">
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <span class="page-link">Anterior</span>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Búsquedas Guardadas -->
<div class="row mt-4">
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-bookmark text-primary me-2"></i>Búsquedas Guardadas</h5>
    </div>
    <div class="col-md-4">
        <div class="saved-search-card" onclick="cargarBusqueda('vip-lima')">
            <h6><i class="fas fa-star me-2"></i>Clientes VIP - Lima</h6>
            <p class="mb-0 small opacity-75">Tipo: VIP | Zona: Lima | Estado: Activo</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="saved-search-card" onclick="cargarBusqueda('deudores')">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Clientes con Deuda</h6>
            <p class="mb-0 small opacity-75">Filtro: Con deuda pendiente > S/ 5,000</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="saved-search-card" onclick="cargarBusqueda('nuevos')">
            <h6><i class="fas fa-user-plus me-2"></i>Clientes Nuevos</h6>
            <p class="mb-0 small opacity-75">Registrados en los últimos 30 días</p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Toggle de filtros avanzados
document.getElementById('busquedaAvanzada').addEventListener('change', function() {
    const filtros = document.getElementById('filtrosAvanzados');
    filtros.style.display = this.checked ? 'block' : 'none';
});

// Cambiar vista
function cambiarVista(vista) {
    const vistaTarjetas = document.getElementById('vistaTarjetas');
    const vistaTabla = document.getElementById('vistaTabla');
    const btnTarjetas = document.getElementById('btnTarjetas');
    const btnTabla = document.getElementById('btnTabla');
    
    if (vista === 'tarjetas') {
        vistaTarjetas.style.display = 'block';
        vistaTabla.style.display = 'none';
        btnTarjetas.classList.add('active');
        btnTabla.classList.remove('active');
    } else {
        vistaTarjetas.style.display = 'none';
        vistaTabla.style.display = 'block';
        btnTarjetas.classList.remove('active');
        btnTabla.classList.add('active');
    }
}

function limpiarBusqueda() {
    document.getElementById('busquedaForm').reset();
    window.location.href = '{{ route("contador.clientes.buscar") }}';
}

function guardarBusqueda() {
    Swal.fire({
        title: 'Guardar Búsqueda',
        input: 'text',
        inputLabel: 'Nombre de la búsqueda',
        inputPlaceholder: 'Ej: Clientes VIP activos',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar un nombre';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: '¡Guardada!',
                text: `La búsqueda "${result.value}" ha sido guardada.`,
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

function exportarResultados() {
    Swal.fire({
        title: 'Exportar Resultados',
        text: '¿En qué formato desea exportar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Excel',
        cancelButtonText: 'PDF',
        showDenyButton: true,
        denyButtonText: 'CSV'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('¡Exportado!', 'Archivo Excel generado.', 'success');
        } else if (result.isDenied) {
            Swal.fire('¡Exportado!', 'Archivo CSV generado.', 'success');
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire('¡Exportado!', 'Archivo PDF generado.', 'success');
        }
    });
}

function cargarBusqueda(nombre) {
    Swal.fire({
        title: 'Cargando búsqueda...',
        text: `Aplicando filtros guardados: ${nombre}`,
        timer: 1500,
        timerProgressBar: true,
        showConfirmButton: false
    });
}

// Animación de entrada
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.client-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endpush