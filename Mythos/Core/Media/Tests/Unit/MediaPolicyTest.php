<?php

namespace Mythos\Core\Media\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mythos\Core\Identity\Models\User;
use Mythos\Core\Media\Models\Media;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MediaPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_media(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->can('viewAny', Media::class));
    }

    public function test_user_with_permission_can_view_media(): void
    {
        Permission::firstOrCreate(['name' => 'media.manage', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->givePermissionTo('media.manage');

        $this->assertTrue($user->can('viewAny', Media::class));
    }
}
