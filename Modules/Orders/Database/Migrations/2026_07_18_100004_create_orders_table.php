<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // NJ-{annee}-{sequence}, generation race-safe — voir Modules\Orders\Services\CreateOrderService.
            $table->string('reference')->unique();
            $table->foreignId('client_id')
                ->constrained('clients')
                ->restrictOnDelete();
            // Modele discute/choisi cote commande. Conserve aux cotes de invitations.template_id
            // (regle "copy-on-create", voir PHASE_1.md §1/§6).
            $table->foreignId('template_id')
                ->nullable()
                ->constrained('templates')
                ->nullOnDelete();
            // TND = 3 decimales (millimes). Jamais "DT" en base : voir config('whatsapp.offer')
            // qui reste une chaine d'affichage, distincte de cette colonne.
            $table->decimal('subtotal', 10, 3);
            $table->decimal('discount', 10, 3)->default(0);
            $table->decimal('total', 10, 3);
            $table->decimal('paid_amount', 10, 3)->default(0);
            $table->string('currency', 3)->default('TND');
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('status')->default('nouveau');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
