<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_testimonials')) {
            return;
        }

        Schema::create('site_testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 16)->default('text'); // text | image
            $table->text('body')->nullable();
            $table->string('author_name', 190)->nullable();
            $table->string('role_label', 190)->nullable();
            $table->string('image_path', 512)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'is_featured', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_testimonials');
    }
};
