@extends('layouts.app')

@section('title', 'Detalle del Cliente')
@section('page-title', 'Información del Cliente')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('contador.clientes.index') }}" class="text-decoration-none">
            <i class="fas fa-users"></i> Clientes
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $cliente->Razon }}</li>
@endsection

@push('styles')
<style>
    .info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    .info-card h3 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .info-card .badge {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
    }
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        height: 100%;
        transition: transform 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-5px);
    }
    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-bottom: 1rem;
    }
    .stats-label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }
    .stats-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2c3e50;
    }
    .contact-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 0.75rem;
    }
    .contact-item i {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 1rem;
        font-size: 1rem;
    }
    .contact-item .label {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
    }
    .contact-item .value {
        font-size: 1rem;
        color: #2c3e50;
        font-weight: 500;
    }
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .purchase-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        transition: background 0.2s ease;
    }
    .purchase-item:hover {
        background: #f8f9fa;
    }
    .purchase-item:last-child {
        border-bottom: none;
    }
    .credit-progress {
        height: 12px;
        border-radius: 6px;
        overflow: hidden;
        background: #e9ecef;
    }
    .credit-progress-bar {
        height: 100%;
        border-radius: 6px;
        transition: width 0.6s ease;
    }
</style>
@endpush

@section('content')

<!-- Tarjeta de Información Principal -->
<div class="info-card">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3>{{ $cliente->Razon }}</h3>
            <p class="mb-2"><i class="fas fa-id-card me-2"></i>{{ $cliente->Documento }}</p>
            <div>
                <span class="badge bg-light text-dark">
                    <i class="fas fa-calendar me-1"></i>
                    Cliente desde {{ \Carbon\Carbon::parse($cliente->Fecha)->format('d/m/Y') }}
                </span>
                <span class="badge {{ $cliente->Activo ? 'bg-success' : 'bg-danger' }} ms-2">
                    <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                    {{ $cliente->Activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('contador.clientes.editar', $cliente->Codclie) }}" 
               class="btn btn-light action-btn">
                <i class="fas fa-edit"></i> Editar
            </a>
            @if($cliente->Activo)
            <button type="button" class="btn btn-danger action-btn" 
                    onclick="confirmarDesactivacion()">
                <i class="fas fa-ban"></i> Desactivar
            </button>
            @endif
        </div>
    </div>
</div>

<!-- Estadísticas del Cliente -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-primary">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stats-label">Total Compras</div>
            <div class="stats-value">{{ $estadisticas['cantidad_compras'] }}</div>
            <small class="text-muted">Operaciones realizadas</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-success">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stats-label">Ticket Promedio</div>
            <div class="stats-value">S/ {{ number_format($estadisticas['ticket_promedio'], 2) }}</div>
            <small class="text-muted">Por compra</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-info">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stats-label">Última Compra</div>
            <div class="stats-value" style="font-size: 1.25rem;">
                {{ $estadisticas['fecha_ultima_compra'] ? \Carbon\Carbon::parse($estadisticas['fecha_ultima_compra'])->format('d/m/Y') : 'N/A' }}
            </div>
            <small class="text-muted">{{ $estadisticas['dias_desde_ultima_compra'] ?? 'N/A' }} días atrás</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stats-label">Deuda Total</div>
            <div class="stats-value text-danger">S/ {{ number_format($credito['saldo_actual'], 2) }}</div>
            <small class="text-muted">Saldo pendiente</small>
        </div>
    </div>
</div>

