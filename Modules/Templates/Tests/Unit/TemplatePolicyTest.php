<?php

namespace Modules\Templates\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Templates\Models\Template;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TemplatePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_view_templates(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->can('viewAny', Template::class));
    }

    public function test_user_with_permission_can_view_templates(): void
    {
        Permission::firstOrCreate(['name' => 'templates.manage', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->givePermissionTo('templates.manage');

        $this->assertTrue($user->can('viewAny', Template::class));
    }
}
