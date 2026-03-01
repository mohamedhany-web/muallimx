<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_tasks', function (Blueprint $table) {
            $table->string('task_type', 50)->default('general')->after('description')->comment('نوع المهمة: general, video_editing');
        });

        Schema::table('employee_task_deliverables', function (Blueprint $table) {
            $table->string('received_from')->nullable()->after('description')->comment('ممن استلم (لتسليمات المونتاج)');
            $table->string('duration_before')->nullable()->after('received_from')->comment('مدة الفيديو قبل المونتاج');
            $table->string('duration_after')->nullable()->after('duration_before')->comment('مدة الفيديو بعد المونتاج');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_tasks', function (Blueprint $table) {
            $table->dropColumn('task_type');
        });

        Schema::table('employee_task_deliverables', function (Blueprint $table) {
            $table->dropColumn(['received_from', 'duration_before', 'duration_after']);
        });
    }
};
