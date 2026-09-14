<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table): void {
            $table->string('subject_type', 191)->nullable()->change();
        });

        Schema::table('analytics_events', function (Blueprint $table): void {
            $table->string('subject_type', 191)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table): void {
            $table->string('subject_type', 40)->nullable()->change();
        });

        Schema::table('analytics_events', function (Blueprint $table): void {
            $table->string('subject_type', 40)->nullable()->change();
        });
    }
};
