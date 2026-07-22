<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\VideoLibraryCategory;
use App\Models\VideoLibraryPreviewOpen;
use App\Models\VideoLibraryVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoLibraryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $hasFullAccess = $user && $user->hasSubscriptionFeature('video_library_access');
        $usedFreePreview = $user ? VideoLibraryPreviewOpen::hasUsedFreePreview($user->id) : false;
        $previewVideoId = $user ? VideoLibraryPreviewOpen::previewVideoId($user->id) : null;

        $categories = VideoLibraryCategory::active()
            ->ordered()
            ->withCount(['activeVideos as videos_count'])
            ->get();

        $query = VideoLibraryVideo::active()->with('category')->ordered();

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category)->where('is_active', true);
            });
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qry) use ($q) {
                $qry->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }
        if ($request->boolean('featured')) {
            $query->featured();
        }

        $videos = $query->paginate(24)->withQueryString();
        $featured = VideoLibraryVideo::active()->featured()->with('category')->ordered()->limit(8)->get();
        $activeCategory = null;
        if ($request->filled('category')) {
            $activeCategory = VideoLibraryCategory::active()->where('slug', $request->category)->first();
        }

        return view('student.video-library.index', compact(
            'videos',
            'categories',
            'featured',
            'activeCategory',
            'hasFullAccess',
            'usedFreePreview',
            'previewVideoId'
        ));
    }

    public function category(Request $request, VideoLibraryCategory $category)
    {
        if (! $category->is_active) {
            abort(404);
        }

        $request->merge(['category' => $category->slug]);

        return $this->index($request);
    }

    public function show(VideoLibraryVideo $video)
    {
        $user = Auth::user();
        $hasFullAccess = $user && $user->hasSubscriptionFeature('video_library_access');

        if (! $video->is_active) {
            abort(404);
        }

        $video->load('category');

        $canWatch = false;
        if ($hasFullAccess) {
            $canWatch = true;
        } elseif ($user) {
            $previewId = VideoLibraryPreviewOpen::previewVideoId($user->id);
            if ($previewId === null) {
                VideoLibraryPreviewOpen::recordFreePreviewUsed($user->id, $video->id);
                $canWatch = true;
            } elseif ((int) $previewId === (int) $video->id) {
                $canWatch = true;
            } else {
                return redirect()->route('student.features.show', ['feature' => 'video_library_access'])
                    ->with('error', 'يمكنك مشاهدة فيديو واحد مجاناً فقط. اشترك للوصول الكامل لمكتبة الفيديو.');
            }
        } else {
            return redirect()->route('login');
        }

        if ($canWatch) {
            $video->incrementViews();
        }

        $related = VideoLibraryVideo::active()
            ->where('id', '!=', $video->id)
            ->when($video->category_id, fn ($q) => $q->where('category_id', $video->category_id))
            ->ordered()
            ->limit(12)
            ->get();

        if ($related->count() < 6) {
            $extra = VideoLibraryVideo::active()
                ->where('id', '!=', $video->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->ordered()
                ->limit(12 - $related->count())
                ->get();
            $related = $related->concat($extra);
        }

        $usedFreePreview = $user ? VideoLibraryPreviewOpen::hasUsedFreePreview($user->id) : false;
        $previewVideoId = $user ? VideoLibraryPreviewOpen::previewVideoId($user->id) : null;

        return view('student.video-library.show', compact(
            'video',
            'related',
            'hasFullAccess',
            'canWatch',
            'usedFreePreview',
            'previewVideoId'
        ));
    }
}
