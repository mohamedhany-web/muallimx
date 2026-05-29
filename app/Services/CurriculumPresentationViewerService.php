<?php

namespace App\Services;

use App\Models\CurriculumLibraryItem;
use App\Models\CurriculumLibraryItemFile;
use App\Models\CurriculumLibraryMaterial;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CurriculumPresentationViewerService
{
    /**
     * رابط موقّع يجلبه عارض Microsoft (بدون كوكيز) مع Content-Type صحيح للـ PPTX.
     */
    public function signedStreamUrl(CurriculumLibraryItem $item, string $kind, int $id): string
    {
        return URL::temporarySignedRoute(
            'curriculum-library.presentation.stream',
            now()->addHours(3),
            [
                'item' => $item->slug,
                'kind' => $kind,
                'id' => $id,
            ]
        );
    }

    /**
     * @return array{
     *   canUseOfficeViewer: bool,
     *   fileUrl: string,
     *   viewUrl: ?string,
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
                'viewUrl' => null,
                'embedUrl' => null,
            ];
        }

        $encoded = rawurlencode($fileUrl);

        return [
            'canUseOfficeViewer' => true,
            'fileUrl' => $fileUrl,
            // view.aspx يدعم وضع عرض الشرائح والانتقالات أفضل من embed.aspx
            'viewUrl' => 'https://view.officeapps.live.com/op/view.aspx?src='.$encoded,
            'embedUrl' => 'https://view.officeapps.live.com/op/embed.aspx?src='.$encoded,
        ];
    }

    public function isOfficeViewerSupportedUrl(string $url): bool
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if ($host === '' || $host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return false;
        }

        if (str_ends_with($host, '.local') || str_ends_with($host, '.test')) {
            return false;
        }

        return $scheme === 'https';
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
