<?php

use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Invitado en una ruta con 'auth': al formulario de login.
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Usuario con sesión en una ruta 'guest' (login, registro): a su inicio según el rol.
        $middleware->redirectUsersTo(fn (Request $request) => AuthController::inicioSegunRol($request->user()));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
