<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3 — étapes du programme du mariage (Accueil / Cérémonie / Cocktail /
 * Dîner / Soirée...), affichées sur la page publique de l'invitation.
 * Table nouvelle, aucune table existante modifiée.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->time('time')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_steps');
    }
};
