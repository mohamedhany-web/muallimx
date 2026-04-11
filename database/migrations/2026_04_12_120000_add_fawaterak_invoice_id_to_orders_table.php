<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'fawaterak_invoice_id')) {
                $table->string('fawaterak_invoice_id', 32)->nullable()->after('payment_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'fawaterak_invoice_id')) {
                $table->dropColumn('fawaterak_invoice_id');
            }
        });
    }
};
