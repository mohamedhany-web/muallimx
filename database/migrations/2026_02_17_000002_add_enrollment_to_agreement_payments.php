<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ربط مدفوعات الاتفاقية بتفعيل تسجيل الطالب (لنوع "نسبة من الكورس").
     */
    public function up(): void
    {
        Schema::table('agreement_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('agreement_payments', 'student_course_enrollment_id')) {
                $table->unsignedBigInteger('student_course_enrollment_id')->nullable()->after('related_lecture_id')
                    ->comment('تفعيل التسجيل المرتبط بهذا الدفع (نسبة من الكورس)');
            }
        });

        try {
            Schema::table('agreement_payments', function (Blueprint $table) {
                $table->foreign('student_course_enrollment_id')
                    ->references('id')->on('student_course_enrollments')->onDelete('set null');
            });
        } catch (\Throwable $e) {
            // العمود أو الفهرس قد يكون مضافاً مسبقاً
        }

        // إضافة القيمة الجديدة لنوع الدفع (MySQL)
        try {
            \DB::statement("ALTER TABLE agreement_payments MODIFY COLUMN type ENUM(
                'course_completion', 'hourly_teaching', 'monthly_salary', 'bonus', 'other', 'course_activation'
            ) DEFAULT 'course_completion'");
        } catch (\Throwable $e) {
            // إن فشل (مثلاً لو كان النوع string في قاعدة أخرى) نترك العمود كما هو
        }
    }

    public function down(): void
    {
        Schema::table('agreement_payments', function (Blueprint $table) {
            if (Schema::hasColumn('agreement_payments', 'student_course_enrollment_id')) {
                $table->dropForeign(['student_course_enrollment_id']);
                $table->dropColumn('student_course_enrollment_id');
            }
        });
        try {
            \DB::statement("ALTER TABLE agreement_payments MODIFY COLUMN type ENUM(
                'course_completion', 'hourly_teaching', 'monthly_salary', 'bonus', 'other'
            ) DEFAULT 'course_completion'");
        } catch (\Throwable $e) {}
    }
};
