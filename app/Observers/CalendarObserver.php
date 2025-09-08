<?php

namespace App\Observers;

use App\Models\Calendar;
use App\Models\User;
use App\Notifications\CalendarAppointmentCreated;
use App\Notifications\CalendarAppointmentUpdated;
use App\Notifications\CalendarAppointmentDeleted;

class CalendarObserver
{
    public function created(Calendar $calendar): void
    {
        if ($calendar->agent) {
            $calendar->agent->notify(new CalendarAppointmentCreated($calendar));
        }
    }

    public function updated(Calendar $calendar): void
    {
        if ($calendar->agent) {
            $calendar->agent->notify(new CalendarAppointmentUpdated($calendar));
        }
    }

    /**
     * Handle the Calendar "deleting" event.
     * GDPR Compliance: Log della cancellazione appuntamento
     */
    public function deleting(Calendar $calendar): void
    {
        \Log::info("GDPR: Cancellazione appuntamento Calendar ID: {$calendar->id}", [
            'customer_id' => $calendar->customer_id,
            'agent_id' => $calendar->agent_id,
            'title' => $calendar->title,
            'start' => $calendar->start,
            'end' => $calendar->end
        ]);
    }

    public function deleted(Calendar $calendar): void
    {
        if ($calendar->agent) {
            $calendar->agent->notify(new CalendarAppointmentDeleted($calendar));
        }
        
        \Log::info("GDPR: Appuntamento Calendar {$calendar->id} eliminato definitivamente", [
            'customer_id' => $calendar->customer_id,
            'title' => $calendar->title
        ]);
    }
}
