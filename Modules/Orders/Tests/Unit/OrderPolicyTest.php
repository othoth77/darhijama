<?php

namespace Modules\Orders\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Orders\Models\Order;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_orders(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->can('viewAny', Order::class));
    }

    public function test_user_with_permission_can_view_orders(): void
    {
        Permission::firstOrCreate(['name' => 'orders.manage', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->givePermissionTo('orders.manage');

        $this->assertTrue($user->can('viewAny', Order::class));
    }
}
