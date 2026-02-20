<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_progress', function (Blueprint $table) {
            if (!Schema::hasColumn('lesson_progress', 'progress_percent')) {
                $table->unsignedTinyInteger('progress_percent')->default(0)->after('watch_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lesson_progress', function (Blueprint $table) {
            $table->dropColumn('progress_percent');
        });
    }
};
