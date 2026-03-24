<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classroom_meetings', function (Blueprint $table) {
            $table->string('recording_disk', 40)->nullable()->after('ended_at');
            $table->string('recording_path', 600)->nullable()->after('recording_disk');
            $table->string('recording_mime_type', 100)->nullable()->after('recording_path');
            $table->unsignedBigInteger('recording_size')->nullable()->after('recording_mime_type');
            $table->unsignedInteger('recording_duration_seconds')->nullable()->after('recording_size');
            $table->timestamp('recording_uploaded_at')->nullable()->after('recording_duration_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('classroom_meetings', function (Blueprint $table) {
            $table->dropColumn([
                'recording_disk',
                'recording_path',
                'recording_mime_type',
                'recording_size',
                'recording_duration_seconds',
                'recording_uploaded_at',
            ]);
        });
    }
};
