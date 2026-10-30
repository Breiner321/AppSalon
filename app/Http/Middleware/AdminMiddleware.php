<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deja pasar solo a los administradores (alias 'admin' en bootstrap/app.php).
 *
 * Va después de 'auth', que ya envía a los invitados al login; aun así, si
 * llega una petición sin usuario también se rechaza con 403.
 */
class AdminMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
