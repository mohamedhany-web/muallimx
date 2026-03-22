<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->foreignId('wallet_transaction_id')
                ->nullable()
                ->after('classroom_meeting_id')
                ->constrained('wallet_transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wallet_transaction_id');
        });
    }
};
