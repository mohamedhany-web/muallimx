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
        Schema::table('employee_salary_payments', function (Blueprint $table) {
            $table->string('transfer_receipt_path')->nullable()->after('notes')->comment('مسار إيصال التحويل');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_salary_payments', function (Blueprint $table) {
            $table->dropColumn('transfer_receipt_path');
        });
    }
};
