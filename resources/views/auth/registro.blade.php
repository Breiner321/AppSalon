{{-- Formulario de registro de clientes (A3). Lo valida App\Http\Requests\RegisterRequest. --}}
@extends('layouts.app')

@section('title', 'Crear cuenta')

@section('content')
    <div class="card card-form">
        <div class="card-body p-4">
            <h1 class="h3 text-center mb-4">Crear cuenta</h1>

            <form method="POST" action="{{ route('registro') }}" novalidate>
                @csrf

                <p class="form-text mt-0 mb-3">Los campos marcados con <span class="text-danger">*</span> son obligatorios.</p>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="nombre" class="form-label requerido">Nombre</label>
                        <input type="text" name="nombre" id="nombre" maxlength="60" required autofocus
                               autocomplete="given-name"
                               class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre') }}">
                        <x-form-error field="nombre" />
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="apellido" class="form-label requerido">Apellido</label>
                        <input type="text" name="apellido" id="apellido" maxlength="60" required
                               autocomplete="family-name"
                               class="form-control @error('apellido') is-invalid @enderror"
                               value="{{ old('apellido') }}">
                        <x-form-error field="apellido" />
                    </div>

                    <div class="col-12">
                        <label for="email" class="form-label requerido">Correo electrónico</label>
                        <input type="email" name="email" id="email" maxlength="30" required
                               autocomplete="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}">
                        <x-form-error field="email" />
                    </div>

                    <div class="col-12">
                        <label for="telefono" class="form-label">
                            Teléfono <span class="text-body-secondary">(opcional)</span>
                        </label>
                        <input type="tel" name="telefono" id="telefono" maxlength="10" inputmode="numeric"
                               autocomplete="tel" aria-describedby="telefono-ayuda"
                               class="form-control @error('telefono') is-invalid @enderror"
                               value="{{ old('telefono') }}">
                        <div id="telefono-ayuda" class="form-text">10 dígitos, sin espacios.</div>
                        <x-form-error field="telefono" />
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="password" class="form-label requerido">Contraseña</label>
                        <input type="password" name="password" id="password" minlength="6" required
                               autocomplete="new-password"
                               class="form-control @error('password') is-invalid @enderror">
                        <x-form-error field="password" />
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="password_confirmation" class="form-label requerido">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               autocomplete="new-password" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-4">
                    <i class="bi bi-person-plus" aria-hidden="true"></i> Crear cuenta
                </button>
            </form>

            <p class="text-center mt-3 mb-0">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
            </p>
        </div>
    </div>
@endsection
