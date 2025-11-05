<?php

namespace App\Observers;

use App\Models\Paperwork;
use App\Notifications\PaperworkCreated;

class PaperworkObserver
{
    // Fields to exclude from change tracking
    protected $excludedFields = ['updated_at', 'transfers_history'];

    /**
     * Handle the Paperwork "created" event.
     */
    public function created(Paperwork $paperwork): void
    {
        // Check who created the paperwork, if has role agente, then send it to the backoffice, otherwise send it to the agent
        if ($paperwork->created_by) {
            $creator = \App\Models\User::find($paperwork->created_by);
            
            if ($creator->hasRole('agente')) {
                // Get all backoffice users that have this brand enabled
                $users = \App\Models\User::whereHas('brands', function ($query) use ($paperwork) {
                    $query->where('brand_id', $paperwork->product->brand_id);
                })->role('backoffice')->where('enabled', true)->get();
        
                $users->each(function ($user) use ($paperwork) {
                    $user->notify(new PaperworkCreated($paperwork));
                });
            } else {
                $agent = \App\Models\User::find($paperwork->user_id);
                $agent->notify(new PaperworkCreated($paperwork));
            }
        }
    }

    /**
     * Handle the Paperwork "updated" event.
     */
    public function updated(Paperwork $paperwork): void
    {
        $changes = $paperwork->getChanges();
        
        // Filter out excluded fields
        $filteredChanges = array_diff_key($changes, array_flip($this->excludedFields));
        
        if (!empty($filteredChanges)) {
            $paperwork->events()->create([
                'user_id' => auth()->id(),
                'event_type' => 'updated',
                'properties' => [
                    'changes' => array_map(function ($field, $newValue) use ($paperwork) {
                        return [
                            'field' => $field,
                            'old' => $paperwork->getOriginal($field),
                            'new' => $newValue,
                        ];
                    }, array_keys($filteredChanges), $filteredChanges),
                ],
            ]);
        }
    }

    /**
     * Handle the Paperwork "deleting" event.
     * GDPR Compliance: Elimina automaticamente tutti i dati collegati al paperwork
     */
    public function deleting(Paperwork $paperwork): void
    {
        \Log::info("GDPR: Iniziando cancellazione Paperwork ID: {$paperwork->id}", [
            'customer_id' => $paperwork->customer_id,
            'tickets_count' => $paperwork->tickets()->count(),
            'documents_count' => $paperwork->documents()->count()
        ]);

        // STEP 1: Elimina tutti i tickets collegati
        // Il TicketObserver gestirà automaticamente attachments e comments
        foreach ($paperwork->tickets as $ticket) {
            $ticket->delete();
        }

        // STEP 2: Elimina tutti i documenti collegati
        foreach ($paperwork->documents as $document) {
            $document->delete();
        }

        \Log::info("GDPR: Paperwork {$paperwork->id} - Cancellazione cascata completata");
    }

    /**
     * Handle the Paperwork "deleted" event.
     */
    public function deleted(Paperwork $paperwork): void
    {
        \Log::info("GDPR: Paperwork {$paperwork->id} eliminato definitivamente", [
            'customer_id' => $paperwork->customer_id,
            'order_code' => $paperwork->order_code
        ]);
    }

    /**
     * Handle the Paperwork "restored" event.
     */
    public function restored(Paperwork $paperwork): void
    {
        //
    }

    /**
     * Handle the Paperwork "force deleted" event.
     */
    public function forceDeleted(Paperwork $paperwork): void
    {
        //
    }
}
