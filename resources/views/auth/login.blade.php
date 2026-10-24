{{-- Formulario de inicio de sesión. La lógica de autenticación llega en A4. --}}
@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
    <div class="card card-form">
        <div class="card-body p-4">
            <h1 class="h3 text-center mb-4">Iniciar sesión</h1>

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" name="email" id="email" maxlength="30" required autofocus
                           autocomplete="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}">
                    <x-form-error field="email" />
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" name="password" id="password" required
                           autocomplete="current-password"
                           class="form-control @error('password') is-invalid @enderror">
                    <x-form-error field="password" />
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2">
                    <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Iniciar sesión
                </button>
            </form>

            <p class="text-center mt-3 mb-0">
                ¿No tienes cuenta? <a href="{{ route('registro') }}">Regístrate</a>
            </p>
        </div>
    </div>
@endsection
