<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnviarDocumentoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.documento_adjunto')
                    ->subject($this->data['asunto'])
                    ->with([
                        'titulo' => $this->data['titulo'],
                        'cuerpo' => $this->data['cuerpo'],
                    ])
                    ->attachData($this->data['pdf'], $this->data['nombreArchivo'], [
                        'mime' => 'application/pdf',
                    ]);
    }
}