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
        $calendar->agent->notify(new CalendarAppointmentCreated($calendar));
    }

    public function updated(Calendar $calendar): void
    {
        $calendar->agent->notify(new CalendarAppointmentUpdated($calendar));
    }

    public function deleted(Calendar $calendar): void
    {
        $calendar->agent->notify(new CalendarAppointmentDeleted($calendar));
    }
}
