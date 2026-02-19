<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة نوع "نسبة من الكورس" لاتفاقيات المدربين.
     * عند اختياره يُربط الاتفاقية بكورس أونلاين (advanced_course) ونسبة مئوية.
     */
    public function up(): void
    {
        Schema::table('instructor_agreements', function (Blueprint $table) {
            if (!Schema::hasColumn('instructor_agreements', 'advanced_course_id')) {
                $table->foreignId('advanced_course_id')->nullable()->after('offline_course_id')
                    ->constrained('advanced_courses')->onDelete('set null')
                    ->comment('كورس أونلاين عند نوع الاتفاقية: نسبة من الكورس');
            }
            if (!Schema::hasColumn('instructor_agreements', 'course_percentage')) {
                $table->decimal('course_percentage', 5, 2)->nullable()->after('advanced_course_id')
                    ->comment('نسبة المدرب من سعر التفعيل (0-100) عند نوع نسبة من الكورس');
            }
        });
    }

    public function down(): void
    {
        Schema::table('instructor_agreements', function (Blueprint $table) {
            if (Schema::hasColumn('instructor_agreements', 'course_percentage')) {
                $table->dropColumn('course_percentage');
            }
            if (Schema::hasColumn('instructor_agreements', 'advanced_course_id')) {
                $table->dropForeign(['advanced_course_id']);
            }
        });
    }
};
