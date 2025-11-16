@extends('layouts.admin')

@section('title', 'Productos por Vencer')

@section('content')
<div class="container-fluid py-4">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Productos Próximos a Vencer
                    </h5>
                </div>
                <div class="card-body">
                    
                    <!-- Filtros -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Días de anticipación</label>
                            <select name="dias" class="form-select">
                                <option value="30" {{ $dias == 30 ? 'selected' : '' }}>30 días</option>
                                <option value="60" {{ $dias == 60 ? 'selected' : '' }}>60 días</option>
                                <option value="90" {{ $dias == 90 ? 'selected' : '' }}>90 días</option>
                                <option value="120" {{ $dias == 120 ? 'selected' : '' }}>120 días</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-warning me-2">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                            <a href="{{ route('admin.reportes.productos-vencer.export', request()->all()) }}" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </a>
                        </div>
                    </form>

                    <!-- Alerta -->
                    @if($productos->count() > 0)
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Atención:</strong> Se encontraron {{ $productos->count() }} productos/lotes próximos a vencer en los próximos {{ $dias }} días.
                    </div>
                    @endif

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Lote</th>
                                    <th>Almacén</th>
                                    <th class="text-center">Stock</th>
                                    <th>Fecha Vencimiento</th>
                                    <th class="text-center">Días para Vencer</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productos as $producto)
                                <tr class="{{ $producto->DiasParaVencer <= 15 ? 'table-danger' : ($producto->DiasParaVencer <= 30 ? 'table-warning' : '') }}">
                                    <td>{{ $producto->CodPro }}</td>
                                    <td>
                                        <strong>{{ $producto->Nombre }}</strong><br>
                                        <small class="text-muted">{{ $producto->Laboratorio ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $producto->Lote }}</td>
                                    <td>{{ $producto->Almacen }}</td>
                                    <td class="text-center">{{ number_format($producto->Stock, 0) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($producto->Vencimiento)->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $producto->DiasParaVencer <= 15 ? 'danger' : ($producto->DiasParaVencer <= 30 ? 'warning' : 'info') }}">
                                            {{ $producto->DiasParaVencer }} días
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($producto->DiasParaVencer <= 15)
                                            <span class="badge bg-danger">Urgente</span>
                                        @elseif($producto->DiasParaVencer <= 30)
                                            <span class="badge bg-warning">Atención</span>
                                        @else
                                            <span class="badge bg-info">Monitoreo</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-success py-4">
                                        <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                        No hay productos próximos a vencer en los próximos {{ $dias }} días
                                    </td>
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
