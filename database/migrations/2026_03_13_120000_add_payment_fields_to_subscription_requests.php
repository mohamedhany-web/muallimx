<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->string('payment_method', 50)->nullable()->after('billing_cycle');
            $table->string('payment_proof')->nullable()->after('payment_method');
            $table->foreignId('wallet_id')->nullable()->after('payment_proof')->constrained('wallets')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->dropForeign(['wallet_id']);
            $table->dropColumn(['payment_method', 'payment_proof', 'wallet_id']);
        });
    }
};
