<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('consultation_requests', 'platform_wallet_id')) {
                $table->foreignId('platform_wallet_id')
                    ->nullable()
                    ->constrained('wallets')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('consultation_requests', 'payment_method')) {
                $table->string('payment_method', 32)->nullable();
            }
            if (! Schema::hasColumn('consultation_requests', 'payment_proof')) {
                $table->string('payment_proof', 512)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            if (Schema::hasColumn('consultation_requests', 'payment_proof')) {
                $table->dropColumn('payment_proof');
            }
            if (Schema::hasColumn('consultation_requests', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('consultation_requests', 'platform_wallet_id')) {
                $table->dropConstrainedForeignId('platform_wallet_id');
            }
        });
    }
};
