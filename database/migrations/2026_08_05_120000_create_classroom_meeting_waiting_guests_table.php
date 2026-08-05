<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('classroom_meeting_waiting_guests')) {
            return;
        }

        Schema::create('classroom_meeting_waiting_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_meeting_id')->constrained('classroom_meetings')->cascadeOnDelete();
            $table->string('waiting_token', 64)->unique();
            $table->string('display_name', 120);
            $table->string('status', 20)->default('pending'); // pending, admitted, denied, consumed, cancelled
            $table->foreignId('classroom_meeting_participant_id')->nullable()->constrained('classroom_meeting_participants')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('admitted_at')->nullable();
            $table->timestamp('denied_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['classroom_meeting_id', 'status'], 'cmwg_meeting_status_idx');
            $table->index(['classroom_meeting_id', 'created_at'], 'cmwg_meeting_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_meeting_waiting_guests');
    }
};
