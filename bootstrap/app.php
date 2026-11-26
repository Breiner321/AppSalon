<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\JsonSinEscaparUnicode;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 'admin': solo administradores. 'activo': saca a las cuentas desactivadas.
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'activo' => EnsureUserIsActive::class,
        ]);

        // JSON de la API con acentos legibles.
        $middleware->api(append: [JsonSinEscaparUnicode::class]);

        // Invitado en una ruta con 'auth': al formulario de login (en la API, 401 en JSON).
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : route('login'));

        // Usuario con sesión en una ruta 'guest' (login, registro): a su inicio según el rol.
        $middleware->redirectUsersTo(fn (Request $request) => AuthController::inicioSegunRol($request->user()));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Las rutas /api/* siempre responden JSON, aunque el cliente no envíe Accept: application/json.
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());

        // Mensajes en español y con la misma forma {"message": "..."} para los errores de la API.
        $mensajes = [
            AuthenticationException::class => [401, 'No autenticado. Envía el token en la cabecera Authorization: Bearer <token>.'],
            AuthorizationException::class => [403, 'No tienes permiso para realizar esta acción.'],
            AccessDeniedHttpException::class => [403, 'No tienes permiso para realizar esta acción.'],
            NotFoundHttpException::class => [404, 'Recurso no encontrado.'],
            MethodNotAllowedHttpException::class => [405, 'Método HTTP no permitido en esta ruta.'],
            TooManyRequestsHttpException::class => [429, 'Demasiadas peticiones. Espera un momento e intenta de nuevo.'],
        ];

        foreach ($mensajes as $clase => [$codigo, $mensaje]) {
            $exceptions->render(function (Throwable $e, Request $request) use ($clase, $codigo, $mensaje) {
                if ($e instanceof $clase && $request->is('api/*')) {
                    return response()->json(['message' => $mensaje], $codigo, $e instanceof TooManyRequestsHttpException ? $e->getHeaders() : [], JSON_UNESCAPED_UNICODE);
                }
            });
        }
    })->create();
