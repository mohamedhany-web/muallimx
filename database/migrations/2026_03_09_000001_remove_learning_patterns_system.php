<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // حذف عناصر المنهج المرتبطة بالأنماط التعليمية
        if (Schema::hasTable('curriculum_items')) {
            DB::table('curriculum_items')->where('item_type', 'App\Models\LearningPattern')->delete();
        }

        if (Schema::hasTable('learning_pattern_attempts')) {
            Schema::drop('learning_pattern_attempts');
        }
        if (Schema::hasTable('learning_patterns')) {
            Schema::drop('learning_patterns');
        }
    }

    public function down(): void
    {
        // لا نعيد إنشاء الجداول عند الـ rollback
    }
};
