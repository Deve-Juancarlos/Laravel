@extends('layouts.app') 

@section('title', 'Reporte de Antigüedad de Cuentas por Cobrar')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Reportes</li>
@endsection


@push('styles')
    <link href="{{ asset('css/contabilidad/reportes/aging.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="reportes-container">    
    <div class="container-fluid">
        
        {{-- =========== NAVEGACIÓN DE REPORTES =========== --}}
        <nav class="nav nav-tabs reportes-nav-wrapper mb-4">
            <a href="{{ route('contador.reportes.index') }}" 
               class="nav-item {{ request()->routeIs('contador.reportes.index') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-usd me-2"></i>
                Cuentas por Cobrar
            </a>
            
            <a href="{{ route('contador.reportes.ventas.rentabilidad') }}" 
               class="nav-item {{ request()->routeIs('contador.reportes.ventas.rentabilidad') ? 'active' : '' }}">
                <i class="fas fa-chart-line me-2"></i>
                Rentabilidad (Ventas)
            </a>
            
            <a href="{{ route('contador.reportes.inventario.sugerencias') }}" 
               class="nav-item {{ request()->routeIs('contador.reportes.inventario.sugerencias') ? 'active' : '' }}">
                <i class="fas fa-dolly-flatbed me-2"></i>
                Sugerencias (Compra)
            </a>
            
            <a href="{{ route('contador.reportes.inventario.vencimientos') }}" 
               class="nav-item {{ request()->routeIs('contador.reportes.inventario.vencimientos') ? 'active' : '' }}">
                <i class="fas fa-calendar-times me-2"></i>
                Productos por Vencer
            </a>
        </nav>
        {{-- =========== FIN NAVEGACIÓN =========== --}}

        {{-- =========== CONTENIDO DEL REPORTE =========== --}}
        <div class="tab-content">
            @yield('report-content')
        </div>

        {{-- =========== SECCIÓN DE RESUMEN =========== --}}
        <div class="resumen-section">
            <div class="totales-grid">
                <div class="total-card card-principal shadow-sm">
                    <div class="card-label">Deuda Total</div>
                    <div class="card-amount">S/ {{ number_format($totales['Total'], 2) }}</div>
                </div>
                
                <div class="total-card card-vigente shadow-sm">
                    <div class="card-label">Vigente</div>
                    <div class="card-amount">S/ {{ number_format($totales['Vigente'], 2) }}</div>
                </div>
                
                <div class="total-card card-rango-1 shadow-sm">
                    <div class="card-label">1-30 Días</div>
                    <div class="card-amount">S/ {{ number_format($totales['Rango1_30'], 2) }}</div>
                </div>
                
                <div class="total-card card-rango-2 shadow-sm">
                    <div class="card-label">31-60 Días</div>
                    <div class="card-amount">S/ {{ number_format($totales['Rango31_60'], 2) }}</div>
                </div>
                
                <div class="total-card card-vencido shadow-sm">
                    <div class="card-label">Vencido (+60 Días)</div>
                    <div class="card-amount">S/ {{ number_format($totales['Rango61_90'] + $totales['Rango90Mas'], 2) }}</div>
                </div>
            </div>
        </div>

        {{-- =========== TABLA DE DETALLE =========== --}}
        <div class="card shadow-sm tabla-section">
            <div class="tabla-header">
                <h2 class="tabla-title mb-0">
                    <i class="fas fa-hand-holding-usd"></i>
                    Antigüedad de Cuentas por Cobrar (Aging de Cartera)
                </h2>
            </div>
            
            <div class="card-body p-0">
                <div class="tabla-wrapper">
                    <div class="table-responsive">
                        <table class="table tabla-aging table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="col-cliente">
                                        <i class="fas fa-user me-2"></i>
                                        Cliente
                                    </th>
                                    <th class="col-numero">
                                        <i class="fas fa-dollar-sign me-2"></i>
                                        Deuda Total
                                    </th>
                                    <th class="col-numero col-vigente">
                                        <i class="fas fa-check me-2"></i>
                                        Vigente
                                    </th>
                                    <th class="col-numero col-rango-1">
                                        <i class="fas fa-calendar-day me-2"></i>
                                        1-30 Días
                                    </th>
                                    <th class="col-numero col-rango-2">
                                        <i class="fas fa-calendar-week me-2"></i>
                                        31-60 Días
                                    </th>
                                    <th class="col-numero col-rango-3">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        61-90 Días
                                    </th>
                                    <th class="col-numero col-rango-4">
                                        <i class="fas fa-calendar-times me-2"></i>
                                        90+ Días
                                    </th>
                                    <th class="col-accion">
                                        <i class="fas fa-cog me-2"></i>
                                        Acción
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reporte as $cliente)
                                    <tr>
                                        <td class="col-cliente">
                                            <div class="cliente-info">
                                                <div class="cliente-nombre">{{ $cliente->Razon }}</div>
                                                <div class="cliente-email">{{ $cliente->ClienteEmail ?? 'Sin Email' }}</div>
                                            </div>
                                        </td>
                                        <td class="col-numero col-total">S/ {{ number_format($cliente->DeudaTotal, 2) }}</td>
                                        <td class="col-numero col-vigente">S/ {{ number_format($cliente->Vigente, 2) }}</td>
                                        <td class="col-numero col-rango-1">S/ {{ number_format($cliente->Rango1_30, 2) }}</td>
                                        <td class="col-numero col-rango-2">S/ {{ number_format($cliente->Rango31_60, 2) }}</td>
                                        <td class="col-numero col-rango-3">S/ {{ number_format($cliente->Rango61_90, 2) }}</td>
                                        <td class="col-numero col-rango-4">S/ {{ number_format($cliente->Rango90Mas, 2) }}</td>
                                        <td class="col-accion">
                                            <button class="btn-recordatorio" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEnviarRecordatorio"
                                                    data-cliente-id="{{ $cliente->CodClie }}"
                                                    data-cliente-nombre="{{ $cliente->Razon }}"
                                                    data-cliente-email="{{ $cliente->ClienteEmail }}"
                                                    data-cliente-deuda="{{ $cliente->DeudaTotal }}">
                                                <i class="fas fa-envelope"></i>
                                                <span>Enviar Recordatorio</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="tabla-vacia">
                                            <i class="fas fa-check-circle"></i>
                                            <span>¡Felicidades! No hay cuentas por cobrar.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- =========== MODAL DE ENVÍO DE RECORDATORIO =========== --}}
    <div class="modal fade" id="modalEnviarRecordatorio" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Enviar Recordatorio de Cobranza</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form id="formRecordatorio" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Se enviará un recordatorio con su estado de cuenta adjunto al cliente:
                            <strong id="modalClienteNombre" class="d-block fs-5 text-primary"></strong>
                        </p>

                        <div class="mb-3">
                            <label for="email_destino" class="form-label fw-bold">Para:</label>
                            <input type="email" class="form-control" id="modalEmailDestino" name="email_destino" required>
                        </div>

                        <div class="mb-3">
                            <label for="email_asunto" class="form-label fw-bold">Asunto:</label>
                            <input type="text" class="form-control" id="modalEmailAsunto" name="email_asunto" required>
                        </div>

                        <div class="mb-3">
                            <label for="email_cuerpo" class="form-label fw-bold">Mensaje:</label>
                            <textarea class="form-control" id="modalEmailCuerpo" name="email_cuerpo" rows="6" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane me-1"></i> Enviar Correo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Script para manejar el modal de envío de correo
    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('modalEnviarRecordatorio');
        
        modal.addEventListener('show.bs.modal', function (event) {
            // Botón que disparó el modal
            var button = event.relatedTarget;

            // Extraer datos de los atributos 'data-*'
            var clienteId = button.getAttribute('data-cliente-id');
            var nombre = button.getAttribute('data-cliente-nombre');
            var email = button.getAttribute('data-cliente-email');
            var deuda = parseFloat(button.getAttribute('data-cliente-deuda')).toFixed(2);

            // Construir la URL de la acción del formulario
            var actionUrl = "{{ route('contador.reportes.index') }}/enviar-recordatorio-cobranza/" + clienteId;
            
            // Construir los textos por defecto
            var asuntoDefecto = "Recordatorio de Pagos Pendientes - " + nombre;
            var cuerpoDefecto = "Estimado(a) " + nombre + ",\n\n" +
                              "Le escribimos cordialmente para recordarle sobre sus facturas pendientes con nosotros, que a la fecha suman un total de S/ " + deuda + ".\n\n" +
                              "Adjuntamos a este correo su Estado de Cuenta detallado para su revisión.\n\n" +
                              "Agradecemos su pronta atención a este asunto.\n\n" +
                              "Atentamente,\n" +
                              "Equipo de Cobranzas\n" +
                              "SEDIMCORP SAC"; // <-- Poner tu nombre de empresa

            // Actualizar el contenido del modal
            modal.querySelector('#formRecordatorio').setAttribute('action', actionUrl);
            modal.querySelector('#modalClienteNombre').textContent = nombre;
            modal.querySelector('#modalEmailDestino').value = email;
            modal.querySelector('#modalEmailAsunto').value = asuntoDefecto;
            modal.querySelector('#modalEmailCuerpo').value = cuerpoDefecto;
        });
    });
</script>
@endpush