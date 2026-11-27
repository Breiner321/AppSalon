<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deja pasar al personal del salón: administradores y empleados (alias
 * 'personal' en bootstrap/app.php). Protege el panel, la agenda y las citas;
 * el resto del panel (usuarios, servicios, horarios y reportes) sigue con 'admin'.
 */
class PersonalMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->esPersonal()) {
            abort(403);
        }

        return $next($request);
    }
}
