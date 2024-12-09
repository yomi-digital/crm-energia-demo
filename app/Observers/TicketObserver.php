<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Notifications\TicketCreated;

class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        // Check who created the ticket and retrieve the paperwork.
        $paperwork = \App\Models\Paperwork::find($ticket->paperwork_id);
        $creator = \App\Models\User::find($ticket->created_by);

        if ($creator->hasRole('agente')) {
            // Get all backoffice users that have this brand enabled
            $users = \App\Models\User::whereHas('brands', function ($query) use ($paperwork) {
                $query->where('brand_id', $paperwork->product->brand_id);
            })->role('backoffice')->where('enabled', true)->get();
            $users->each(function ($user) use ($ticket) {
                $user->notify(new TicketCreated($ticket));
            });
        } else {
            // Get the agent whose assigned the paperwork
            $user = \App\Models\User::find($paperwork->user_id);
            $user->notify(new TicketCreated($ticket));
        }
    }
}
