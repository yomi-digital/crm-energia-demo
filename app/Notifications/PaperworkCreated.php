<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaperworkCreated extends Notification
{
    use Queueable;

    protected $paperwork;

    public function databaseType(): string
    {
        return 'paperwork-created';
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
        return (new MailMessage)
                    ->subject('Nuovo Paperwork Creato - ' . $this->paperwork->product->name)
                    ->line('È stato creato un nuovo paperwork nel sistema.')
                    ->line('**Prodotto:** ' . $this->paperwork->product->name)
                    ->line('**Brand:** ' . $this->paperwork->product->brand->name)
                    ->line('**ID Paperwork:** ' . $this->paperwork->id)
                    ->action('Visualizza Paperwork', url('/paperworks/' . $this->paperwork->id))
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
        ];
    }
}
