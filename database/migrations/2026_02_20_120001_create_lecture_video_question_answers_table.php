<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecture_video_question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecture_video_question_id')->constrained('lecture_video_questions')->cascadeOnDelete();
            $table->string('answer', 1000)->nullable();
            $table->boolean('is_correct')->default(false);
            $table->decimal('score_earned', 10, 2)->default(0);
            $table->timestamp('answered_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecture_video_question_answers');
    }
};
