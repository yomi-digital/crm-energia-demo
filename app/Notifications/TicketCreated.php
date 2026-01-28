<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;

class TicketCreated extends Notification
{
    use Queueable;

    protected $ticket;

    public function databaseType(): string
    {
        return 'ticket-created';
    }

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
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
        return (new MailMessage)
                    ->subject('Nuovo Ticket Creato: ' . $this->ticket->title)
                    ->line('È stato creato un nuovo ticket nel sistema.')
                    ->line('**Titolo:** ' . $this->ticket->title)
                    ->line('**ID Ticket:** ' . $this->ticket->id)
                    ->action('Visualizza Ticket', url('/workflow/tickets/' . $this->ticket->id))
                    ->line('Grazie per aver utilizzato Alfacom CRM!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $createdBy = \App\Models\User::find($this->ticket->created_by);
        return [
            'paperwork_id' => $this->ticket->paperwork_id,
            'ticket_id' => $this->ticket->id,
            'ticket_title' => $this->ticket->title,
            'created_by_id' => $createdBy->id,
            'created_by_name' => implode(' ', [$createdBy->name, $createdBy->last_name]),
        ];
    }
}
