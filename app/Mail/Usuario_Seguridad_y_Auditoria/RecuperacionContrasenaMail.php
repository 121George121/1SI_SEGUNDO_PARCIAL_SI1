<?php

namespace App\Mail\Usuario_Seguridad_y_Auditoria;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecuperacionContrasenaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $codigo,
        public string $nombreUsuario = 'Usuario',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Codigo de recuperacion de contrasena',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.Usuario_Seguridad_y_Auditoria.recuperacion-contrasena',
        );
    }
}
