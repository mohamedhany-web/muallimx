<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CurriculumLibraryItemFile extends Model
{
    protected $fillable = [
        'curriculum_library_item_id',
        'path',
        'label',
        'file_type',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(CurriculumLibraryItem::class, 'curriculum_library_item_id');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::url($this->path) : null;
    }

    public function scopePresentations($query)
    {
        return $query->where('file_type', 'presentation');
    }

    public function scopeAssignments($query)
    {
        return $query->where('file_type', 'assignment');
    }
}
