<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_datasets', function (Blueprint $table) {
            if (!Schema::hasColumn('community_datasets', 'status')) {
                $table->string('status', 20)->default('approved')->after('is_active'); // pending, approved, rejected
            }
            if (!Schema::hasColumn('community_datasets', 'created_by_user_id')) {
                $table->foreignId('created_by_user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('community_datasets', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn(['status', 'created_by_user_id']);
        });
    }
};
