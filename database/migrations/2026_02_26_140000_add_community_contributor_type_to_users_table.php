<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'community_contributor_type')) {
                $table->string('community_contributor_type', 20)->nullable()->after('is_community_contributor')
                    ->comment('data = مجتمع البيانات, ai = الذكاء الاصطناعي');
            }
        });

        // تحويل المساهمين الحاليين إلى نوع "بيانات" للحفاظ على السلوك
        \DB::table('users')
            ->where('is_community_contributor', true)
            ->whereNull('community_contributor_type')
            ->update(['community_contributor_type' => 'data']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('community_contributor_type');
        });
    }
};
