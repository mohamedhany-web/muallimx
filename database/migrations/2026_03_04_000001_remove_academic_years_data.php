<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إزالة كل بيانات السنوات الدراسية (الميزة ملغاة).
     */
    public function up(): void
    {
        if (!Schema::hasTable('academic_years')) {
            return;
        }

        // تجنب حذف الطلبات: جعل ربط الطلبات بالسنوات الدراسية فارغاً
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'academic_year_id')) {
            DB::table('orders')->update(['academic_year_id' => null]);
        }

        // إفراغ ربط الكورسات بالسنوات (SET NULL عند الحذف، لكن نُصفّي يدوياً للوضوح)
        if (Schema::hasTable('advanced_courses') && Schema::hasColumn('advanced_courses', 'academic_year_id')) {
            DB::table('advanced_courses')->update(['academic_year_id' => null]);
        }

        // حذف المواد الدراسية المرتبطة بالسنوات (قبل حذف السنوات بسبب FK)
        if (Schema::hasTable('academic_subjects')) {
            DB::table('academic_subjects')->delete();
        }

        // حذف جداول الربط ثم السنوات
        if (Schema::hasTable('academic_year_courses')) {
            DB::table('academic_year_courses')->delete();
        }
        if (Schema::hasTable('academic_year_instructors')) {
            DB::table('academic_year_instructors')->delete();
        }

        // إزالة تسجيلات المسارات المرتبطة بالسنوات الدراسية
        if (Schema::hasTable('learning_path_enrollments') && Schema::hasColumn('learning_path_enrollments', 'academic_year_id')) {
            DB::table('learning_path_enrollments')->whereNotNull('academic_year_id')->delete();
        }

        DB::table('academic_years')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا استعادة للبيانات المحذوفة
    }
};
