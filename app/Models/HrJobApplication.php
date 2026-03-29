<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrJobApplication extends Model
{
    public const STATUS_APPLIED = 'applied';

    public const STATUS_SCREENING = 'screening';

    public const STATUS_INTERVIEW = 'interview';

    public const STATUS_OFFER = 'offer';

    public const STATUS_HIRED = 'hired';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $fillable = [
        'hr_job_opening_id',
        'hr_candidate_id',
        'status',
        'cover_letter',
        'internal_notes',
        'applied_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    public function opening(): BelongsTo
    {
        return $this->belongsTo(HrJobOpening::class, 'hr_job_opening_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(HrCandidate::class, 'hr_candidate_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(HrInterview::class, 'hr_job_application_id')->orderBy('scheduled_at');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPLIED => 'تم التقديم',
            self::STATUS_SCREENING => 'فرز أولي',
            self::STATUS_INTERVIEW => 'مقابلات',
            self::STATUS_OFFER => 'عرض وظيفي',
            self::STATUS_HIRED => 'تم التعيين',
            self::STATUS_REJECTED => 'مرفوض',
            self::STATUS_WITHDRAWN => 'انسحب',
            default => $this->status,
        };
    }

    /** @return array<string, string> */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_APPLIED => 'تم التقديم',
            self::STATUS_SCREENING => 'فرز أولي',
            self::STATUS_INTERVIEW => 'مقابلات',
            self::STATUS_OFFER => 'عرض وظيفي',
            self::STATUS_HIRED => 'تم التعيين',
            self::STATUS_REJECTED => 'مرفوض',
            self::STATUS_WITHDRAWN => 'انسحب',
        ];
    }
}
