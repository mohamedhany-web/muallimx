<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('advanced_courses')) {
            return;
        }

        Schema::table('advanced_courses', function (Blueprint $table) {
            if (! Schema::hasColumn('advanced_courses', 'course_category_id')) {
                $table->foreignId('course_category_id')
                    ->nullable()
                    ->after('academic_subject_id')
                    ->constrained('course_categories')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('advanced_courses') || ! Schema::hasColumn('advanced_courses', 'course_category_id')) {
            return;
        }

        Schema::table('advanced_courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_category_id');
        });
    }
};
