<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;

class InvitoFatturareEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Report $report;
    public $entries;
    public $totalCompenso;
    public $periodoRiferimento;
    public $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(Report $report, $entries, $totalCompenso, $periodoRiferimento, $pdfPath)
    {
        $this->report = $report;
        $this->entries = $entries; // Le entries sono già preparate dal controller
        $this->totalCompenso = $totalCompenso;
        $this->periodoRiferimento = $periodoRiferimento;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: 'Invito a Fatturare',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invito-fatturare',
            with: [
                'report' => $this->report,
                'entries' => $this->entries,
                'totalCompenso' => $this->totalCompenso,
                'periodoRiferimento' => $this->periodoRiferimento,
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
        if (!$this->pdfPath || !Storage::disk('do')->exists($this->pdfPath)) {
            return [];
        }

        // Genera il nome del file
        $filename = 'Invito-a-Fatturare-' . $this->report->id . '.pdf';
        
        // Scarica il PDF da DigitalOcean Spaces quando viene allegato
        return [
            Attachment::fromData(
                fn () => Storage::disk('do')->get($this->pdfPath),
                $filename
            )->withMime('application/pdf'),
        ];
    }
}
