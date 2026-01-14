<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use App\Models\Report;

class InvitoFatturareEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Report $report;
    public $entries;
    public $totalCompenso;
    public $periodoRiferimento;

    /**
     * Create a new message instance.
     */
    public function __construct(Report $report, $entries, $totalCompenso, $periodoRiferimento)
    {
        $this->report = $report;
        $this->entries = $this->prepareEntries($entries);
        $this->totalCompenso = $totalCompenso;
        $this->periodoRiferimento = $periodoRiferimento;
    }

    /**
     * Prepara le entries con i dati del cliente e dell'account
     */
    private function prepareEntries($entries)
    {
        $preparedEntries = [];
        
        // Ottieni tutti gli ID dei paperwork per evitare query N+1
        $paperworkIds = $entries->pluck('paperwork_id')->filter()->unique()->toArray();
        
        // Carica tutti i paperwork con i loro clienti in una sola query
        $paperworks = \App\Models\Paperwork::with('customer')
            ->whereIn('id', $paperworkIds)
            ->get()
            ->keyBy('id');
        
        foreach ($entries as $entry) {
            $preparedEntry = [
                'id' => $entry->id,
                'customer' => 'N/A',
                'account' => 'N/A',
                'activated_at' => $entry->activated_at ?? 'N/A',
                'product' => $entry->product ?? 'N/A',
                'payout_confirmed' => $entry->payout_confirmed ?? 0,
            ];
            
            if ($entry->paperwork_id && isset($paperworks[$entry->paperwork_id])) {
                $paperwork = $paperworks[$entry->paperwork_id];
                
                // Cliente
                if ($paperwork->customer) {
                    if (!empty($paperwork->customer->business_name)) {
                        $preparedEntry['customer'] = $paperwork->customer->business_name;
                    } else if (!empty($paperwork->customer->name) && !empty($paperwork->customer->last_name)) {
                        $preparedEntry['customer'] = implode(' ', array_filter([$paperwork->customer->name, $paperwork->customer->last_name]));
                    } else if (!empty($paperwork->customer->name)) {
                        $preparedEntry['customer'] = $paperwork->customer->name;
                    } else if (!empty($paperwork->customer->last_name)) {
                        $preparedEntry['customer'] = $paperwork->customer->last_name;
                    }
                }
                
                // Account POD/PDR
                if ($paperwork->account_pod_pdr) {
                    $preparedEntry['account'] = $paperwork->account_pod_pdr;
                }
            }
            
            $preparedEntries[] = (object) $preparedEntry;
        }
        
        return $preparedEntries;
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
        return [];
    }
}
