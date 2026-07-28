<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            // Morph map applique (Modules\Media\Providers\MediaServiceProvider) : 'template',
            // 'invitation' - jamais le FQCN brut en base. Pas de contrainte FK (relation morph).
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');
            $table->string('disk');
            $table->string('path');
            $table->string('type');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
