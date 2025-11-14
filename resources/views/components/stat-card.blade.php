@props(['titulo', 'valor', 'icono', 'color'])

<div class="col-md-3">
    <div class="card stat-card shadow-sm" style="border-left-color: {{ $color }};">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">{{ $titulo }}</p>
                    <h3 class="mb-0 fw-bold" style="color: {{ $color }};">{{ $valor }}</h3>
                </div>
                <i class="{{ $icono }} stat-icon" style="color: {{ $color }}; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>
