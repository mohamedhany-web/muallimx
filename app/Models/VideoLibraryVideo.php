<?php

namespace App\Models;

use App\Services\YouTubeVideoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VideoLibraryVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'youtube_url',
        'youtube_id',
        'thumbnail_url',
        'duration_seconds',
        'views_count',
        'order',
        'is_active',
        'is_featured',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'order' => 'integer',
        'views_count' => 'integer',
        'duration_seconds' => 'integer',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(VideoLibraryCategory::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderByDesc('published_at')->orderByDesc('id');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function embedUrl(array $params = []): string
    {
        return YouTubeVideoService::embedUrl($this->youtube_id, $params);
    }

    public function displayThumbnail(): string
    {
        if ($this->thumbnail_url) {
            return $this->thumbnail_url;
        }

        return YouTubeVideoService::thumbnailUrl($this->youtube_id);
    }

    public function formattedDuration(): ?string
    {
        return YouTubeVideoService::formatDuration($this->duration_seconds);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public static function uniqueSlugFromTitle(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'video-'.Str::lower(Str::random(8));
        }

        $slug = $base;
        $i = 2;
        while (
            static::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
