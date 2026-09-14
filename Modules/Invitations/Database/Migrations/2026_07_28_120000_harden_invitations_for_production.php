<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('external_video_url')->nullable()->after('maps_embed_url');
            $table->string('external_audio_url')->nullable()->after('external_video_url');
            $table->string('facebook_url')->nullable()->after('external_audio_url');
            $table->string('instagram_url')->nullable()->after('facebook_url');
        });

        Schema::table('rsvp_responses', function (Blueprint $table) {
            $table->char('identity_hash', 64)->nullable()->after('invitation_id');
            $table->char('correction_token', 64)->nullable()->unique()->after('identity_hash');
            $table->unsignedSmallInteger('submissions_count')->default(1)->after('comment');
            $table->timestamp('last_submitted_at')->nullable()->after('submissions_count');
            $table->unique(['invitation_id', 'identity_hash']);
        });
    }

    public function down(): void
    {
        Schema::table('rsvp_responses', function (Blueprint $table) {
            $table->index('invitation_id', 'rsvp_responses_invitation_id_rollback_index');
        });

        Schema::table('rsvp_responses', function (Blueprint $table) {
            $table->dropUnique(['invitation_id', 'identity_hash']);
            $table->dropUnique(['correction_token']);
            $table->dropColumn([
                'identity_hash',
                'correction_token',
                'submissions_count',
                'last_submitted_at',
            ]);
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn([
                'external_video_url',
                'external_audio_url',
                'facebook_url',
                'instagram_url',
            ]);
        });
    }
};
