<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('classroom_meeting_reports')) {
            return;
        }

        Schema::create('classroom_meeting_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('classroom_meeting_id');
            $table->unsignedBigInteger('user_id');
            $table->string('title')->nullable();
            $table->text('summary')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('n8n_execution_id')->nullable();
            $table->string('audio_path')->nullable();
            $table->string('storage_disk')->nullable();
            $table->timestamps();

            $table->foreign('classroom_meeting_id')->references('id')->on('classroom_meetings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['classroom_meeting_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_meeting_reports');
    }
};
