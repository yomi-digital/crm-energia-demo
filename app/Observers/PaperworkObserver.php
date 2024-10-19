<?php

namespace App\Observers;

use App\Models\Paperwork;

class PaperworkObserver
{
    // Fields to exclude from change tracking
    protected $excludedFields = ['updated_at'];

    /**
     * Handle the Paperwork "created" event.
     */
    public function created(Paperwork $paperwork): void
    {
        //
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
     * Handle the Paperwork "deleted" event.
     */
    public function deleted(Paperwork $paperwork): void
    {
        //
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
