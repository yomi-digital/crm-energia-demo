<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\Communication;

class CommunicationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $communication;

    /**
     * Create a new message instance.
     */
    public function __construct(Communication $communication)
    {
        $this->communication = $communication;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuova Comunicazione - ' . $this->communication->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.communication',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Carica i documenti associati alla comunicazione
        foreach ($this->communication->documents as $document) {
            try {
                // Crea l'allegato dal file su DigitalOcean Spaces
                $attachments[] = Attachment::fromStorageDisk('do', $document->url)
                    ->as($document->name);
            } catch (\Exception $e) {
                // Log dell'errore ma continua con gli altri allegati
                \Log::error('Error attaching document to email: ' . $e->getMessage());
            }
        }

        return $attachments;
    }
}
