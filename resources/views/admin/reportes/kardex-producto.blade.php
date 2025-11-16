@extends('layouts.admin')

@section('title', 'Kardex de Producto')

@section('content')
<div class="container-fluid py-4">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Kardex de Producto
                    </h5>
                </div>
                <div class="card-body">
                    
                    <!-- Filtros -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Código Producto</label>
                            <input type="text" name="codigo" class="form-control" 
                                   placeholder="Ingrese código" value="{{ $codPro }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" 
                                   value="{{ request('fecha_inicio', $fechaInicio->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" 
                                   value="{{ request('fecha_fin', $fechaFin->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </form>

                    @if(isset($kardex) && $kardex->count() > 0)
                    
                    <!-- Tabla Kardex -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo Mov.</th>
                                    <th>Documento</th>
                                    <th class="text-center">Entradas</th>
                                    <th class="text-center">Salidas</th>
                                    <th class="text-center">Saldo</th>
                                    <th class="text-end">Costo Unit.</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $saldoAcumulado = 0; @endphp
                                @foreach($kardex as $movimiento)
                                @php 
                                    $saldoAcumulado += ($movimiento->cantidad - $movimiento->salida);
                                    $valorTotal = $saldoAcumulado * $movimiento->costo_unitario;
                                @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                                    <td>
                                        @if($movimiento->tipo_movimiento == 'COMPRA')
                                            <span class="badge bg-success">Compra</span>
                                        @else
                                            <span class="badge bg-danger">Venta</span>
                                        @endif
                                    </td>
                                    <td>{{ $movimiento->Serie }}-{{ $movimiento->Numero }}</td>
                                    <td class="text-center {{ $movimiento->cantidad > 0 ? 'text-success fw-bold' : '' }}">
                                        {{ $movimiento->cantidad > 0 ? number_format($movimiento->cantidad, 0) : '-' }}
                                    </td>
                                    <td class="text-center {{ $movimiento->salida > 0 ? 'text-danger fw-bold' : '' }}">
                                        {{ $movimiento->salida > 0 ? number_format($movimiento->salida, 0) : '-' }}
                                    </td>
                                    <td class="text-center fw-bold">{{ number_format($saldoAcumulado, 0) }}</td>
                                    <td class="text-end">S/ {{ number_format($movimiento->costo_unitario, 2) }}</td>
                                    <td class="text-end fw-bold">S/ {{ number_format($valorTotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3">TOTALES:</th>
                                    <th class="text-center">{{ number_format($kardex->sum('cantidad'), 0) }}</th>
                                    <th class="text-center">{{ number_format($kardex->sum('salida'), 0) }}</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @elseif(isset($codPro))
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        No se encontraron movimientos para este producto en el período seleccionado.
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

</div>
@endsection
