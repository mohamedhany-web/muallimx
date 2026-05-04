<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurriculumLibraryItem;
use App\Models\CurriculumLibraryMaterial;
use App\Models\CurriculumLibrarySection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CurriculumLibraryStructureController extends Controller
{
    /** امتدادات مسموحة لمواد المناهج (رفع لوحة التحكم) — متوافقة تقريباً مع FileUploadSecurityMiddleware */
    private const CURRICULUM_MATERIAL_EXTENSIONS = [
        'pdf', 'html', 'htm', 'ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'zip', 'rar',
        'png', 'jpg', 'jpeg', 'gif', 'webp',
    ];

    private const DANGEROUS_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'phar', 'exe', 'bat', 'cmd', 'com', 'msi',
        'sh', 'js', 'jsp', 'asp', 'aspx', 'dll', 'sys',
    ];

    public function show(CurriculumLibraryItem $item)
    {
        $tree = CurriculumLibrarySection::treeForItem($item, false);
        $flatSections = $item->sections()->withCount('materials')->orderBy('order')->orderBy('id')->get();
        $materialDirectUpload = $this->curriculumMaterialDiskSupportsDirectUpload();

        return view('admin.curriculum-library.structure', compact('item', 'tree', 'flatSections', 'materialDirectUpload'));
    }

    public function storeSection(Request $request, CurriculumLibraryItem $item)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:curriculum_library_sections,id',
            'order' => 'nullable|integer|min:0',
        ]);

        if (!empty($validated['parent_id'])) {
            CurriculumLibrarySection::where('id', $validated['parent_id'])
                ->where('curriculum_library_item_id', $item->id)
                ->firstOrFail();
        }

        CurriculumLibrarySection::create([
            'curriculum_library_item_id' => $item->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'order' => (int) ($validated['order'] ?? 0),
            'is_active' => true,
        ]);

        return redirect()->route('admin.curriculum-library.items.structure', $item)
            ->with('success', 'تم إنشاء القسم.');
    }

    public function updateSection(Request $request, CurriculumLibraryItem $item, CurriculumLibrarySection $section)
    {
        $this->assertSectionBelongs($item, $section);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:curriculum_library_sections,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $newParentId = $validated['parent_id'] ?? null;
        if ($newParentId !== null && (int) $newParentId === (int) $section->id) {
            return back()->with('error', 'لا يمكن جعل القسم أباً لنفسه.');
        }

        if ($newParentId !== null) {
            CurriculumLibrarySection::where('id', $newParentId)
                ->where('curriculum_library_item_id', $item->id)
                ->firstOrFail();

            if ($this->wouldCreateCycle($section, (int) $newParentId)) {
                return back()->with('error', 'لا يمكن نقل القسم تحت أحد أبنائه.');
            }
        }

        $section->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'parent_id' => $newParentId,
            'order' => (int) ($validated['order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.curriculum-library.items.structure', $item)
            ->with('success', 'تم تحديث القسم.');
    }

    public function destroySection(CurriculumLibraryItem $item, CurriculumLibrarySection $section)
    {
        $this->assertSectionBelongs($item, $section);
        $section->deleteWithStorage();

        return redirect()->route('admin.curriculum-library.items.structure', $item)
            ->with('success', 'تم حذف القسم وما بداخله.');
    }

    public function storeMaterial(Request $request, CurriculumLibraryItem $item, CurriculumLibrarySection $section)
    {
        $this->assertSectionBelongs($item, $section);

        $maxKb = (int) config('upload_limits.curriculum_material_max_kb', 150 * 1024);
        $maxMbLabel = max(1, (int) round($maxKb / 1024));

        $request->validate([
            'file' => 'required|file|max:' . $maxKb,
            'title' => 'nullable|string|max:255',
            'view_in_platform' => 'nullable|boolean',
            'allow_download' => 'nullable|boolean',
        ], [
            'file.max' => 'حجم الملف يتجاوز الحد المسموح لمادة المنهج (' . $maxMbLabel . ' ميجابايت كحد أقصى).',
        ]);

        @ini_set('max_input_time', '7200');
        @set_time_limit(0);

        try {
            $upload = $request->file('file');
            $ext = strtolower((string) $upload->getClientOriginalExtension());
            $fileKind = CurriculumLibraryMaterial::fileKindFromExtension($ext);

            $viewIn = $request->boolean('view_in_platform');
            $allowDl = $request->boolean('allow_download');
            [$viewIn, $allowDl] = $this->applyCurriculumMaterialKindRules($fileKind, $viewIn, $allowDl);

            $path = $upload->store('curriculum-library/materials/'.$section->id, 'r2');
            $order = ((int) ($section->materials()->max('order') ?? 0)) + 1;

            CurriculumLibraryMaterial::create([
                'curriculum_library_section_id' => $section->id,
                'title' => $request->input('title') ?: null,
                'path' => $path,
                'storage_disk' => 'r2',
                'original_name' => $upload->getClientOriginalName(),
                'file_kind' => $fileKind,
                'view_in_platform' => $viewIn,
                'allow_download' => $allowDl,
                'order' => $order,
                'is_active' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error('Curriculum material upload failed', [
                'item_id' => $item->id,
                'section_id' => $section->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('admin.curriculum-library.items.structure', $item)
                ->with('error', 'فشل رفع الملف. تحقق من إعدادات Cloudflare R2 أو نوع الملف ثم أعد المحاولة.');
        }

        return redirect()->route('admin.curriculum-library.items.structure', $item)
            ->with('success', 'تم رفع المادة إلى Cloudflare R2.');
    }

    public function updateMaterial(Request $request, CurriculumLibraryItem $item, CurriculumLibraryMaterial $material)
    {
        $this->assertMaterialBelongs($item, $material);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'view_in_platform' => 'nullable|boolean',
            'allow_download' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $fileKind = $material->file_kind;

        $viewIn = $request->boolean('view_in_platform');
        $allowDl = $request->boolean('allow_download');
        [$viewIn, $allowDl] = $this->applyCurriculumMaterialKindRules($fileKind, $viewIn, $allowDl);

        $material->update([
            'title' => $validated['title'] ?? $material->title,
            'view_in_platform' => $viewIn,
            'allow_download' => $allowDl,
            'order' => (int) ($validated['order'] ?? $material->order),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.curriculum-library.items.structure', $item)
            ->with('success', 'تم تحديث المادة.');
    }

    public function destroyMaterial(CurriculumLibraryItem $item, CurriculumLibraryMaterial $material)
    {
        $this->assertMaterialBelongs($item, $material);

        $disk = $material->storage_disk ?: 'r2';
        if ($material->path && Storage::disk($disk)->exists($material->path)) {
            Storage::disk($disk)->delete($material->path);
        }
        $material->delete();

        return redirect()->route('admin.curriculum-library.items.structure', $item)
            ->with('success', 'تم حذف المادة.');
    }

    /**
     * تجهيز رابط PUT موقّت: الرفع من المتصفح مباشرة إلى R2 (لا يمر بحدود PHP).
     */
    public function presignMaterialUpload(Request $request, CurriculumLibraryItem $item, CurriculumLibrarySection $section)
    {
        $this->assertSectionBelongs($item, $section);
        @set_time_limit(120);

        if (! $this->curriculumMaterialDiskSupportsDirectUpload()) {
            return response()->json([
                'direct_upload' => false,
                'message' => 'التخزين R2 غير مهيأ أو لا يدعم روابط الرفع الموقّت.',
            ]);
        }

        $maxBytes = (int) config('upload_limits.curriculum_material_max_bytes', 150 * 1024 * 1024);

        $validated = $request->validate([
            'content_type' => ['nullable', 'string', 'max:191'],
            'original_name' => ['required', 'string', 'max:255'],
            'file_size' => ['required', 'integer', 'min:1', 'max:'.$maxBytes],
        ]);

        $originalName = basename(str_replace(["\0", '\\'], '', $validated['original_name']));
        if ($originalName === '' || $originalName === '.' || $originalName === '..') {
            return response()->json(['message' => 'اسم الملف غير صالح.'], 422);
        }

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($ext === '' || in_array($ext, self::DANGEROUS_EXTENSIONS, true)) {
            return response()->json(['message' => 'امتداد الملف غير مسموح.'], 422);
        }
        if (! in_array($ext, self::CURRICULUM_MATERIAL_EXTENSIONS, true)) {
            return response()->json(['message' => 'امتداد الملف غير مسموح لمادة المنهج.'], 422);
        }

        $mime = $this->normalizeCurriculumMaterialMime(
            (string) ($validated['content_type'] ?? ''),
            $originalName,
            $ext
        );

        $diskName = 'r2';
        $disk = Storage::disk($diskName);
        $baseDir = 'curriculum-library/materials/'.$section->id;
        $newPath = $baseDir.'/'.Str::uuid()->toString().'.'.$ext;

        $uploadToken = Str::random(64);
        Cache::put(
            'curriculum_library_mat_presign:'.$uploadToken,
            [
                'path' => $newPath,
                'curriculum_library_item_id' => (int) $item->id,
                'curriculum_library_section_id' => (int) $section->id,
                'user_id' => (int) auth()->id(),
                'mime' => $mime,
                'disk' => $diskName,
                'original_name' => $originalName,
                'max_bytes' => $maxBytes,
            ],
            now()->addMinutes(75)
        );

        try {
            $signed = $disk->temporaryUploadUrl(
                $newPath,
                now()->addMinutes(70),
                [
                    'ContentType' => $mime,
                ]
            );
        } catch (\Throwable $e) {
            Cache::forget('curriculum_library_mat_presign:'.$uploadToken);
            Log::error('Curriculum material presign failed', [
                'item_id' => $item->id,
                'section_id' => $section->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'direct_upload' => false,
                'message' => 'تعذر تجهيز رابط الرفع. تحقق من مفاتيح R2 وCORS للـ bucket (PUT من نطاق الموقع).',
            ], 503);
        }

        return response()->json([
            'direct_upload' => true,
            'upload_url' => $signed['url'],
            'upload_token' => $uploadToken,
            'content_type' => $mime,
            'headers' => $signed['headers'] ?? [],
        ]);
    }

    /**
     * بعد PUT الناجح إلى R2: إنشاء سجل المادة في قاعدة البيانات.
     */
    public function completeMaterialDirectUpload(Request $request, CurriculumLibraryItem $item, CurriculumLibrarySection $section)
    {
        $this->assertSectionBelongs($item, $section);
        @set_time_limit(120);

        if (! $this->curriculumMaterialDiskSupportsDirectUpload()) {
            return response()->json(['message' => 'الرفع المباشر غير متاح.'], 503);
        }

        $validated = $request->validate([
            'upload_token' => ['required', 'string', 'size:64'],
            'title' => ['nullable', 'string', 'max:255'],
            'view_in_platform' => ['nullable', 'boolean'],
            'allow_download' => ['nullable', 'boolean'],
        ]);

        $cacheKey = 'curriculum_library_mat_presign:'.$validated['upload_token'];
        $payload = Cache::pull($cacheKey);
        if (! is_array($payload)
            || (int) ($payload['curriculum_library_item_id'] ?? 0) !== (int) $item->id
            || (int) ($payload['curriculum_library_section_id'] ?? 0) !== (int) $section->id
            || (int) ($payload['user_id'] ?? 0) !== (int) auth()->id()) {
            return response()->json([
                'message' => 'انتهت صلاحية الرفع أو أنه غير صالح. أعد المحاولة.',
            ], 422);
        }

        $path = (string) ($payload['path'] ?? '');
        $diskName = (string) ($payload['disk'] ?? 'r2');
        $originalName = (string) ($payload['original_name'] ?? '');
        $maxBytes = (int) ($payload['max_bytes'] ?? config('upload_limits.curriculum_material_max_bytes', 150 * 1024 * 1024));

        if ($path === '' || str_contains($path, '..') || $diskName !== 'r2') {
            return response()->json(['message' => 'مسار التخزين غير صالح.'], 422);
        }

        $disk = Storage::disk($diskName);
        if (! $disk->exists($path)) {
            return response()->json([
                'message' => 'الملف غير ظاهر بعد على التخزين. انتظر قليلاً ثم أعد المحاولة.',
            ], 422);
        }

        $size = (int) $disk->size($path);
        if ($size <= 0) {
            try {
                $disk->delete($path);
            } catch (\Throwable) {
            }

            return response()->json(['message' => 'الملف فارغ.'], 422);
        }
        if ($size > $maxBytes) {
            try {
                $disk->delete($path);
            } catch (\Throwable) {
            }

            return response()->json(['message' => 'حجم الملف يتجاوز الحد المسموح.'], 422);
        }

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $fileKind = CurriculumLibraryMaterial::fileKindFromExtension($ext);

        $viewIn = $request->boolean('view_in_platform');
        $allowDl = $request->boolean('allow_download');
        [$viewIn, $allowDl] = $this->applyCurriculumMaterialKindRules($fileKind, $viewIn, $allowDl);

        try {
            $order = ((int) ($section->materials()->max('order') ?? 0)) + 1;

            CurriculumLibraryMaterial::create([
                'curriculum_library_section_id' => $section->id,
                'title' => $validated['title'] ?: null,
                'path' => $path,
                'storage_disk' => 'r2',
                'original_name' => $originalName,
                'file_kind' => $fileKind,
                'view_in_platform' => $viewIn,
                'allow_download' => $allowDl,
                'order' => $order,
                'is_active' => true,
            ]);
        } catch (\Throwable $e) {
            try {
                $disk->delete($path);
            } catch (\Throwable) {
            }
            Log::error('Curriculum material direct complete failed', [
                'item_id' => $item->id,
                'section_id' => $section->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'فشل حفظ المادة بعد الرفع. أعد المحاولة.'], 500);
        }

        session()->flash('success', 'تم رفع المادة مباشرة إلى Cloudflare R2.');

        return response()->json([
            'ok' => true,
            'redirect' => route('admin.curriculum-library.items.structure', $item),
        ]);
    }

    protected function assertSectionBelongs(CurriculumLibraryItem $item, CurriculumLibrarySection $section): void
    {
        if ((int) $section->curriculum_library_item_id !== (int) $item->id) {
            abort(404);
        }
    }

    protected function assertMaterialBelongs(CurriculumLibraryItem $item, CurriculumLibraryMaterial $material): void
    {
        $material->loadMissing('section');
        if (!$material->section || (int) $material->section->curriculum_library_item_id !== (int) $item->id) {
            abort(404);
        }
    }

    protected function wouldCreateCycle(CurriculumLibrarySection $section, int $newParentId): bool
    {
        $walk = $newParentId;
        $guard = 0;
        while ($walk && $guard++ < 200) {
            if ((int) $walk === (int) $section->id) {
                return true;
            }
            $walk = (int) (CurriculumLibrarySection::where('id', $walk)->value('parent_id') ?? 0);
        }

        return false;
    }

    /**
     * @return array{0: bool, 1: bool} [view_in_platform, allow_download]
     */
    protected function applyCurriculumMaterialKindRules(string $fileKind, bool $viewIn, bool $allowDl): array
    {
        if ($fileKind === 'html') {
            return [true, false];
        }
        if ($fileKind === 'pptx') {
            return [$viewIn, false];
        }
        if ($fileKind === 'other') {
            return [false, $allowDl];
        }

        return [$viewIn, $allowDl];
    }

    protected function curriculumMaterialDiskSupportsDirectUpload(): bool
    {
        try {
            $disk = Storage::disk('r2');

            return method_exists($disk, 'providesTemporaryUploadUrls')
                && $disk->providesTemporaryUploadUrls();
        } catch (\Throwable) {
            return false;
        }
    }

    private function normalizeCurriculumMaterialMime(string $contentType, string $originalName, string $ext): string
    {
        $contentType = strtolower(trim($contentType));
        $fromExt = $this->curriculumMimeFromExtension($ext);

        if ($contentType === '' || $contentType === 'application/octet-stream' || $contentType === 'binary/octet-stream') {
            return $fromExt;
        }

        return $fromExt;
    }

    private function curriculumMimeFromExtension(string $ext): string
    {
        $ext = strtolower($ext);

        return match ($ext) {
            'pdf' => 'application/pdf',
            'html', 'htm' => 'text/html',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            'rar' => 'application/vnd.rar',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
