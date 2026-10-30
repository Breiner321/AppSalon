<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Correo con el enlace para confirmar una cuenta nueva (A7).
 *
 * Con MAIL_MAILER=log no se envía de verdad: el correo completo, con el
 * enlace, queda escrito en storage/logs/laravel.log.
 */
class ConfirmarCuenta extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  User  $usuario  Usuario recién registrado; su token ya está guardado.
     */
    public function __construct(public User $usuario)
    {
        //
    }

    /**
     * Asunto del correo.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirma tu cuenta en '.config('app.name'),
        );
    }

    /**
     * Vista del correo y el enlace de confirmación (GET /confirmar/{token}).
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmar-cuenta',
            with: [
                'enlace' => route('confirmar', $this->usuario->token),
            ],
        );
    }
}
