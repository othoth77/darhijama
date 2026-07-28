<?php

namespace Applications\DarHijama\Application\Services;

use Applications\DarHijama\Domain\ApplicationEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Mythos\Core\Analytics\Contracts\AnalyticsRecorder;
use Mythos\Core\Audit\AuditAction;
use Mythos\Core\Audit\Contracts\AuditLogger;
use Mythos\Core\Media\Contracts\MediaManager;
use Mythos\Core\Notifications\Contracts\NotificationDispatcher;
use Mythos\Core\Notifications\NotificationMessage;

class DarHijamaOperations
{
    public function __construct(
        private readonly MediaManager $media,
        private readonly NotificationDispatcher $notifications,
        private readonly AnalyticsRecorder $analytics,
        private readonly AuditLogger $audit,
    ) {}

    public function recordDashboardView(string $visitorHash): void
    {
        $this->analytics->recordPageView(
            source: 'dar-hijama.dashboard',
            visitorHash: $visitorHash,
        );
    }

    public function audit(AuditAction $action, Model $model, array $changes = []): void
    {
        $this->audit->record(
            action: $action,
            user: auth()->id(),
            entityType: $model->getMorphClass(),
            entityId: $model->getKey(),
            changes: $changes,
        );
    }

    public function attachPatientMedia(Model $patient, UploadedFile $file): void
    {
        $this->media->uploadFor(
            mediable: $patient,
            file: $file,
            type: 'patient-document',
            directory: 'dar-hijama/patients',
            disk: 'local',
        );
    }

    public function notifyAppointmentCreated(Model $appointment): void
    {
        $this->notify($appointment, 'appointment-created', 'Appointment created');
    }

    public function notify(
        Model $subject,
        string $event,
        string $title,
        ?string $idempotencyKey = null,
    ): void {
        try {
            $this->notifications->send($subject, new NotificationMessage(
                type: "dar-hijama.{$event}",
                title: $title,
                body: 'A Dar Hijama appointment update is available.',
                data: ['subject_id' => $subject->getKey()],
                channels: config('applications.dar-hijama.notifications.channels', ['database']),
                idempotencyKey: $idempotencyKey
                    ?? "dar-hijama.{$event}.{$subject->getMorphClass()}.{$subject->getKey()}",
            ));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function track(string $event, Model $subject, array $metadata = []): void
    {
        ApplicationEvent::query()->create([
            'event' => $event,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => (string) $subject->getKey(),
            'actor_id' => auth()->id(),
            'metadata' => $metadata ?: null,
            'occurred_at' => now(),
        ]);
    }

    public function recordSensitiveRead(string $resource, ?string $subjectId = null): void
    {
        DB::table('dar_hijama_access_logs')->insert([
            'actor_id' => auth()->id(),
            'resource' => $resource,
            'action' => 'read',
            'subject_id' => $subjectId,
            'occurred_at' => now(),
        ]);
    }
}
