<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('curriculum_library_items', 'language')) {
            Schema::table('curriculum_library_items', function (Blueprint $table) {
                $table->string('language', 5)->default('ar')->after('subject')
                    ->comment('لغة المحتوى: ar, en, fr');
            });
        }
        if (!Schema::hasColumn('curriculum_library_items', 'item_type')) {
            Schema::table('curriculum_library_items', function (Blueprint $table) {
                $table->string('item_type', 20)->default('presentation')->after('language')
                    ->comment('presentation=بوربوينت تفاعلي، assignment=وجبة تحميل/إرسال');
            });
        }

        if (!Schema::hasTable('curriculum_library_item_files')) {
            Schema::create('curriculum_library_item_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('curriculum_library_item_id')->constrained('curriculum_library_items')->onDelete('cascade');
                $table->string('path')->comment('مسار الملف في storage');
                $table->string('label')->nullable()->comment('اسم ظاهر للملف');
                $table->string('file_type', 20)->default('presentation')->comment('presentation | assignment');
                $table->unsignedInteger('order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('curriculum_library_preview_opens')) {
            Schema::create('curriculum_library_preview_opens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('curriculum_library_item_id');
                $table->timestamp('opened_at');
                $table->unique('user_id');
                $table->foreign('curriculum_library_item_id', 'cl_preview_item_fk')
                    ->references('id')->on('curriculum_library_items')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_library_preview_opens');
        Schema::dropIfExists('curriculum_library_item_files');
        Schema::table('curriculum_library_items', function (Blueprint $table) {
            $table->dropColumn(['language', 'item_type']);
        });
    }
};
