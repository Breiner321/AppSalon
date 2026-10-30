{{-- Panel de administración (U7): conteos y accesos rápidos.
     Recibe $serviciosActivos, $serviciosInactivos y $usuariosRegistrados desde AdminController@index. --}}
@extends('layouts.app')

@section('title', 'Panel de administración')

@section('content')
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item active" aria-current="page">Panel</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="mb-1">Panel de administración</h1>
            <p class="text-body-secondary mb-0">Hola, {{ auth()->user()->nombre }}. Este es el resumen del salón.</p>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2">
            <a href="{{ route('admin.servicios.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo servicio
            </a>
            <a href="{{ route('admin.servicios.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-list-check" aria-hidden="true"></i> Ver servicios
            </a>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <article class="card card-estadistica h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icono-estadistica text-success"><i class="bi bi-check-circle" aria-hidden="true"></i></span>
                    <div>
                        <p class="numero-estadistica mb-0" data-test="servicios-activos">{{ $serviciosActivos }}</p>
                        <h2 class="h6 text-body-secondary mb-0">Servicios activos</h2>
                    </div>
                </div>
            </article>
        </div>
        <div class="col">
            <article class="card card-estadistica h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icono-estadistica text-secondary"><i class="bi bi-eye-slash" aria-hidden="true"></i></span>
                    <div>
                        <p class="numero-estadistica mb-0" data-test="servicios-inactivos">{{ $serviciosInactivos }}</p>
                        <h2 class="h6 text-body-secondary mb-0">Servicios inactivos</h2>
                    </div>
                </div>
            </article>
        </div>
        <div class="col">
            <article class="card card-estadistica h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icono-estadistica text-primary"><i class="bi bi-people" aria-hidden="true"></i></span>
                    <div>
                        <p class="numero-estadistica mb-0" data-test="usuarios-registrados">{{ $usuariosRegistrados }}</p>
                        <h2 class="h6 text-body-secondary mb-0">Usuarios registrados</h2>
                    </div>
                </div>
            </article>
        </div>
    </div>
@endsection
