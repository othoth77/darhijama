<?php

namespace App\Providers;

use App\Events\InvitationPublished;
use App\Events\NotificationRequested;
use App\Listeners\QueueRequestedNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Events\FeatureDeleted;
use Laravel\Pennant\Events\FeatureUpdated;
use Laravel\Pennant\Events\FeatureUpdatedForAllScopes;
use Mythos\Core\Analytics\Models\AnalyticsEvent;
use Mythos\Core\Analytics\Models\PageView;
use Mythos\Core\Analytics\Models\WhatsappClickEvent;
use Mythos\Core\Audit\AuditAction;
use Mythos\Core\Audit\Contracts\AuditLogger as AuditService;
use Mythos\Core\Audit\Models\AuditLog;
use Mythos\Core\Notifications\Contracts\NotificationQueue;
use Mythos\Core\Notifications\LaravelNotificationQueue;
use Mythos\Core\Notifications\Models\NotificationDelivery;
use Spatie\Permission\Events\PermissionAttached;
use Spatie\Permission\Events\PermissionDetached;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;

class AuditNotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationQueue::class, LaravelNotificationQueue::class);
    }

    public function boot(AuditService $audit): void
    {
        $this->registerModelAuditListeners($audit);

        Event::listen(Login::class, fn (Login $event) => $audit->record(
            AuditAction::Login,
            $event->user,
            $event->user::class,
            $event->user->getAuthIdentifier(),
        ));
        Event::listen(Logout::class, fn (Logout $event) => $audit->record(
            AuditAction::Logout,
            $event->user,
            $event->user?->getMorphClass(),
            $event->user?->getAuthIdentifier(),
        ));

        foreach ([PermissionAttached::class, PermissionDetached::class, RoleAttached::class, RoleDetached::class] as $event) {
            Event::listen($event, fn (object $event) => $audit->record(
                AuditAction::PermissionChange,
                auth()->user(),
                $event->model->getMorphClass(),
                $event->model->getKey(),
                [
                    'event' => class_basename($event),
                    'values' => $this->permissionValues($event->permissionsOrIds ?? $event->rolesOrIds),
                ],
            ));
        }

        Event::listen(FeatureUpdated::class, fn (FeatureUpdated $event) => $audit->record(
            AuditAction::FeatureFlagChange,
            auth()->user(),
            'feature',
            $event->feature,
            ['value' => $event->value],
        ));
        Event::listen(FeatureUpdatedForAllScopes::class, fn (FeatureUpdatedForAllScopes $event) => $audit->record(
            AuditAction::FeatureFlagChange,
            auth()->user(),
            'feature',
            $event->feature,
            ['value' => $event->value],
        ));
        Event::listen(FeatureDeleted::class, fn (FeatureDeleted $event) => $audit->record(
            AuditAction::FeatureFlagChange,
            auth()->user(),
            'feature',
            $event->feature,
            ['deleted' => true],
        ));

        Event::listen(InvitationPublished::class, fn (InvitationPublished $event) => $audit->record(
            AuditAction::Publish,
            auth()->user(),
            'invitation',
            $event->invitationId,
        ));
        Event::listen(NotificationRequested::class, QueueRequestedNotification::class);
    }

    private function registerModelAuditListeners(AuditService $audit): void
    {
        Event::listen('eloquent.created: *', function (string $event, array $models) use ($audit): void {
            $model = $models[0];

            if ($this->shouldAudit($model)) {
                $audit->recordModel(AuditAction::Create, $model, ['after' => $model->getAttributes()]);
            }
        });

        Event::listen('eloquent.updated: *', function (string $event, array $models) use ($audit): void {
            $model = $models[0];

            if (! $this->shouldAudit($model)) {
                return;
            }

            $after = $model->getChanges();
            $before = array_intersect_key($model->getRawOriginal(), $after);
            $action = ($after['status'] ?? null) === 'archive'
                ? AuditAction::Archive
                : AuditAction::Update;

            $audit->recordModel($action, $model, compact('before', 'after'));
        });

        Event::listen('eloquent.deleted: *', function (string $event, array $models) use ($audit): void {
            $model = $models[0];

            if ($this->shouldAudit($model)) {
                $audit->recordModel(AuditAction::Delete, $model, ['before' => $model->getAttributes()]);
            }
        });

        Event::listen('eloquent.restored: *', function (string $event, array $models) use ($audit): void {
            $model = $models[0];

            if ($this->shouldAudit($model)) {
                $audit->recordModel(AuditAction::Restore, $model);
            }
        });
    }

    private function permissionValues(mixed $values): array
    {
        if ($values instanceof Model) {
            return [$values->getKey()];
        }

        return collect($values)->map(
            fn ($value) => $value instanceof Model ? $value->getKey() : $value,
        )->values()->all();
    }

    private function shouldAudit(Model $model): bool
    {
        return ! $model instanceof AuditLog
            && ! $model instanceof AnalyticsEvent
            && ! $model instanceof PageView
            && ! $model instanceof WhatsappClickEvent
            && ! $model instanceof NotificationDelivery
            && ! $model instanceof DatabaseNotification;
    }
}
