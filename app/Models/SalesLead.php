<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesLead extends Model
{
    use SoftDeletes;

    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_QUALIFIED = 'qualified';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_LOST = 'lost';

    public const SOURCE_WEBSITE = 'website';

    public const SOURCE_PHONE = 'phone';

    public const SOURCE_SOCIAL = 'social';

    public const SOURCE_REFERRAL = 'referral';

    public const SOURCE_EVENT = 'event';

    public const SOURCE_WALK_IN = 'walk_in';

    public const SOURCE_OTHER = 'other';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'source',
        'status',
        'notes',
        'interested_advanced_course_id',
        'assigned_to',
        'created_by',
        'linked_user_id',
        'converted_order_id',
        'converted_at',
        'lost_reason',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function linkedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    public function interestedCourse(): BelongsTo
    {
        return $this->belongsTo(AdvancedCourse::class, 'interested_advanced_course_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'جديد',
            self::STATUS_CONTACTED => 'تم التواصل',
            self::STATUS_QUALIFIED => 'مؤهل',
            self::STATUS_CONVERTED => 'مُحوَّل',
            self::STATUS_LOST => 'خاسر',
            default => $this->status,
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            self::SOURCE_WEBSITE => 'الموقع',
            self::SOURCE_PHONE => 'هاتف',
            self::SOURCE_SOCIAL => 'سوشيال',
            self::SOURCE_REFERRAL => 'إحالة',
            self::SOURCE_EVENT => 'فعالية',
            self::SOURCE_WALK_IN => 'زيارة',
            self::SOURCE_OTHER => 'أخرى',
            default => $this->source,
        };
    }

    public function isConverted(): bool
    {
        return $this->status === self::STATUS_CONVERTED;
    }

    public function isLost(): bool
    {
        return $this->status === self::STATUS_LOST;
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CONVERTED, self::STATUS_LOST]);
    }

    /** @return array<string, string> */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'جديد',
            self::STATUS_CONTACTED => 'تم التواصل',
            self::STATUS_QUALIFIED => 'مؤهل',
            self::STATUS_CONVERTED => 'مُحوَّل',
            self::STATUS_LOST => 'خاسر',
        ];
    }

    /** @return array<string, string> */
    public static function sourceLabels(): array
    {
        return [
            self::SOURCE_WEBSITE => 'الموقع',
            self::SOURCE_PHONE => 'هاتف',
            self::SOURCE_SOCIAL => 'سوشيال',
            self::SOURCE_REFERRAL => 'إحالة',
            self::SOURCE_EVENT => 'فعالية',
            self::SOURCE_WALK_IN => 'زيارة',
            self::SOURCE_OTHER => 'أخرى',
        ];
    }
}
