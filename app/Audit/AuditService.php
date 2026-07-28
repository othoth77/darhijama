<?php

namespace App\Audit;

use App\Audit\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    private const SENSITIVE_FIELDS = [
        'password',
        'remember_token',
    ];

    public function record(
        AuditAction $action,
        Authenticatable|int|null $user = null,
        ?string $entityType = null,
        int|string|null $entityId = null,
        array $changes = [],
        ?Request $request = null,
    ): AuditLog {
        $request ??= app()->bound('request') ? request() : null;

        return AuditLog::create([
            'user_id' => $user instanceof Authenticatable ? $user->getAuthIdentifier() : $user,
            'entity_type' => $entityType,
            'entity_id' => $entityId === null ? null : (string) $entityId,
            'action' => $action,
            'changes' => $this->sanitize($changes) ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 1024) : null,
            'occurred_at' => now(),
        ]);
    }

    public function recordModel(AuditAction $action, Model $model, array $changes = []): ?AuditLog
    {
        if (! auth()->check() || $model instanceof AuditLog) {
            return null;
        }

        return $this->record(
            $action,
            auth()->user(),
            $model->getMorphClass(),
            $model->getKey(),
            $changes,
        );
    }

    private function sanitize(array $changes): array
    {
        foreach (self::SENSITIVE_FIELDS as $field) {
            unset($changes[$field]);

            if (isset($changes['before']) && is_array($changes['before'])) {
                unset($changes['before'][$field]);
            }

            if (isset($changes['after']) && is_array($changes['after'])) {
                unset($changes['after'][$field]);
            }
        }

        return $changes;
    }
}
