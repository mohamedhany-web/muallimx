<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('live_session_reports')) {
            return;
        }

        Schema::create('live_session_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('live_session_id');
            $table->unsignedBigInteger('instructor_id');
            $table->unsignedBigInteger('live_recording_id')->nullable();
            $table->string('title')->nullable();
            $table->text('summary')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('n8n_execution_id')->nullable();
            $table->string('audio_path')->nullable();
            $table->string('storage_disk')->nullable();
            $table->timestamps();

            $table->foreign('live_session_id')->references('id')->on('live_sessions')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('live_recording_id')->references('id')->on('live_recordings')->nullOnDelete();

            $table->index(['live_session_id', 'status']);
            $table->index(['instructor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_session_reports');
    }
};

