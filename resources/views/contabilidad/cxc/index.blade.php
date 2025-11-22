@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Cuentas por Cobrar')

@section('page-title', 'Centro de Cuentas por Cobrar')

@section('breadcrumbs')
    <li class="breadcrumb-item">Contabilidad</li>
    <li class="breadcrumb-item active" aria-current="page">Cuentas por Cobrar</li>
@endsection

@push('styles')
    <link href="{{ asset('css/contabilidad/cxc.css') }}" rel="stylesheet">        
@endpush

@section('content')
    {{-- === KPI DASHBOARD === --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon bg-primary">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Deuda Total</div>
                    <div class="kpi-value">
                        S/ {{ number_format($kpis['totalDeuda'], 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon bg-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Deuda Vencida</div>
                    <div class="kpi-value">
                        S/ {{ number_format($kpis['totalVencido'], 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Por Vencer</div>
                    <div class="kpi-value">
                        S/ {{ number_format($kpis['totalPorVencer'], 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon bg-warning">
                    <i class="fas fa-users"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Clientes c/ Deuda</div>
                    <div class="kpi-value">
                        {{ $documentos->total() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- === ANTIQUEDAD DE SALDOS === --}}
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-clock me-2"></i>
                Antigüedad de Saldos Vencidos
            </h6>
        </div>
        <div class="card-body">
            <div class="row text-center aging-card">
                <div class="col">
                    <div class="h5 text-danger">
                        <i class="fas fa-calendar-day me-1"></i>
                        1-30 Días
                    </div>
                    <div class="h4 fw-bold">
                        S/ {{ number_format($kpis['aging_1_30'], 2) }}
                    </div>
                </div>
                
                <div class="col">
                    <div class="h5 text-danger">
                        <i class="fas fa-calendar-alt me-1"></i>
                        31-60 Días
                    </div>
                    <div class="h4 fw-bold">
                        S/ {{ number_format($kpis['aging_31_60'], 2) }}
                    </div>
                </div>
                
                <div class="col">
                    <div class="h5 text-danger fw-bolder">
                        <i class="fas fa-calendar-times me-1"></i>
                        60+ Días
                    </div>
                    <div class="h4 fw-bold text-danger">
                        S/ {{ number_format($kpis['aging_60_mas'], 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- === TABLA DE DOCUMENTOS === --}}
    <div class="card shadow">
        <div class="card-header">
            <h5 class="card-title m-0">
                <i class="fas fa-file-invoice-dollar me-2"></i>
                Detalle de Documentos Pendientes
            </h5>
        </div>
        
        <div class="card-body">
            {{-- === FILTROS === --}}
            <form method="GET" action="{{ route('contador.cxc.index') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="cliente" class="form-label">
                            <i class="fas fa-user me-1"></i>
                            Buscar Cliente
                        </label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="cliente" 
                            id="cliente" 
                            placeholder="Nombre o Razón Social..." 
                            value="{{ $filtros['cliente'] }}"
                        >
                    </div>
                    
                    <div class="col-md-3">
                        <label for="vendedor" class="form-label">
                            <i class="fas fa-user-tie me-1"></i>
                            Vendedor
                        </label>
                        <select name="vendedor" id="vendedor" class="form-select">
                            <option value="">Todos</option>
                            @foreach($vendedores as $vendedor)
                                <option 
                                    value="{{ $vendedor->Codemp }}" 
                                    @selected($filtros['vendedor'] == $vendedor->Codemp)
                                >
                                    {{ $vendedor->Nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="estado" class="form-label">
                            <i class="fas fa-filter me-1"></i>
                            Estado
                        </label>
                        <select name="estado" id="estado" class="form-select">
                            <option value="pendientes" @selected($filtros['estado'] == 'pendientes')>
                                Pendientes (Todas)
                            </option>
                            <option value="vencidas" @selected($filtros['estado'] == 'vencidas')>
                                Solo Vencidas
                            </option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 btn-filter">
                            <i class="fas fa-filter"></i>
                            <span class="ms-1">Filtrar</span>
                        </button>
                    </div>
                </div>
            </form>

            {{-- === TABLA === --}}
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <i class="fas fa-user me-1"></i>
                                Cliente
                            </th>
                            <th>
                                <i class="fas fa-user-tie me-1"></i>
                                Vendedor
                            </th>
                            <th>
                                <i class="fas fa-file-alt me-1"></i>
                                Documento
                            </th>
                            <th>
                                <i class="fas fa-calendar me-1"></i>
                                Emisión
                            </th>
                            <th>
                                <i class="fas fa-calendar-check me-1"></i>
                                Vencimiento
                            </th>
                            <th class="text-center">
                                <i class="fas fa-clock me-1"></i>
                                Días Venc.
                            </th>
                            <th class="text-end">
                                <i class="fas fa-dollar-sign me-1"></i>
                                Importe Total
                            </th>
                            <th class="text-end">
                                <i class="fas fa-balance-scale me-1"></i>
                                Saldo Pendiente
                            </th>
                            <th class="text-center">
                                <i class="fas fa-cogs me-1"></i>
                                Acción
                            </th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        @forelse($documentos as $doc)
                            @php
                                $isVencido = $doc->dias_vencidos > 0;
                            @endphp
                            
                            <tr class="{{ $isVencido ? 'table-danger-light' : '' }}">
                                <td>
                                    <div class="fw-bold">
                                        {{ $doc->Razon }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $doc->RucCliente }}
                                    </small>
                                </td>
                                
                                <td>
                                    <div class="fw-medium">
                                        {{ $doc->VendedorNombre }}
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">
                                            {{ $doc->Documento }}
                                        </span>
                                        <span class="badge bg-secondary">
                                            {{ $doc->Tipo }}
                                        </span>
                                    </div>
                                </td>
                                
                                <td>
                                    {{ Carbon\Carbon::parse($doc->FechaF)->format('d/m/Y') }}
                                </td>
                                
                                <td>
                                    <span class="fw-bold">
                                        {{ Carbon\Carbon::parse($doc->FechaV)->format('d/m/Y') }}
                                    </span>
                                </td>
                                
                                <td class="text-center">
                                    <span class="fw-bold {{ $isVencido ? 'text-danger' : 'text-success' }}">
                                        {{ $isVencido ? $doc->dias_vencidos : 'Por Vencer' }}
                                    </span>
                                </td>
                                
                                <td class="text-end">
                                    S/ {{ number_format($doc->Importe, 2) }}
                                </td>
                                
                                <td class="text-end">
                                    <span class="fw-bold fs-6">
                                        S/ {{ number_format($doc->Saldo, 2) }}
                                    </span>
                                </td>
                                
                                <td class="text-center">
                                    <form 
                                        action="{{ route('contador.flujo.cobranzas.handlePaso1') }}" 
                                        method="POST" 
                                        class="m-0"
                                    >
                                        @csrf
                                        <input 
                                            type="hidden" 
                                            name="cliente_id" 
                                            value="{{ $doc->Codclie }}"
                                        >
                                        <button 
                                            type="submit" 
                                            class="btn btn-primary btn-sm" 
                                            title="Registrar Pago"
                                        >
                                            <i class="fas fa-hand-holding-usd"></i>
                                            <span class="ms-1">Pagar</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center p-5">
                                    <div class="text-muted">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3 d-block"></i>
                                        <h5>¡Felicidades!</h5>
                                        <p>No se encontraron deudas pendientes con estos filtros.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- === PAGINACIÓN === --}}
            <div class="d-flex justify-content-end mt-4">
                {{ $documentos->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection