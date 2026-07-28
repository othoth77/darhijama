<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('whatsapp_phone');
            // Chiffres uniquement + indicatif pays (convention config('whatsapp.phone_e164'),
            // ex. 21698123456), calculée par Modules\Orders\Observers\ClientObserver::saving().
            // Clé de déduplication — voir PHASE_1.md §4.
            $table->string('whatsapp_phone_normalized')->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
