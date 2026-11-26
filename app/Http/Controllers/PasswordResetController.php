<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * Recuperación de contraseña por correo, con el "password broker" de Laravel.
 *
 * 1. El usuario escribe su correo y recibe un enlace (válido 60 minutos).
 * 2. El enlace abre el formulario para elegir la contraseña nueva.
 * Los tokens se guardan cifrados en la tabla password_reset_tokens.
 */
class PasswordResetController extends Controller
{
    /**
     * Mensaje que se muestra siempre después de pedir el enlace, exista o no
     * el correo, para no revelar qué cuentas están registradas.
     */
    public const MENSAJE_ENVIADO = 'Si el correo está registrado, te enviamos un enlace para restablecer la contraseña. Revisa tu bandeja de entrada.';

    /**
     * Formulario para pedir el enlace.
     */
    public function solicitar(): View
    {
        return view('auth.olvide-contrasena');
    }

    /**
     * Envía el enlace (User::sendPasswordResetNotification). El broker no
     * reenvía otro enlace al mismo correo antes de 60 segundos.
     */
    public function enviar(Request $request): RedirectResponse
    {
        $request->validate(
            ['email' => ['required', 'email', 'max:30']],
            [
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'Escribe un correo electrónico válido.',
                'email.max' => 'El correo electrónico no debe tener más de 30 caracteres.',
            ],
        );

        Password::sendResetLink($request->only('email'));

        return redirect()->route('login')->with('info', self::MENSAJE_ENVIADO);
    }

    /**
     * Formulario para elegir la contraseña nueva (enlace del correo).
     */
    public function formulario(Request $request, string $token): View
    {
        return view('auth.restablecer-contrasena', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    /**
     * Cambia la contraseña. Como el enlace llegó a su correo, la cuenta queda
     * además confirmada. Se cierran sus tokens de la API por seguridad.
     */
    public function restablecer(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Escribe un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $estado = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $usuario, string $password) {
                $usuario->password = $password; // el cast 'hashed' aplica bcrypt
                $usuario->forceFill(['confirmado' => true, 'token' => null])->save();
                $usuario->tokens()->delete();
            }
        );

        if ($estado !== Password::PASSWORD_RESET) {
            // Token vencido o de otro correo: el mismo mensaje en los dos casos.
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('passwords.token')]);
        }

        return redirect()->route('login')->with('success', 'Contraseña actualizada. Ya puedes iniciar sesión.');
    }
}
