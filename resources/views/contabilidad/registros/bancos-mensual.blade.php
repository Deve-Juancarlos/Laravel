@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-12">
      <h2><i class="fas fa-university"></i> Reporte de Bancos - Mensual</h2>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Reportes</a></li>
          <li class="breadcrumb-item active">Bancos Mensual</li>
        </ol>
      </nav>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card mb-4">
    <div class="card-header">
      <h5><i class="fas fa-filter"></i> Filtros de Búsqueda</h5>
    </div>
    <div class="card-body">
      <form method="GET" action="{{ route('contador.bancos.mensual') }}">
        <div class="row">
          <div class="col-md-3">
            <label for="mes">Mes</label>
            <select name="mes" id="mes" class="form-control">
              @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }}>
                  {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                </option>
              @endfor
            </select>
          </div>
          <div class="col-md-3">
            <label for="anio">Año</label>
            <select name="anio" id="anio" class="form-control">
              @for($a = date('Y'); $a >= date('Y') - 5; $a--)
                <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
              @endfor
            </select>
          </div>
          <div class="col-md-4">
            <label for="cuenta_id">Cuenta Bancaria</label>
            <select name="cuenta_id" id="cuenta_id" class="form-control">
              <option value="">Todas las cuentas</option>
              @foreach($cuentas as $cuenta)
                <option value="{{ $cuenta->id }}" {{ $cuenta_id == $cuenta->id ? 'selected' : '' }}>
                  {{ $cuenta->banco }} - {{ $cuenta->numero_cuenta }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label> </label>
            <button type="submit" class="btn btn-primary btn-block">
              <i class="fas fa-search"></i> Buscar
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Resumen General -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card bg-primary text-white">
        <div class="card-body">
          <h6>Saldo Inicial</h6>
          <h3>${{ number_format($saldoInicial, 2) }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-success text-white">
        <div class="card-body">
          <h6>Total Ingresos</h6>
          <h3>${{ number_format($totalIngresos, 2) }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-danger text-white">
        <div class="card-body">
          <h6>Total Egresos</h6>
          <h3>${{ number_format($totalEgresos, 2) }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-info text-white">
        <div class="card-body">
          <h6>Saldo Final</h6>
          <h3>${{ number_format($saldoFinal, 2) }}</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla de Movimientos por Cuenta -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5><i class="fas fa-table"></i> Movimientos del Mes</h5>
      <div>
        <button onclick="exportarExcel()" class="btn btn-success btn-sm">
          <i class="fas fa-file-excel"></i> Excel
        </button>
        <button onclick="exportarPDF()" class="btn btn-danger btn-sm">
          <i class="fas fa-file-pdf"></i> PDF
        </button>
        <button onclick="window.print()" class="btn btn-secondary btn-sm">
          <i class="fas fa-print"></i> Imprimir
        </button>
      </div>
    </div>
    <div class="card-body">
      @foreach($movimientosPorCuenta as $cuentaData)
        <h5 class="mt-3">
          <i class="fas fa-university"></i> {{ $cuentaData['cuenta']->banco }} - {{ $cuentaData['cuenta']->numero_cuenta }}
        </h5>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="thead-dark">
              <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Concepto</th>
                <th>Referencia</th>
                <th class="text-right">Ingresos</th>
                <th class="text-right">Egresos</th>
                <th class="text-right">Saldo</th>
              </tr>
            </thead>
            <tbody>
              <tr class="table-info">
                <td colspan="6"><strong>Saldo Inicial</strong></td>
                <td class="text-right"><strong>${{ number_format($cuentaData['saldo_inicial'], 2) }}</strong></td>
              </tr>
              @php $saldo = $cuentaData['saldo_inicial']; @endphp
              @foreach($cuentaData['movimientos'] as $mov)
                @php
                  $saldo += $mov->ingreso - $mov->egreso;
                @endphp
                <tr>
                  <td>{{ $mov->fecha->format('d/m/Y') }}</td>
                  <td>
                    <span class="badge badge-{{ $mov->tipo == 'ingreso' ? 'success' : 'danger' }}">
                      {{ ucfirst($mov->tipo) }}
                    </span>
                  </td>
                  <td>{{ $mov->concepto }}</td>
                  <td>{{ $mov->referencia }}</td>
                  <td class="text-right text-success">
                    {{ $mov->ingreso > 0 ? '$'.number_format($mov->ingreso, 2) : '-' }}
                  </td>
                  <td class="text-right text-danger">
                    {{ $mov->egreso > 0 ? '$'.number_format($mov->egreso, 2) : '-' }}
                  </td>
                  <td class="text-right">
                    <strong>${{ number_format($saldo, 2) }}</strong>
                  </td>
                </tr>
              @endforeach
              <tr class="table-warning">
                <td colspan="4"><strong>Totales</strong></td>
                <td class="text-right"><strong>${{ number_format($cuentaData['total_ingresos'], 2) }}</strong></td>
                <td class="text-right"><strong>${{ number_format($cuentaData['total_egresos'], 2) }}</strong></td>
                <td class="text-right"><strong>${{ number_format($saldo, 2) }}</strong></td>
              </tr>
            </tbody>
          </table>
        </div>
        <hr>
      @endforeach

      @if($movimientosPorCuenta->isEmpty())
        <div class="alert alert-info text-center">
          <i class="fas fa-info-circle"></i> No hay movimientos para el período seleccionado
        </div>
      @endif
    </div>
  </div>
</div>

<script>
function exportarExcel() {
  const params = new URLSearchParams(window.location.search);
  params.append('formato', 'excel');
  window.location.href = '{{ route("contador.bancos.mensual") }}?' + params.toString();
}

function exportarPDF() {
  const params = new URLSearchParams(window.location.search);
  params.append('formato', 'pdf');
  window.open('{{ route("contador.bancos.mensual") }}?' + params.toString(), '_blank');
}
</script>

<style>
@media print {
  .card-header button,
  .breadcrumb,
  form {
    display: none;
  }
}
</style>
@endsection