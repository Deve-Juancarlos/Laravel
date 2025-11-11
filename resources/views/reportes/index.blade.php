@extends('layouts.app') 

@section('title', 'Reporte de Antigüedad de Cuentas por Cobrar')

@push('styles')
    <link href="{{ asset('css/contabilidad/reportes/aging.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid mt-4">

   <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white p-0">
            <ul class="nav nav-tabs nav-fill" id="reportesTab" role="tablist">
                
                <li class="nav-item" role="presentation">
                    <a class="nav-link fs-5 fw-bold p-3 {{ request()->routeIs('contador.reportes.index') ? 'active' : '' }}"
                    id="aging-tab"
                    href="{{ route('contador.reportes.index') }}">
                        <i class="fas fa-hand-holding-usd me-1"></i> Cuentas por Cobrar
                    </a>
                </li>
                
                <li class="nav-item" role="presentation">
                    <a class="nav-link fs-5 fw-bold p-3 {{ request()->routeIs('contador.reportes.ventas.rentabilidad') ? 'active' : '' }}"
                    id="rentabilidad-tab"
                    href="{{ route('contador.reportes.ventas.rentabilidad') }}">
                        <i class="fas fa-chart-line me-1"></i> Rentabilidad (Ventas)
                    </a>
                </li>
                
                <li class="nav-item" role="presentation">
                    <a class="nav-link fs-5 fw-bold p-3 {{ request()->routeIs('contador.reportes.inventario.sugerencias') ? 'active' : '' }}"
                    id="sugerencias-tab"
                    href="{{ route('contador.reportes.inventario.sugerencias') }}">
                        <i class="fas fa-dolly-flatbed me-1"></i> Sugerencias (Compra)
                    </a>
                </li>
                
                <li class="nav-item" role="presentation">
                    <a class="nav-link fs-5 fw-bold p-3 {{ request()->routeIs('contador.reportes.inventario.vencimientos') ? 'active' : '' }}"
                    id="vencimientos-tab"
                    href="{{ route('contador.reportes.inventario.vencimientos') }}">
                        <i class="fas fa-calendar-times me-1"></i> Productos por Vencer
                    </a>
                </li>

            </ul>
        </div>
    </div>

    <div class="tab-content">
        @yield('report-content')
    </div>

    <!-- Tarjetas de Totales -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body total-general">
                    <h5 class="card-title text-primary">DEUDA TOTAL</h5>
                    <p class="display-6 fw-bold mb-0">S/ {{ number_format($totales['Total'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body bucket-vigente">
                    <h5 class="card-title">Vigente</h5>
                    <p class="h5 fw-bold mb-0">S/ {{ number_format($totales['Vigente'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body bucket-30">
                    <h5 class="card-title">1-30 Días</h5>
                    <p class="h5 fw-bold mb-0">S/ {{ number_format($totales['Rango1_30'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body bucket-60">
                    <h5 class="card-title">31-60 Días</h5>
                    <p class="h5 fw-bold mb-0">S/ {{ number_format($totales['Rango31_60'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body bucket-90mas">
                    <h5 class="card-title text-danger">Vencido (+60 Días)</h5>
                    <p class="h5 fw-bold mb-0">S/ {{ number_format($totales['Rango61_90'] + $totales['Rango90Mas'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Principal del Reporte -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h4 class="mb-0">
                <i class="fas fa-hand-holding-usd me-2 text-success"></i>
                Antigüedad de Cuentas por Cobrar (Aging de Cartera)
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Cliente</th>
                            <th class="text-end">Deuda Total</th>
                            <th class="text-end bucket-vigente">Vigente</th>
                            <th class="text-end bucket-30">1-30 Días</th>
                            <th class="text-end bucket-60">31-60 Días</th>
                            <th class="text-end bucket-90">61-90 Días</th>
                            <th class="text-end bucket-90mas">90+ Días</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reporte as $cliente)
                            <tr>
                                <td>
                                    <strong>{{ $cliente->Razon }}</strong>
                                    <br><small class="text-muted">{{ $cliente->ClienteEmail ?? 'Sin Email' }}</small>
                                </td>
                                <td class="text-end fw-bold fs-5">S/ {{ number_format($cliente->DeudaTotal, 2) }}</td>
                                <td class="text-end bucket-vigente">S/ {{ number_format($cliente->Vigente, 2) }}</td>
                                <td class="text-end bucket-30">S/ {{ number_format($cliente->Rango1_30, 2) }}</td>
                                <td class="text-end bucket-60">S/ {{ number_format($cliente->Rango31_60, 2) }}</td>
                                <td class="text-end bucket-90">S/ {{ number_format($cliente->Rango61_90, 2) }}</td>
                                <td class="text-end bucket-90mas">S/ {{ number_format($cliente->Rango90Mas, 2) }}</td>
                                <td class="text-center">
                                    <button class="btn btn-success btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEnviarRecordatorio"
                                            data-cliente-id="{{ $cliente->CodClie }}"
                                            data-cliente-nombre="{{ $cliente->Razon }}"
                                            data-cliente-email="{{ $cliente->ClienteEmail }}"
                                            data-cliente-deuda="{{ $cliente->DeudaTotal }}">
                                        <i class="fas fa-envelope me-1"></i> Enviar Recordatorio
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center p-4">
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    ¡Felicidades! No hay cuentas por cobrar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- ▼▼▼ EL MODAL PARA ENVIAR EL CORREO ▼▼▼ -->
<div class="modal fade" id="modalEnviarRecordatorio" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Enviar Recordatorio de Cobranza</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- El 'action' del formulario se llenará con JavaScript -->
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