{{-- Pedir el enlace para restablecer la contraseña (PasswordResetController@solicitar). --}}
@extends('layouts.app')

@section('title', 'Recuperar contraseña')

@section('content')
    <div class="card card-form">
        <div class="card-body p-4">
            <h1 class="h3 text-center mb-2">¿Olvidaste tu contraseña?</h1>
            <p class="text-body-secondary text-center mb-4">Escribe el correo de tu cuenta y te enviaremos un enlace para elegir una nueva.</p>

            <form method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label requerido">Correo electrónico</label>
                    <input type="email" name="email" id="email" maxlength="30" required autofocus autocomplete="email"
                           class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                    <x-form-error field="email" />
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2">
                    <i class="bi bi-envelope" aria-hidden="true"></i> Enviar enlace
                </button>
            </form>

            <p class="text-center mt-3 mb-0"><a href="{{ route('login') }}">Volver a iniciar sesión</a></p>
        </div>
    </div>
@endsection
