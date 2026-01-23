<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaperworkStatusUpdated extends Notification
{
    use Queueable;

    protected $paperwork;

    public function databaseType(): string
    {
        return 'paperwork-status-updated';
    }

    /**
     * Create a new notification instance.
     */
    public function __construct($paperwork)
    {
        $this->paperwork = $paperwork;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Carica le relazioni necessarie se non già caricate
        $this->paperwork->loadMissing(['customer', 'product']);
        
        // Formatta la data inserimento
        $dataInserimento = 'N/A';
        if ($this->paperwork->partner_sent_at) {
            $dataInserimento = $this->paperwork->partner_sent_at->format('d/m/Y');
        } elseif ($this->paperwork->created_at) {
            $dataInserimento = $this->paperwork->created_at->format('d/m/Y');
        }
        
        // Formatta il cliente: business_name (se presente) + codice fiscale o p.iva
        $clienteInfo = 'N/A';
        if ($this->paperwork->customer) {
            $customer = $this->paperwork->customer;
            $parts = [];
            
            // Aggiungi business_name se presente
            if (!empty($customer->business_name)) {
                $parts[] = $customer->business_name;
            }
            
            // Aggiungi codice fiscale o p.iva
            if (!empty($customer->tax_id_code)) {
                $parts[] = 'CF: ' . $customer->tax_id_code;
            } elseif (!empty($customer->vat_number)) {
                $parts[] = 'P.IVA: ' . $customer->vat_number;
            }
            
            $clienteInfo = !empty($parts) ? implode(' - ', $parts) : 'N/A';
        }
        
        // Formatta le note pratica
        $notePratica = $this->paperwork->notes ?? 'N/A';
        
        // Formatta la data esito partner
        $dataEsitoPartner = 'N/A';
        if ($this->paperwork->partner_outcome_at) {
            $dataEsitoPartner = $this->paperwork->partner_outcome_at->format('d/m/Y');
        }
        
        // Formatta l'esito partner
        $esitoPartner = $this->paperwork->partner_outcome ?? 'N/A';
        
        return (new MailMessage)
                    ->subject('Stato Pratica Aggiornato - Pratica #' . $this->paperwork->id)
                    ->greeting(null)
                    ->line('Lo stato della pratica è stato aggiornato.')
                    ->line('**Data Inserimento:** ' . $dataInserimento)
                    ->line('**Cliente:** ' . $clienteInfo)
                    ->line('**Prodotto:** ' . ($this->paperwork->product ? $this->paperwork->product->name : 'N/A'))
                    ->line('**Note Pratica:** ' . $notePratica)
                    ->line('**Stato Pratica:** ' . ($this->paperwork->order_status ?? 'N/A'))
                    ->line('**Sotto Stato:** ' . ($this->paperwork->order_substatus ?? 'N/A'))
                    ->line('**Esito Partner:** ' . $esitoPartner)
                    ->line('**Data Esito Partner:** ' . $dataEsitoPartner)
                    ->action('Visualizza Pratica', url('/workflow/paperworks/' . $this->paperwork->id))
                    ->line('Grazie per aver utilizzato Alfacom CRM!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'paperwork_id' => $this->paperwork->id,
            'product' => $this->paperwork->product->name,
            'brand' => $this->paperwork->product->brand->name,
            'order_status' => $this->paperwork->order_status,
            'order_substatus' => $this->paperwork->order_substatus,
            'partner_sent_at' => $this->paperwork->partner_sent_at,
            'partner_outcome_at' => $this->paperwork->partner_outcome_at,
            'partner_outcome' => $this->paperwork->partner_outcome,
        ];
    }
}
