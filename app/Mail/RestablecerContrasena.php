<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Correo con el enlace para restablecer la contraseña. Lo envía
 * User::sendPasswordResetNotification cuando el usuario lo pide en /olvide-contrasena.
 */
class RestablecerContrasena extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $token  Token en claro; en la base solo queda su hash.
     */
    public function __construct(public User $usuario, public string $token)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablece tu contraseña de '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.restablecer-contrasena',
            with: [
                'enlace' => route('password.reset', ['token' => $this->token, 'email' => $this->usuario->email]),
                'minutos' => config('auth.passwords.users.expire'),
            ],
        );
    }
}
