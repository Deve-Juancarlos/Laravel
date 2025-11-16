@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Buscar producto para Kardex</h3>

    <form action="{{ route('admin.reportes.kardex-producto') }}" method="GET">
        <label>Seleccione el producto</label>
        <select name="codigo" class="form-control select2" required>
            <option value="">-- Seleccione --</option>
            @foreach ($productos as $p)
                <option value="{{ $p->CodPro }}">{{ $p->CodPro }} - {{ $p->Nombre }}</option>
            @endforeach
        </select>

        <button class="btn btn-primary mt-3">Ver Kardex</button>
    </form>
</div>
@endsection
