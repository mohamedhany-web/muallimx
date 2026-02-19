<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityCompetition;
use App\Models\CommunityDataset;
use App\Services\Community\DatasetFileReaderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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

    public function datasets(): View
    {
        $datasets = CommunityDataset::active()->ordered()->paginate(12);
        return view('community.datasets.index', compact('datasets'));
    }

    public function datasetShow(DatasetFileReaderService $reader, CommunityDataset $dataset): View|RedirectResponse
    {
        if (!$dataset->is_active) {
            abort(404);
        }

        $preview = ['headers' => [], 'rows' => []];

        if ($dataset->file_path) {
            $fullPath = Storage::disk('local')->path($dataset->file_path);
            $preview = $reader->readPreview($fullPath);
        }

        return view('community.datasets.show', [
            'dataset' => $dataset,
            'previewHeaders' => $preview['headers'],
            'previewRows' => $preview['rows'],
        ]);
    }

    public function datasetDownload(CommunityDataset $dataset): StreamedResponse
    {
        if (!$dataset->is_active || !$dataset->file_path) {
            abort(404);
        }

        if (!Storage::disk('local')->exists($dataset->file_path)) {
            abort(404);
        }

        $name = basename($dataset->file_path);

        return Storage::disk('local')->download($dataset->file_path, $name);
    }

    public function discussions(): View
    {
        return view('community.discussions.index');
    }
}
