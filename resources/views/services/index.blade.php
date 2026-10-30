{{-- Catálogo público de servicios (U4): tarjetas con los servicios activos.
     Recibe $servicios desde ServiceController@catalog. --}}
@extends('layouts.app')

@section('title', 'Servicios')

@section('content')
    <header class="mb-4">
        <h1 class="mb-2">Nuestros servicios</h1>
        <p class="text-body-secondary mb-0">Elige lo que necesitas y agenda tu cita.</p>
    </header>

    @guest
        <div class="cta-registro d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 p-3 mb-4">
            <div>
                <h2 class="h5 mb-1">¿Primera vez en el salón?</h2>
                <p class="mb-0 text-body-secondary">Crea tu cuenta gratis para reservar tus citas en línea.</p>
            </div>
            <a href="{{ route('registro') }}" class="btn btn-primary flex-shrink-0">
                <i class="bi bi-person-plus" aria-hidden="true"></i> Registrarse
            </a>
        </div>
    @endguest

    @if ($servicios->isEmpty())
        <div class="estado-vacio text-center p-5" role="status">
            <i class="bi bi-calendar-x display-4 text-primary" aria-hidden="true"></i>
            <h2 class="h5 mt-3">Aún no hay servicios disponibles</h2>
            <p class="text-body-secondary mb-0">Vuelve pronto: estamos preparando nuestro catálogo.</p>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach ($servicios as $servicio)
                <div class="col">
                    <article class="card card-servicio h-100">
                        <div class="card-body d-flex flex-column">
                            <h2 class="h5 card-title">{{ $servicio->nombre }}</h2>
                            @if ($servicio->descripcion)
                                <p class="card-text text-body-secondary">{{ $servicio->descripcion }}</p>
                            @endif
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                <span class="text-body-secondary small">
                                    <i class="bi bi-clock" aria-hidden="true"></i> {{ $servicio->duracion }} min
                                </span>
                                <span class="precio fs-5">${{ number_format((float) $servicio->precio, 2) }}</span>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    @endif
@endsection
