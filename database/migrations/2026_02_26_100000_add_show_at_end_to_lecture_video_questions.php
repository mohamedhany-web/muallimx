<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lecture_video_questions', function (Blueprint $table) {
            $table->boolean('show_at_end')->default(false)->after('timestamp_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('lecture_video_questions', function (Blueprint $table) {
            $table->dropColumn('show_at_end');
        });
    }
};
