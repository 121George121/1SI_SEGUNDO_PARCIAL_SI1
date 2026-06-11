<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacionSistemaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $titulo;
    public $mensaje;

    /**
     * Create a new message instance.
     *
     * @param string $titulo
     * @param string $mensaje
     */
    public function __construct(string $titulo, string $mensaje)
    {
        $this->titulo = $titulo;
        $this->mensaje = $mensaje;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->titulo)
            ->view('emails.notificacion_sistema');
    }
}
