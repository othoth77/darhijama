<?php

namespace Applications\DarHijama\Application\Operations;

use Applications\DarHijama\Application\Services\DarHijamaOperations;
use Applications\DarHijama\Domain\Appointment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Mythos\Core\Audit\AuditAction;

class OperationalEvidenceJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly int $appointmentId,
        public readonly string $operation,
        public readonly string $probeId,
    ) {
        $this->onQueue('default');
    }

    public function handle(DarHijamaOperations $operations): void
    {
        $appointment = Appointment::query()->findOrFail($this->appointmentId);

        match ($this->operation) {
            'audit' => $operations->audit(AuditAction::Update, $appointment, [
                'operational_probe' => $this->probeId,
            ]),
            'analytics' => $operations->track('operational_probe', $appointment, [
                'probe_id' => $this->probeId,
            ]),
            'reminder' => $operations->notify(
                $appointment,
                'operational-reminder-probe',
                "Operational reminder probe {$this->probeId}",
                "dar-hijama.operational-reminder.{$this->probeId}",
            ),
            default => throw new \InvalidArgumentException('Unknown operational evidence type.'),
        };
    }
}
