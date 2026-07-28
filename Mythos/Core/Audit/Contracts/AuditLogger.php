<?php

namespace Mythos\Core\Audit\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Mythos\Core\Audit\AuditAction;
use Mythos\Core\Audit\Models\AuditLog;
use Mythos\Core\Support\Api\ApiStatus;
use Mythos\Core\Support\Api\PublicApi;

#[PublicApi(ApiStatus::Stable, since: '1.0.0')]
interface AuditLogger
{
    public function record(
        AuditAction $action,
        Authenticatable|int|null $user = null,
        ?string $entityType = null,
        int|string|null $entityId = null,
        array $changes = [],
        ?Request $request = null,
    ): AuditLog;

    public function recordModel(AuditAction $action, Model $model, array $changes = []): ?AuditLog;
}
