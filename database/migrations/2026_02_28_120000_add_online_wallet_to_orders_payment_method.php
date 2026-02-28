<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * إضافة قيم 'online' و 'wallet' لطريقة الدفع في الطلبات (لدفع كاشير وغيره).
     */
    public function up(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'payment_method')) {
            return;
        }
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('bank_transfer', 'cash', 'other', 'online', 'wallet') DEFAULT 'bank_transfer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'payment_method')) {
            return;
        }
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('bank_transfer', 'cash', 'other') DEFAULT 'bank_transfer'");
    }
};
