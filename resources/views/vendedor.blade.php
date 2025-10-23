@extends('layouts.app')

@section('content')
<div class="container">
    <h1> Dashboard de Vendedor</h1>
    <p>Bienvenido, <strong>{{ Auth::user()->usuario }}</strong>!</p>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    Resumen de Actividad
                </div>
                <div class="card-body">
                    <p><strong>Ventas este mes:</strong> ${{ $data['ventasMes'] }}</p>
                    <p><strong>Pedidos completados:</strong> {{ $data['pedidosCompletados'] }}</p>
                    <p><strong>Clientes activos:</strong> {{ $data['clientesActivos'] }}</p>
                    <p><strong>Comisiones ganadas:</strong> ${{ $data['comisionesGanadas'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                     Progreso de Meta Mensual
                </div>
                <div class="card-body">
                    <div class="progress">
                        <div class="progress-bar bg-success"
                            role="progressbar"
                            style="width: {{ $data['porcentajeMeta'] ?? 0 }}<?php echo '%'; ?>;"
                            aria-valuenow="{{ $data['porcentajeMeta'] ?? 0 }}"
                            aria-valuemin="0"
                            aria-valuemax="100">
                        </div>
                    </div>
                    <p class="mt-2">Meta: ${{ $data['metaMes'] }} | Actual: ${{ $data['ventasActuales'] }}</p>
                    <p>Días restantes: {{ $data['diasRestantes'] }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    Productos Estrella
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($data['productosEstrella'] as $producto)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $producto['nombre'] }}
                            <span class="badge bg-primary rounded-pill">{{ $producto['vendidos'] }} vendidos</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection