<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3 — réponses RSVP soumises par les invités depuis la page publique
 * de l'invitation (Présent / Absent + coordonnées). Table nouvelle, aucune
 * table existante modifiée.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvp_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('status');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->unsignedSmallInteger('guests_count')->default(0);
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvp_responses');
    }
};
