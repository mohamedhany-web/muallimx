<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoLibraryPreviewOpen extends Model
{
    protected $fillable = [
        'user_id',
        'video_id',
        'opened_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
    ];

    public static function hasUsedFreePreview(int $userId): bool
    {
        return static::query()->where('user_id', $userId)->exists();
    }

    public static function recordFreePreviewUsed(int $userId, int $videoId): void
    {
        static::query()->firstOrCreate(
            ['user_id' => $userId],
            ['video_id' => $videoId, 'opened_at' => now()]
        );
    }

    public static function previewVideoId(int $userId): ?int
    {
        $row = static::query()->where('user_id', $userId)->first();

        return $row?->video_id;
    }
}
