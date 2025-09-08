<?php

namespace App\Observers;

use App\Models\Customer;

class CustomerObserver
{
    /**
     * Handle the Customer "deleting" event.
     * GDPR Compliance: Elimina automaticamente tutti i dati collegati al customer
     */
    public function deleting(Customer $customer)
    {
        \Log::info("GDPR: Iniziando cancellazione completa Customer ID: {$customer->id}", [
            'customer_name' => $customer->name . ' ' . $customer->last_name,
            'customer_email' => $customer->email,
            'paperworks_count' => $customer->paperworks()->count(),
            'calendar_count' => $customer->calendar()->count()
        ]);

        // STEP 1: Elimina tutti i paperworks collegati
        // Il PaperworkObserver gestirà automaticamente tickets e documents
        foreach ($customer->paperworks as $paperwork) {
            $paperwork->delete();
        }

        // STEP 2: Elimina tutti gli appuntamenti calendar collegati
        // Il CalendarObserver gestirà eventuali dati collegati
        foreach ($customer->calendar as $appointment) {
            $appointment->delete();
        }

        \Log::info("GDPR: Customer {$customer->id} - Cancellazione cascata completata");
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer)
    {
        \Log::info("GDPR: Customer {$customer->id} eliminato definitivamente dal sistema", [
            'deleted_at' => now(),
            'customer_data' => [
                'name' => $customer->name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'business_name' => $customer->business_name
            ]
        ]);
    }
}
