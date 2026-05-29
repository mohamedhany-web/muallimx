<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherCalendarAppointment extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location',
        'schedule_type',
        'family_timezone',
        'family_time',
        'duration_minutes',
        'teacher_timezone',
        'weekday',
        'month_key',
        'selected_dates',
        'color',
        'notify_platform',
        'notify_email',
        'reminder_minutes',
        'status',
    ];

    protected $casts = [
        'selected_dates' => 'array',
        'notify_platform' => 'boolean',
        'notify_email' => 'boolean',
        'duration_minutes' => 'integer',
        'reminder_minutes' => 'integer',
        'weekday' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(TeacherCalendarOccurrence::class, 'appointment_id');
    }

    public function isFixed(): bool
    {
        return $this->schedule_type === 'fixed';
    }

    public function isTemporary(): bool
    {
        return $this->schedule_type === 'temporary';
    }

    public function familyTimeString(): string
    {
        $value = $this->attributes['family_time'] ?? $this->family_time ?? '09:00';

        return substr((string) $value, 0, 5);
    }
}
