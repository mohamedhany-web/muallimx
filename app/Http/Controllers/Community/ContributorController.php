<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityDataset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ContributorController extends Controller
{
    private const DIRECTORY = 'community_datasets';

    private static function disk(): string
    {
        return config('filesystems.community_disk', 'local');
    }

    public function dashboard(): View
    {
        $user = Auth::user();
        $myDatasets = CommunityDataset::where('created_by_user_id', $user->id)->ordered()->get();
        $pending = $myDatasets->where('status', CommunityDataset::STATUS_PENDING)->count();
        $approved = $myDatasets->where('status', CommunityDataset::STATUS_APPROVED)->count();
        $rejected = $myDatasets->where('status', CommunityDataset::STATUS_REJECTED)->count();

        return view('community.contributor.dashboard', [
            'user' => $user,
            'myDatasetsCount' => $myDatasets->count(),
            'pendingCount' => $pending,
            'approvedCount' => $approved,
            'rejectedCount' => $rejected,
            'recentSubmissions' => $myDatasets->take(5),
        ]);
    }

    public function datasets(): View
    {
        $user = Auth::user();
        $datasets = CommunityDataset::where('created_by_user_id', $user->id)->ordered()->paginate(15);
        return view('community.contributor.datasets.index', compact('datasets'));
    }

    public function createDataset(): View
    {
        return view('community.contributor.datasets.create');
    }

    public function storeDataset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:xlsx,xls,csv|max:10240',
            'file_url' => 'nullable|url|max:500',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();
        $validated['status'] = CommunityDataset::STATUS_PENDING;
        $validated['is_active'] = false;
        $validated['created_by_user_id'] = Auth::id();

        if ($request->hasFile('file')) {
            $name = $this->uniqueFilename(self::DIRECTORY, $request->file('file')->getClientOriginalName());
            $path = $request->file('file')->storeAs(self::DIRECTORY, $name, self::disk());
            $validated['file_path'] = $path;
            $validated['file_size'] = $this->humanFileSize($request->file('file')->getSize());
        }

        CommunityDataset::create($validated);
        return redirect()->route('community.contributor.datasets.index')
            ->with('success', 'تم إرسال مجموعة البيانات بنجاح. ستتم مراجعتها من الإدارة قبل النشر.');
    }

    private function uniqueFilename(string $directory, string $originalName): string
    {
        $safe = basename(preg_replace('/[\\\\\/:\*\?"<>|]/', '_', $originalName) ?: 'file');
        if (!Storage::disk(self::disk())->exists($directory . '/' . $safe)) {
            return $safe;
        }
        $ext = pathinfo($safe, PATHINFO_EXTENSION);
        $base = pathinfo($safe, PATHINFO_FILENAME) ?: 'file';
        return $base . '_' . uniqid() . ($ext ? '.' . $ext : '');
    }

    private function humanFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
