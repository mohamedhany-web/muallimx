<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrJobOpening extends Model
{
    public const EMP_FULL_TIME = 'full_time';

    public const EMP_PART_TIME = 'part_time';

    public const EMP_CONTRACT = 'contract';

    public const EMP_INTERNSHIP = 'internship';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_OPEN = 'open';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_FILLED = 'filled';

    protected $fillable = [
        'title',
        'department',
        'description',
        'requirements',
        'employment_type',
        'status',
        'closes_at',
        'created_by',
    ];

    protected $casts = [
        'closes_at' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(HrJobApplication::class, 'hr_job_opening_id');
    }

    public function getEmploymentTypeLabelAttribute(): string
    {
        return match ($this->employment_type) {
            self::EMP_FULL_TIME => 'دوام كامل',
            self::EMP_PART_TIME => 'دوام جزئي',
            self::EMP_CONTRACT => 'عقد',
            self::EMP_INTERNSHIP => 'تدريب',
            default => $this->employment_type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_OPEN => 'مفتوحة',
            self::STATUS_PAUSED => 'معلّقة',
            self::STATUS_CLOSED => 'مغلقة',
            self::STATUS_FILLED => 'تم الشغل',
            default => $this->status,
        };
    }

    public function isAcceptingApplications(): bool
    {
        if ($this->status !== self::STATUS_OPEN) {
            return false;
        }
        if ($this->closes_at === null) {
            return true;
        }

        return ! now()->startOfDay()->isAfter($this->closes_at);
    }

    /** @return array<string, string> */
    public static function employmentTypeLabels(): array
    {
        return [
            self::EMP_FULL_TIME => 'دوام كامل',
            self::EMP_PART_TIME => 'دوام جزئي',
            self::EMP_CONTRACT => 'عقد',
            self::EMP_INTERNSHIP => 'تدريب',
        ];
    }

    /** @return array<string, string> */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_OPEN => 'مفتوحة',
            self::STATUS_PAUSED => 'معلّقة',
            self::STATUS_CLOSED => 'مغلقة',
            self::STATUS_FILLED => 'تم الشغل',
        ];
    }
}
