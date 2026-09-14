<?php

namespace Modules\Orders\Policies;

use Modules\Orders\Models\Client;
use Mythos\Core\Identity\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('orders.manage');
    }

    public function view(User $user, Client $client): bool
    {
        return $user->can('orders.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('orders.manage');
    }

    public function update(User $user, Client $client): bool
    {
        return $user->can('orders.manage');
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->can('orders.manage');
    }
}
