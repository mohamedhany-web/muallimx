<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoLibraryCategory;
use App\Models\VideoLibraryVideo;
use App\Services\YouTubeVideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VideoLibraryController extends Controller
{
    public function index(Request $request)
    {
        $query = VideoLibraryVideo::with('category')->ordered();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qry) use ($q) {
                $qry->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('youtube_id', 'like', "%{$q}%");
            });
        }

        $videos = $query->paginate(20)->withQueryString();
        $categories = VideoLibraryCategory::ordered()->get();

        return view('admin.video-library.index', compact('videos', 'categories'));
    }

    public function categories()
    {
        $categories = VideoLibraryCategory::withCount('videos')->ordered()->get();

        return view('admin.video-library.categories', compact('categories'));
    }

    public function createCategory()
    {
        return view('admin.video-library.categories-form', ['category' => null]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $this->validateCategory($request);
        VideoLibraryCategory::create($validated);

        return redirect()->route('admin.video-library.categories')->with('success', 'تم إنشاء التصنيف (القناة) بنجاح.');
    }

    public function editCategory(VideoLibraryCategory $category)
    {
        return view('admin.video-library.categories-form', compact('category'));
    }

    public function updateCategory(Request $request, VideoLibraryCategory $category)
    {
        $validated = $this->validateCategory($request, $category->id);
        $category->update($validated);

        return redirect()->route('admin.video-library.categories')->with('success', 'تم تحديث التصنيف بنجاح.');
    }

    public function destroyCategory(VideoLibraryCategory $category)
    {
        $category->videos()->update(['category_id' => null]);
        $category->delete();

        return redirect()->route('admin.video-library.categories')->with('success', 'تم حذف التصنيف.');
    }

    public function createVideo()
    {
        $categories = VideoLibraryCategory::ordered()->get();

        return view('admin.video-library.videos-form', ['video' => null, 'categories' => $categories]);
    }

    public function storeVideo(Request $request)
    {
        $validated = $this->validateVideo($request);
        VideoLibraryVideo::create($validated);

        return redirect()->route('admin.video-library.index')->with('success', 'تم إضافة الفيديو بنجاح.');
    }

    public function editVideo(VideoLibraryVideo $video)
    {
        $categories = VideoLibraryCategory::ordered()->get();

        return view('admin.video-library.videos-form', compact('video', 'categories'));
    }

    public function updateVideo(Request $request, VideoLibraryVideo $video)
    {
        $validated = $this->validateVideo($request, $video->id);
        $video->update($validated);

        return redirect()->route('admin.video-library.index')->with('success', 'تم تحديث الفيديو بنجاح.');
    }

    public function destroyVideo(VideoLibraryVideo $video)
    {
        $video->delete();

        return redirect()->route('admin.video-library.index')->with('success', 'تم حذف الفيديو.');
    }

    private function validateCategory(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('video_library_categories', 'slug')->ignore($ignoreId),
            ],
            'description' => 'nullable|string|max:5000',
            'cover_color' => 'nullable|string|max:32',
            'icon' => 'nullable|string|max:64',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        if ($validated['slug'] === '') {
            $validated['slug'] = 'channel-'.Str::lower(Str::random(6));
        }
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order'] = (int) ($validated['order'] ?? 0);
        $validated['cover_color'] = $validated['cover_color'] ?: '#c62828';
        $validated['icon'] = $validated['icon'] ?: 'fa-play-circle';

        return $validated;
    }

    private function validateVideo(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('video_library_videos', 'slug')->ignore($ignoreId),
            ],
            'category_id' => 'nullable|exists:video_library_categories,id',
            'description' => 'nullable|string|max:10000',
            'youtube_url' => [
                'required',
                'string',
                'max:500',
                function ($attribute, $value, $fail) {
                    if (! YouTubeVideoService::isValidUrl($value)) {
                        $fail('رابط يوتيوب غير صالح. الصق رابط watch أو youtu.be أو Shorts.');
                    }
                },
            ],
            'duration_seconds' => 'nullable|integer|min:0|max:86400',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $yt = YouTubeVideoService::normalizeFromInput($validated['youtube_url']);
        $validated['youtube_id'] = $yt['youtube_id'];
        $validated['youtube_url'] = $yt['youtube_url'];
        $validated['thumbnail_url'] = $yt['thumbnail_url'];
        $validated['slug'] = filled($validated['slug'] ?? null)
            ? $validated['slug']
            : VideoLibraryVideo::uniqueSlugFromTitle($validated['title'], $ignoreId);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['order'] = (int) ($validated['order'] ?? 0);
        $validated['duration_seconds'] = isset($validated['duration_seconds']) && $validated['duration_seconds'] !== ''
            ? (int) $validated['duration_seconds']
            : null;
        $validated['published_at'] = $validated['published_at'] ?? now();
        $validated['category_id'] = $validated['category_id'] ?: null;

        return $validated;
    }
}
