<?php

namespace Modules\Orders\Policies;

use Modules\Orders\Models\Order;
use Mythos\Core\Identity\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('orders.manage');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can('orders.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('orders.manage');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('orders.manage');
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can('orders.manage');
    }
}
