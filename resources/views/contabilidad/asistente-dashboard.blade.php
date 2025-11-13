@extends('layouts.app') {{-- Asumo que usas tu layout principal --}}

@section('title', 'Dashboard de Operaciones Contables')

{{-- Puedes agregar estilos CSS específicos si lo deseas --}}
@push('styles')
    <style>
        .kpi-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 20px; display: flex; align-items: center; }
        .kpi-icon { font-size: 24px; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-right: 15px; }
        .kpi-icon.primary { background: #e6f3ff; color: #007bff; }
        .kpi-icon.success { background: #e6f9f0; color: #28a745; }
        .kpi-icon.warning { background: #fff9e6; color: #ffc107; }
        .kpi-icon.danger { background: #ffeeee; color: #dc3545; }
        .kpi-content .kpi-label { font-size: 14px; color: #6c757d; }
        .kpi-content .kpi-value { font-size: 28px; font-weight: 700; color: #343a40; }
        .card-header h6 { font-weight: 600; }
        .table-responsive { max-height: 400px; overflow-y: auto; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .status-badge-success { background: #e6f9f0; color: #28a745; }
        .status-badge-warning { background: #fff9e6; color: #ffc107; }
        .status-badge-danger { background: #ffeeee; color: #dc3545; }
        .status-badge-secondary { background: #f8f9fa; color: #6c757d; }
    </style>
@endpush

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Dashboard de Operaciones Contables</h1>

    {{-- Notificación de Error si la carga de datos falló --}}
    @if(isset($errorDashboard))
        <div class="alert alert-danger">
            <strong><i class="fas fa-server me-2"></i> Error de Carga:</strong> {{ $errorDashboard }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon primary">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Asientos del Día (Hoy)</div>
                    <div class="kpi-value">{{ $kpisOperativos['asientosHoy'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon {{ $kpisOperativos['asientosPendientes'] > 0 ? 'warning' : 'success' }}">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Asientos Pendientes de Revisión</div>
                    <div class="kpi-value">{{ $kpisOperativos['asientosPendientes'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon {{ $kpisOperativos['asientosDescuadrados'] > 0 ? 'danger' : 'success' }}">
                    <i class="fas fa-balance-scale-right"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Asientos Descuadrados</div>
                    <div class="kpi-value">{{ $kpisOperativos['asientosDescuadrados'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0"><i class="fas fa-link me-2"></i>Integridad de Módulos (Últ. 90 días)</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-receipt me-2 text-primary"></i>
                                Ventas (Fac/Bol) sin Asiento Contable
                            </div>
                            @if($integridadModulos['ventasSinAsiento'] > 0)
                                <span class="badge bg-danger badge-pill p-2">{{ $integridadModulos['ventasSinAsiento'] }}</span>
                            @else
                                <span class="badge bg-success badge-pill p-2"><i class="fas fa-check"></i></span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-truck-loading me-2 text-warning"></i>
                                Compras (Aprobadas) sin Asiento Contable
                            </div>
                            @if($integridadModulos['comprasSinAsiento'] > 0)
                                <span class="badge bg-danger badge-pill p-2">{{ $integridadModulos['comprasSinAsiento'] }}</span>
                            @else
                                <span class="badge bg-success badge-pill p-2"><i class="fas fa-check"></i></span>
                            @endif
                        </li>
                    </ul>
                </div>
                <div class="card-footer text-end">
                    <a href="#" class="btn btn-sm btn-primary">Revisar Inconsistencias</a>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0"><i class="fas fa-university me-2"></i>Estado de Conciliación Bancaria</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Movimientos pendientes por conciliar
                            @if($estadoBancos['movimientosSinConciliar'] > 0)
                                <span class="badge bg-warning badge-pill p-2">{{ $estadoBancos['movimientosSinConciliar'] }}</span>
                            @else
                                <span class="badge bg-success badge-pill p-2"><i class="fas fa-check"></i></span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Última conciliación guardada
                            <span class="fw-bold">{{ $estadoBancos['ultimaConciliacionFecha'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Diferencia en última conciliación
                            @if($estadoBancos['ultimaConciliacionDiferencia'] != 0)
                                <span class="fw-bold text-danger">S/ {{ number_format($estadoBancos['ultimaConciliacionDiferencia'], 2) }}</span>
                            @else
                                <span class="fw-bold text-success">S/ 0.00</span>
                            @endif
                        </li>
                    </ul>
                </div>
                <div class="card-footer text-end">
                    <a href="#" class="btn btn-sm btn-primary">Ir a Conciliación</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0"><i class="fas fa-history me-2"></i>Actividad Contable Reciente</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Número</th>
                                    <th>Fecha</th>
                                    <th>Glosa</th>
                                    <th class="text-end">Debe</th>
                                    <th class="text-end">Haber</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($asientosRecientes as $asiento)
                                    <tr>
                                        <td>
                                            <a href="#"><strong>{{ $asiento->numero }}</strong></a>
                                        </td>
                                        <td>{{ $asiento->fechaCorta }}</td>
                                        <td>{{ Str::limit($asiento->glosa, 35) }}</td>
                                        <td class="text-end text-nowrap">S/ {{ number_format($asiento->total_debe, 2) }}</td>
                                        <td class="text-end text-nowrap">S/ {{ number_format($asiento->total_haber, 2) }}</td>
                                        <td class="text-center">
                                            <span class="status-badge status-badge-{{ $asiento->estadoColor }}">
                                                {{ $asiento->estadoTexto }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center p-4">No hay actividad contable reciente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0"><i class="fas fa-star me-2"></i>Cuentas con Mayor Movimiento</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Nombre</th>
                                    <th class="text-end">Movs.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($resumenCuentas as $cuenta)
                                    <tr>
                                        <td><strong>{{ $cuenta->codigo }}</strong></td>
                                        <td>{{ Str::limit($cuenta->cuenta_nombre, 25) }}</td>
                                        <td class="text-end fw-bold">{{ $cuenta->total_movimientos }}</td>
                                    </tr>
                                @empty
                                     <tr>
                                        <td colspan="3" class="text-center p-4">No hay movimientos de cuentas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    {{-- (Aquí puedes agregar JS para el botón de limpiar caché, si lo deseas) --}}
@endpush