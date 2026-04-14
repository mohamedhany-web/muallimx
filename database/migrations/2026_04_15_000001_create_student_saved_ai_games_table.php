<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_saved_ai_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('storage_path', 512);
            $table->string('title', 200)->nullable();
            $table->string('question_type', 64)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'storage_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_saved_ai_games');
    }
};
