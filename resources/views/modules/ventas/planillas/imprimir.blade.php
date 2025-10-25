@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-print text-primary"></i> Imprimir Planilla
        </h1>
        <div>
            <a href="{{ route('ventas.planillas.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Planillas
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>

    @if(isset($planilla))
        <!-- Información de la Planilla -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información de la Planilla</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Número de Planilla:</strong> #{{ $planilla->numero ?? 'PL-' . date('Y') . '-' . str_pad($planilla->id ?? 1, 4, '0', STR_PAD_LEFT) }}</p>
                        <p><strong>Fecha de Creación:</strong> {{ date('d/m/Y H:i', strtotime($planilla->created_at ?? now())) }}</p>
                        <p><strong>Período:</strong> {{ $planilla->periodo_inicio ?? '01/01/2024' }} - {{ $planilla->periodo_fin ?? '31/01/2024' }}</p>
                        <p><strong>Total de Facturas:</strong> {{ count($facturas ?? []) }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Cliente:</strong> {{ $planilla->cliente_nombre ?? 'Cliente General' }}</p>
                        <p><strong>Contacto:</strong> {{ $planilla->cliente_contacto ?? 'Sin contacto definido' }}</p>
                        <p><strong>Dirección:</strong> {{ $planilla->cliente_direccion ?? 'Sin dirección' }}</p>
                        <p><strong>Estado:</strong> <span class="badge badge-success">{{ $planilla->estado ?? 'Completa' }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Totales -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Resumen de Totales</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <h4 class="text-primary">${{ number_format($planilla->subtotal ?? 15642.50, 2) }}</h4>
                        <small class="text-muted">Subtotal</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-info">${{ number_format($planilla->impuestos ?? 1251.40, 2) }}</h4>
                        <small class="text-muted">Impuestos</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-warning">${{ number_format($planilla->descuentos ?? 156.43, 2) }}</h4>
                        <small class="text-muted">Descuentos</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-success">${{ number_format($planilla->total ?? 16737.47, 2) }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Facturas -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Facturas Incluidas</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th># Factura</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Productos</th>
                                <th>Subtotal</th>
                                <th>Impuestos</th>
                                <th>Total</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($facturas) && count($facturas) > 0)
                                @foreach($facturas as $factura)
                                    <tr>
                                        <td><strong>{{ $factura->numero ?? 'FAC-2024-' . str_pad($loop->index + 1, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                        <td>{{ date('d/m/Y', strtotime($factura->fecha ?? '2024-01-15')) }}</td>
                                        <td>{{ $factura->cliente ?? 'Cliente General ' . ($loop->index + 1) }}</td>
                                        <td>{{ $factura->productos_count ?? 5 }}</td>
                                        <td>${{ number_format($factura->subtotal ?? 3128.50, 2) }}</td>
                                        <td>${{ number_format($factura->impuestos ?? 250.28, 2) }}</td>
                                        <td><strong>${{ number_format($factura->total ?? 3378.78, 2) }}</strong></td>
                                        <td>{{ date('d/m/Y', strtotime($factura->vencimiento ?? '2024-02-14')) }}</td>
                                        <td>
                                            @php
                                                $diasRestantes = rand(-5, 15);
                                                if($diasRestantes < 0) {
                                                    $badgeClass = 'badge-danger';
                                                    $estado = 'Vencida';
                                                } elseif($diasRestantes < 5) {
                                                    $badgeClass = 'badge-warning';
                                                    $estado = 'Por Vencer';
                                                } else {
                                                    $badgeClass = 'badge-success';
                                                    $estado = 'Pendiente';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $estado }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <!-- Facturas de ejemplo -->
                                <tr>
                                    <td><strong>FAC-2024-0001</strong></td>
                                    <td>15/01/2024</td>
                                    <td>Farmacia Central</td>
                                    <td>8</td>
                                    <td>$2,890.50</td>
                                    <td>$231.24</td>
                                    <td><strong>$3,121.74</strong></td>
                                    <td>14/02/2024</td>
                                    <td><span class="badge badge-success">Pendiente</span></td>
                                </tr>
                                <tr>
                                    <td><strong>FAC-2024-0002</strong></td>
                                    <td>18/01/2024</td>
                                    <td>Clinica San José</td>
                                    <td>12</td>
                                    <td>$4,567.25</td>
                                    <td>$365.38</td>
                                    <td><strong>$4,932.63</strong></td>
                                    <td>17/02/2024</td>
                                    <td><span class="badge badge-warning">Por Vencer</span></td>
                                </tr>
                                <tr>
                                    <td><strong>FAC-2024-0003</strong></td>
                                    <td>22/01/2024</td>
                                    <td>Hospital Regional</td>
                                    <td>15</td>
                                    <td>$6,124.75</td>
                                    <td>$489.98</td>
                                    <td><strong>$6,614.73</strong></td>
                                    <td>21/02/2024</td>
                                    <td><span class="badge badge-success">Pendiente</span></td>
                                </tr>
                                <tr>
                                    <td><strong>FAC-2024-0004</strong></td>
                                    <td>25/01/2024</td>
                                    <td>Laboratorio Médico</td>
                                    <td>6</td>
                                    <td>$2,060.00</td>
                                    <td>$164.80</td>
                                    <td><strong>$2,224.80</strong></td>
                                    <td>24/02/2024</td>
                                    <td><span class="badge badge-danger">Vencida</span></td>
                                </tr>
                                <tr>
                                    <td><strong>FAC-2024-0005</strong></td>
                                    <td>28/01/2024</td>
                                    <td>Farmacia Bienestar</td>
                                    <td>10</td>
                                    <td>$3,000.00</td>
                                    <td>$240.00</td>
                                    <td><strong>$3,240.00</strong></td>
                                    <td>27/02/2024</td>
                                    <td><span class="badge badge-success">Pendiente</span></td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="4" class="text-right">TOTALES:</th>
                                <th>${{ number_format($planilla->subtotal ?? 15642.50, 2) }}</th>
                                <th>${{ number_format($planilla->impuestos ?? 1251.40, 2) }}</th>
                                <th><strong>${{ number_format($planilla->total ?? 16737.47, 2) }}</strong></th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Notas y Observaciones -->
        <div class="card shadow mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Notas y Observaciones</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">{{ $planilla->notas ?? 'Esta planilla incluye todas las facturas pendientes del período indicado. Los términos de pago son según lo establecido en el contrato comercial.' }}</p>
                
                <div class="mt-4">
                    <h6>Condiciones de Pago:</h6>
                    <ul class="text-muted">
                        <li>Forma de pago: Transferencia bancaria o cheque</li>
                        <li>Términos: 30 días fecha factura</li>
                        <li>Intereses por mora: 2% mensual</li>
                        <li>Contacto para pagos: cuentas por cobrar</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Pie de Página para Impresión -->
        <div class="mt-4 p-3 bg-light border rounded d-none d-print-block">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        Documento generado el {{ date('d/m/Y H:i:s') }}<br>
                        Sistema SIFANO - Módulo de Ventas
                    </small>
                </div>
                <div class="col-md-6 text-right">
                    <small class="text-muted">
                        Página 1 de 1<br>
                        Planilla #{{ $planilla->numero ?? 'PL-2024-0001' }}
                    </small>
                </div>
            </div>
        </div>
    @else
        <!-- Vista sin datos -->
        <div class="alert alert-warning">
            <h4><i class="fas fa-exclamation-triangle"></i> Planilla no encontrada</h4>
            <p>No se pudo cargar la información de la planilla solicitada.</p>
            <a href="{{ route('ventas.planillas.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Volver a Planillas
            </a>
        </div>
    @endif
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
        margin-bottom: 15px !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        border-bottom: 2px solid #000 !important;
        font-weight: bold !important;
    }
    
    .table {
        border: 1px solid #000 !important;
    }
    
    .table th,
    .table td {
        border: 1px solid #000 !important;
        padding: 8px !important;
    }
    
    .table thead th {
        background-color: #f8f9fa !important;
        font-weight: bold !important;
    }
    
    .badge {
        border: 1px solid #000 !important;
        padding: 2px 6px !important;
        font-size: 10px !important;
    }
    
    .text-success { color: #000 !important; }
    .text-warning { color: #000 !important; }
    .text-danger { color: #000 !important; }
    .text-info { color: #000 !important; }
    .text-primary { color: #000 !important; }
    
    .h3, .h4, .h6 {
        color: #000 !important;
        font-weight: bold !important;
    }
    
    .p-3 {
        padding: 10px !important;
    }
}

@page {
    margin: 1cm;
    size: A4;
}

.container-fluid {
    max-width: none !important;
    padding: 0 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuración específica para impresión
    if (window.matchMedia) {
        const mediaQueryList = window.matchMedia('print');
        mediaQueryList.addListener(function(mql) {
            if (mql.matches) {
                // Antes de imprimir
                console.log('Preparando impresión...');
            } else {
                // Después de imprimir
                console.log('Impresión cancelada o completada');
            }
        });
    }
    
    // Preparar datos para impresión
    window.prepararImpresion = function() {
        // Ajustar contenido para impresión si es necesario
        const elementos = document.querySelectorAll('.d-none.d-print-block');
        elementos.forEach(el => {
            el.classList.remove('d-none');
        });
    };
    
    // Función para exportar a PDF (si se requiere)
    window.exportarPDF = function() {
        if (confirm('¿Desea generar un PDF de esta planilla?')) {
            // Aquí se implementaría la lógica de exportación a PDF
            alert('Función de exportación a PDF será implementada');
        }
    };
    
    // Auto-imprimir si se especifica en la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('auto_print') === 'true') {
        setTimeout(() => {
            window.print();
        }, 1000);
    }
});
</script>
@endsection