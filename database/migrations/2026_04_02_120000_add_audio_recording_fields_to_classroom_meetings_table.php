<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('classroom_meetings')) {
            return;
        }

        Schema::table('classroom_meetings', function (Blueprint $table) {
            if (!Schema::hasColumn('classroom_meetings', 'recording_audio_path')) {
                $table->string('recording_audio_path', 600)->nullable()->after('recording_path');
            }
            if (!Schema::hasColumn('classroom_meetings', 'recording_audio_mime_type')) {
                $table->string('recording_audio_mime_type', 100)->nullable()->after('recording_audio_path');
            }
            if (!Schema::hasColumn('classroom_meetings', 'recording_audio_size')) {
                $table->unsignedBigInteger('recording_audio_size')->nullable()->after('recording_audio_mime_type');
            }
            if (!Schema::hasColumn('classroom_meetings', 'recording_audio_duration_seconds')) {
                $table->unsignedInteger('recording_audio_duration_seconds')->nullable()->after('recording_audio_size');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('classroom_meetings')) {
            return;
        }

        Schema::table('classroom_meetings', function (Blueprint $table) {
            foreach ([
                'recording_audio_duration_seconds',
                'recording_audio_size',
                'recording_audio_mime_type',
                'recording_audio_path',
            ] as $col) {
                if (Schema::hasColumn('classroom_meetings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
