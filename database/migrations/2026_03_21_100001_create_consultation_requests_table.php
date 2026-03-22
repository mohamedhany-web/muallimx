<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('price_amount', 10, 2);
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->text('student_message')->nullable();
            $table->string('payment_reference', 255)->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->timestamp('payment_reported_at')->nullable();
            $table->timestamp('paid_confirmed_at')->nullable();
            $table->foreignId('paid_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('classroom_meeting_id')->nullable()->index();
            $table->timestamps();

            $table->index(['instructor_id', 'status']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_requests');
    }
};
