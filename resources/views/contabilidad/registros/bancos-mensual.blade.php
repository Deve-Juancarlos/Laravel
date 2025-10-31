@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-12">
      <h2><i class="fas fa-university"></i> Reporte de Bancos - Mensual</h2>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
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
              @foreach($meses as $num => $nombre)
                <option value="{{ $num }}" {{ $mes == $num ? 'selected' : '' }}>
                  {{ $nombre }}
                </option>
              @endforeach
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
          <div class="col-md-2 align-self-end">
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
    <div class="col-md-4">
      <div class="card bg-success text-white">
        <div class="card-body">
          <h6>Total Ingresos</h6>
          <h3>S/ {{ number_format($totalesMes['total_ingresos'], 2) }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card bg-danger text-white">
        <div class="card-body">
          <h6>Total Egresos</h6>
          <h3>S/ {{ number_format($totalesMes['total_egresos'], 2) }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card bg-info text-white">
        <div class="card-body">
          <h6>Saldo Neto</h6>
          <h3>S/ {{ number_format($totalesMes['total_ingresos'] - $totalesMes['total_egresos'], 2) }}</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Resumen por Banco -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5><i class="fas fa-table"></i> Resumen Mensual por Banco</h5>
      <div>
        <button onclick="window.print()" class="btn btn-secondary btn-sm">
          <i class="fas fa-print"></i> Imprimir
        </button>
      </div>
    </div>
    <div class="card-body">
      @if($resumenMensual->isEmpty())
        <div class="alert alert-info text-center">
          <i class="fas fa-info-circle"></i> No hay movimientos bancarios para el período seleccionado.
        </div>
      @else
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="thead-dark">
              <tr>
                <th>Banco</th>
                <th>Moneda</th>
                <th class="text-right">Ingresos</th>
                <th class="text-right">Egresos</th>
                <th class="text-right">Saldo del Mes</th>
                <th class="text-center">Movimientos</th>
              </tr>
            </thead>
            <tbody>
              @foreach($resumenMensual as $item)
                <tr>
                  <td>{{ $item->Banco }}</td>
                  <td>{{ $item->Moneda == 1 ? 'PEN' : ($item->Moneda == 2 ? 'USD' : 'Otro') }}</td>
                  <td class="text-right text-success">S/ {{ number_format($item->ingresos_mes, 2) }}</td>
                  <td class="text-right text-danger">S/ {{ number_format($item->egresos_mes, 2) }}</td>
                  <td class="text-right">
                    <strong>S/ {{ number_format($item->saldo_mes, 2) }}</strong>
                  </td>
                  <td class="text-center">{{ $item->total_movimientos }}</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr class="font-weight-bold">
                <td colspan="2">TOTALES</td>
                <td class="text-right text-success">S/ {{ number_format($totalesMes['total_ingresos'], 2) }}</td>
                <td class="text-right text-danger">S/ {{ number_format($totalesMes['total_egresos'], 2) }}</td>
                <td class="text-right">S/ {{ number_format($totalesMes['total_ingresos'] - $totalesMes['total_egresos'], 2) }}</td>
                <td class="text-center">{{ $totalesMes['total_movimientos'] }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Detalle Diario (opcional) -->
        <h5 class="mt-5"><i class="fas fa-calendar-day"></i> Detalle Diario</h5>
        <div class="table-responsive mt-3">
          <table class="table table-sm table-bordered">
            <thead class="thead-light">
              <tr>
                <th>Día</th>
                <th>Banco</th>
                <th class="text-right">Ingresos</th>
                <th class="text-right">Egresos</th>
                <th class="text-center">Movimientos</th>
              </tr>
            </thead>
            <tbody>
              @foreach($detalleDiario as $dia)
                <tr>
                  <td>{{ str_pad($dia->dia, 2, '0', STR_PAD_LEFT) }}</td>
                  <td>{{ $dia->Banco }}</td>
                  <td class="text-right">S/ {{ number_format($dia->ingresos, 2) }}</td>
                  <td class="text-right">S/ {{ number_format($dia->egresos, 2) }}</td>
                  <td class="text-center">{{ $dia->movimientos }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</div>

<style>
@media print {
  .card-header button,
  .breadcrumb,
  form {
    display: none !important;
  }
  body {
    font-size: 12px;
  }
}
</style>
@endsection