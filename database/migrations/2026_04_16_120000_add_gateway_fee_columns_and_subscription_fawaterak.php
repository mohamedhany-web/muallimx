<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'gateway_fee_amount')) {
                $table->decimal('gateway_fee_amount', 12, 2)->nullable()->after('amount');
            }
            if (! Schema::hasColumn('payments', 'net_after_gateway_fee')) {
                $table->decimal('net_after_gateway_fee', 12, 2)->nullable()->after('gateway_fee_amount');
            }
        });

        if (Schema::hasTable('subscription_requests')) {
            Schema::table('subscription_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('subscription_requests', 'fawaterak_invoice_id')) {
                    $table->string('fawaterak_invoice_id', 64)->nullable()->after('wallet_id');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'net_after_gateway_fee')) {
                $table->dropColumn('net_after_gateway_fee');
            }
            if (Schema::hasColumn('payments', 'gateway_fee_amount')) {
                $table->dropColumn('gateway_fee_amount');
            }
        });

        if (Schema::hasTable('subscription_requests')) {
            Schema::table('subscription_requests', function (Blueprint $table) {
                if (Schema::hasColumn('subscription_requests', 'fawaterak_invoice_id')) {
                    $table->dropColumn('fawaterak_invoice_id');
                }
            });
        }
    }
};
