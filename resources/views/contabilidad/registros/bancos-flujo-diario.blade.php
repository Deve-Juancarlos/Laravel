@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-calendar-day me-2"></i>Flujo Diario de Caja</h2>
        <div class="d-flex gap-2">
          <a href="{{ route('contador.bancos.mensual', ['mes' => date('Y-m')]) }}" class="btn btn-warning">
            <i class="fas fa-chart-line me-2"></i>Ver Flujo Mensual
          </a>
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportarModal">
            <i class="fas fa-file-excel me-2"></i>Exportar
          </button>
          <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Imprimir
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('contador.bancos.flujo-diario') }}">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Fecha</label>
            <input type="date" name="fecha" class="form-control" value="{{ request('fecha', date('Y-m-d')) }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Banco</label>
            <select name="banco_id" class="form-select">
              <option value="">Todos los bancos</option>
              @foreach($bancos as $banco)
                <option value="{{ $banco->id }}" {{ request('banco_id') == $banco->id ? 'selected' : '' }}>
                  {{ $banco->nombre }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Tipo de Movimiento</label>
            <select name="tipo" class="form-select">
              <option value="">Todos</option>
              <option value="ingreso" {{ request('tipo') == 'ingreso' ? 'selected' : '' }}>Ingresos</option>
              <option value="egreso" {{ request('tipo') == 'egreso' ? 'selected' : '' }}>Egresos</option>
            </select>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">
              <i class="fas fa-filter me-2"></i>Filtrar
            </button>
            <a href="{{ route('contador.bancos.flujo-diario') }}" class="btn btn-secondary">
              <i class="fas fa-times me-2"></i>Limpiar
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Resumen del Día -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card bg-info text-white">
        <div class="card-body">
          <h6 class="card-title">Saldo Inicial</h6>
          <h3 class="mb-0">{{ number_format($saldoInicial, 2) }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-success text-white">
        <div class="card-body">
          <h6 class="card-title">Total Ingresos</h6>
          <h3 class="mb-0">{{ number_format($totalIngresos, 2) }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-danger text-white">
        <div class="card-body">
          <h6 class="card-title">Total Egresos</h6>
          <h3 class="mb-0">{{ number_format($totalEgresos, 2) }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-primary text-white">
        <div class="card-body">
          <h6 class="card-title">Saldo Final</h6>
          <h3 class="mb-0">{{ number_format($saldoFinal, 2) }}</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Movimientos del Día -->
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">Movimientos del Día - {{ \Carbon\Carbon::parse(request('fecha', date('Y-m-d')))->format('d/m/Y') }}</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Hora</th>
              <th>Banco</th>
              <th>Tipo</th>
              <th>Concepto</th>
              <th>Referencia</th>
              <th class="text-end">Ingreso</th>
              <th class="text-end">Egreso</th>
              <th class="text-end">Saldo</th>
              <th>Usuario</th>
            </tr>
          </thead>
          <tbody>
            @php $saldoAcumulado = $saldoInicial; @endphp
            @forelse($movimientos as $mov)
              @php
                $saldoAcumulado += ($mov->tipo == 'ingreso' ? $mov->monto : -$mov->monto);
              @endphp
              <tr>
                <td>{{ $mov->created_at->format('H:i') }}</td>
                <td>
                  <span class="badge bg-secondary">{{ $mov->banco->nombre }}</span>
                </td>
                <td>
                  @if($mov->tipo == 'ingreso')
                    <span class="badge bg-success">
                      <i class="fas fa-arrow-up me-1"></i>Ingreso
                    </span>
                  @else
                    <span class="badge bg-danger">
                      <i class="fas fa-arrow-down me-1"></i>Egreso
                    </span>
                  @endif
                </td>
                <td>{{ $mov->concepto }}</td>
                <td>{{ $mov->referencia ?? '-' }}</td>
                <td class="text-end text-success fw-bold">
                  {{ $mov->tipo == 'ingreso' ? number_format($mov->monto, 2) : '-' }}
                </td>
                <td class="text-end text-danger fw-bold">
                  {{ $mov->tipo == 'egreso' ? number_format($mov->monto, 2) : '-' }}
                </td>
                <td class="text-end fw-bold">{{ number_format($saldoAcumulado, 2) }}</td>
                <td>{{ $mov->usuario->name ?? '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center text-muted py-4">
                  <i class="fas fa-inbox fa-2x mb-2"></i>
                  <p>No hay movimientos para esta fecha</p>
                </td>
              </tr>
            @endforelse
          </tbody>
          <tfoot class="table-light">
            <tr>
              <th colspan="5" class="text-end">TOTALES:</th>
              <th class="text-end text-success">{{ number_format($totalIngresos, 2) }}</th>
              <th class="text-end text-danger">{{ number_format($totalEgresos, 2) }}</th>
              <th class="text-end text-primary">{{ number_format($saldoFinal, 2) }}</th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Exportar -->
<div class="modal fade" id="exportarModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Exportar Flujo Diario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="#">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Formato</label>
            <select name="formato" class="form-select" required>
              <option value="excel">Excel (.xlsx)</option>
              <option value="pdf">PDF</option>
              <option value="csv">CSV</option>
            </select>
          </div>
          <input type="hidden" name="fecha" value="{{ request('fecha', date('Y-m-d')) }}">
          <input type="hidden" name="banco_id" value="{{ request('banco_id') }}">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-download me-2"></i>Exportar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
@media print {
  .btn, .card-header, nav { display: none !important; }
  .card { border: none !important; box-shadow: none !important; }
}
</style>
@endsection