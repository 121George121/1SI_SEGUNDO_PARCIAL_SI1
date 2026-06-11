<?php

namespace App\Mail\Gestion_Financiera;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ComprobantePagoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pago;
    public $pdfPath;

    /**
     * Create a new message instance.
     *
     * @param object $pago
     * @param string $pdfPath
     */
    public function __construct(object $pago, string $pdfPath)
    {
        $this->pago = $pago;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Comprobante de Pago de Inscripción - CUP FICCT')
            ->view('emails.comprobante_pago')
            ->attach($this->pdfPath, [
                'as' => 'Comprobante_Pago_' . ($this->pago->nro_comprobante ?? 'pago') . '.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
