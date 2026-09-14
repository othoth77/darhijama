<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dar_hijama_patients', function (Blueprint $table): void {
            $table->uuid('public_id')->nullable()->unique();
            $table->string('reference', 30)->nullable()->unique();
            $table->string('normalized_phone', 30)->nullable()->index();
            $table->string('gender', 20)->nullable();
            $table->string('secondary_phone', 30)->nullable();
            $table->string('governorate', 100)->nullable()->index();
            $table->string('city', 100)->nullable()->index();
            $table->text('address')->nullable();
            $table->string('preferred_language', 10)->default('fr');
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->boolean('active')->default(true)->index();
            $table->string('consent_status', 30)->default('pending')->index();
            $table->timestamp('consent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('duplicate_override_reason')->nullable();
            $table->index(['last_name', 'first_name']);
        });

        Schema::table('dar_hijama_practitioners', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('maximum_daily_appointments')->default(12);
            $table->unsignedSmallInteger('appointment_duration_minutes')->default(45);
            $table->unsignedSmallInteger('preparation_buffer_minutes')->default(0);
            $table->unsignedSmallInteger('travel_buffer_minutes')->default(0);
            $table->boolean('home_visits')->default(false)->index();
            $table->json('service_areas')->nullable();
        });

        Schema::table('dar_hijama_appointments', function (Blueprint $table): void {
            $table->uuid('public_id')->nullable()->unique();
            $table->string('reference', 30)->nullable()->unique();
            $table->string('type', 40)->default('initial_consultation')->index();
            $table->string('visit_mode', 20)->default('clinic')->index();
            $table->unsignedSmallInteger('expected_duration_minutes')->default(45);
            $table->string('governorate', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->text('address')->nullable();
            $table->text('location_notes')->nullable();
            $table->renameColumn('notes', 'internal_notes');
            $table->text('patient_visible_notes')->nullable();
            $table->string('source', 30)->default('administration')->index();
            $table->text('cancellation_reason')->nullable();
            $table->text('rescheduling_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('original_appointment_id')->nullable()
                ->constrained('dar_hijama_appointments')->nullOnDelete();
            $table->index(['practitioner_id', 'starts_at', 'status'], 'dh_practitioner_slot_idx');
            $table->index(['patient_id', 'starts_at'], 'dh_patient_date_idx');
        });

        Schema::create('dar_hijama_practitioner_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('practitioner_id')->constrained('dar_hijama_practitioners')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->time('break_starts_at')->nullable();
            $table->time('break_ends_at')->nullable();
            $table->string('service_area', 100)->nullable();
            $table->boolean('home_visits')->default(false);
            $table->unique(['practitioner_id', 'day_of_week', 'service_area'], 'dh_schedule_unique');
        });

        Schema::create('dar_hijama_practitioner_unavailability', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('practitioner_id')->constrained('dar_hijama_practitioners')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('reason')->nullable();
            $table->index(['practitioner_id', 'starts_at', 'ends_at'], 'dh_unavailable_idx');
        });

        Schema::create('dar_hijama_appointment_transitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->constrained('dar_hijama_appointments')->cascadeOnDelete();
            $table->string('from_status', 30);
            $table->string('to_status', 30)->index();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('channel', 30)->default('administration');
            $table->timestamp('occurred_at')->index();
        });

        Schema::create('dar_hijama_session_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('dar_hijama_appointments')->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('attendance', 30)->default('present');
            $table->text('general_note')->nullable();
            $table->text('practitioner_note')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->date('recommended_follow_up_date')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dar_hijama_analytics_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event', 60)->index();
            $table->string('subject_type');
            $table->string('subject_id');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->index(['subject_type', 'subject_id'], 'dh_analytics_subject_idx');
        });

        Schema::create('dar_hijama_access_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resource', 100)->index();
            $table->string('action', 30);
            $table->string('subject_id')->nullable();
            $table->timestamp('occurred_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dar_hijama_access_logs');
        Schema::dropIfExists('dar_hijama_analytics_events');
        Schema::dropIfExists('dar_hijama_session_records');
        Schema::dropIfExists('dar_hijama_appointment_transitions');
        Schema::dropIfExists('dar_hijama_practitioner_unavailability');
        Schema::dropIfExists('dar_hijama_practitioner_schedules');

        Schema::table('dar_hijama_appointments', function (Blueprint $table): void {
            $table->index('practitioner_id', 'dh_practitioner_rollback_idx');
            $table->index('patient_id', 'dh_patient_rollback_idx');
        });

        Schema::table('dar_hijama_appointments', function (Blueprint $table): void {
            $table->dropIndex('dh_practitioner_slot_idx');
            $table->dropIndex('dh_patient_date_idx');
            $table->dropUnique(['public_id']);
            $table->dropUnique(['reference']);
            $table->dropIndex(['type']);
            $table->dropIndex(['visit_mode']);
            $table->dropIndex(['source']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['original_appointment_id']);
            $table->dropColumn([
                'public_id', 'reference', 'type', 'visit_mode', 'expected_duration_minutes',
                'governorate', 'city', 'address', 'location_notes', 'patient_visible_notes',
                'source', 'cancellation_reason', 'rescheduling_reason', 'confirmed_at',
                'completed_at', 'cancelled_at', 'created_by', 'updated_by',
                'original_appointment_id',
            ]);
            $table->renameColumn('internal_notes', 'notes');
        });

        Schema::table('dar_hijama_practitioners', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropIndex(['home_visits']);
            $table->dropColumn([
                'user_id', 'maximum_daily_appointments', 'appointment_duration_minutes',
                'preparation_buffer_minutes', 'travel_buffer_minutes', 'home_visits',
                'service_areas',
            ]);
        });

        Schema::table('dar_hijama_patients', function (Blueprint $table): void {
            $table->dropIndex(['last_name', 'first_name']);
            $table->dropUnique(['public_id']);
            $table->dropUnique(['reference']);
            $table->dropIndex(['normalized_phone']);
            $table->dropIndex(['governorate']);
            $table->dropIndex(['city']);
            $table->dropIndex(['active']);
            $table->dropIndex(['consent_status']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'public_id', 'reference', 'normalized_phone', 'gender', 'secondary_phone',
                'governorate', 'city', 'address', 'preferred_language',
                'emergency_contact_name', 'emergency_contact_phone', 'active',
                'consent_status', 'consent_at', 'created_by', 'updated_by',
                'duplicate_override_reason',
            ]);
        });
    }
};
