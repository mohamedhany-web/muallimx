<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CurriculumLibraryItem;
use App\Models\CurriculumLibraryItemFile;
use App\Models\CurriculumLibraryMaterial;
use App\Models\CurriculumLibraryPreviewOpen;
use App\Models\CurriculumLibrarySection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CurriculumLibraryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $hasFullAccess = $user && $user->hasSubscriptionFeature('library_access');
        $usedFreePreview = $user ? CurriculumLibraryPreviewOpen::hasUsedFreePreview($user->id) : false;

        $query = CurriculumLibraryItem::active()
            ->with('category')
            ->ordered()
            ->where(function ($q) use ($user) {
                $q->whereNull('curriculum_library_items.category_id')
                    ->orWhereHas('category', fn ($cq) => $cq->accessibleByStudent($user));
            });

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('language') && in_array($request->language, ['ar', 'en', 'fr'], true)) {
            $query->byLanguage($request->language);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qry) use ($q) {
                $qry->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('subject', 'like', "%{$q}%");
            });
        }
        $items = $query->paginate(12)->withQueryString();
        $categories = \App\Models\CurriculumLibraryCategory::active()
            ->ordered()
            ->accessibleByStudent($user)
            ->get();

        return view('student.curriculum-library.index', compact('items', 'categories', 'hasFullAccess', 'usedFreePreview'));
    }

    public function show(Request $request, CurriculumLibraryItem $item)
    {
        $user = Auth::user();
        $hasFullAccess = $user && $user->hasSubscriptionFeature('library_access');

        if (!$item->is_active) {
            abort(404);
        }

        $item->load('category');

        if (!$item->isAccessibleByStudent($user)) {
            abort(403, 'هذا المنهج غير متاح لحسابك.');
        }

        if (!$hasFullAccess) {
            $usedFreePreview = $user ? CurriculumLibraryPreviewOpen::hasUsedFreePreview($user->id) : false;
            if ($usedFreePreview) {
                return redirect()->route('student.features.show', ['feature' => 'library_access'])
                    ->with('error', 'يمكنك معاينة ملف واحد مجاناً فقط. لفتح باقي مناهج X اشترك في الباقة المناسبة.');
            }
            CurriculumLibraryPreviewOpen::recordFreePreviewUsed($user->id, $item->id);
        }

        $sectionTree = CurriculumLibrarySection::treeForItem($item);
        $item->load(['category', 'files']);

        return view('student.curriculum-library.show', compact('item', 'hasFullAccess', 'sectionTree'));
    }

    public function download(CurriculumLibraryItem $item, CurriculumLibraryItemFile $file)
    {
        if ($file->curriculum_library_item_id !== $item->id) {
            abort(404);
        }
        if (!$item->is_active) {
            abort(404);
        }

        $user = Auth::user();
        if (!$item->isAccessibleByStudent($user)) {
            abort(403, 'هذا المنهج غير متاح لحسابك.');
        }

        if (in_array($file->file_type, ['html', 'presentation'], true)) {
            return back()->with('error', 'هذا النوع من الملفات متاح للعرض داخل المنصة فقط ولا يمكن تحميله.');
        }

        $redirect = $this->previewOrSubscriptionGate($user, $item);
        if ($redirect) {
            return $redirect;
        }

        $diskName = $file->storage_disk ?: 'public';
        $disk = Storage::disk($diskName);
        $path = $file->path;
        if (!$path || !$disk->exists($path)) {
            abort(404);
        }

        $filename = $file->label ?: basename($path);

        return $disk->download($path, $filename);
    }

    public function viewHtml(CurriculumLibraryItem $item, CurriculumLibraryItemFile $file)
    {
        if ($file->curriculum_library_item_id !== $item->id) {
            abort(404);
        }
        if (!$item->is_active) {
            abort(404);
        }
        if ($file->file_type !== 'html') {
            abort(404);
        }

        $user = Auth::user();
        if (!$item->isAccessibleByStudent($user)) {
            abort(403, 'هذا المنهج غير متاح لحسابك.');
        }

        $redirect = $this->previewOrSubscriptionGate($user, $item);
        if ($redirect) {
            return $redirect;
        }

        $diskName = $file->storage_disk ?: 'r2';
        $disk = Storage::disk($diskName);
        if (!$file->path || !$disk->exists($file->path)) {
            abort(404);
        }

        $html = $disk->get($file->path);

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Content-Security-Policy' => "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; font-src 'self' data:; media-src 'self' data:; object-src 'none'; base-uri 'none'; frame-ancestors 'self';",
        ]);
    }

    public function viewPdf(CurriculumLibraryItem $item, CurriculumLibraryItemFile $file)
    {
        if ($file->curriculum_library_item_id !== $item->id) {
            abort(404);
        }
        if (!$item->is_active) {
            abort(404);
        }
        if ($file->file_type !== 'pdf') {
            abort(404);
        }

        $user = Auth::user();
        if (!$item->isAccessibleByStudent($user)) {
            abort(403, 'هذا المنهج غير متاح لحسابك.');
        }

        $redirect = $this->previewOrSubscriptionGate($user, $item);
        if ($redirect) {
            return $redirect;
        }

        $diskName = $file->storage_disk ?: 'public';
        $disk = Storage::disk($diskName);
        if (!$file->path || !$disk->exists($file->path)) {
            abort(404);
        }

        $filename = $file->label ?: basename($file->path);

        if ($diskName === 'public' || $diskName === 'local') {
            $fullPath = $disk->path($file->path);
            if (!is_file($fullPath)) {
                abort(404);
            }

            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            ]);
        }

        $bin = $disk->get($file->path);

        return response($bin, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
        ]);
    }

    public function viewPresentation(CurriculumLibraryItem $item, CurriculumLibraryItemFile $file)
    {
        if ($file->curriculum_library_item_id !== $item->id) {
            abort(404);
        }
        if (!$item->is_active) {
            abort(404);
        }
        if ($file->file_type !== 'presentation') {
            abort(404);
        }

        $user = Auth::user();
        if (!$item->isAccessibleByStudent($user)) {
            abort(403, 'هذا المنهج غير متاح لحسابك.');
        }

        $redirect = $this->previewOrSubscriptionGate($user, $item);
        if ($redirect) {
            return $redirect;
        }

        $diskName = $file->storage_disk ?: 'public';
        $disk = Storage::disk($diskName);
        if (!$file->path || !$disk->exists($file->path)) {
            abort(404);
        }

        $publicUrl = $this->absoluteStorageUrl($diskName, $file->path);
        $embedUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' . rawurlencode($publicUrl);
        $presentationTitle = $file->label ?: 'عرض تفاعلي (PowerPoint)';
        $canUseOfficeViewer = $this->isOfficeViewerSupportedUrl($publicUrl);

        return view('student.curriculum-library.presentation', [
            'item' => $item,
            'presentationTitle' => $presentationTitle,
            'embedUrl' => $embedUrl,
            'publicUrl' => $publicUrl,
            'canUseOfficeViewer' => $canUseOfficeViewer,
        ]);
    }

    public function downloadMaterial(CurriculumLibraryItem $item, CurriculumLibraryMaterial $material)
    {
        $this->assertMaterialForItem($item, $material);
        if (!$item->is_active || !$material->is_active) {
            abort(404);
        }

        $user = Auth::user();
        if (!$item->isAccessibleByStudent($user)) {
            abort(403, 'هذا المنهج غير متاح لحسابك.');
        }

        if (!$material->effectiveAllowDownload()) {
            return back()->with('error', 'تحميل هذه المادة غير متاح.');
        }

        $redirect = $this->previewOrSubscriptionGate($user, $item);
        if ($redirect) {
            return $redirect;
        }

        $diskName = $material->storage_disk ?: 'r2';
        $disk = Storage::disk($diskName);
        if (!$material->path || !$disk->exists($material->path)) {
            abort(404);
        }

        $filename = $material->displayTitle();

        return $disk->download($material->path, $filename);
    }

    public function viewMaterialHtml(CurriculumLibraryItem $item, CurriculumLibraryMaterial $material)
    {
        $this->assertMaterialForItem($item, $material);
        if (!$item->is_active || !$material->is_active) {
            abort(404);
        }
        if ($material->file_kind !== 'html') {
            abort(404);
        }

        $user = Auth::user();
        if (!$item->isAccessibleByStudent($user)) {
            abort(403, 'هذا المنهج غير متاح لحسابك.');
        }

        if (!$material->effectiveAllowViewInPlatform()) {
            abort(404);
        }

        $redirect = $this->previewOrSubscriptionGate($user, $item);
        if ($redirect) {
            return $redirect;
        }

        $diskName = $material->storage_disk ?: 'r2';
        $disk = Storage::disk($diskName);
        if (!$material->path || !$disk->exists($material->path)) {
            abort(404);
        }

        $html = $disk->get($material->path);

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Content-Security-Policy' => "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; font-src 'self' data:; media-src 'self' data:; object-src 'none'; base-uri 'none'; frame-ancestors 'self';",
        ]);
    }

    public function viewMaterialPdf(CurriculumLibraryItem $item, CurriculumLibraryMaterial $material)
    {
        $this->assertMaterialForItem($item, $material);
        if (!$item->is_active || !$material->is_active) {
            abort(404);
        }
        if ($material->file_kind !== 'pdf') {
            abort(404);
        }

        $user = Auth::user();
        if (!$item->isAccessibleByStudent($user)) {
            abort(403, 'هذا المنهج غير متاح لحسابك.');
        }

        if (!$material->effectiveAllowViewInPlatform()) {
            abort(404);
        }

        $redirect = $this->previewOrSubscriptionGate($user, $item);
        if ($redirect) {
            return $redirect;
        }

        $diskName = $material->storage_disk ?: 'r2';
        $disk = Storage::disk($diskName);
        if (!$material->path || !$disk->exists($material->path)) {
            abort(404);
        }

        $filename = $material->displayTitle();

        if ($diskName === 'public' || $diskName === 'local') {
            $fullPath = $disk->path($material->path);
            if (!is_file($fullPath)) {
                abort(404);
            }

            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            ]);
        }

        $bin = $disk->get($material->path);

        return response($bin, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
        ]);
    }

    public function viewMaterialPresentation(CurriculumLibraryItem $item, CurriculumLibraryMaterial $material)
    {
        $this->assertMaterialForItem($item, $material);
        if (!$item->is_active || !$material->is_active) {
            abort(404);
        }
        if ($material->file_kind !== 'pptx') {
            abort(404);
        }

        $user = Auth::user();
        if (!$item->isAccessibleByStudent($user)) {
            abort(403, 'هذا المنهج غير متاح لحسابك.');
        }

        if (!$material->effectiveAllowViewInPlatform()) {
            abort(404);
        }

        $redirect = $this->previewOrSubscriptionGate($user, $item);
        if ($redirect) {
            return $redirect;
        }

        $diskName = $material->storage_disk ?: 'r2';
        $disk = Storage::disk($diskName);
        if (!$material->path || !$disk->exists($material->path)) {
            abort(404);
        }

        $publicUrl = $this->absoluteStorageUrl($diskName, $material->path);
        $embedUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' . rawurlencode($publicUrl);
        $canUseOfficeViewer = $this->isOfficeViewerSupportedUrl($publicUrl);

        return view('student.curriculum-library.presentation', [
            'item' => $item,
            'presentationTitle' => $material->displayTitle(),
            'embedUrl' => $embedUrl,
            'publicUrl' => $publicUrl,
            'canUseOfficeViewer' => $canUseOfficeViewer,
        ]);
    }

    protected function assertMaterialForItem(CurriculumLibraryItem $item, CurriculumLibraryMaterial $material): void
    {
        $material->loadMissing('section');
        if (!$material->section || (int) $material->section->curriculum_library_item_id !== (int) $item->id) {
            abort(404);
        }
    }

    protected function previewOrSubscriptionGate($user, CurriculumLibraryItem $item): ?\Illuminate\Http\RedirectResponse
    {
        $hasFullAccess = $user && $user->hasSubscriptionFeature('library_access');
        if ($hasFullAccess) {
            return null;
        }
        $used = $user ? CurriculumLibraryPreviewOpen::where('user_id', $user->id)->first() : null;
        if (!$user || !$used || (int) $used->curriculum_library_item_id !== (int) $item->id) {
            return redirect()->route('student.features.show', ['feature' => 'library_access'])
                ->with('error', 'يتطلب هذا المحتوى اشتراك مناهج X أو معاينة ضمن نفس المنهج.');
        }

        return null;
    }

    protected function absoluteStorageUrl(string $diskName, string $path): string
    {
        $disk = Storage::disk($diskName);

        if ($diskName === 'r2') {
            return $disk->temporaryUrl($path, now()->addHours(2));
        }

        $rel = $disk->url($path);
        if (str_starts_with($rel, 'http://') || str_starts_with($rel, 'https://')) {
            return $rel;
        }

        return url($rel);
    }

    protected function isOfficeViewerSupportedUrl(string $url): bool
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($scheme !== 'https') {
            return false;
        }

        if ($host === '' || $host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return false;
        }

        if (str_ends_with($host, '.local') || str_ends_with($host, '.test')) {
            return false;
        }

        return true;
    }
}
