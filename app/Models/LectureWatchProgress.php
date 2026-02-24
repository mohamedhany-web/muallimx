<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LectureWatchProgress extends Model
{
    use HasFactory;

    protected $table = 'lecture_watch_progress';

    protected $fillable = [
        'lecture_id',
        'user_id',
        'watch_time_seconds',
        'video_duration_seconds',
        'progress_percent',
        'is_completed',
    ];

    protected $casts = [
        'watch_time_seconds' => 'integer',
        'video_duration_seconds' => 'integer',
        'progress_percent' => 'integer',
        'is_completed' => 'boolean',
    ];

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function updateFromSample(int $currentSec, int $durationSec, ?int $minPercentToComplete = null): void
    {
        $durationSec = max(1, $durationSec);
        $currentSec = max(0, min($currentSec, $durationSec));
        $percent = (int) round(($currentSec / $durationSec) * 100);

        $threshold = $minPercentToComplete ?? 90;
        $completed = $percent >= $threshold;

        $this->fill([
            'watch_time_seconds' => $currentSec,
            'video_duration_seconds' => $durationSec,
            'progress_percent' => min(100, $percent),
            'is_completed' => $completed,
        ])->save();
    }
}

