{{-- Elegir la contraseña nueva desde el enlace del correo (PasswordResetController@formulario).
     Recibe $token y $email. --}}
@extends('layouts.app')

@section('title', 'Nueva contraseña')

@section('content')
    <div class="card card-form">
        <div class="card-body p-4">
            <h1 class="h3 text-center mb-4">Elige una contraseña nueva</h1>

            <form method="POST" action="{{ route('password.update') }}" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label requerido">Correo electrónico</label>
                    <input type="email" name="email" id="email" maxlength="30" required autocomplete="email"
                           class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $email) }}">
                    <x-form-error field="email" />
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label requerido">Contraseña nueva</label>
                    <input type="password" name="password" id="password" required autocomplete="new-password" minlength="6"
                           aria-describedby="password-ayuda"
                           class="form-control @error('password') is-invalid @enderror">
                    <div id="password-ayuda" class="form-text">Mínimo 6 caracteres.</div>
                    <x-form-error field="password" />
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label requerido">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                           class="form-control">
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2">
                    <i class="bi bi-check-lg" aria-hidden="true"></i> Guardar contraseña
                </button>
            </form>
        </div>
    </div>
@endsection
