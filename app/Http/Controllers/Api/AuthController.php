<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Autenticación de la API con Laravel Sanctum (tokens de acceso personal).
 *
 * El cliente envía su correo y contraseña a POST /api/login y recibe un token.
 * En las demás peticiones lo manda en la cabecera Authorization: Bearer <token>.
 */
class AuthController extends Controller
{
    /**
     * Emite un token si las credenciales son correctas y la cuenta está
     * confirmada y activa. Igual que en la web, el mensaje de error no dice si
     * el correo existe.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $usuario = User::where('email', $request->input('email'))->first();

        if (! $usuario || ! Hash::check($request->input('password'), $usuario->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        if (! $usuario->confirmado) {
            return response()->json(['message' => 'Confirma tu cuenta desde el enlace enviado a tu correo'], 403);
        }

        if (! $usuario->isActivo()) {
            return response()->json(['message' => 'Tu cuenta está desactivada. Comunícate con el salón.'], 403);
        }

        $nombreDispositivo = substr((string) $request->input('dispositivo', 'api'), 0, 60);
        $token = $usuario->createToken($nombreDispositivo);

        return response()->json([
            'message' => "Bienvenido, {$usuario->nombre}",
            'data' => [
                'token' => $token->plainTextToken,
                'tipo' => 'Bearer',
                'expiraEl' => $token->accessToken->expires_at?->toIso8601String(),
                'usuario' => new UsuarioResource($usuario),
            ],
        ]);
    }

    /**
     * Datos del usuario dueño del token.
     */
    public function yo(Request $request): JsonResponse
    {
        return response()->json(['data' => new UsuarioResource($request->user())]);
    }

    /**
     * Revoca el token con el que se hizo la petición.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada. El token ya no es válido.']);
    }
}
