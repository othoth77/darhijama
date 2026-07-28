<?php

namespace Tests\Feature;

use App\Audit\AuditAction;
use App\Audit\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Laravel\Pennant\Events\FeatureUpdated;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Services\PublishInvitationService;
use Modules\Orders\Models\Client;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_model_changes_are_audited(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $client = Client::create(['name' => 'Initial', 'whatsapp_phone' => '98123456']);
        $client->update(['name' => 'Updated']);
        $client->delete();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'entity_type' => Client::class,
            'entity_id' => (string) $client->id,
            'action' => AuditAction::Create->value,
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => AuditAction::Update->value]);
        $this->assertDatabaseHas('audit_logs', ['action' => AuditAction::Delete->value]);

        $update = AuditLog::where('action', AuditAction::Update)->sole();
        $this->assertSame('Initial', $update->changes['before']['name']);
        $this->assertSame('Updated', $update->changes['after']['name']);
    }

    public function test_restore_archive_and_publish_actions_are_audited(): void
    {
        Schema::create('audit_test_records', function ($table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        $this->actingAs(User::factory()->create());

        $record = AuditTestRecord::create(['name' => 'Restorable']);
        $record->delete();
        $record->restore();

        $invitation = Invitation::factory()->create();
        app(PublishInvitationService::class)->execute($invitation);
        $invitation->update(['status' => InvitationStatus::Archive]);

        $this->assertDatabaseHas('audit_logs', ['action' => AuditAction::Restore->value]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Publish->value,
            'entity_id' => (string) $invitation->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Archive->value,
            'entity_id' => (string) $invitation->id,
        ]);
    }

    public function test_login_logout_permission_and_feature_changes_are_audited(): void
    {
        $user = User::factory()->create();
        $request = Request::create('/admin', 'POST', server: [
            'REMOTE_ADDR' => '192.0.2.10',
            'HTTP_USER_AGENT' => 'Audit Browser',
        ]);
        $this->app->instance('request', $request);

        Event::dispatch(new Login('web', $user, false));
        $this->actingAs($user);

        $permission = Permission::create(['name' => 'audit.test', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        Event::dispatch(new FeatureUpdated('guestbook', null, true));
        Event::dispatch(new Logout('web', $user));

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Login->value,
            'ip_address' => '192.0.2.10',
            'user_agent' => 'Audit Browser',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => AuditAction::Logout->value]);
        $permissionAudit = AuditLog::where('action', AuditAction::PermissionChange)->sole();
        $this->assertSame([$permission->id], $permissionAudit->changes['values']);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::FeatureFlagChange->value,
            'entity_id' => 'guestbook',
        ]);
    }

    public function test_sensitive_authentication_fields_are_never_stored_in_changes(): void
    {
        $this->actingAs(User::factory()->create());

        User::factory()->create([
            'password' => 'secret-password',
            'remember_token' => 'secret-token',
        ]);

        $changes = AuditLog::where('entity_type', User::class)
            ->where('action', AuditAction::Create)
            ->sole()
            ->changes;

        $this->assertArrayNotHasKey('password', $changes['after']);
        $this->assertArrayNotHasKey('remember_token', $changes['after']);
    }
}

class AuditTestRecord extends Model
{
    use SoftDeletes;

    protected $table = 'audit_test_records';

    protected $guarded = [];
}
