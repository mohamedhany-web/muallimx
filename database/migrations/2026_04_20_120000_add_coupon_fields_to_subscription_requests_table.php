<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_requests', 'coupon_id')) {
                $table->foreignId('coupon_id')
                    ->nullable()
                    ->after('fawaterak_invoice_id')
                    ->constrained('coupons')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('subscription_requests', 'coupon_code')) {
                $table->string('coupon_code', 64)->nullable()->after('coupon_id');
            }
            if (! Schema::hasColumn('subscription_requests', 'original_price')) {
                $table->decimal('original_price', 10, 2)->nullable()->after('coupon_code');
            }
            if (! Schema::hasColumn('subscription_requests', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->nullable()->after('original_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_requests', 'coupon_id')) {
                $table->dropConstrainedForeignId('coupon_id');
            }
            if (Schema::hasColumn('subscription_requests', 'coupon_code')) {
                $table->dropColumn('coupon_code');
            }
            if (Schema::hasColumn('subscription_requests', 'original_price')) {
                $table->dropColumn('original_price');
            }
            if (Schema::hasColumn('subscription_requests', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });
    }
};
