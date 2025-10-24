@extends('layouts.app')

@section('title', '🚫 403 - Acceso Denegado')

@section('content')
<div class="container text-center mt-5">

    <h1 class="display-4 text-danger">🚫 403 - Acceso Denegado</h1>
    <p class="lead">No tienes permisos para acceder a esta sección.</p>

    {{-- ✅ Mensaje de error detallado --}}
    @if(isset($exception) && $exception->getMessage())
        <div class="alert alert-warning mt-3">
            <strong>Mensaje técnico:</strong> {{ $exception->getMessage() }}
        </div>
    @else
        <div class="alert alert-secondary mt-3">
            <strong>Mensaje técnico:</strong> No se encontró objeto <code>$exception</code> en la vista.
        </div>
    @endif

    {{-- 🧠 Depuración técnica --}}
    <div class="card mt-4 text-start mx-auto shadow-sm" style="max-width: 700px;">
        <div class="card-header bg-dark text-white">
            🧠 Depuración técnica
        </div>
        <div class="card-body">
            <p><strong>Ruta actual:</strong> {{ request()->path() }}</p>
            <p><strong>URL completa:</strong> {{ request()->fullUrl() }}</p>
            <p><strong>Método HTTP:</strong> {{ request()->method() }}</p>
            <p><strong>IP del cliente:</strong> {{ request()->ip() }}</p>

            @php
                $user = Auth::user();
            @endphp

            <p><strong>Usuario logueado:</strong> {{ $user->usuario ?? 'N/A' }}</p>
            <p><strong>Rol detectado:</strong> {{ $user->tipousuario ?? 'N/A' }}</p>
            <p><strong>ID Usuario:</strong> {{ $user->idusuario ?? 'N/A' }}</p>
        </div>
    </div>

    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary mt-4">
        ← Volver atrás
    </a>
    <a href="{{ route('contabilidad.dashboard') }}" class="btn btn-primary mt-4">
        Ir al Dashboard
    </a>

</div>
@endsection
