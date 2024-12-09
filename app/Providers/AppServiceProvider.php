<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Paperwork;
use App\Observers\PaperworkObserver;
use App\Models\Calendar;
use App\Observers\CalendarObserver;
use App\Models\Ticket;
use App\Observers\TicketObserver;
use App\Models\TicketComment;
use App\Observers\TicketCommentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('APP_ENV') === 'production') {
            \URL::forceScheme('https');
        }

        Paperwork::observe(PaperworkObserver::class);
        Calendar::observe(CalendarObserver::class);
        Ticket::observe(TicketObserver::class);
        TicketComment::observe(TicketCommentObserver::class);
    }
}
