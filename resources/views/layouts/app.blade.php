{{-- Layout principal de AppSalon. Las vistas lo extienden con @extends('layouts.app')
     y definen @section('title') y @section('content'). --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') · @endif{{ config('app.name', 'AppSalon') }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('servicios.catalogo') }}">
                <i class="bi bi-scissors" aria-hidden="true"></i> {{ config('app.name', 'AppSalon') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                    aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Menú según el rol: invitado, cliente o administrador. Colapsa en pantallas < lg. --}}
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('servicios.catalogo', 'inicio')) active @endif"
                           href="{{ route('servicios.catalogo') }}"
                           @if (request()->routeIs('servicios.catalogo', 'inicio')) aria-current="page" @endif>
                            <i class="bi bi-grid" aria-hidden="true"></i> Servicios
                        </a>
                    </li>

                    @guest
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('login')) active @endif" href="{{ route('login') }}"
                               @if (request()->routeIs('login')) aria-current="page" @endif>
                                <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Iniciar sesión
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('registro')) active @endif" href="{{ route('registro') }}"
                               @if (request()->routeIs('registro')) aria-current="page" @endif>
                                <i class="bi bi-person-plus" aria-hidden="true"></i> Registrarse
                            </a>
                        </li>
                    @endguest

                    @auth
                        @if (auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link @if (request()->routeIs('admin.index')) active @endif" href="{{ route('admin.index') }}"
                                   @if (request()->routeIs('admin.index')) aria-current="page" @endif>
                                    <i class="bi bi-speedometer2" aria-hidden="true"></i> Panel
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (request()->routeIs('admin.servicios.*')) active @endif"
                                   href="{{ route('admin.servicios.index') }}"
                                   @if (request()->routeIs('admin.servicios.*')) aria-current="page" @endif>
                                    <i class="bi bi-list-check" aria-hidden="true"></i> Gestionar servicios
                                </a>
                            </li>
                        @endif

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="menuUsuario" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle" aria-hidden="true"></i> {{ auth()->user()->nombre }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuUsuario">
                                <li>
                                    <span class="dropdown-item-text small text-body-secondary">{{ auth()->user()->email }}</span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Cerrar sesión
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4 flex-grow-1">
        <x-alert />

        @yield('content')
    </main>

    <footer class="footer-salon py-3 mt-auto">
        <div class="container text-center small">
            &copy; {{ date('Y') }} {{ config('app.name', 'AppSalon') }} · Salón de belleza
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Las alertas flash (<x-alert />) se cierran solas a los 5 segundos.
        document.querySelectorAll('.alert[data-autocierre]').forEach(function (alerta) {
            setTimeout(function () { bootstrap.Alert.getOrCreateInstance(alerta).close(); }, 5000);
        });
    </script>
    {{-- Scripts propios de cada vista, con @push('scripts'). Van después de Bootstrap. --}}
    @stack('scripts')
</body>
</html>
