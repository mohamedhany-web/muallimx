<?php

namespace App\Services;

use App\Models\CurriculumLibraryItem;
use App\Models\CurriculumLibraryItemFile;
use App\Models\CurriculumLibraryMaterial;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CurriculumPresentationViewerService
{
    /**
     * رابط عام/مؤقت يمكن لعارض Microsoft جلب الملف منه (كما كان قبل التعديل).
     */
    public function absoluteStorageUrl(string $diskName, string $path): string
    {
        $disk = Storage::disk($diskName);

        if ($diskName === 'r2') {
            return $disk->temporaryUrl($path, now()->addHours(2));
        }

        $rel = $disk->url($path);
        if (str_starts_with($rel, 'http://') || str_starts_with($rel, 'https://')) {
            return $rel;
        }

        $host = request()->getSchemeAndHttpHost();
        if (str_starts_with($rel, '/')) {
            return rtrim($host, '/').$rel;
        }

        return rtrim($host, '/').'/'.ltrim($rel, '/');
    }

    /**
     * @return array{
     *   canUseOfficeViewer: bool,
     *   fileUrl: string,
     *   embedUrl: ?string
     * }
     */
    public function officeViewerPayload(string $fileUrl): array
    {
        $canUse = $this->isOfficeViewerSupportedUrl($fileUrl);
        if (! $canUse) {
            return [
                'canUseOfficeViewer' => false,
                'fileUrl' => $fileUrl,
                'embedUrl' => null,
            ];
        }

        $encoded = rawurlencode($fileUrl);

        return [
            'canUseOfficeViewer' => true,
            'fileUrl' => $fileUrl,
            'embedUrl' => 'https://view.officeapps.live.com/op/embed.aspx?src='.$encoded,
        ];
    }

    public function isOfficeViewerSupportedUrl(string $url): bool
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($host === '' || $host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return false;
        }

        if (str_ends_with($host, '.local') || str_ends_with($host, '.test')) {
            return false;
        }

        return true;
    }

    public function streamPresentation(CurriculumLibraryItem $item, string $kind, int $id): StreamedResponse
    {
        [$diskName, $path, $filename] = match ($kind) {
            'file' => $this->resolveItemFile($item, $id),
            'material' => $this->resolveMaterial($item, $id),
            default => abort(404),
        };

        $disk = Storage::disk($diskName);
        if (! $path || ! $disk->exists($path)) {
            abort(404);
        }

        $mime = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        $safeName = preg_replace('/[^\p{L}\p{N}\-_. ]/u', '_', $filename) ?: 'presentation.pptx';
        if (! str_ends_with(strtolower($safeName), '.pptx') && ! str_ends_with(strtolower($safeName), '.ppt')) {
            $safeName .= '.pptx';
        }

        $headers = [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$safeName.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ];

        if ($diskName === 'public' || $diskName === 'local') {
            $fullPath = $disk->path($path);
            if (! is_file($fullPath)) {
                abort(404);
            }

            return response()->file($fullPath, $headers);
        }

        return response()->stream(function () use ($disk, $path) {
            $stream = $disk->readStream($path);
            if ($stream === false) {
                return;
            }
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $headers);
    }

    /**
     * @return array{0: string, 1: string, 2: string} [disk, path, filename]
     */
    private function resolveItemFile(CurriculumLibraryItem $item, int $fileId): array
    {
        $file = CurriculumLibraryItemFile::query()
            ->where('curriculum_library_item_id', $item->id)
            ->whereKey($fileId)
            ->firstOrFail();

        if ($file->file_type !== 'presentation') {
            abort(404);
        }

        $diskName = $file->storage_disk ?: 'public';
        $filename = $file->label ?: basename((string) $file->path);

        return [$diskName, (string) $file->path, $filename];
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function resolveMaterial(CurriculumLibraryItem $item, int $materialId): array
    {
        $material = CurriculumLibraryMaterial::query()
            ->with('section')
            ->whereKey($materialId)
            ->firstOrFail();

        if ($material->file_kind !== 'pptx') {
            abort(404);
        }

        $material->loadMissing('section');
        if (! $material->section || (int) $material->section->curriculum_library_item_id !== (int) $item->id) {
            abort(404);
        }

        $diskName = $material->storage_disk ?: 'r2';

        return [$diskName, (string) $material->path, $material->displayTitle()];
    }
}
