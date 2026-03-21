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
            if (!Schema::hasColumn('classroom_meetings', 'planned_duration_minutes')) {
                $table->unsignedInteger('planned_duration_minutes')->nullable()->after('scheduled_for');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('classroom_meetings')) {
            return;
        }

        Schema::table('classroom_meetings', function (Blueprint $table) {
            if (Schema::hasColumn('classroom_meetings', 'planned_duration_minutes')) {
                $table->dropColumn('planned_duration_minutes');
            }
        });
    }
};

