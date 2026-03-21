<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('classroom_meeting_participants')) {
            return;
        }

        Schema::create('classroom_meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_meeting_id')->constrained('classroom_meetings')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('display_name', 120)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->index(['classroom_meeting_id', 'left_at'], 'cmp_meeting_left_idx');
            $table->index(['classroom_meeting_id', 'last_seen_at'], 'cmp_meeting_seen_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_meeting_participants');
    }
};

