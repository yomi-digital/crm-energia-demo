<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Incentivo;

class IncentivoEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Incentivo $incentivo;

    /**
     * Create a new message instance.
     */
    public function __construct(Incentivo $incentivo)
    {
        $this->incentivo = $incentivo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Calcolo Incentivo Fotovoltaico - ' . $this->incentivo->nominativo,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.incentivo',
            with: [
                'incentivo' => $this->incentivo,
                'nominativo' => $this->incentivo->nominativo,
                'email' => $this->incentivo->email,
                'citta' => $this->incentivo->citta,
                'provincia' => $this->incentivo->provincia,
                'incentivo_cer' => $this->incentivo->incentivo_cer,
                'incentivo_dedicated' => $this->incentivo->incentivo_dedicated,
                'incentivo_pod' => $this->incentivo->incentivo_pod,
                'autoconsume_savings' => $this->incentivo->autoconsume_savings,
                'hasPanels' => $this->incentivo->hasPanels,
                'periodoBolletta' => $this->incentivo->periodoBolletta,
                'kwhSpesi' => $this->incentivo->kwhSpesi,
                'spesaBollettaMensile' => $this->incentivo->spesaBollettaMensile,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
