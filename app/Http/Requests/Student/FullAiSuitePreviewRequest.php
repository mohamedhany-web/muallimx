<?php

namespace App\Http\Requests\Student;

use App\Services\FullAiSuiteContextService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FullAiSuitePreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasSubscriptionFeature('full_ai_suite');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'advanced_course_id' => ['required', 'integer', Rule::exists('advanced_courses', 'id')],
            'question_type' => ['required', 'string', Rule::in(FullAiSuiteContextService::questionTypeKeys())],
            'question' => ['required', 'string', 'min:10', 'max:4000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = $this->user();
            if ($user === null) {
                return;
            }
            $courseId = (int) $this->input('advanced_course_id');
            if ($courseId > 0 && ! $user->isEnrolledIn($courseId)) {
                $validator->errors()->add(
                    'advanced_course_id',
                    __('student.full_ai_suite.validation.not_enrolled')
                );
            }
        });
    }
}
