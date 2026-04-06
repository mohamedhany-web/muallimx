<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (! Schema::hasColumn('coupons', 'beneficiary_user_id')) {
                $table->foreignId('beneficiary_user_id')->nullable()->after('is_public')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('coupons', 'commission_percent')) {
                $table->decimal('commission_percent', 5, 2)->nullable()->after('beneficiary_user_id');
            }
            if (! Schema::hasColumn('coupons', 'commission_on')) {
                $table->string('commission_on', 32)->default('final_paid')->after('commission_percent');
            }
        });

        Schema::table('coupon_usages', function (Blueprint $table) {
            if (! Schema::hasColumn('coupon_usages', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('user_id')->constrained('orders')->nullOnDelete();
            }
        });

        if (! Schema::hasTable('coupon_commission_accruals')) {
            Schema::create('coupon_commission_accruals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
                $table->foreignId('beneficiary_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
                $table->decimal('base_amount_egp', 12, 2);
                $table->decimal('commission_percent', 5, 2);
                $table->decimal('commission_amount_egp', 12, 2);
                $table->string('status', 32)->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
                $table->index('beneficiary_user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_commission_accruals');

        Schema::table('coupon_usages', function (Blueprint $table) {
            if (Schema::hasColumn('coupon_usages', 'order_id')) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            }
        });

        Schema::table('coupons', function (Blueprint $table) {
            if (Schema::hasColumn('coupons', 'beneficiary_user_id')) {
                $table->dropForeign(['beneficiary_user_id']);
                $table->dropColumn(['beneficiary_user_id', 'commission_percent', 'commission_on']);
            }
        });
    }
};
