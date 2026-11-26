<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Correo al cliente cuando su cita se reserva, se modifica, se confirma o se cancela.
 *
 * Con MAIL_MAILER=log el correo queda escrito en storage/logs/laravel.log.
 */
class CitaNotificacion extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Asunto y frase principal de cada tipo de aviso.
     */
    public const TIPOS = [
        'reservada' => ['asunto' => 'Recibimos tu cita', 'texto' => 'Tu cita quedó reservada. El salón la confirmará pronto.'],
        'modificada' => ['asunto' => 'Tu cita cambió', 'texto' => 'Guardamos los cambios de tu cita. El salón la confirmará de nuevo.'],
        'confirmada' => ['asunto' => 'Tu cita está confirmada', 'texto' => 'El salón confirmó tu cita. ¡Te esperamos!'],
        'cancelada' => ['asunto' => 'Tu cita fue cancelada', 'texto' => 'Tu cita fue cancelada y su horario quedó libre.'],
    ];

    /**
     * @param  Appointment  $cita  Cita con sus servicios cargados.
     * @param  string  $tipo  reservada, modificada, confirmada o cancelada.
     */
    public function __construct(public Appointment $cita, public string $tipo)
    {
        //
    }

    /**
     * Asunto del correo según el tipo.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: self::TIPOS[$this->tipo]['asunto'].' · '.config('app.name'),
        );
    }

    /**
     * Vista del correo con el detalle de la cita.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.cita',
            with: [
                'texto' => self::TIPOS[$this->tipo]['texto'],
                'enlace' => route('citas.index'),
            ],
        );
    }
}
