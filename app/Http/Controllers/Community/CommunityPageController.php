<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityCompetition;
use App\Models\CommunityDataset;
use App\Models\ContributorProfile;
use App\Models\User;
use App\Services\Community\DatasetFileReaderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * صفحات مجتمع البيانات: المسابقات، مجموعات البيانات، المناقشات.
 * عرض عادي للمستخدم (قراءة فقط — بدون CRUD).
 */
class CommunityPageController extends Controller
{
    public function competitions(): View
    {
        $competitions = CommunityCompetition::active()->ordered()->paginate(12);
        return view('community.competitions.index', compact('competitions'));
    }

    public function datasets(Request $request): View
    {
        $search = $request->input('q');
        $category = $request->input('category');

        $query = CommunityDataset::public()
            ->search($search)
            ->category($category)
            ->ordered();

        $datasets = $query->paginate(12)->withQueryString();

        $categoriesWithCount = collect(CommunityDataset::CATEGORIES)->map(function ($label, $key) {
            $count = CommunityDataset::public()->where('category', $key)->count();
            return ['key' => $key, 'label' => $label, 'count' => $count];
        })->values();

        return view('community.datasets.index', [
            'datasets' => $datasets,
            'categoriesWithCount' => $categoriesWithCount,
            'currentSearch' => $search,
            'currentCategory' => $category,
        ]);
    }

    public function datasetShow(DatasetFileReaderService $reader, CommunityDataset $dataset): View|RedirectResponse
    {
        if ($dataset->status !== CommunityDataset::STATUS_APPROVED || !$dataset->is_active) {
            abort(404);
        }

        $disk = community_disk();
        $preview = ['headers' => [], 'rows' => []];

        if ($dataset->file_path) {
            $preview = $reader->readPreviewFromStorage($disk, $dataset->file_path);
        }

        return view('community.datasets.show', [
            'dataset' => $dataset,
            'previewHeaders' => $preview['headers'],
            'previewRows' => $preview['rows'],
        ]);
    }

    public function datasetDownload(CommunityDataset $dataset): StreamedResponse
    {
        if ($dataset->status !== CommunityDataset::STATUS_APPROVED || !$dataset->is_active || !$dataset->file_path) {
            abort(404);
        }

        $disk = community_disk();
        if (!Storage::disk($disk)->exists($dataset->file_path)) {
            abort(404);
        }

        $name = basename($dataset->file_path);

        return Storage::disk($disk)->download($dataset->file_path, $name);
    }

    /**
     * معاينة البيانات كـ JSON (تحميل كسول — لا يُبطئ فتح الصفحة).
     */
    public function datasetPreview(DatasetFileReaderService $reader, CommunityDataset $dataset): JsonResponse
    {
        if ($dataset->status !== CommunityDataset::STATUS_APPROVED || !$dataset->is_active) {
            abort(404);
        }

        $disk = community_disk();
        $pathToRead = $dataset->file_path;
        if (!$pathToRead && !empty($dataset->files)) {
            $first = $dataset->files[0];
            $pathToRead = is_array($first) ? ($first['path'] ?? null) : null;
        }
        if (!$pathToRead || !Storage::disk($disk)->exists($pathToRead)) {
            return response()->json(['headers' => [], 'rows' => []]);
        }

        $preview = $reader->readPreviewFromStorage($disk, $pathToRead);
        return response()->json([
            'headers' => $preview['headers'],
            'rows' => $preview['rows'],
        ]);
    }

    public function discussions(): View
    {
        return view('community.discussions.index');
    }

    /**
     * صفحة البيانات العامة — بدون تسجيل دخول.
     * بحث، تصنيفات، بطاقات، ترقيم؛ تتحمل آلاف السجلات.
     */
    public function publicDatasets(Request $request): View
    {
        $search = $request->input('q');
        $category = $request->input('category');

        $query = CommunityDataset::public()
            ->with('creator')
            ->search($search)
            ->category($category)
            ->ordered();

        $datasets = $query->paginate(24)->withQueryString();

        $categoriesWithCount = collect(CommunityDataset::CATEGORIES)->map(function ($label, $key) {
            $count = CommunityDataset::public()->where('category', $key)->count();
            return ['key' => $key, 'label' => $label, 'count' => $count];
        })->values();

        return view('public.community.datasets', [
            'datasets' => $datasets,
            'categoriesWithCount' => $categoriesWithCount,
            'currentSearch' => $search,
            'currentCategory' => $category,
        ]);
    }

    /**
     * عرض مجموعة بيانات واحدة (عام — بدون تسجيل).
     */
    public function publicDatasetShow(CommunityDataset $dataset): View|RedirectResponse
    {
        if ($dataset->status !== CommunityDataset::STATUS_APPROVED || !$dataset->is_active) {
            abort(404);
        }

        return view('public.community.dataset-show', [
            'dataset' => $dataset,
            'previewUrl' => route('community.data.preview', $dataset),
        ]);
    }

    /** صفحة المساهمين (عامة — بدون تسجيل دخول) */
    public function contributors(): View
    {
        $contributors = ContributorProfile::approved()
            ->with('user')
            ->orderBy('reviewed_at', 'desc')
            ->get();
        return view('community.contributors.index', compact('contributors'));
    }

    /** صفحة عرض ملف مساهم واحد (مثل الملف التعريفي للمدرب) */
    public function contributorShow(User $user): View
    {
        $profile = ContributorProfile::where('user_id', $user->id)
            ->approved()
            ->with('user')
            ->firstOrFail();
        return view('community.contributors.show', compact('profile'));
    }
}
