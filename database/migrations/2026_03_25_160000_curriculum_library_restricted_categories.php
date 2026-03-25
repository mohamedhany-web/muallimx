<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('curriculum_library_categories') && !Schema::hasColumn('curriculum_library_categories', 'is_restricted')) {
            Schema::table('curriculum_library_categories', function (Blueprint $table) {
                $table->boolean('is_restricted')->default(false)->after('is_active')
                    ->comment('قسم خاص: يظهر فقط للمستخدمين المحددين');
            });
        }

        Schema::dropIfExists('curriculum_library_category_user');

        if (!Schema::hasTable('curriculum_library_category_user')) {
            Schema::create('curriculum_library_category_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                $table->unique(['category_id', 'user_id'], 'cl_cat_user_unique');
                $table->foreign('category_id', 'fk_cl_cu_cat')->references('id')->on('curriculum_library_categories')->cascadeOnDelete();
                $table->foreign('user_id', 'fk_cl_cu_usr')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_library_category_user');
        if (Schema::hasTable('curriculum_library_categories') && Schema::hasColumn('curriculum_library_categories', 'is_restricted')) {
            Schema::table('curriculum_library_categories', function (Blueprint $table) {
                $table->dropColumn('is_restricted');
            });
        }
    }
};
