{{-- Panel de administración. Por ahora es un marcador (A5); el contenido real lo define el Dev 2. --}}
@extends('layouts.app')

@section('title', 'Panel de administración')

@section('content')
    <h1 class="mb-2">Panel de administración</h1>
    <p class="text-body-secondary mb-4">Hola, {{ auth()->user()->nombre }}.</p>

    <div class="alert alert-info" role="status">
        <i class="bi bi-cone-striped" aria-hidden="true"></i> Panel en construcción
    </div>
@endsection
