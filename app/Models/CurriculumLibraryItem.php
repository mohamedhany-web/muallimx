<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CurriculumLibraryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'content',
        'grade_level',
        'subject',
        'language',
        'item_type',
        'meta',
        'order',
        'is_active',
        'is_free_preview',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
        'is_free_preview' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(CurriculumLibraryCategory::class, 'category_id');
    }

    public function files()
    {
        return $this->hasMany(CurriculumLibraryItemFile::class, 'curriculum_library_item_id')->orderBy('order')->orderBy('id');
    }

    public function scopeByLanguage($query, ?string $lang)
    {
        if ($lang && in_array($lang, ['ar', 'en', 'fr'], true)) {
            return $query->where('language', $lang);
        }
        return $query;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('title');
    }

    public function scopeInCategory($query, $categoryId)
    {
        if ($categoryId) {
            return $query->where('category_id', $categoryId);
        }
        return $query;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
