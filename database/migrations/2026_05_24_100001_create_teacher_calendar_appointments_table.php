<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_calendar_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->enum('schedule_type', ['fixed', 'temporary'])->default('temporary');
            $table->string('family_timezone', 64);
            $table->time('family_time');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('teacher_timezone', 64);
            $table->unsignedTinyInteger('weekday')->nullable()->comment('0=Sunday .. 6=Saturday');
            $table->char('month_key', 7)->nullable()->comment('Y-m for fixed month selection');
            $table->json('selected_dates')->nullable();
            $table->string('color', 7)->default('#8B5CF6');
            $table->boolean('notify_platform')->default(true);
            $table->boolean('notify_email')->default(true);
            $table->unsignedSmallInteger('reminder_minutes')->default(5);
            $table->enum('status', ['active', 'cancelled'])->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['schedule_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_calendar_appointments');
    }
};
