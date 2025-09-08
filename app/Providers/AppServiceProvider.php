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
use App\Models\Customer;
use App\Observers\CustomerObserver;
use App\Models\PaperworkDocument;
use App\Observers\PaperworkDocumentObserver;
use App\Models\TicketAttachment;
use App\Observers\TicketAttachmentObserver;

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

        // Observer esistenti
        Paperwork::observe(PaperworkObserver::class);
        Calendar::observe(CalendarObserver::class);
        Ticket::observe(TicketObserver::class);
        TicketComment::observe(TicketCommentObserver::class);
        
        // Observer per GDPR Compliance - Cancellazione cascata
        Customer::observe(CustomerObserver::class);
        PaperworkDocument::observe(PaperworkDocumentObserver::class);
        TicketAttachment::observe(TicketAttachmentObserver::class);
    }
}
