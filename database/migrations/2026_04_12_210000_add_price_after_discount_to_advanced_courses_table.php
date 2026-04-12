<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advanced_courses', function (Blueprint $table) {
            $table->decimal('price_after_discount', 12, 2)
                ->nullable()
                ->after('price')
                ->comment('سعر العرض بعد الخصم — اختياري؛ يُعرض مع خط على السعر الأساسي');
        });
    }

    public function down(): void
    {
        Schema::table('advanced_courses', function (Blueprint $table) {
            $table->dropColumn('price_after_discount');
        });
    }
};
