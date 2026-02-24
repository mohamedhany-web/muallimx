<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LectureVideoQuestion extends Model
{
    protected $fillable = [
        'lecture_id',
        'timestamp_seconds',
        'question_source',
        'question_id',
        'custom_question_text',
        'custom_options',
        'custom_correct_answer',
        'on_wrong',
        'rewind_seconds',
        'points',
        'order',
    ];

    protected $casts = [
        'custom_options' => 'array',
        'timestamp_seconds' => 'integer',
        'rewind_seconds' => 'integer',
        'points' => 'integer',
        'order' => 'integer',
    ];

    public const SOURCE_BANK = 'bank';
    public const SOURCE_CUSTOM = 'custom';
    public const ON_WRONG_REWIND = 'rewind';
    public const ON_WRONG_CONTINUE = 'continue';

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(LectureVideoQuestionAnswer::class, 'lecture_video_question_id');
    }

    /**
     * بيانات السؤال جاهزة للعرض (عنوان + خيارات + إجابة صحيحة للتحقق فقط).
     */
    public function getPayloadForStudent(): array
    {
        if ($this->question_source === self::SOURCE_BANK && $this->question_id) {
            $q = $this->question;
            if (!$q || !$q->is_active) {
                return ['text' => '', 'options' => [], 'type' => 'multiple_choice'];
            }
            $options = $q->options;
            if (is_array($options)) {
                $options = array_values($options);
            } else {
                $options = [];
            }
            return [
                'id' => $this->id,
                'text' => $q->question,
                'options' => $options,
                'type' => $q->type ?? 'multiple_choice',
                'points' => $this->points,
            ];
        }
        return [
            'id' => $this->id,
            'text' => $this->custom_question_text ?? '',
            'options' => is_array($this->custom_options) ? array_values($this->custom_options) : [],
            'type' => 'multiple_choice',
            'points' => $this->points,
        ];
    }

    /**
     * التحقق من صحة الإجابة (لا نرسل الإجابة الصحيحة للعميل).
     */
    public function checkAnswer(string $answer): bool
    {
        if ($this->question_source === self::SOURCE_BANK && $this->question_id) {
            return $this->question->isCorrectAnswer($answer);
        }
        $correct = trim((string) $this->custom_correct_answer);
        return strcasecmp(trim($answer), $correct) === 0;
    }
}
