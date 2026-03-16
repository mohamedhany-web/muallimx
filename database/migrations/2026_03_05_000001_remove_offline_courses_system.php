<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إزالة نظام الكورسات الأوفلاين بالكامل وجميع الجداول والعلاقات المرتبطة.
     */
    public function up(): void
    {
        // إزالة المفاتيح الأجنبية التي تشير إلى جداول الأوفلاين (إن وُجدت)
        if (Schema::hasTable('exams') && Schema::hasColumn('exams', 'offline_course_id')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropForeign(['offline_course_id']);
            });
        }
        if (Schema::hasTable('instructor_agreements') && Schema::hasColumn('instructor_agreements', 'offline_course_id')) {
            Schema::table('instructor_agreements', function (Blueprint $table) {
                $table->dropForeign(['offline_course_id']);
            });
        }

        // حذف الجداول المرتبطة بالأوفلاين (بالترتيب بسبب التبعيات)
        Schema::dropIfExists('offline_activity_submissions');
        Schema::dropIfExists('offline_attendance');
        Schema::dropIfExists('offline_course_resources');
        Schema::dropIfExists('offline_lectures');
        Schema::dropIfExists('offline_activities');
        Schema::dropIfExists('offline_course_enrollments');
        Schema::dropIfExists('offline_course_groups');
        Schema::dropIfExists('offline_courses');
        Schema::dropIfExists('offline_locations');

        // إزالة عمود offline_course_id من exams و instructor_agreements
        if (Schema::hasTable('exams') && Schema::hasColumn('exams', 'offline_course_id')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropColumn('offline_course_id');
            });
        }
        if (Schema::hasTable('instructor_agreements') && Schema::hasColumn('instructor_agreements', 'offline_course_id')) {
            Schema::table('instructor_agreements', function (Blueprint $table) {
                $table->dropColumn('offline_course_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // الاسترجاع يتطلب إعادة تشغيل migrations إنشاء جداول الأوفلاين الأصلية.
    }
};
