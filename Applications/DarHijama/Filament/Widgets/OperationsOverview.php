<?php

namespace Applications\DarHijama\Filament\Widgets;

use Applications\DarHijama\Domain\Appointment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = now()->toDateString();

        return [
            Stat::make('Appointments today', Appointment::query()->whereDate('starts_at', $today)->count()),
            Stat::make('Pending confirmations', Appointment::query()->where('status', 'pending')->count()),
            Stat::make('Completed today', Appointment::query()->where('status', 'completed')->whereDate('completed_at', $today)->count()),
            Stat::make('Cancellations', Appointment::query()->where('status', 'cancelled')->whereDate('cancelled_at', $today)->count()),
            Stat::make('No-shows', Appointment::query()->where('status', 'no_show')->whereDate('updated_at', $today)->count()),
            Stat::make('Practitioner workload', Appointment::query()->whereDate('starts_at', $today)->whereNotIn('status', ['cancelled', 'rescheduled'])->count()),
            Stat::make('Upcoming follow-ups', Appointment::query()->where('type', 'follow_up')->where('starts_at', '>=', now())->count()),
        ];
    }
}
