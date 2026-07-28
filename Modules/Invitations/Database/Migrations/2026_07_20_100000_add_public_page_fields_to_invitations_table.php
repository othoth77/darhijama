<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3 — champs additifs pour la page publique d'invitation. Purement
 * additif (colonnes nullable), aucune colonne existante modifiée ou
 * supprimée — voir contrainte explicite Phase 3 : "Ne modifier aucune
 * fonctionnalité des phases précédentes."
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('dress_code')->nullable()->after('message');
            $table->text('additional_info')->nullable()->after('dress_code');
            $table->string('contact_name')->nullable()->after('additional_info');
            $table->string('contact_phone')->nullable()->after('contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['dress_code', 'additional_info', 'contact_name', 'contact_phone']);
        });
    }
};
