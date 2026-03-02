<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('platform')->default('bunny'); // bunny, youtube, vimeo, etc.
            $table->boolean('is_active')->default(true);

            // API access information
            $table->string('library_id')->nullable();
            $table->string('cdn_hostname')->nullable();
            $table->string('api_key')->nullable();
            $table->string('token_auth_key')->nullable();

            // حقل اختياري لأي إعدادات إضافية (JSON)
            $table->json('extra_config')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_providers');
    }
};

