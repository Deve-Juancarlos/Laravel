@extends('layouts.app')
@section('content')

<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-balance-scale me-2"></i>Conciliación Bancaria</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaConciliacionModal">
          <i class="fas fa-plus me-2"></i>Nueva Conciliación
        </button>
      </div>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('bancos.conciliacion') }}">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Banco</label>
            <select name="banco_id" class="form-select" required>
              <option value="">Seleccionar banco</option>
              @foreach($bancos as $banco)
                <option value="{{ $banco->id }}" {{ request('banco_id') == $banco->id ? 'selected' : '' }}>
                  {{ $banco->nombre }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Mes</label>
            <select name="mes" class="form-select" required>
              @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" {{ request('mes', date('n')) == $i ? 'selected' : '' }}>
                  {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                </option>
              @endfor
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Año</label>
            <select name="anio" class="form-select" required>
              @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                <option value="{{ $year }}" {{ request('anio', date('Y')) == $year ? 'selected' : '' }}>
                  {{ $year }}
                </option>
              @endfor
            </select>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">
              <i class="fas fa-search me-2"></i>Consultar
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  @if(request('banco_id'))
    <!-- Resumen de Conciliación -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h6 class="text-muted">Saldo Según Sistema</h6>
            <h3 class="mb-0 text-primary">{{ number_format($saldoSistema, 2) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h6 class="text-muted">Saldo Según Banco</h6>
            <h3 class="mb-0 text-info">{{ number_format($saldoBanco, 2) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h6 class="text-muted">Diferencia</h6>
            <h3 class="mb-0 {{ abs($diferencia) < 0.01 ? 'text-success' : 'text-danger' }}">
              {{ number_format($diferencia, 2) }}
            </h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h6 class="text-muted">Estado</h6>
            <h5 class="mb-0">
              @if(abs($diferencia) < 0.01)
                <span class="badge bg-success">
                  <i class="fas fa-check-circle me-1"></i>Conciliado
                </span>
              @else
                <span class="badge bg-warning">
                  <i class="fas fa-exclamation-triangle me-1"></i>Pendiente
                </span>
              @endif
            </h5>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Movimientos del Sistema -->
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-desktop me-2"></i>Movimientos del Sistema</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
              <table class="table table-sm mb-0">
                <thead class="sticky-top bg-light">
                  <tr>
                    <th>
                      <input type="checkbox" id="selectAllSistema" class="form-check-input">
                    </th>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th class="text-end">Monto</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($movimientosSistema as $mov)
                    <tr class="{{ $mov->conciliado ? 'table-success' : '' }}">
                      <td>
                        <input type="checkbox" class="form-check-input mov-sistema"
                               value="{{ $mov->id }}"
                               {{ $mov->conciliado ? 'checked disabled' : '' }}>
                      </td>
                      <td>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
                      <td>
                        <small>{{ Str::limit($mov->concepto, 30) }}</small>
                        @if($mov->referencia)
                          <br><span class="badge bg-secondary">{{ $mov->referencia }}</span>
                        @endif
                      </td>
                      <td class="text-end {{ $mov->tipo == 'ingreso' ? 'text-success' : 'text-danger' }}">
                        {{ ($mov->tipo == 'egreso' ? '-' : '+') . number_format($mov->monto, 2) }}
                      </td>
                      <td>
                        @if($mov->conciliado)
                          <i class="fas fa-check-circle text-success"></i>
                        @else
                          <i class="fas fa-clock text-warning"></i>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            <strong>Total: {{ number_format($totalSistema, 2) }}</strong>
            <span class="float-end text-muted">
              {{ $movimientosSistema->where('conciliado', false)->count() }} pendientes
            </span>
          </div>
        </div>
      </div>

      <!-- Movimientos del Banco -->
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-university me-2"></i>Estado de Cuenta Bancario</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
              <table class="table table-sm mb-0">
                <thead class="sticky-top bg-light">
                  <tr>
                    <th>
                      <input type="checkbox" id="selectAllBanco" class="form-check-input">
                    </th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th class="text-end">Monto</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($movimientosBanco as $mov)
                    <tr class="{{ $mov->conciliado ? 'table-success' : '' }}">
                      <td>
                        <input type="checkbox" class="form-check-input mov-banco"
                               value="{{ $mov->id }}"
                               {{ $mov->conciliado ? 'checked disabled' : '' }}>
                      </td>
                      <td>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
                      <td><small>{{ Str::limit($mov->descripcion, 30) }}</small></td>
                      <td class="text-end {{ $mov->monto > 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($mov->monto, 2) }}
                      </td>
                      <td>
                        @if($mov->conciliado)
                          <i class="fas fa-check-circle text-success"></i>
                        @else
                          <i class="fas fa-clock text-warning"></i>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            <strong>Total: {{ number_format($totalBanco, 2) }}</strong>
            <span class="float-end text-muted">
              {{ $movimientosBanco->where('conciliado', false)->count() }} pendientes
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Botones de Acción -->
    <div class="row mt-3">
      <div class="col-12 text-center">
        <button type="button" class="btn btn-success btn-lg" id="btnConciliar">
          <i class="fas fa-check me-2"></i>Conciliar Seleccionados
        </button>
        <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#ajusteModal">
          <i class="fas fa-edit me-2"></i>Registrar Ajuste
        </button>
      </div>
    </div>
  @endif
</div>

<!-- Modal Nueva Conciliación -->
<div class="modal fade" id="nuevaConciliacionModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nueva Conciliación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('contador.bancos.conciliacion') }}">
        @csrf
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Banco *</label>
              <select name="banco_id" class="form-select" required>
                <option value="">Seleccionar</option>
                @foreach($bancos as $banco)
                  <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Fecha de Conciliación *</label>
              <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Saldo Según Banco *</label>
              <input type="number" step="0.01" name="saldo_banco" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Saldo Según Sistema</label>
              <input type="number" step="0.01" name="saldo_sistema" class="form-control" readonly>
            </div>
            <div class="col-12">
              <label class="form-label">Observaciones</label>
              <textarea name="observaciones" class="form-control" rows="3"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Ajuste -->
<div class="modal fade" id="ajusteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Ajuste</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('contador.bancos.detalle') }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Tipo de Ajuste *</label>
            <select name="tipo" class="form-select" required>
              <option value="ingreso">Ingreso no registrado</option>
              <option value="egreso">Egreso no registrado</option>
              <option value="error">Corrección de error</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Monto *</label>
            <input type="number" step="0.01" name="monto" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Concepto *</label>
            <textarea name="concepto" class="form-control" rows="3" required></textarea>
          </div>
          <input type="hidden" name="banco_id" value="{{ request('banco_id') }}">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Registrar Ajuste</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Seleccionar todos - Sistema
  document.getElementById('selectAllSistema')?.addEventListener('change', function() {
    document.querySelectorAll('.mov-sistema:not(:disabled)').forEach(cb => {
      cb.checked = this.checked;
    });
  });

  // Seleccionar todos - Banco
  document.getElementById('selectAllBanco')?.addEventListener('change', function() {
    document.querySelectorAll('.mov-banco:not(:disabled)').forEach(cb => {
      cb.checked = this.checked;
    });
  });

  // Conciliar seleccionados
  document.getElementById('btnConciliar')?.addEventListener('click', function() {
    const movsSistema = Array.from(document.querySelectorAll('.mov-sistema:checked')).map(cb => cb.value);
    const movsBanco = Array.from(document.querySelectorAll('.mov-banco:checked')).map(cb => cb.value);

    if (movsSistema.length === 0 && movsBanco.length === 0) {
      alert('Debe seleccionar al menos un movimiento');
      return;
    }

    if (confirm('¿Confirma que desea conciliar los movimientos seleccionados?')) {
      fetch('{{ route("contador.bancos.transferencias") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          movimientos_sistema: movsSistema,
          movimientos_banco: movsBanco
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert('Error al conciliar: ' + data.message);
        }
      });
    }
  });
});
</script>
@endpush