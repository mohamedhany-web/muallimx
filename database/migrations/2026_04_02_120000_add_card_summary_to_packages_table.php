<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('packages') && !Schema::hasColumn('packages', 'card_summary')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->text('card_summary')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('packages') && Schema::hasColumn('packages', 'card_summary')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->dropColumn('card_summary');
            });
        }
    }
};
