<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            // 1 commande -> N invitations (pas de contrainte unique sur order_id).
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            // Copie depuis orders.template_id a la creation si non fourni ("copy-on-create").
            $table->foreignId('template_id')
                ->nullable()
                ->constrained('templates')
                ->nullOnDelete();
            // Str::ulid()->toBase32() = 26 caracteres Crockford base32, non enumerable.
            $table->string('public_token', 26)->unique();
            $table->string('slug')->nullable();
            $table->string('title')->nullable();
            $table->string('event_type')->default('mariage');
            $table->string('locale', 5)->default('fr');
            $table->string('timezone')->default('Africa/Tunis');
            $table->string('groom_name');
            $table->string('bride_name');
            $table->dateTime('wedding_date');
            $table->string('venue_name')->nullable();
            $table->string('venue_address')->nullable();
            $table->string('maps_embed_url')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->text('message')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->string('status')->default('brouillon');
            // Jamais reecrit si deja publie (idempotence) - voir PublishInvitationService.
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
