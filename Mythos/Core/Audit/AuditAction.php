<?php

namespace Mythos\Core\Audit;

enum AuditAction: string
{
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Restore = 'restore';
    case Publish = 'publish';
    case Archive = 'archive';
    case Login = 'login';
    case Logout = 'logout';
    case PermissionChange = 'permission_change';
    case FeatureFlagChange = 'feature_flag_change';
}
