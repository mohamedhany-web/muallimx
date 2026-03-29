<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrInterview extends Model
{
    public const ROUND_PHONE = 'phone';

    public const ROUND_HR = 'hr';

    public const ROUND_TECHNICAL = 'technical';

    public const ROUND_FINAL = 'final';

    public const ROUND_OTHER = 'other';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    public const RESULT_PENDING = 'pending';

    public const RESULT_PASS = 'pass';

    public const RESULT_FAIL = 'fail';

    public const RESULT_HOLD = 'hold';

    protected $fillable = [
        'hr_job_application_id',
        'round_key',
        'round_label',
        'scheduled_at',
        'duration_minutes',
        'meeting_details',
        'interviewer_id',
        'status',
        'result',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(HrJobApplication::class, 'hr_job_application_id');
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRoundTitleAttribute(): string
    {
        if ($this->round_label) {
            return $this->round_label;
        }

        return match ($this->round_key) {
            self::ROUND_PHONE => 'مكالمة أولية',
            self::ROUND_HR => 'مقابلة موارد بشرية',
            self::ROUND_TECHNICAL => 'مقابلة فنية',
            self::ROUND_FINAL => 'مقابلة نهائية',
            self::ROUND_OTHER => 'أخرى',
            default => $this->round_key,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'مجدولة',
            self::STATUS_COMPLETED => 'اكتملت',
            self::STATUS_CANCELLED => 'ملغاة',
            self::STATUS_NO_SHOW => 'لم يحضر',
            default => $this->status,
        };
    }

    public function getResultLabelAttribute(): string
    {
        return match ($this->result) {
            self::RESULT_PENDING => 'قيد التقييم',
            self::RESULT_PASS => 'نجاح',
            self::RESULT_FAIL => 'عدم اجتياز',
            self::RESULT_HOLD => 'معلّق',
            default => $this->result,
        };
    }

    /** @return array<string, string> */
    public static function roundKeyLabels(): array
    {
        return [
            self::ROUND_PHONE => 'مكالمة أولية',
            self::ROUND_HR => 'موارد بشرية',
            self::ROUND_TECHNICAL => 'فنية',
            self::ROUND_FINAL => 'نهائية',
            self::ROUND_OTHER => 'أخرى (عنوان مخصص)',
        ];
    }

    /** @return array<string, string> */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_SCHEDULED => 'مجدولة',
            self::STATUS_COMPLETED => 'اكتملت',
            self::STATUS_CANCELLED => 'ملغاة',
            self::STATUS_NO_SHOW => 'لم يحضر',
        ];
    }

    /** @return array<string, string> */
    public static function resultLabels(): array
    {
        return [
            self::RESULT_PENDING => 'قيد التقييم',
            self::RESULT_PASS => 'نجاح',
            self::RESULT_FAIL => 'عدم اجتياز',
            self::RESULT_HOLD => 'معلّق',
        ];
    }
}
