@extends('layouts.app')

@section('title', 'Acceso Denegado')

@section('content')
<div class="container text-center mt-5">
    <h1 class="display-4 text-danger">403 - Acceso Denegado</h1>
    <p class="lead">No tienes permisos para acceder a esta sección.</p>
    <a href="{{ route('contabilidad.dashboard') }}" class="btn btn-primary mt-3">Volver al Dashboard</a>
</div>
@endsection
