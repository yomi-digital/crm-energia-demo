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

    /**
     * Handle the Ticket "deleting" event.
     * GDPR Compliance: Elimina automaticamente tutti i dati collegati al ticket
     */
    public function deleting(Ticket $ticket): void
    {
        \Log::info("GDPR: Iniziando cancellazione Ticket ID: {$ticket->id}", [
            'paperwork_id' => $ticket->paperwork_id,
            'attachments_count' => $ticket->attachments()->count(),
            'comments_count' => $ticket->comments()->count()
        ]);

        // STEP 1: Elimina tutti gli attachments collegati
        foreach ($ticket->attachments as $attachment) {
            // Elimina il file fisico se esiste
            if ($attachment->url && \Storage::exists($attachment->url)) {
                \Storage::delete($attachment->url);
            }
            $attachment->delete();
        }

        // STEP 2: Elimina tutti i commenti collegati
        foreach ($ticket->comments as $comment) {
            $comment->delete();
        }

        \Log::info("GDPR: Ticket {$ticket->id} - Cancellazione cascata completata");
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        \Log::info("GDPR: Ticket {$ticket->id} eliminato definitivamente", [
            'paperwork_id' => $ticket->paperwork_id,
            'title' => $ticket->title
        ]);
    }
}
