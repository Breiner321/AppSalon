{{-- Error 419: el token CSRF venció, normalmente porque el formulario estuvo abierto mucho tiempo. --}}
@extends('layouts.app')

@section('title', 'La página expiró')

@section('content')
    <div class="text-center py-5">
        <i class="bi bi-hourglass-bottom display-1 text-primary" aria-hidden="true"></i>
        <h1 class="mt-3">La página expiró</h1>
        <p class="text-body-secondary mb-4">
            Por seguridad, los formularios caducan después de un tiempo sin uso.
            Vuelve a la página anterior, recárgala y envía el formulario otra vez.
        </p>
        <a href="{{ route('servicios.catalogo') }}" class="btn btn-primary">
            <i class="bi bi-scissors" aria-hidden="true"></i> Volver a los servicios
        </a>
    </div>
@endsection
