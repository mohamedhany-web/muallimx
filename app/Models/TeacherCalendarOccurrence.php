<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherCalendarOccurrence extends Model
{
    protected $fillable = [
        'appointment_id',
        'user_id',
        'starts_at',
        'ends_at',
        'occurrence_date',
        'reminder_sent_at',
        'auto_remove_after_end',
        'removed_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'occurrence_date' => 'date',
        'reminder_sent_at' => 'datetime',
        'auto_remove_after_end' => 'boolean',
        'removed_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(TeacherCalendarAppointment::class, 'appointment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('removed_at');
    }

    public function scopeInRange(Builder $query, $start, $end): Builder
    {
        return $query->where('starts_at', '>=', $start)
            ->where('starts_at', '<=', $end);
    }
}
