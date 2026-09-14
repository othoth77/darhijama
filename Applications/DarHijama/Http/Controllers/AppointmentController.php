<?php

namespace Applications\DarHijama\Http\Controllers;

use Applications\DarHijama\Application\Services\AppointmentStateMachine;
use Applications\DarHijama\Application\Services\AppointmentWorkflowService;
use Applications\DarHijama\Application\Services\DarHijamaOperations;
use Applications\DarHijama\Domain\Appointment;
use Applications\DarHijama\Domain\AppointmentStatus;
use Applications\DarHijama\Domain\Practitioner;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Mythos\Core\Audit\AuditAction;

class AppointmentController
{
    public function index(Request $request, DarHijamaOperations $operations): JsonResponse
    {
        $operations->recordSensitiveRead('appointments');
        $query = Appointment::query()
            ->with([
                'patient:id,public_id,reference,first_name,last_name',
                'practitioner:id,name',
            ])
            ->latest('starts_at');

        if (! $request->user()?->can('dar-hijama.appointments.manage')) {
            $query->whereHas(
                'practitioner',
                fn ($practitioner) => $practitioner->where('user_id', $request->user()?->getAuthIdentifier()),
            );
        }

        if (! $request->user()?->can('dar-hijama.appointments.view-private-notes')) {
            $query->select([
                'id', 'public_id', 'reference', 'patient_id', 'practitioner_id',
                'type', 'visit_mode', 'starts_at', 'ends_at', 'expected_duration_minutes',
                'governorate', 'city', 'patient_visible_notes', 'source', 'status',
                'confirmed_at', 'completed_at', 'cancelled_at', 'original_appointment_id',
                'created_at', 'updated_at',
            ]);
        }

        return response()->json($query->paginate());
    }

    public function store(Request $request, AppointmentWorkflowService $workflow): JsonResponse
    {
        $appointment = $workflow->book(
            $this->validate($request),
            (int) $request->user()?->getAuthIdentifier(),
        );

        return response()->json($appointment, 201);
    }

    public function update(Request $request, Appointment $appointment, DarHijamaOperations $operations): JsonResponse
    {
        $appointment->update($this->validate($request));
        $operations->audit(AuditAction::Update, $appointment);

        return response()->json($appointment->fresh());
    }

    public function confirm(
        Request $request,
        Appointment $appointment,
        AppointmentWorkflowService $workflow,
    ): JsonResponse {
        return response()->json($workflow->confirm(
            $appointment,
            (int) $request->user()?->getAuthIdentifier(),
        ));
    }

    public function assign(
        Request $request,
        Appointment $appointment,
        AppointmentWorkflowService $workflow,
    ): JsonResponse {
        $validated = $request->validate([
            'practitioner_id' => ['required', 'integer', 'exists:dar_hijama_practitioners,id'],
        ]);

        return response()->json($workflow->assign(
            $appointment,
            Practitioner::query()->findOrFail($validated['practitioner_id']),
            (int) $request->user()?->getAuthIdentifier(),
        ));
    }

    public function transition(
        Request $request,
        Appointment $appointment,
        AppointmentStateMachine $stateMachine,
    ): JsonResponse {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
            'reason' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'channel' => ['sometimes', Rule::in(['administration', 'practitioner', 'patient'])],
        ]);

        return response()->json($stateMachine->transition(
            $appointment,
            AppointmentStatus::from($validated['status']),
            (int) $request->user()?->getAuthIdentifier(),
            $validated['reason'] ?? null,
            $validated['notes'] ?? null,
            $validated['channel'] ?? 'administration',
        ));
    }

    public function reschedule(
        Request $request,
        Appointment $appointment,
        AppointmentWorkflowService $workflow,
    ): JsonResponse {
        $validated = $request->validate([
            'starts_at' => ['required', 'date', 'after:now'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        return response()->json($workflow->reschedule(
            $appointment,
            CarbonImmutable::parse($validated['starts_at']),
            $validated['reason'],
            (int) $request->user()?->getAuthIdentifier(),
        ), 201);
    }

    public function cancel(
        Request $request,
        Appointment $appointment,
        AppointmentWorkflowService $workflow,
    ): JsonResponse {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
            'channel' => ['required', Rule::in(['administration', 'practitioner', 'patient'])],
        ]);

        return response()->json($workflow->cancel(
            $appointment,
            $validated['reason'],
            (int) $request->user()?->getAuthIdentifier(),
            $validated['channel'],
        ));
    }

    public function start(
        Request $request,
        Appointment $appointment,
        AppointmentWorkflowService $workflow,
    ): JsonResponse {
        Gate::authorize('start', $appointment);

        return response()->json($workflow->start(
            $appointment,
            (int) $request->user()?->getAuthIdentifier(),
        ));
    }

    public function complete(
        Request $request,
        Appointment $appointment,
        AppointmentWorkflowService $workflow,
    ): JsonResponse {
        Gate::authorize('complete', $appointment);

        $validated = $request->validate([
            'attendance' => ['required', Rule::in(['present', 'late'])],
            'general_note' => ['nullable', 'string', 'max:5000'],
            'practitioner_note' => ['nullable', 'string', 'max:5000'],
            'follow_up_required' => ['sometimes', 'boolean'],
            'recommended_follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'attachment' => ['nullable', 'file'],
        ]);

        return response()->json($workflow->complete(
            $appointment,
            $validated,
            (int) $request->user()?->getAuthIdentifier(),
            $request->file('attachment'),
        ));
    }

    public function followUp(
        Request $request,
        Appointment $appointment,
        AppointmentWorkflowService $workflow,
    ): JsonResponse {
        $validated = $request->validate([
            'starts_at' => ['required', 'date', 'after:now'],
        ]);

        return response()->json($workflow->createFollowUp(
            $appointment,
            CarbonImmutable::parse($validated['starts_at']),
            (int) $request->user()?->getAuthIdentifier(),
        ), 201);
    }

    public function destroy(Appointment $appointment, DarHijamaOperations $operations): JsonResponse
    {
        $operations->audit(AuditAction::Delete, $appointment);
        $appointment->delete();

        return response()->json(status: 204);
    }

    private function validate(Request $request): array
    {
        return $request->validate([
            'patient_id' => ['required', 'integer', 'exists:dar_hijama_patients,id'],
            'practitioner_id' => ['required', 'integer', 'exists:dar_hijama_practitioners,id'],
            'starts_at' => ['required', 'date'],
            'type' => ['required', Rule::in(['initial_consultation', 'hijama_session', 'follow_up', 'home_visit'])],
            'visit_mode' => ['required', Rule::in(['clinic', 'home'])],
            'expected_duration_minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
            'governorate' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'location_notes' => ['nullable', 'string', 'max:2000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'patient_visible_notes' => ['nullable', 'string', 'max:2000'],
            'source' => ['required', Rule::in(['administration', 'phone', 'whatsapp', 'website', 'walk_in'])],
        ]);
    }
}
