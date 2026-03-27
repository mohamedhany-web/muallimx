<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        try {
            \DB::statement("ALTER TABLE agreement_payments MODIFY COLUMN type ENUM(
                'course_completion', 'hourly_teaching', 'monthly_salary', 'bonus', 'other', 'course_activation', 'consultation_session'
            ) DEFAULT 'course_completion'");
        } catch (\Throwable $e) {
            // في بعض البيئات قد يكون type من نوع string وليس enum.
        }
    }

    public function down(): void
    {
        try {
            \DB::statement("ALTER TABLE agreement_payments MODIFY COLUMN type ENUM(
                'course_completion', 'hourly_teaching', 'monthly_salary', 'bonus', 'other', 'course_activation'
            ) DEFAULT 'course_completion'");
        } catch (\Throwable $e) {
            // تجاهل في حال كان النوع مختلفاً.
        }
    }
};

