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
        // Formatta le date
        $partnerSentAt = $this->paperwork->partner_sent_at 
            ? \Carbon\Carbon::parse($this->paperwork->partner_sent_at)->format('d/m/Y')
            : 'N/A';
        
        $partnerOutcomeAt = $this->paperwork->partner_outcome_at 
            ? \Carbon\Carbon::parse($this->paperwork->partner_outcome_at)->format('d/m/Y')
            : 'N/A';

        return (new MailMessage)
                    ->subject('Stato Pratica Aggiornato - Pratica #' . $this->paperwork->id)
                    ->greeting(null)
                    ->line('Lo stato della pratica è stato aggiornato.')
                    ->line('**ID Pratica:** ' . $this->paperwork->id)
                    ->line('**Prodotto:** ' . $this->paperwork->product->name)
                    ->line('**Brand:** ' . $this->paperwork->product->brand->name)
                    ->line('**Stato Ordine:** ' . ($this->paperwork->order_status ?? 'N/A'))
                    ->line('**Sottostato Ordine:** ' . ($this->paperwork->order_substatus ?? 'N/A'))
                    ->line('**Data Inserimento:** ' . $partnerSentAt)
                    ->line('**Data Esito Partner:** ' . $partnerOutcomeAt)
                    ->line('**Esito Partner:** ' . ($this->paperwork->partner_outcome ?? 'N/A'))
                    ->line('**Note:** ' . ($this->paperwork->notes ?? 'N/A'))
                    ->line('**Note Alfacom:** ' . ($this->paperwork->owner_notes ?? 'N/A'))
                    ->action('Visualizza Pratica', url('/workflow/paperworks/' . $this->paperwork->id))
                    ->line('Grazie per aver utilizzato EasyWork CRM!');
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
