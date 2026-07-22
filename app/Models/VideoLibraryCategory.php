<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoLibraryCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'cover_color',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function videos()
    {
        return $this->hasMany(VideoLibraryVideo::class, 'category_id')->orderBy('order')->orderByDesc('published_at');
    }

    public function activeVideos()
    {
        return $this->hasMany(VideoLibraryVideo::class, 'category_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->orderByDesc('published_at');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }
}
