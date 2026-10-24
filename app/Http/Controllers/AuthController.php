<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * Registro, inicio y cierre de sesión de los clientes del salón.
 */
class AuthController extends Controller
{
    /**
     * Muestra el formulario de registro.
     */
    public function showRegister(): View
    {
        return view('auth.registro');
    }

    /**
     * Crea la cuenta de un cliente nuevo y lo envía al login.
     *
     * Solo se guardan los campos validados; admin y confirmado quedan con su
     * valor por defecto (false). El cast 'hashed' del modelo aplica bcrypt.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            User::create($request->safe()->only(['nombre', 'apellido', 'email', 'password', 'telefono']));
        } catch (Throwable $e) {
            Log::error('No se pudo registrar el usuario', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'No pudimos crear tu cuenta. Intenta de nuevo en unos minutos.');
        }

        return redirect()
            ->route('login')
            ->with('success', 'Cuenta creada, ya puedes iniciar sesión');
    }

    /**
     * Muestra el formulario de inicio de sesión.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Autentica al usuario con email y contraseña.
     */
    public function login()
    {
        // TODO A4: validar con LoginRequest, Auth::attempt y regenerar la sesión
        abort(501);
    }

    /**
     * Cierra la sesión del usuario actual.
     */
    public function logout()
    {
        // TODO A4: Auth::logout, invalidar la sesión y regenerar el token CSRF
        abort(501);
    }
}
