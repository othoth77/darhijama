<?php

namespace Applications\DarHijama\Application\Console;

use Applications\DarHijama\Application\Services\DarHijamaOperations;
use Applications\DarHijama\Domain\Appointment;
use Illuminate\Console\Command;

class DispatchRemindersCommand extends Command
{
    protected $signature = 'dar-hijama:dispatch-reminders {--date=}';

    protected $description = 'Queue appointment and follow-up reminder notifications';

    public function handle(DarHijamaOperations $operations): int
    {
        $date = $this->option('date') ?: now()->addDay()->toDateString();

        Appointment::query()
            ->whereDate('starts_at', $date)
            ->whereIn('status', ['confirmed', 'practitioner_assigned'])
            ->each(function (Appointment $appointment) use ($operations, $date): void {
                $followUp = $appointment->getAttribute('type') === 'follow_up';
                $event = $followUp ? 'follow-up-reminder' : 'appointment-reminder';
                $operations->notify(
                    $appointment,
                    $event,
                    $followUp ? 'Follow-up reminder' : 'Appointment reminder',
                    "dar-hijama.{$event}.{$appointment->getKey()}.{$date}",
                );
            });

        return self::SUCCESS;
    }
}
