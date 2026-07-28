<?php

namespace Modules\Invitations\Providers;

use App\Events\RsvpSubmitted;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Modules\Invitations\Listeners\NotifyAdminsOfRsvp;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Models\ProgramStep;
use Modules\Invitations\Models\RsvpResponse;
use Modules\Invitations\Observers\InvitationMediaObserver;
use Modules\Invitations\Observers\InvitationObserver;
use Modules\Invitations\Observers\ProgramStepObserver;
use Modules\Invitations\Policies\InvitationPolicy;
use Modules\Invitations\Policies\RsvpResponsePolicy;
use Modules\Media\Models\Media;

/**
 * Module core (MVP) — toujours actif, aucun Feature Flag requis.
 * Creation, publication et affichage public des invitations.
 */
class InvitationsServiceProvider extends ServiceProvider
{
    protected string $name = 'Invitations';

    protected string $nameLower = 'invitations';

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));
        $this->loadViewsFrom(module_path($this->name, 'resources/views'), $this->nameLower);
        $this->loadRoutesFrom(module_path($this->name, 'routes/web.php'));

        Invitation::observe(InvitationObserver::class);
        ProgramStep::observe(ProgramStepObserver::class);
        Media::observe(InvitationMediaObserver::class);

        Gate::policy(Invitation::class, InvitationPolicy::class);
        Gate::policy(RsvpResponse::class, RsvpResponsePolicy::class);
        Event::listen(RsvpSubmitted::class, NotifyAdminsOfRsvp::class);

        RateLimiter::for('rsvp', fn (Request $request) => Limit::perMinute(10)->by(
            $request->route('token').'|'.$request->ip()
        ));

        $this->registerRepositoryBindings();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->name, 'config/config.php'), $this->nameLower);
    }

    /**
     * Liaison des interfaces de Repository vers leur implémentation Eloquent
     * (Dependency Inversion — voir Repositories/Contracts).
     */
    protected function registerRepositoryBindings(): void
    {
        // Exemple :
        // $this->app->bind(
        //     \Modules\Invitations\Repositories\Contracts\InvitationsRepositoryInterface::class,
        //     \Modules\Invitations\Repositories\EloquentInvitationsRepository::class
        // );
    }
}
