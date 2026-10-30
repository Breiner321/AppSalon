{{-- Error 404: la página o el registro solicitado no existe. --}}
@extends('layouts.app')

@section('title', 'Página no encontrada')

@section('content')
    <div class="text-center py-5">
        <i class="bi bi-signpost-split display-1 text-primary" aria-hidden="true"></i>
        <h1 class="mt-3">No encontramos esta página</h1>
        <p class="text-body-secondary mb-4">
            Es posible que la dirección esté mal escrita o que la página ya no exista.
        </p>
        <a href="{{ route('servicios.catalogo') }}" class="btn btn-primary">
            <i class="bi bi-scissors" aria-hidden="true"></i> Ver servicios
        </a>
    </div>
@endsection
