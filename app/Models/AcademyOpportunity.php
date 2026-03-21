<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademyOpportunity extends Model
{
    protected $fillable = [
        'organization_name',
        'title',
        'specialization',
        'city',
        'work_mode',
        'status',
        'is_featured',
        'requirements',
        'apply_until',
        'created_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'apply_until' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(AcademyOpportunityApplication::class);
    }
}

