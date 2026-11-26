<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Las respuestas JSON de la API se envían con los acentos y la ñ tal cual
 * ("está" en lugar de "está"). Va en el grupo de middleware 'api'.
 */
class JsonSinEscaparUnicode
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        if ($respuesta instanceof JsonResponse) {
            $respuesta->setEncodingOptions($respuesta->getEncodingOptions() | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $respuesta;
    }
}
