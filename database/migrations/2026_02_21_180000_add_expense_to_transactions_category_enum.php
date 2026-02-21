<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * إضافة 'expense' إلى قيم category في جدول transactions (للمصروفات المعتمدة).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `transactions` MODIFY COLUMN `category` ENUM('course_payment', 'subscription', 'refund', 'commission', 'fee', 'learning_path_payment', 'expense', 'other') DEFAULT 'other'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `transactions` MODIFY COLUMN `category` ENUM('course_payment', 'subscription', 'refund', 'commission', 'fee', 'learning_path_payment', 'other') DEFAULT 'other'");
    }
};
