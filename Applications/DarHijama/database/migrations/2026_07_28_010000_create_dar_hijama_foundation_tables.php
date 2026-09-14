<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dar_hijama_patients', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 30)->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->date('date_of_birth')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dar_hijama_practitioners', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('license_number', 100)->nullable()->unique();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dar_hijama_appointments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained('dar_hijama_patients')->restrictOnDelete();
            $table->foreignId('practitioner_id')->constrained('dar_hijama_practitioners')->restrictOnDelete();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->nullable();
            $table->string('status', 30)->default('scheduled')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dar_hijama_settings', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->json('value');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dar_hijama_settings');
        Schema::dropIfExists('dar_hijama_appointments');
        Schema::dropIfExists('dar_hijama_practitioners');
        Schema::dropIfExists('dar_hijama_patients');
    }
};
