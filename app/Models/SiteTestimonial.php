<?php

namespace App\Models;

use App\Services\SiteTestimonialImageStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SiteTestimonial extends Model
{
    public const CONTENT_TEXT = 'text';

    public const CONTENT_IMAGE = 'image';

    protected $table = 'site_testimonials';

    protected $fillable = [
        'content_type',
        'body',
        'author_name',
        'role_label',
        'image_path',
        'is_featured',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('id');
    }

    public function publicImageUrl(): ?string
    {
        return SiteTestimonialImageStorage::publicUrl($this->image_path);
    }

    public function isImageType(): bool
    {
        return $this->content_type === self::CONTENT_IMAGE;
    }

    protected static function booted(): void
    {
        static::deleting(function (SiteTestimonial $row) {
            if (is_string($row->image_path) && $row->image_path !== '') {
                SiteTestimonialImageStorage::delete($row->image_path);
            }
        });
    }
}
