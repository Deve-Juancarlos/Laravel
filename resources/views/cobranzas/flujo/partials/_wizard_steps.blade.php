{{-- Este partial muestra los 4 pasos del asistente --}}
{{-- Recibe una variable: $paso_actual (1, 2, 3, o 4) --}}
@php
    $pasos = [
        1 => ['titulo' => 'Identificar Cliente', 'icono' => 'fa-user-check'],
        2 => ['titulo' => 'Registrar Pago', 'icono' => 'fa-dollar-sign'],
        3 => ['titulo' => 'Aplicar Facturas', 'icono' => 'fa-file-invoice-dollar'],
        4 => ['titulo' => 'Confirmar', 'icono' => 'fa-check-circle'],
    ];
@endphp



<div class="d-flex justify-content-between mb-4">
    @foreach($pasos as $numero => $paso)
        @php
            $isCompleted = $numero < $paso_actual;
            isActive = $numero == $paso_actual;
            isPending = $numero > $paso_actual;
        @endphp

        <div class="text-center" style="flex-basis: 25%;">
            <div class="d-flex align-items-center justify-content-center mx-auto
                        {{ $isCompleted || $isActive ? 'bg-primary text-white' : 'bg-light border' }}"
                 style="width: 50px; height: 50px; border-radius: 50%; font-size: 1.25rem;">
                <i class="fas {{ $paso['icono'] }}"></i>
            </div>
            <h6 class="mt-2 mb-0 {{ $isActive ? 'text-primary fw-bold' : ($isPending ? 'text-muted' : '') }}">
                Paso {{ $numero }}
            </h6>
            <small class_="{{ $isActive ? 'text-primary' : 'text-muted' }}">{{ $paso['titulo'] }}</small>
        </div>

        @if(!$loop->last)
            {{-- Línea conectora --}}
            <div style="flex-grow: 1; height: 2px; background: #e0e0e0; margin-top: 25px;"></div>
        @endif
    @endforeach
</div>