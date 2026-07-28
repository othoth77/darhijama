<?php

namespace Modules\Orders\Policies;

use App\Models\User;
use Modules\Orders\Models\Client;

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
