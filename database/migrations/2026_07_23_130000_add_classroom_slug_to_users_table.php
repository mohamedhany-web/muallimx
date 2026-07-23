<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'classroom_slug')) {
                $table->string('classroom_slug', 80)->nullable()->unique()->after('calendar_timezone');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'classroom_slug')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['classroom_slug']);
            $table->dropColumn('classroom_slug');
        });
    }
};
