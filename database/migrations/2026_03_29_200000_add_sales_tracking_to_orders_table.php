<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('sales_owner_id')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
            $table->timestamp('sales_contacted_at')->nullable()->after('sales_owner_id');
        });

        Schema::create('sales_order_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_notes');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['sales_owner_id']);
            $table->dropColumn(['sales_owner_id', 'sales_contacted_at']);
        });
    }
};
