@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Historial de Notificaciones')
@section('page-title', 'Historial de Notificaciones')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($notificaciones as $notif)
                            <li class_group="list-group-item d-flex align-items-center p-3 {{ $notif->leida ? 'bg-light text-muted' : '' }}">
                                <i class="fas {{ $notif->icono ?? 'fa-info-circle' }} fa-2x me-3" style="color: {{ $notif->color ?? '#ccc' }};"></i>
                                <div class="w-100">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">{{ $notif->titulo }}</h5>
                                        <small>{{ \Carbon\Carbon::parse($notif->created_at)->format('d/m/Y h:i A') }}</small>
                                    </div>
                                    <p class="mb-1">{{ $notif->mensaje }}</p>
                                    @if($notif->url)
                                        <a href="{{ $notif->url }}" class="stretched-link">Ir a ver</a>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center p-5">
                                <i class="fas fa-envelope-open fa-3x text-muted mb-3"></i>
                                <h5>No tienes notificaciones.</h5>
                            </li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer">
                    {{ $notificaciones->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection