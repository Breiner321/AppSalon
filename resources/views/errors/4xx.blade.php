{{-- Respaldo para cualquier error 4xx sin vista propia (401, 405, 413…), para que no salga
     la página en inglés de Laravel. Ejemplo: abrir /logout en el navegador (GET en vez de POST) da 405.
     Los errores frecuentes (403, 404, 419, 429) tienen su propia vista. --}}
@extends('layouts.app')

@section('title', 'No pudimos completar la solicitud')

@section('content')
    <div class="text-center py-5">
        <i class="bi bi-exclamation-circle display-1 text-primary" aria-hidden="true"></i>
        <h1 class="mt-3">No pudimos completar la solicitud</h1>
        <p class="text-body-secondary mb-4">
            La página no se puede abrir de esta forma (código {{ $exception->getStatusCode() }}).
            Vuelve al inicio y usa los botones y enlaces del sitio.
        </p>
        <a href="{{ route('servicios.catalogo') }}" class="btn btn-primary">
            <i class="bi bi-scissors" aria-hidden="true"></i> Volver a los servicios
        </a>
    </div>
@endsection
