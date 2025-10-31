@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-12">
      <h2><i class="fas fa-calendar-day"></i> Reporte de Bancos - Diario</h2>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Reportes</a></li>
          <li class="breadcrumb-item active">Bancos Diario</li>
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
      <form method="GET" action="{{ route('contador.bancos.diario') }}">
        <div class="row">
          <div class="col-md-3">
            <label for="fecha_inicio">Fecha Inicio</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio"
                   class="form-control" value="{{ $fechaInicio }}">
          </div>
          <div class="col-md-3">
            <label for="fecha_fin">Fecha Fin</label>
            <input type="date" name="fecha_fin" id="fecha_fin"
                   class="form-control" value="{{ $fechaFin }}">
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

  <!-- Resumen del Período -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card bg-primary text-white">
        <div class="card-body">
          <h6>Saldo Inicial</h6>
          <h3>${{ number_format($saldoInicial, 2) }}</h3>
          <small>{{ $fechaInicio }}</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-success text-white">
        <div class="card-body">
          <h6>Total Ingresos</h6>
          <h3>${{ number_format($totalIngresos, 2) }}</h3>
          <small>{{ $cantidadIngresos }} movimientos</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-danger text-white">
        <div class="card-body">
          <h6>Total Egresos</h6>
          <h3>${{ number_format($totalEgresos, 2) }}</h3>
          <small>{{ $cantidadEgresos }} movimientos</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-info text-white">
        <div class="card-body">
          <h6>Saldo Final</h6>
          <h3>${{ number_format($saldoFinal, 2) }}</h3>
          <small>{{ $fechaFin }}</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Gráfica de Movimientos Diarios -->
  <div class="card mb-4">
    <div class="card-header">
      <h5><i class="fas fa-chart-line"></i> Movimientos Diarios</h5>
    </div>
    <div class="card-body">
      <canvas id="chartMovimientos" height="80"></canvas>
    </div>
  </div>

  <!-- Detalle de Movimientos por Día -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5><i class="fas fa-list"></i> Detalle de Movimientos</h5>
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
      @if($movimientos->isEmpty())
        <div class="alert alert-info text-center">
          <i class="fas fa-info-circle"></i> No hay movimientos para el período seleccionado
        </div>
      @else
        @php 
          $fechaActual = null;
          $saldo = $saldoInicial;
        @endphp
        
        <div class="table-responsive">
          <table class="table table-striped table-hover" id="tablaMovimientos">
            <thead class="thead-dark">
              <tr>
                <th>Fecha/Hora</th>
                <th>Cuenta</th>
                <th>Tipo</th>
                <th>Concepto</th>
                <th>Referencia</th>
                <th class="text-right">Ingresos</th>
                <th class="text-right">Egresos</th>
                <th class="text-right">Saldo</th>
              </tr>
            </thead>
            <tbody>
              @foreach($movimientos as $mov)
                @php
                  $fecha = $mov->fecha->format('Y-m-d');
                  if ($fechaActual != $fecha) {
                    $fechaActual = $fecha;
                    echo '<tr class="table-secondary"><td colspan="8"><strong><i class="fas fa-calendar"></i> ' . $mov->fecha->format('d/m/Y - l') . '</strong></td></tr>';
                  }
                  $saldo += $mov->ingreso - $mov->egreso;
                @endphp
                <tr>
                  <td>{{ $mov->fecha->format('H:i') }}</td>
                  <td>
                    <small>{{ $mov->cuenta->banco }}<br>{{ $mov->cuenta->numero_cuenta }}</small>
                  </td>
                  <td>
                    <span class="badge badge-{{ $mov->tipo == 'ingreso' ? 'success' : 'danger' }}">
                      {{ ucfirst($mov->tipo) }}
                    </span>
                  </td>
                  <td>{{ $mov->concepto }}</td>
                  <td>{{ $mov->referencia ?? '-' }}</td>
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
                <td colspan="5"><strong>TOTALES</strong></td>
                <td class="text-right"><strong>${{ number_format($totalIngresos, 2) }}</strong></td>
                <td class="text-right"><strong>${{ number_format($totalEgresos, 2) }}</strong></td>
                <td class="text-right"><strong>${{ number_format($saldoFinal, 2) }}</strong></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div class="mt-3">
          {{ $movimientos->appends(request()->query())->links() }}
        </div>
      @endif
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Datos para la gráfica
const chartData = @json($chartData);

// Crear gráfica
const ctx = document.getElementById('chartMovimientos').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: chartData.labels,
    datasets: [
      {
        label: 'Ingresos',
        data: chartData.ingresos,
        borderColor: 'rgb(40, 167, 69)',
        backgroundColor: 'rgba(40, 167, 69, 0.1)',
        tension: 0.1
      },
      {
        label: 'Egresos',
        data: chartData.egresos,
        borderColor: 'rgb(220, 53, 69)',
        backgroundColor: 'rgba(220, 53, 69, 0.1)',
        tension: 0.1
      },
      {
        label: 'Saldo',
        data: chartData.saldos,
        borderColor: 'rgb(0, 123, 255)',
        backgroundColor: 'rgba(0, 123, 255, 0.1)',
        tension: 0.1,
        borderWidth: 2
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: {
        position: 'top',
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            return context.dataset.label + ': $' + context.parsed.y.toFixed(2);
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            return '$' + value.toFixed(2);
          }
        }
      }
    }
  }
});

function exportarExcel() {
  const params = new URLSearchParams(window.location.search);
  params.append('formato', 'excel');
  window.location.href = '{{ route("contador.bancos.diario") }}?' + params.toString();
}

function exportarPDF() {
  const params = new URLSearchParams(window.location.search);
  params.append('formato', 'pdf');
  window.open('{{ route("contador.bancos.diario") }}?' + params.toString(), '_blank');
}
</script>

<style>
@media print {
  .card-header button,
  .breadcrumb,
  form,
  #chartMovimientos {
    display: none;
  }

  .table {
    font-size: 10px;
  }
}

.table-secondary {
  background-color: #e9ecef !important;
}

.badge {
  font-size: 0.85em;
  padding: 0.35em 0.65em;
}
</style>
@endsection