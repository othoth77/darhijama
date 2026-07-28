<?php

namespace App\Providers;

use App\Events\InvitationPublished;
use App\Events\InvitationViewed;
use App\Events\OrderCreated;
use App\Events\PublicPageViewed;
use App\Events\RsvpSubmitted;
use App\Events\WhatsappClicked;
use App\Http\Controllers\AnalyticsController;
use App\Listeners\RecordInvitationPublication;
use App\Listeners\RecordInvitationView;
use App\Listeners\RecordOrderCreation;
use App\Listeners\RecordPublicPageView;
use App\Listeners\RecordRsvpSubmission;
use App\Listeners\RecordWhatsappClick;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(WhatsappClicked::class, RecordWhatsappClick::class);
        Event::listen(PublicPageViewed::class, RecordPublicPageView::class);
        Event::listen(InvitationViewed::class, RecordInvitationView::class);
        Event::listen(InvitationPublished::class, RecordInvitationPublication::class);
        Event::listen(RsvpSubmitted::class, RecordRsvpSubmission::class);
        Event::listen(OrderCreated::class, RecordOrderCreation::class);

        RateLimiter::for('analytics', fn (Request $request) => Limit::perMinute(30)
            ->by($request->ip()));

        Route::middleware(['api', 'throttle:analytics'])
            ->post('/analytics/whatsapp-click', [
                AnalyticsController::class,
                'whatsappClick',
            ])
            ->name('analytics.whatsapp-click');
    }
}
