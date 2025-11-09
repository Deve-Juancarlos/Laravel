@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">

    <!-- ▼▼▼ NAVEGACIÓN DE REPORTES DE AUDITORÍA ▼▼▼ -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white p-0">
            <ul class="nav nav-tabs nav-fill" id="auditoriaTab" role="tablist">
                
                <li class="nav-item" role="presentation">
                    <a class="nav-link fs-5 fw-bold p-3 {{ request()->routeIs('contador.reportes.auditoria.libro_diario') ? 'active' : '' }}"
                       href="{{ route('contador.reportes.auditoria.libro_diario') }}">
                        <i class="fas fa-book me-1"></i> Auditoría de Libro Diario
                    </a>
                </li>
                
                <li class="nav-item" role="presentation">
                    <a class="nav-link fs-5 fw-bold p-3 {{ request()->routeIs('contador.reportes.auditoria.sistema_general') ? 'active' : '' }}"
                       href="{{ route('contador.reportes.auditoria.sistema_general') }}">
                        <i class="fas fa-history me-1"></i> Auditoría General del Sistema
                    </a>
                </li>

            </ul>
        </div>
    </div>
    
    <div class="tab-content">
        @yield('audit-content') {{-- <--- ¡¡ESTE ES EL NOMBRE CLAVE!! --}}
    </div>
    
</div>
@endsection