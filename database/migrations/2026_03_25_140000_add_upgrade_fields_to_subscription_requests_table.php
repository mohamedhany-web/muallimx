<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscription_requests')) {
            return;
        }

        Schema::table('subscription_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_requests', 'request_type')) {
                $table->string('request_type', 20)->default('new')->after('billing_cycle'); // new|upgrade
            }
            if (!Schema::hasColumn('subscription_requests', 'from_subscription_id')) {
                $table->foreignId('from_subscription_id')->nullable()->after('subscription_id')
                    ->constrained('subscriptions')->nullOnDelete();
            }
            if (!Schema::hasColumn('subscription_requests', 'from_teacher_plan_key')) {
                $table->string('from_teacher_plan_key', 50)->nullable()->after('teacher_plan_key');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('subscription_requests')) {
            return;
        }

        Schema::table('subscription_requests', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_requests', 'from_subscription_id')) {
                $table->dropForeign(['from_subscription_id']);
                $table->dropColumn('from_subscription_id');
            }
            if (Schema::hasColumn('subscription_requests', 'from_teacher_plan_key')) {
                $table->dropColumn('from_teacher_plan_key');
            }
            if (Schema::hasColumn('subscription_requests', 'request_type')) {
                $table->dropColumn('request_type');
            }
        });
    }
};