<div class="row">
    <!-- Información de Contacto y Crédito -->
    <div class="col-lg-4">
        <!-- Contacto -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0 fw-bold"><i class="fas fa-address-card text-primary me-2"></i>Información de Contacto</h6>
            </div>
            <div class="card-body">
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="flex-grow-1">
                        <div class="label">Dirección</div>
                        <div class="value">{{ $cliente->Direccion ?: 'No especificada' }}</div>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <div class="flex-grow-1">
                        <div class="label">Teléfono</div>
                        <div class="value">{{ $cliente->Telefono1 ?: 'No especificado' }}</div>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div class="flex-grow-1">
                        <div class="label">Email</div>
                        <div class="value">{{ $cliente->Email ?: 'No especificado' }}</div>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-user-tie"></i>
                    <div class="flex-grow-1">
                        <div class="label">Vendedor Asignado</div>
                        <div class="value">{{ $cliente->Vendedor ?: 'No asignado' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado de Crédito -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0 fw-bold"><i class="fas fa-credit-card text-success me-2"></i>Estado de Crédito</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Límite de Crédito</span>
                        <span class="fw-bold">S/ {{ number_format($credito['limite_credito'], 2) }}</span>
                    </div>
                    <div class="credit-progress">
                        <div class="credit-progress-bar {{ $credito['porcentaje_utilizado'] > 80 ? 'bg-danger' : ($credito['porcentaje_utilizado'] > 60 ? 'bg-warning' : 'bg-success') }}" 
                             style="width: {{ $credito['porcentaje_utilizado'] }}%;">
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">{{ number_format($credito['porcentaje_utilizado'], 1) }}% Utilizado</small>
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="text-muted small">Disponible</div>
                        <div class="h5 mb-0 text-success fw-bold">S/ {{ number_format($credito['credito_disponible'], 2) }}</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="text-muted small">Utilizado</div>
                        <div class="h5 mb-0 text-danger fw-bold">S/ {{ number_format($credito['saldo_actual'], 2) }}</div>
                    </div>
                </div>

                <div class="alert alert-{{ $credito['categoria_riesgo'] == 'ALTO' ? 'danger' : ($credito['categoria_riesgo'] == 'MEDIO' ? 'warning' : 'success') }} mb-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-{{ $credito['categoria_riesgo'] == 'ALTO' ? 'exclamation-circle' : ($credito['categoria_riesgo'] == 'MEDIO' ? 'exclamation-triangle' : 'check-circle') }} fa-2x me-3"></i>
                        <div>
                            <div class="fw-bold">Riesgo: {{ $credito['categoria_riesgo'] }}</div>
                            <small>Días de crédito: {{ $credito['dias_credito'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Compras -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-history text-info me-2"></i>Historial de Compras</h6>
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-export me-1"></i>Exportar
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div style="max-height: 600px; overflow-y: auto;">
                    @forelse($compras as $compra)
                    <div class="purchase-item">
                        <div>
                            <div class="fw-bold text-primary">
                                <i class="fas fa-file-invoice me-2"></i>{{ $compra->Numero }}
                            </div>
                            <small class="text-muted">{{ $compra->Producto }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">S/ {{ number_format($compra->Total, 2) }}</div>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($compra->Fecha)->format('d/m/Y') }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center p-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay compras registradas</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="modalDesactivar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar Desactivación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-user-slash fa-4x text-danger mb-3"></i>
                    <h5>¿Está seguro de desactivar este cliente?</h5>
                    <p class="text-muted">Esta acción impedirá realizar nuevas operaciones con <strong>{{ $cliente->Razon }}</strong></p>
                </div>
                @if($credito['saldo_actual'] > 0)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Advertencia:</strong> El cliente tiene una deuda pendiente de 
                    <strong>S/ {{ number_format($credito['saldo_actual'], 2) }}</strong>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <form action="{{ route('contador.clientes.update', $cliente->Codclie) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="Activo" value="0">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-1"></i>Sí, Desactivar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmarDesactivacion() {
    const modal = new bootstrap.Modal(document.getElementById('modalDesactivar'));
    modal.show();
}

// Animación de las estadísticas al cargar
document.addEventListener('DOMContentLoaded', function() {
    const statsCards = document.querySelectorAll('.stats-card');
    statsCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.5s ease';
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
});
</script>
@endpush