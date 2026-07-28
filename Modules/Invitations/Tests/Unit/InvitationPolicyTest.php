<?php

namespace Modules\Invitations\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Invitations\Models\Invitation;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvitationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_invitations(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->can('viewAny', Invitation::class));
    }

    public function test_user_with_permission_can_view_invitations(): void
    {
        Permission::firstOrCreate(['name' => 'invitations.manage', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->givePermissionTo('invitations.manage');

        $this->assertTrue($user->can('viewAny', Invitation::class));
    }
}
