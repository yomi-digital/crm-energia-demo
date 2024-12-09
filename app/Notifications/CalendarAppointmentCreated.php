<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Calendar;

class CalendarAppointmentCreated extends Notification
{
    use Queueable;

    protected $calendar;

    public function databaseType(): string
    {
        return 'calendar-created';
    }

    /**
     * Create a new notification instance.
     */
    public function __construct(Calendar $calendar)
    {
        $this->calendar = $calendar;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'calendar_id' => $this->calendar->id,
            'title' => $this->calendar->title,
            'status' => $this->calendar->status,
            'start' => $this->calendar->start ? \Carbon\Carbon::parse($this->calendar->start)->format('d/m/Y H:i') : null,
            'end' => $this->calendar->end ? \Carbon\Carbon::parse($this->calendar->end)->format('d/m/Y H:i') : null,
        ];
    }
}
