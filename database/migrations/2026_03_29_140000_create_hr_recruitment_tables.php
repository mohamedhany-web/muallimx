<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('department')->nullable();
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->string('employment_type', 32)->default('full_time');
            $table->string('status', 32)->default('draft');
            $table->date('closes_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('hr_candidates', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 64)->nullable();
            $table->string('cv_path')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('source', 32)->default('other');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('email');
        });

        Schema::create('hr_job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_job_opening_id')->constrained('hr_job_openings')->cascadeOnDelete();
            $table->foreignId('hr_candidate_id')->constrained('hr_candidates')->cascadeOnDelete();
            $table->string('status', 32)->default('applied');
            $table->text('cover_letter')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            $table->unique(['hr_job_opening_id', 'hr_candidate_id'], 'hr_job_app_opening_candidate_unique');
            $table->index(['status', 'applied_at']);
        });

        Schema::create('hr_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_job_application_id')->constrained('hr_job_applications')->cascadeOnDelete();
            $table->string('round_key', 32);
            $table->string('round_label')->nullable();
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->text('meeting_details')->nullable();
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 32)->default('scheduled');
            $table->string('result', 32)->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['scheduled_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_interviews');
        Schema::dropIfExists('hr_job_applications');
        Schema::dropIfExists('hr_candidates');
        Schema::dropIfExists('hr_job_openings');
    }
};
