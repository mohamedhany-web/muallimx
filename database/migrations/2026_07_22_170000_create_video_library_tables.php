<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_library_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_color', 32)->nullable()->default('#c62828');
            $table->string('icon', 64)->nullable()->default('fa-play-circle');
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('video_library_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('video_library_categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('youtube_url', 500);
            $table->string('youtube_id', 20);
            $table->string('thumbnail_url', 500)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'order']);
            $table->index(['category_id', 'is_active']);
            $table->index('youtube_id');
        });

        Schema::create('video_library_preview_opens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained('video_library_videos')->cascadeOnDelete();
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_library_preview_opens');
        Schema::dropIfExists('video_library_videos');
        Schema::dropIfExists('video_library_categories');
    }
};
