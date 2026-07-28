<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_click_events', function (Blueprint $table) {
            $table->id();
            $table->string('source', 64)->index();
            $table->unsignedBigInteger('invitation_id')->nullable()->index();
            $table->char('visitor_hash', 64);
            $table->char('deduplication_key', 64)->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
        });

        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('source', 64)->index();
            $table->string('subject_type', 40)->nullable()->index();
            $table->string('subject_id', 64)->nullable()->index();
            $table->char('visitor_hash', 64);
            $table->char('deduplication_key', 64)->unique();
            $table->timestamp('occurred_at')->index();
        });

        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40)->index();
            $table->string('subject_type', 40)->nullable()->index();
            $table->string('subject_id', 64)->nullable()->index();
            $table->string('source', 64)->nullable()->index();
            $table->char('visitor_hash', 64)->nullable();
            $table->char('deduplication_key', 64)->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('page_views');
        Schema::dropIfExists('whatsapp_click_events');
    }
};
