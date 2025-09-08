<?php

namespace App\Observers;

use App\Models\TicketAttachment;

class TicketAttachmentObserver
{
    /**
     * Handle the TicketAttachment "deleting" event.
     * GDPR Compliance: Elimina i file fisici collegati all'allegato
     */
    public function deleting(TicketAttachment $attachment): void
    {
        \Log::info("GDPR: Cancellazione allegato TicketAttachment ID: {$attachment->id}", [
            'ticket_id' => $attachment->ticket_id,
            'paperwork_id' => $attachment->paperwork_id,
            'name' => $attachment->name,
            'url' => $attachment->url,
            'size' => $attachment->size
        ]);

        // Elimina il file fisico se esiste
        if ($attachment->url && \Storage::exists($attachment->url)) {
            \Storage::delete($attachment->url);
            \Log::info("GDPR: File fisico eliminato: {$attachment->url}");
        }
    }

    /**
     * Handle the TicketAttachment "deleted" event.
     */
    public function deleted(TicketAttachment $attachment): void
    {
        \Log::info("GDPR: Allegato TicketAttachment {$attachment->id} eliminato definitivamente", [
            'ticket_id' => $attachment->ticket_id,
            'name' => $attachment->name,
            'size' => $attachment->size
        ]);
    }
}
