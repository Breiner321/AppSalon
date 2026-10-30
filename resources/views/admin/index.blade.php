{{-- Panel de administración: accesos a las secciones del panel.
     Recibe $totalServicios y $serviciosActivos desde AdminController@index. --}}
@extends('layouts.app')

@section('title', 'Panel de administración')

@section('content')
    <h1 class="mb-2">Panel de administración</h1>
    <p class="text-body-secondary mb-4">Hola, {{ auth()->user()->nombre }}.</p>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <div class="col">
            <article class="card card-servicio h-100">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5 card-title">
                        <i class="bi bi-scissors" aria-hidden="true"></i> Servicios
                    </h2>
                    <p class="card-text text-body-secondary">
                        {{ $serviciosActivos }} activos de {{ $totalServicios }} registrados.
                    </p>
                    <a href="{{ route('admin.servicios.index') }}" class="btn btn-primary mt-auto align-self-start">
                        Administrar servicios
                    </a>
                </div>
            </article>
        </div>
    </div>
@endsection
