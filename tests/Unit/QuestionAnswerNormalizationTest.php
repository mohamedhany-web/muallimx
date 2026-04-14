<?php

namespace Tests\Unit;

use App\Models\Question;
use Tests\TestCase;

class QuestionAnswerNormalizationTest extends TestCase
{
    public function test_multiple_choice_accepts_index_and_option_text(): void
    {
        $question = new Question([
            'type' => 'multiple_choice',
            'options' => ['القاهرة', 'الرياض', 'دبي'],
            'correct_answer' => [1],
        ]);

        $this->assertTrue($question->isCorrectAnswer(1));
        $this->assertTrue($question->isCorrectAnswer('1'));
        $this->assertTrue($question->isCorrectAnswer('الرياض'));
        $this->assertFalse($question->isCorrectAnswer('دبي'));
    }

    public function test_true_false_normalization_supports_common_variants(): void
    {
        $question = new Question([
            'type' => 'true_false',
            'correct_answer' => ['صح'],
        ]);

        $this->assertTrue($question->isCorrectAnswer('صح'));
        $this->assertTrue($question->isCorrectAnswer('true'));
        $this->assertTrue($question->isCorrectAnswer('1'));
        $this->assertFalse($question->isCorrectAnswer('false'));
    }
}
