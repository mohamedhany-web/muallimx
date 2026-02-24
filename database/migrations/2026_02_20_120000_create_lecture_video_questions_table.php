<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecture_video_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecture_id')->constrained('lectures')->cascadeOnDelete();
            /** الوقت بالثواني في الفيديو الذي يظهر فيه السؤال (مثلاً 90 = دقيقة ونصف) */
            $table->unsignedInteger('timestamp_seconds');
            /** مصدر السؤال: bank = من بنك الأسئلة، custom = سؤال مخصص للمحاضرة */
            $table->string('question_source', 20)->default('custom');
            $table->foreignId('question_id')->nullable()->constrained('questions')->nullOnDelete();
            /** للسؤال المخصص فقط */
            $table->text('custom_question_text')->nullable();
            $table->json('custom_options')->nullable();
            $table->string('custom_correct_answer', 500)->nullable();
            /** عند الإجابة الخاطئة: rewind = إعادة الفيديو، continue = متابعة بدون إعادة */
            $table->string('on_wrong', 20)->default('continue');
            /** ثوانٍ للرجوع للوراء عند rewind (0 = من بداية الفيديو أو السؤال السابق) */
            $table->unsignedInteger('rewind_seconds')->default(0);
            $table->unsignedTinyInteger('points')->default(1);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecture_video_questions');
    }
};
