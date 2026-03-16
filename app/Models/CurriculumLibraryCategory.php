<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurriculumLibraryCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(CurriculumLibraryItem::class, 'category_id')->orderBy('order')->orderBy('title');
    }

    public function activeItems()
    {
        return $this->hasMany(CurriculumLibraryItem::class, 'category_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('title');
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
