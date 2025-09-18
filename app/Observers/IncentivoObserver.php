<?php

namespace App\Observers;

use App\Models\Incentivo;
use App\Mail\IncentivoEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class IncentivoObserver
{
    /**
     * Handle the Incentivo "created" event.
     */
    public function created(Incentivo $incentivo): void
    {
        try {
            // Invia email al cliente
            Mail::to($incentivo->email)->send(new IncentivoEmail($incentivo));
            
            Log::info('Email incentivo inviata con successo', [
                'incentivo_id' => $incentivo->id,
                'email' => $incentivo->email,
                'nominativo' => $incentivo->nominativo
            ]);
            
        } catch (\Exception $e) {
            Log::error('Errore invio email incentivo', [
                'incentivo_id' => $incentivo->id,
                'email' => $incentivo->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the Incentivo "updated" event.
     */
    public function updated(Incentivo $incentivo): void
    {
        //
    }

    /**
     * Handle the Incentivo "deleted" event.
     */
    public function deleted(Incentivo $incentivo): void
    {
        //
    }

    /**
     * Handle the Incentivo "restored" event.
     */
    public function restored(Incentivo $incentivo): void
    {
        //
    }

    /**
     * Handle the Incentivo "force deleted" event.
     */
    public function forceDeleted(Incentivo $incentivo): void
    {
        //
    }
}
