<?php

namespace Applications\DarHijama\Providers;

use Applications\DarHijama\Application\Services\DarHijamaOperations;
use Applications\DarHijama\Application\Services\Seo\ArticleSeoResolver;
use Applications\DarHijama\Domain\Appointment;
use Applications\DarHijama\Domain\Article;
use Applications\DarHijama\Domain\Patient;
use Applications\DarHijama\Domain\Practitioner;
use Applications\DarHijama\Policies\AppointmentPolicy;
use Applications\DarHijama\Policies\ArticlePolicy;
use Applications\DarHijama\Policies\PatientPolicy;
use Applications\DarHijama\Policies\PractitionerPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;

class DarHijamaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DarHijamaOperations::class);
        $this->app->singleton(ArticleSeoResolver::class);
    }

    public function boot(): void
    {
        Gate::policy(Patient::class, PatientPolicy::class);
        Gate::policy(Practitioner::class, PractitionerPolicy::class);
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(Article::class, ArticlePolicy::class);

        Schedule::command('dar-hijama:dispatch-reminders')
            ->dailyAt('08:00')
            ->withoutOverlapping();
        Schedule::command('dar-hijama:health-heartbeat')
            ->everyMinute()
            ->withoutOverlapping();
    }
}
