<?php

namespace Modules\Invitations\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Models\RsvpResponse;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RsvpResponsePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_rsvp_responses(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->can('viewAny', RsvpResponse::class));
    }

    public function test_user_with_invitations_manage_permission_can_view_rsvp_responses(): void
    {
        Permission::firstOrCreate(['name' => 'invitations.manage', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->givePermissionTo('invitations.manage');

        $this->assertTrue($user->can('viewAny', RsvpResponse::class));
    }

    public function test_user_with_permission_can_delete_a_rsvp_response(): void
    {
        Permission::firstOrCreate(['name' => 'invitations.manage', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->givePermissionTo('invitations.manage');

        $response = RsvpResponse::factory()->for(Invitation::factory())->create();

        $this->assertTrue($user->can('delete', $response));
    }
}
