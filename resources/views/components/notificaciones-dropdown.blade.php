@php
    $usuarioId = auth()->user()->id ?? null;
    $notificaciones = DB::table('Notificaciones')
        ->where(function($query) use ($usuarioId) {
            $query->where('usuario_id', $usuarioId)
                  ->orWhereNull('usuario_id');
        })
        ->where('leida', 0)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    $totalNoLeidas = $notificaciones->count();
@endphp

<div class="dropdown">
    <button class="btn btn-link position-relative p-2" type="button" data-bs-toggle="dropdown" aria-label="Notificaciones">
        <i class="fas fa-bell fa-lg text-dark"></i>
        @if($totalNoLeidas > 0)
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            {{ $totalNoLeidas > 9 ? '9+' : $totalNoLeidas }}
            <span class="visually-hidden">notificaciones no leídas</span>
        </span>
        @endif
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end shadow" style="width: 380px; max-height: 500px; overflow-y: auto;">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <strong>Notificaciones</strong>
            @if($totalNoLeidas > 0)
                <span class="badge bg-danger">{{ $totalNoLeidas }}</span>
            @endif
        </li>
        <li><hr class="dropdown-divider"></li>
        
        @forelse($notificaciones as $notif)
        <li>
            <a class="dropdown-item py-3" 
               href="{{ $notif->url ?? '#' }}"
               onclick="marcarComoLeidaDropdown({{ $notif->id }})">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle bg-{{ $notif->color }}-subtle p-2">
                            <i class="fas {{ $notif->icono }} text-{{ $notif->color }}"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 fw-bold" style="font-size: 0.875rem;">{{ $notif->titulo }}</h6>
                        <p class="mb-1 text-muted small">{{ Str::limit($notif->mensaje, 80) }}</p>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            <i class="fas fa-clock me-1"></i>
                            {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        @empty
        <li>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-bell-slash fa-2x mb-2"></i>
                <p class="mb-0 small">No hay notificaciones nuevas</p>
            </div>
        </li>
        @endforelse
        
        @if($totalNoLeidas > 0)
        <li>
            <a class="dropdown-item text-center text-primary fw-bold" href="{{ route('admin.notificaciones.index') }}">
                Ver todas las notificaciones
            </a>
        </li>
        @endif
    </ul>
</div>

@push('scripts')
<script>
function marcarComoLeidaDropdown(id) {
    fetch(`/admin/notificaciones/${id}/marcar-leida`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    });
}
</script>
@endpush
