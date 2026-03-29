<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('support_inquiry_categories')) {
            Schema::create('support_inquiry_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $now = now();
            DB::table('support_inquiry_categories')->insert([
                ['name' => 'مشكلة تقنية في المنصة', 'sort_order' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'الفوترة والدفع والاشتراك', 'sort_order' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'استفسار عن كورس أو محتوى', 'sort_order' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'أخرى', 'sort_order' => 90, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (Schema::hasTable('support_tickets') && !Schema::hasColumn('support_tickets', 'support_inquiry_category_id')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->foreignId('support_inquiry_category_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('support_inquiry_categories')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('support_tickets') && Schema::hasColumn('support_tickets', 'support_inquiry_category_id')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->dropConstrainedForeignId('support_inquiry_category_id');
            });
        }
        Schema::dropIfExists('support_inquiry_categories');
    }
};
