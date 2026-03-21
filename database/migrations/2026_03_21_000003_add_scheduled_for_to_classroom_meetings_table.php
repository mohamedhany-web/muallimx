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
            if (!Schema::hasColumn('classroom_meetings', 'scheduled_for')) {
                $table->timestamp('scheduled_for')->nullable()->after('title');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('classroom_meetings')) {
            return;
        }

        Schema::table('classroom_meetings', function (Blueprint $table) {
            if (Schema::hasColumn('classroom_meetings', 'scheduled_for')) {
                $table->dropColumn('scheduled_for');
            }
        });
    }
};

