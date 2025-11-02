@extends('layouts.app')

@section('title', 'Cuentas por Cobrar')

@section('page-title', 'Centro de Cuentas por Cobrar')

@section('breadcrumbs')
    <li class="breadcrumb-item">Contabilidad</li>
    <li class="breadcrumb-item active" aria-current="page">Cuentas por Cobrar</li>
@endsection

@push('styles')
{{-- Estilos para los KPIs (los mismos de tu dashboard) --}}
<style>
.kpi-card { display: flex; align-items: center; background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
.kpi-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1.25rem; font-size: 1.5rem; color: #fff; }
.kpi-content .kpi-label { font-size: 0.875rem; font-weight: 600; color: #6c757d; text-transform: uppercase; margin-bottom: 0.25rem; }
.kpi-content .kpi-value { font-size: 1.5rem; font-weight: 700; color: #343a40; }
.table-danger-light { --bs-table-bg: #f8d7da; --bs-table-color: #58151c; }
</style>
@endpush

@section('content')

<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="kpi-card h-100">
            <div class="kpi-icon bg-primary"><i class="fas fa-dollar-sign"></i></div>
            <div class="kpi-content">
                <div class="kpi-label">Deuda Total</div>
                <div class="kpi-value">S/ {{ number_format($kpis['totalDeuda'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="kpi-card h-100">
            <div class="kpi-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="kpi-content">
                <div class="kpi-label">Deuda Vencida</div>
                <div class="kpi-value">S/ {{ number_format($kpis['totalVencido'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="kpi-card h-100">
            <div class="kpi-icon bg-success"><i class="fas fa-check-circle"></i></div>
            <div class="kpi-content">
                <div class="kpi-label">Por Vencer</div>
                <div class="kpi-value">S/ {{ number_format($kpis['totalPorVencer'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="kpi-card h-100">
            <div class="kpi-icon bg-warning"><i class="fas fa-users"></i></div>
            <div class="kpi-content">
                <div class="kpi-label">Clientes c/ Deuda</div>
                <div class="kpi-value">{{ $documentos->total() }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Antigüedad de Saldos Vencidos</h6>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col">
                <div class="h5 text-danger">1-30 Días</div>
                <div class="h4 fw-bold">S/ {{ number_format($kpis['aging_1_30'], 2) }}</div>
            </div>
            <div class="col">
                <div class="h5 text-danger">31-60 Días</div>
                <div class="h4 fw-bold">S/ {{ number_format($kpis['aging_31_60'], 2) }}</div>
            </div>
            <div class="col">
                <div class="h5 text-danger fw-bolder">60+ Días</div>
                <div class="h4 fw-bold text-danger">S/ {{ number_format($kpis['aging_60_mas'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow">
    <div class="card-header">
        <h5 class="card-title m-0">Detalle de Documentos Pendientes</h5>
    </div>
    <div class="card-body">
        
        <form method="GET" action="{{ route('contador.cxc.index') }}" class="mb-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="cliente" class="form-label">Buscar Cliente</label>
                    <input type="text" class="form-control" name="cliente" id="cliente" placeholder="Nombre o Razón Social..." value="{{ $filtros['cliente'] }}">
                </div>
                <div class="col-md-3">
                    <label for="vendedor" class="form-label">Vendedor</label>
                    <select name="vendedor" id="vendedor" class="form-select">
                        <option value="">Todos</option>
                        @foreach($vendedores as $vendedor)
                            <option value="{{ $vendedor->Codemp }}" @selected($filtros['vendedor'] == $vendedor->Codemp)>
                                {{ $vendedor->Nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="pendientes" @selected($filtros['estado'] == 'pendientes')>Pendientes (Todas)</option>
                        <option value="vencidas" @selected($filtros['estado'] == 'vencidas')>Solo Vencidas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Documento</th>
                        <th>Emisión</th>
                        <th>Vencimiento</th>
                        <th class="text-center">Días Venc.</th>
                        <th class="text-end">Importe Total</th>
                        <th class="text-end">Saldo Pendiente</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documentos as $doc)
                        @php
                            $isVencido = $doc->dias_vencidos > 0;
                        @endphp
                        <tr class="{{ $isVencido ? 'table-danger-light' : '' }}">
                            <td>
                                <strong>{{ $doc->Razon }}</strong>
                                <br><small class="text-muted">{{ $doc->RucCliente }}</small>
                            </td>
                            <td>{{ $doc->VendedorNombre }}</td>
                            <td>
                                {{ $doc->Documento }} 
                                <span class="badge bg-secondary">{{ $doc->Tipo }}</span>
                            </td>
                            <td>{{ Carbon\Carbon::parse($doc->FechaF)->format('d/m/Y') }}</td>
                            <td class="fw-bold">{{ Carbon\Carbon::parse($doc->FechaV)->format('d/m/Y') }}</td>
                            <td class="text-center fw-bold {{ $isVencido ? 'text-danger' : 'text-success' }}">
                                {{ $isVencido ? $doc->dias_vencidos : 'Por Vencer' }}
                            </td>
                            <td class="text-end">S/ {{ number_format($doc->Importe, 2) }}</td>
                            <td class="text-end fw-bold fs-6">S/ {{ number_format($doc->Saldo, 2) }}</td>
                            <td class="text-center">
                                {{-- 
                                  ¡AQUÍ ESTÁ LA MAGIA!
                                  Este formulario envía el CodClie al Paso 1 del flujo
                                  para registrar el pago de este cliente.
                                --}}
                                <form action="{{ route('contador.flujo.cobranzas.handlePaso1') }}" method="POST" class="m-0">
                                    @csrf
                                    <input type="hidden" name="cliente_id" value="{{ $doc->Codclie }}">
                                    <button type="submit" class="btn btn-primary btn-sm" title="Registrar Pago">
                                        <i class="fas fa-hand-holding-usd"></i> Pagar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center p-4 text-muted">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i><br>
                                ¡Felicidades! No se encontraron deudas pendientes con estos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            {{ $documentos->appends(request()->query())->links() }}
        </div>

    </div>
</div>
@endsection