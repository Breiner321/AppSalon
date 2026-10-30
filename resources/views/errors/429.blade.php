{{-- Error 429: demasiados intentos seguidos (throttle en login y registro). --}}
@extends('layouts.app')

@section('title', 'Demasiados intentos')

@section('content')
    <div class="text-center py-5">
        <i class="bi bi-stopwatch display-1 text-primary" aria-hidden="true"></i>
        <h1 class="mt-3">Demasiados intentos</h1>
        <p class="text-body-secondary mb-4">
            Hiciste muchos intentos en poco tiempo. Espera un minuto y vuelve a intentarlo.
        </p>
        <a href="{{ route('servicios.catalogo') }}" class="btn btn-primary">
            <i class="bi bi-scissors" aria-hidden="true"></i> Volver a los servicios
        </a>
    </div>
@endsection
