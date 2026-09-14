<?php

use Applications\DarHijama\Application\Services\AppointmentWorkflowService;
use Applications\DarHijama\Domain\Appointment;
use Applications\DarHijama\Domain\Patient;
use Applications\DarHijama\Domain\Practitioner;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mythos\Core\Identity\Models\User;
use Spatie\Permission\Models\Permission;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$mode = $argv[1] ?? '';

if ($mode === 'setup') {
    $user = User::query()->create([
        'name' => 'Contention Probe',
        'email' => 'contention-'.Str::uuid().'@example.test',
        'password' => Str::random(48),
    ]);
    foreach (['dar-hijama.appointments.create', 'dar-hijama.appointments.confirm', 'dar-hijama.appointments.reschedule'] as $name) {
        $user->givePermissionTo(Permission::findOrCreate($name));
    }
    $patient = Patient::query()->create([
        'public_id' => (string) Str::uuid(),
        'reference' => 'LOAD-'.Str::upper(Str::random(8)),
        'first_name' => 'Load',
        'last_name' => 'Probe',
        'active' => true,
        'created_by' => $user->getKey(),
        'updated_by' => $user->getKey(),
    ]);
    $practitioner = Practitioner::query()->create([
        'name' => 'Contention Practitioner',
        'active' => true,
        'home_visits' => false,
        'maximum_daily_appointments' => 100,
        'appointment_duration_minutes' => 30,
        'preparation_buffer_minutes' => 0,
        'travel_buffer_minutes' => 0,
    ]);
    $target = CarbonImmutable::now()->addWeeks(3)->startOfWeek()->setTime(10, 0);
    $practitioner->schedules()->create([
        'day_of_week' => $target->dayOfWeekIso,
        'starts_at' => '08:00',
        'ends_at' => '20:00',
    ]);

    echo json_encode([
        'user' => $user->getKey(),
        'patient' => $patient->getKey(),
        'practitioner' => $practitioner->getKey(),
        'target' => $target->toIso8601String(),
    ], JSON_THROW_ON_ERROR);
    exit(0);
}

$workflow = app(AppointmentWorkflowService::class);

if ($mode === 'confirm') {
    $userId = (int) ($argv[2] ?? 0);
    Auth::loginUsingId($userId);
    $appointment = Appointment::query()->findOrFail((int) ($argv[3] ?? 0));
    $workflow->confirm($appointment, $userId);
    echo 'CONFIRMED';
    exit(0);
}

if ($mode === 'reschedule') {
    $userId = (int) ($argv[2] ?? 0);
    Auth::loginUsingId($userId);
    $appointment = Appointment::query()->findOrFail((int) ($argv[3] ?? 0));

    try {
        $replacement = $workflow->reschedule(
            $appointment,
            CarbonImmutable::parse((string) ($argv[4] ?? '')),
            'Concurrent rescheduling probe',
            $userId,
        );
        echo "RESCHEDULED:{$replacement->getKey()}";
    } catch (ValidationException) {
        echo 'CONFLICT';
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception::class.': '.$exception->getMessage());
        exit(2);
    }
    exit(0);
}

$userId = (int) ($argv[2] ?? 0);
$patientId = (int) ($argv[3] ?? 0);
$practitionerId = (int) ($argv[4] ?? 0);
$startsAt = $argv[5] ?? '';
Auth::loginUsingId($userId);

try {
    $appointment = $workflow->book([
        'patient_id' => $patientId,
        'practitioner_id' => $practitionerId,
        'starts_at' => $startsAt,
        'type' => 'initial_consultation',
        'visit_mode' => 'clinic',
        'source' => 'administration',
    ], $userId);
    echo "CREATED:{$appointment->getKey()}";
} catch (ValidationException) {
    echo 'CONFLICT';
} catch (Throwable $exception) {
    fwrite(STDERR, $exception::class.': '.$exception->getMessage());
    exit(2);
}
