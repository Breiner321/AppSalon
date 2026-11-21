<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Saca a un usuario cuya cuenta fue desactivada mientras tenía la sesión abierta
 * (alias 'activo' en bootstrap/app.php).
 *
 * En la web cierra la sesión y lo lleva al login con un aviso. En la API
 * responde 403 en JSON.
 */
class EnsureUserIsActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario || $usuario->isActivo()) {
            return $next($request);
        }

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json(['message' => 'Tu cuenta está desactivada. Comunícate con el salón.'], 403);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'Tu cuenta está desactivada. Comunícate con el salón.');
    }
}
