{{-- Error 403: el usuario no tiene permiso, por ejemplo un cliente que entra al panel de administración. --}}
@extends('layouts.app')

@section('title', 'Acceso denegado')

@section('content')
    <div class="text-center py-5">
        <i class="bi bi-shield-lock display-1 text-primary" aria-hidden="true"></i>
        <h1 class="mt-3">No tienes permiso para ver esta página</h1>
        <p class="text-body-secondary mb-4">
            Esta sección es solo para el personal del salón. Si crees que es un error, habla con un administrador.
        </p>
        <a href="{{ route('servicios.catalogo') }}" class="btn btn-primary">
            <i class="bi bi-scissors" aria-hidden="true"></i> Volver a los servicios
        </a>
    </div>
@endsection
