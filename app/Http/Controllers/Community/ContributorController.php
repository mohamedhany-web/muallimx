<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityDataset;
use App\Models\ContributorProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ContributorController extends Controller
{
    private const DIRECTORY = 'community_datasets';

    /** امتدادات مسموحة للرفع (ملفات بيانات، أرشيفات، نصوص) — تُخزَّن على Cloudflare R2 عند ضبط FILESYSTEM_DISK_COMMUNITY=r2 */
    public const ALLOWED_EXTENSIONS = 'xlsx,xls,csv,json,txt,zip,pdf,xml,tsv';

    /** حد حجم ملف واحد بالكيلوبايت (25 ميجا) */
    public const MAX_FILE_KB = 25600;

    /** أقصى عدد ملفات في تقديم واحد */
    public const MAX_FILES = 20;

    private static function disk(): string
    {
        return community_disk();
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
            'category' => 'nullable|string|in:' . implode(',', array_keys(CommunityDataset::CATEGORIES)),
            'file' => 'nullable|file|mimes:xlsx,xls,csv,json,txt,zip,pdf,xml|max:' . self::MAX_FILE_KB,
            'files' => 'nullable|array|max:' . self::MAX_FILES,
            'files.*' => 'file|mimes:xlsx,xls,csv,json,txt,zip,pdf,xml,tsv|max:' . self::MAX_FILE_KB,
            'file_url' => 'nullable|url|max:500',
        ], [
            'files.*.mimes' => 'الملفات المسموحة: xlsx, xls, csv, json, txt, zip, pdf, xml, tsv',
            'files.*.max' => 'حجم كل ملف لا يتجاوز ' . (self::MAX_FILE_KB / 1024) . ' ميجا',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();
        $validated['status'] = CommunityDataset::STATUS_PENDING;
        $validated['is_active'] = false;
        $validated['created_by_user_id'] = Auth::id();

        $uploadedFiles = [];
        $disk = self::disk();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = $this->uniqueFilename(self::DIRECTORY, $file->getClientOriginalName());
            $path = $file->storeAs(self::DIRECTORY, $name, $disk);
            $uploadedFiles[] = [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => $this->humanFileSize($file->getSize()),
            ];
        }

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if (!$file->isValid()) {
                    continue;
                }
                $name = $this->uniqueFilename(self::DIRECTORY, $file->getClientOriginalName());
                $path = $file->storeAs(self::DIRECTORY, $name, $disk);
                $uploadedFiles[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $this->humanFileSize($file->getSize()),
                ];
            }
        }

        if (!empty($uploadedFiles)) {
            $validated['files'] = $uploadedFiles;
            $first = $uploadedFiles[0];
            $validated['file_path'] = $first['path'];
            $validated['file_size'] = count($uploadedFiles) > 1
                ? $first['size'] . ' + ' . (count($uploadedFiles) - 1) . ' ملف'
                : $first['size'];
        }

        CommunityDataset::create($validated);
        return redirect()->route('community.contributor.datasets.index')
            ->with('success', 'تم إرسال مجموعة البيانات بنجاح. ستتم مراجعتها من الإدارة قبل النشر.');
    }

    public function profileEdit(): View
    {
        $user = Auth::user();
        $profile = $user->contributorProfile ?? new ContributorProfile(['user_id' => $user->id]);
        return view('community.contributor.profile.edit', compact('user', 'profile'));
    }

    public function profileStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bio' => 'nullable|string|max:2000',
            'experience' => 'nullable|string|max:3000',
            'linkedin_url' => 'nullable|url|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'photo.image' => 'يجب أن يكون الملف صورة (jpeg, png, jpg, webp).',
            'photo.max' => 'حجم الصورة لا يتجاوز 2 ميجا.',
        ]);

        $user = Auth::user();
        $profile = $user->contributorProfile ?? new ContributorProfile(['user_id' => $user->id]);

        if ($request->hasFile('photo')) {
            if ($profile->photo_path && Storage::disk('public')->exists($profile->photo_path)) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $path = $request->file('photo')->store('contributor-profiles', 'public');
            $validated['photo_path'] = $path;
        }

        $validated['status'] = ContributorProfile::STATUS_PENDING;
        $validated['submitted_at'] = now();
        $profile->fill($validated);
        $profile->save();

        return redirect()->route('community.contributor.profile.edit')
            ->with('success', 'تم إرسال نبذتك بنجاح. ستتم مراجعتها من الإدارة قبل ظهورها في صفحة المساهمين.');
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
