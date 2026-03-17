<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculum_library_items', function (Blueprint $table) {
            $table->boolean('is_free_preview')
                ->default(false)
                ->after('is_active')
                ->comment('عنصر متاح كمشاهدة / تجربة مجانية بدون اشتراك');
        });
    }

    public function down(): void
    {
        Schema::table('curriculum_library_items', function (Blueprint $table) {
            $table->dropColumn('is_free_preview');
        });
    }
};

