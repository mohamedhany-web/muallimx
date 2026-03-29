<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeEvent extends Model
{
    public const TYPE_NOTE = 'note';

    public const TYPE_VERBAL_WARNING = 'verbal_warning';

    public const TYPE_WRITTEN_WARNING = 'written_warning';

    public const TYPE_RECOGNITION = 'recognition';

    protected $fillable = [
        'employee_id',
        'created_by',
        'event_type',
        'title',
        'body',
        'event_date',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->event_type) {
            self::TYPE_NOTE => 'ملاحظة',
            self::TYPE_VERBAL_WARNING => 'إنذار شفهي',
            self::TYPE_WRITTEN_WARNING => 'إنذار كتابي',
            self::TYPE_RECOGNITION => 'تقدير',
            default => $this->event_type,
        };
    }

    /** @return array<string, string> */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_NOTE => 'ملاحظة',
            self::TYPE_VERBAL_WARNING => 'إنذار شفهي',
            self::TYPE_WRITTEN_WARNING => 'إنذار كتابي',
            self::TYPE_RECOGNITION => 'تقدير',
        ];
    }
}
