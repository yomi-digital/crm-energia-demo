<?php

namespace App\Observers;

use App\Models\TicketComment;
use App\Notifications\TicketCommentCreated;

class TicketCommentObserver
{
    public function created(TicketComment $comment): void
    {
        $createdBy = \App\Models\User::find($comment->user_id);
        $ticket = \App\Models\Ticket::find($comment->ticket_id);
        $paperwork = \App\Models\Paperwork::find($ticket->paperwork_id);
        if ($createdBy->hasRole('agente')) {
            // Get all backoffice users that have this brand enabled
            $users = \App\Models\User::whereHas('brands', function ($query) use ($paperwork) {
                $query->where('brand_id', $paperwork->product->brand_id);
            })->role('backoffice')->where('enabled', true)->get();
            $users->each(function ($user) use ($comment) {
                $user->notify(new TicketCommentCreated($comment));
            });
        } else {
            // Get the agent whose assigned the paperwork
            $user = \App\Models\User::find($paperwork->user_id);
            $user->notify(new TicketCommentCreated($comment));
        }
    }
}
