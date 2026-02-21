<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contributor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('photo_path')->nullable();
            $table->text('bio')->nullable();
            $table->text('experience')->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->string('twitter_url', 500)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->string('status', 20)->default('pending'); // pending, approved, rejected
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributor_profiles');
    }
};
