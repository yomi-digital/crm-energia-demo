<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\TicketComment;

class TicketCommentCreated extends Notification
{
    use Queueable;

    protected $comment;

    public function databaseType(): string
    {
        return 'ticket-comment-created';
    }

    /**
     * Create a new notification instance.
     */
    public function __construct(TicketComment $comment)
    {
        $this->comment = $comment;
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
        $user = \App\Models\User::find($this->comment->user_id);
        $ticket = \App\Models\Ticket::find($this->comment->ticket_id);
        return [
            'ticket_id' => $ticket->id,
            'comment_id' => $this->comment->id,
            'comment_text' => $this->comment->comment,
            'ticket_title' => $ticket->title,
            'user_id' => $user->id,
            'user_name' => $user->name . ' ' . $user->last_name,
        ];
    }
}
