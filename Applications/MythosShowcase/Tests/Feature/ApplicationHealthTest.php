<?php

namespace Applications\MythosShowcase\Tests\Feature;

use Applications\MythosShowcase\Domain\ShowcaseEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mythos\Core\Analytics\Contracts\AnalyticsRecorder;
use Mythos\Core\Applications\Contracts\ApplicationRegistry;
use Mythos\Core\Audit\Contracts\AuditLogger;
use Mythos\Core\Media\Contracts\MediaManager;
use Mythos\Core\Notifications\Contracts\NotificationDispatcher;
use Tests\TestCase;

class ApplicationHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_health_route_is_available(): void
    {
        $this->get(route('mythos.mythos-showcase.health'))
            ->assertOk()
            ->assertJson([
                'application' => 'mythos-showcase',
                'status' => 'ok',
            ]);
    }

    public function test_application_registers_its_sdk_resources_and_core_contracts(): void
    {
        $registry = app(ApplicationRegistry::class);
        $manifest = $registry->find('mythos-showcase');

        $this->assertNotNull($manifest);
        $this->assertSame('1.0.0', $manifest->version);
        $this->assertSame([
            'identity',
            'authorization',
            'media',
            'notifications',
            'analytics',
            'audit',
        ], $manifest->capabilities);
        $this->assertSame([
            'mythos-showcase.view',
            'mythos-showcase.manage',
        ], array_map(
            fn ($permission) => $permission->name,
            $registry->permissions('mythos-showcase'),
        ));
        $this->assertSame(
            'Mythos Showcase',
            $registry->navigation('mythos-showcase')[0]->label,
        );
        $this->assertTrue(app()->bound(MediaManager::class));
        $this->assertTrue(app()->bound(NotificationDispatcher::class));
        $this->assertTrue(app()->bound(AnalyticsRecorder::class));
        $this->assertTrue(app()->bound(AuditLogger::class));
        $this->assertTrue(\Schema::hasTable('mythos_showcase_entries'));
        $this->assertSame(
            'MythosShowcase',
            config('applications.mythos-showcase.name'),
        );
    }

    public function test_application_uses_core_media_notifications_analytics_and_audit(): void
    {
        Storage::fake('local');

        $this->get(route('mythos.showcase.index'))
            ->assertOk()
            ->assertSee('Mythos Showcase');

        $response = $this->postJson(route('mythos.showcase.store'), [
            'title' => 'Independent application',
            'message' => 'Powered by public Core services.',
            'media' => UploadedFile::fake()->image('showcase.jpg'),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('title', 'Independent application');

        $entry = ShowcaseEntry::query()->sole();

        $this->assertDatabaseHas('media', [
            'mediable_type' => ShowcaseEntry::class,
            'mediable_id' => $entry->getKey(),
            'type' => 'attachment',
        ]);
        $this->assertDatabaseHas('notification_deliveries', [
            'recipient_type' => ShowcaseEntry::class,
            'recipient_id' => (string) $entry->getKey(),
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => ShowcaseEntry::class,
            'entity_id' => (string) $entry->getKey(),
            'action' => 'create',
        ]);
        $this->assertDatabaseHas('page_views', [
            'source' => 'mythos-showcase',
            'subject_type' => ShowcaseEntry::class,
        ]);
    }
}
