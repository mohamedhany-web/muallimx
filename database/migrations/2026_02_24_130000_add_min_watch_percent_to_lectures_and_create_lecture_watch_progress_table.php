<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lectures')) {
            Schema::table('lectures', function (Blueprint $table) {
                if (!Schema::hasColumn('lectures', 'min_watch_percent_to_unlock_next')) {
                    $table->unsignedTinyInteger('min_watch_percent_to_unlock_next')
                        ->nullable()
                        ->after('duration_minutes');
                }
            });
        }

        if (!Schema::hasTable('lecture_watch_progress')) {
            Schema::create('lecture_watch_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lecture_id')->constrained('lectures')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedInteger('watch_time_seconds')->default(0);
                $table->unsignedInteger('video_duration_seconds')->default(0);
                $table->unsignedTinyInteger('progress_percent')->default(0);
                $table->boolean('is_completed')->default(false);
                $table->timestamps();
                $table->unique(['lecture_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lecture_watch_progress')) {
            Schema::dropIfExists('lecture_watch_progress');
        }

        if (Schema::hasTable('lectures')) {
            Schema::table('lectures', function (Blueprint $table) {
                if (Schema::hasColumn('lectures', 'min_watch_percent_to_unlock_next')) {
                    $table->dropColumn('min_watch_percent_to_unlock_next');
                }
            });
        }
    }
};

