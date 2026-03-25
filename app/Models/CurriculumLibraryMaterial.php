<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CurriculumLibraryMaterial extends Model
{
    protected $fillable = [
        'curriculum_library_section_id',
        'title',
        'path',
        'storage_disk',
        'original_name',
        'file_kind',
        'view_in_platform',
        'allow_download',
        'order',
        'is_active',
    ];

    protected $casts = [
        'view_in_platform' => 'boolean',
        'allow_download' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(CurriculumLibrarySection::class, 'curriculum_library_section_id');
    }

    /** يفرض منع تحميل HTML وعروض PPTX حتى لو خُيّر خطأ في لوحة التحكم */
    public function effectiveAllowDownload(): bool
    {
        if (in_array($this->file_kind, ['html', 'pptx'], true)) {
            return false;
        }

        return $this->allow_download;
    }

    public function effectiveAllowViewInPlatform(): bool
    {
        return $this->view_in_platform;
    }

    public function displayTitle(): string
    {
        return $this->title ?: $this->original_name ?: basename($this->path);
    }

    public function getUrlAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }
        $disk = $this->storage_disk ?: 'r2';

        try {
            if ($disk === 'r2') {
                return Storage::disk('r2')->temporaryUrl($this->path, now()->addHours(2));
            }

            return Storage::disk($disk)->url($this->path);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function fileKindFromExtension(string $ext): string
    {
        $ext = strtolower($ext);

        return match ($ext) {
            'html', 'htm' => 'html',
            'pdf' => 'pdf',
            'ppt', 'pptx' => 'pptx',
            default => 'other',
        };
    }
}
