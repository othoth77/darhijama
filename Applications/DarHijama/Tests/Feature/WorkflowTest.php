<?php

namespace Applications\DarHijama\Tests\Feature;

use Applications\DarHijama\Application\Services\AppointmentStateMachine;
use Applications\DarHijama\Application\Services\AppointmentWorkflowService;
use Applications\DarHijama\Application\Services\PatientService;
use Applications\DarHijama\Domain\AppointmentStatus;
use Applications\DarHijama\Domain\Patient;
use Applications\DarHijama\Domain\Practitioner;
use Applications\DarHijama\Filament\Resources\AppointmentResource;
use Applications\DarHijama\Filament\Resources\PatientResource;
use Applications\DarHijama\Filament\Resources\PractitionerResource;
use Applications\DarHijama\Filament\Resources\PractitionerScheduleResource;
use Applications\DarHijama\Filament\Widgets\OperationsOverview;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mythos\Core\Applications\Contracts\ApplicationRegistry;
use Mythos\Core\Identity\Models\User;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_authorization_schema_and_roles_are_production_ready(): void
    {
        $registry = app(ApplicationRegistry::class);
        $manifest = $registry->find('dar-hijama');

        $this->assertNotNull($manifest);
        $this->assertCount(23, $registry->permissions('dar-hijama'));
        $this->assertTrue(Schema::hasTable('dar_hijama_appointment_transitions'));
        $this->assertTrue(Schema::hasTable('dar_hijama_access_logs'));
        $resources = Filament::getPanel('admin')->getResources();
        $this->assertContains(PatientResource::class, $resources);
        $this->assertContains(PractitionerResource::class, $resources);
        $this->assertContains(AppointmentResource::class, $resources);
        $this->assertContains(PractitionerScheduleResource::class, $resources);
        $this->assertContains(OperationsOverview::class, Filament::getPanel('admin')->getWidgets());
        $this->get(route('dar-hijama.dashboard'))->assertRedirect();

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('dar-hijama.dashboard'))->assertForbidden();
        $this->artisan('dar-hijama:install')->assertSuccessful();
        $this->assertDatabaseHas('roles', ['name' => 'dar-hijama-admin']);
        $this->assertDatabaseHas('roles', ['name' => 'dar-hijama-reception']);
        $this->assertDatabaseHas('roles', ['name' => 'dar-hijama-practitioner']);
        $this->assertDatabaseHas('roles', ['name' => 'dar-hijama-manager']);
    }

    public function test_patient_duplicates_override_search_private_notes_media_analytics_and_audit(): void
    {
        Storage::fake('local');
        $user = $this->authorizedUser();
        $service = app(PatientService::class);
        $patient = $service->create([
            'first_name' => 'Amina',
            'last_name' => 'Ben Salem',
            'phone' => '+216 20 000 000',
            'notes' => 'Private note',
        ], (int) $user->getKey());

        $this->assertNotEmpty($patient->public_id);
        $this->assertNotEmpty($patient->reference);
        $this->assertSame('21620000000', $patient->getAttribute('normalized_phone'));

        try {
            $service->create([
                'first_name' => 'Amal', 'last_name' => 'Ben Salem', 'phone' => '21620000000',
            ], (int) $user->getKey());
            $this->fail('Duplicate should fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('phone', $exception->errors());
        }

        $service->create([
            'first_name' => 'Amal',
            'last_name' => 'Ben Salem',
            'phone' => '21620000000',
            'duplicate_override_reason' => 'Identity verified as a distinct patient.',
        ], (int) $user->getKey());

        $this->getJson(route('dar-hijama.patients.index', ['search' => $patient->reference]))
            ->assertOk()
            ->assertJsonPath('total', 1);
        $this->postJson(route('dar-hijama.patients.store'), [
            'first_name' => 'Media',
            'last_name' => 'Patient',
            'media' => UploadedFile::fake()->image('patient.jpg'),
        ])->assertCreated();
        $this->assertDatabaseHas('media', ['type' => 'patient-document', 'disk' => 'local']);
        $this->assertDatabaseHas('dar_hijama_analytics_events', ['event' => 'patient_created']);
        $this->assertDatabaseHas('audit_logs', ['entity_type' => Patient::class, 'action' => 'create']);
        $this->assertDatabaseHas('dar_hijama_access_logs', ['resource' => 'patients', 'action' => 'read']);
    }

    public function test_availability_state_machine_reschedule_cancel_completion_follow_up_and_failure_recovery(): void
    {
        Storage::fake('local');
        [$user, $patient, $practitioner, $startsAt] = $this->fixture();
        $workflow = app(AppointmentWorkflowService::class);
        $machine = app(AppointmentStateMachine::class);
        $appointment = $workflow->book($this->booking($patient, $practitioner, $startsAt), (int) $user->getKey());

        try {
            $workflow->book($this->booking($patient, $practitioner, $startsAt->addMinutes(10)), (int) $user->getKey());
            $this->fail('Overlap should fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('starts_at', $exception->errors());
        }

        try {
            $workflow->book(
                $this->booking($patient, $practitioner, $startsAt->startOfDay()->addHours(6)),
                (int) $user->getKey(),
            );
            $this->fail('Outside-hours booking should fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('starts_at', $exception->errors());
        }

        $practitioner->update(['active' => false]);
        try {
            $workflow->book(
                $this->booking($patient, $practitioner, $startsAt->addWeeks(2)),
                (int) $user->getKey(),
            );
            $this->fail('Inactive practitioner booking should fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('practitioner_id', $exception->errors());
        }
        $practitioner->update(['active' => true]);

        try {
            $machine->transition($appointment, AppointmentStatus::Completed, (int) $user->getKey());
            $this->fail('Invalid transition should fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }

        $appointment = $workflow->confirm($appointment, (int) $user->getKey());
        $appointment = $workflow->assign($appointment, $practitioner, (int) $user->getKey());
        $appointment = $workflow->start($appointment, (int) $user->getKey());
        $appointment = $workflow->complete($appointment, [
            'attendance' => 'present',
            'general_note' => 'Completed',
            'practitioner_note' => 'Private',
            'follow_up_required' => true,
        ], (int) $user->getKey(), UploadedFile::fake()->create('session.pdf', 10));
        $this->assertSame(AppointmentStatus::Completed, $appointment->status);
        $this->assertDatabaseHas('media', ['type' => 'session-attachment', 'disk' => 'local']);

        $followUp = $workflow->createFollowUp($appointment, $startsAt->addWeek(), (int) $user->getKey());
        $this->assertSame($appointment->getKey(), $followUp->getAttribute('original_appointment_id'));

        try {
            $workflow->createFollowUp($appointment, $startsAt->addWeeks(2), (int) $user->getKey());
            $this->fail('Duplicate follow-up should fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('follow_up', $exception->errors());
        }

        [$user2, $patient2, $practitioner2, $time2] = $this->fixture(3);
        $rescheduled = $workflow->book($this->booking($patient2, $practitioner2, $time2), (int) $user2->getKey());
        $rescheduled = $workflow->confirm($rescheduled, (int) $user2->getKey());
        $replacement = $workflow->reschedule(
            $rescheduled, $time2->addWeek(), 'Patient requested change.', (int) $user2->getKey(),
        );
        $this->assertSame(AppointmentStatus::Rescheduled, $rescheduled->fresh()?->status);
        $cancelled = $workflow->cancel(
            $replacement, 'Cancelled by patient.', (int) $user2->getKey(), 'patient',
        );
        $this->assertSame(AppointmentStatus::Cancelled, $cancelled->status);

        config()->set('applications.dar-hijama.notifications.channels', ['email']);
        [$user3, $patient3, $practitioner3, $time3] = $this->fixture(5);
        $created = $workflow->book($this->booking($patient3, $practitioner3, $time3), (int) $user3->getKey());
        $this->assertTrue($created->exists);
        $this->assertDatabaseHas('notification_deliveries', [
            'recipient_id' => (string) $created->getKey(), 'status' => 'failed',
        ]);
        $this->assertDatabaseHas('dar_hijama_analytics_events', ['event' => 'appointment_completed']);
        $this->assertDatabaseHas('dar_hijama_analytics_events', ['event' => 'follow_up_created']);
        $this->assertDatabaseHas('dar_hijama_analytics_events', ['event' => 'appointment_rescheduled']);
        $this->assertDatabaseHas('dar_hijama_analytics_events', ['event' => 'appointment_cancelled']);

        [$user4, $patient4, $practitioner4, $time4] = $this->fixture(7);
        $noShow = $workflow->book($this->booking($patient4, $practitioner4, $time4), (int) $user4->getKey());
        $noShow = $workflow->confirm($noShow, (int) $user4->getKey());
        $noShow = $workflow->assign($noShow, $practitioner4, (int) $user4->getKey());
        $noShow = $machine->transition(
            $noShow,
            AppointmentStatus::NoShow,
            (int) $user4->getKey(),
            'Patient did not attend.',
        );
        $this->assertSame(AppointmentStatus::NoShow, $noShow->status);
        $this->assertDatabaseHas('dar_hijama_analytics_events', ['event' => 'appointment_no_show']);
    }

    public function test_private_fields_and_sensitive_actions_require_permissions(): void
    {
        [$admin, $patient, $practitioner, $time] = $this->fixture();
        $appointment = app(AppointmentWorkflowService::class)->book(
            $this->booking($patient, $practitioner, $time),
            (int) $admin->getKey(),
        );

        $limited = User::factory()->create();
        foreach ([
            'dar-hijama.access',
            'dar-hijama.patients.view',
            'dar-hijama.appointments.view',
        ] as $permission) {
            Permission::findOrCreate($permission);
        }
        $limited->givePermissionTo([
            'dar-hijama.access',
            'dar-hijama.patients.view',
            'dar-hijama.appointments.view',
        ]);

        $this->actingAs($limited)
            ->getJson(route('dar-hijama.patients.index'))
            ->assertOk()
            ->assertJsonMissing(['notes' => 'Private note']);
        $this->actingAs($limited)
            ->postJson(route('dar-hijama.appointments.cancel', $appointment), [
                'reason' => 'Unauthorized cancellation.',
                'channel' => 'administration',
            ])
            ->assertForbidden();
    }

    public function test_workflow_migration_rolls_back_and_reapplies_on_sqlite(): void
    {
        $migration = require base_path(
            'Applications/DarHijama/database/migrations/2026_07_28_020000_add_production_workflow_to_dar_hijama.php',
        );
        $migration->down();
        $this->assertFalse(Schema::hasColumn('dar_hijama_patients', 'normalized_phone'));
        $migration->up();
        $this->assertTrue(Schema::hasColumn('dar_hijama_patients', 'normalized_phone'));
    }

    private function fixture(int $weeks = 1): array
    {
        $user = $this->authorizedUser();
        $patient = app(PatientService::class)->create([
            'first_name' => 'Patient',
            'last_name' => (string) $weeks,
            'phone' => '+21620000'.$weeks,
            'notes' => 'Private note',
        ], (int) $user->getKey());
        $practitioner = Practitioner::query()->create([
            'name' => 'Practitioner '.$weeks,
            'user_id' => $user->getKey(),
            'active' => true,
            'home_visits' => true,
            'maximum_daily_appointments' => 8,
            'appointment_duration_minutes' => 45,
            'preparation_buffer_minutes' => 5,
        ]);
        $time = CarbonImmutable::now()->addWeeks($weeks)->startOfWeek()->setTime(10, 0);
        $practitioner->schedules()->create([
            'day_of_week' => $time->dayOfWeekIso,
            'starts_at' => '08:00',
            'ends_at' => '18:00',
            'break_starts_at' => '12:00',
            'break_ends_at' => '13:00',
        ]);

        return [$user, $patient, $practitioner, $time];
    }

    private function booking(Patient $patient, Practitioner $practitioner, CarbonImmutable $time): array
    {
        return [
            'patient_id' => $patient->getKey(),
            'practitioner_id' => $practitioner->getKey(),
            'starts_at' => $time,
            'type' => 'initial_consultation',
            'visit_mode' => 'clinic',
            'source' => 'administration',
            'internal_notes' => 'Private',
        ];
    }

    private function authorizedUser(): User
    {
        $user = User::factory()->create();
        $permissions = collect(app(ApplicationRegistry::class)->permissions('dar-hijama'))
            ->map(fn ($definition) => $definition->name)
            ->all();

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $user->givePermissionTo($permissions);
        $this->actingAs($user);

        return $user;
    }
}
