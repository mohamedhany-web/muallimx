<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lecture_video_questions', function (Blueprint $table) {
            /** عدد مرات ظهور السؤال للطالب: null أو 0 = كل مرة، 1 = مرة واحدة، N = حتى N مرات */
            $table->unsignedTinyInteger('show_count')->nullable()->after('points');
        });
    }

    public function down(): void
    {
        Schema::table('lecture_video_questions', function (Blueprint $table) {
            $table->dropColumn('show_count');
        });
    }
};
