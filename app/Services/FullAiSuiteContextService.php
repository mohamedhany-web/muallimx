<?php

namespace App\Services;

use App\Models\AdvancedCourse;
use App\Models\User;

/**
 * طبقة السياق والقالب للمزايا التعليمية فقط — بدون اتصال بـ Gemini (مرحلة لاحقة).
 */
class FullAiSuiteContextService
{
    /** مفاتيح ثابتة للـ API لاحقاً */
    public const QUESTION_TYPES = [
        'explain_concept' => 'student.full_ai_suite.question_types.explain_concept',
        'summarize' => 'student.full_ai_suite.question_types.summarize',
        'practice' => 'student.full_ai_suite.question_types.practice',
        'exam_prep' => 'student.full_ai_suite.question_types.exam_prep',
    ];

    /** @return list<string> */
    public static function questionTypeKeys(): array
    {
        return array_keys(self::QUESTION_TYPES);
    }

    public function filterQuestion(string $raw): string
    {
        $t = trim(preg_replace('/\s+/u', ' ', $raw));

        return mb_substr($t, 0, 4000);
    }

    public function buildContext(User $user, int $courseId, string $questionType, string $question): array
    {
        $course = AdvancedCourse::query()->findOrFail($courseId);
        $filtered = $this->filterQuestion($question);

        return [
            'locale' => app()->getLocale(),
            'student_id' => $user->id,
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'category' => $course->category,
            ],
            'question_type' => $questionType,
            'question_type_label' => __(self::QUESTION_TYPES[$questionType] ?? $questionType),
            'question' => $filtered,
            'filters_applied' => [
                'educational_only' => true,
                'max_question_length' => 4000,
            ],
        ];
    }

    /**
     * معاينة نص يُرسل لاحقاً إلى Gemini — بنية ثابتة للمراجعة فقط.
     *
     * @param  array<string, mixed>  $context
     */
    public function buildPromptPreview(array $context): string
    {
        $courseTitle = $context['course']['title'] ?? '';
        $category = $context['course']['category'] ?? '—';
        $typeLabel = $context['question_type_label'] ?? ($context['question_type'] ?? '');
        $body = $context['question'] ?? '';

        $lines = [
            '[SYSTEM — educational assistant only]',
            'You are an educational assistant in the Muallimx learning context. Refuse non-educational or harmful requests.',
            '',
            '[CONTEXT]',
            '- Course: ' . $courseTitle,
            '- Course type / category: ' . $category,
            '- Request type: ' . $typeLabel,
            '',
            '[STUDENT REQUEST]',
            $body,
            '',
            '[OUTPUT]',
            'Respond appropriately for the selected request type, in the student\'s language when possible.',
        ];

        return implode("\n", $lines);
    }
}
