<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\PortfolioProject;
use App\Models\PortfolioProjectImage;
use App\Models\User;
use App\Services\SubscriptionLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PortfolioProjectController extends Controller
{
    private function ensureTeacherProfileSubscription(User $user): void
    {
        abort_unless($user->hasSubscriptionFeature('teacher_profile'), 403, 'ميزة البروفايل والمشاريع للمعلم غير مفعّلة في اشتراكك. يمكنك الترقية من صفحة التسعير.');
    }

    public function index()
    {
        $user = auth()->user();
        $this->ensureTeacherProfileSubscription($user);
        $projects = $user->portfolioProjects()->with(['academicYear', 'advancedCourse', 'images'])->latest()->paginate(10);
        $subscription = $user->activeSubscription();
        $limits = SubscriptionLimitService::limitsForUser($user);

        return view('student.portfolio.index', compact('projects', 'subscription', 'limits'));
    }

    public function create()
    {
        $this->ensureTeacherProfileSubscription(auth()->user());
        $contentTypeLabels = PortfolioProject::contentTypeLabels();
        return view('student.portfolio.create', compact('contentTypeLabels'));
    }

    public function store(Request $request)
    {
        $this->ensureTeacherProfileSubscription(auth()->user());
        $request->validate([
            'title' => 'required|string|max:255',
            'project_type' => 'nullable|string|in:web_app,mobile_app,api,library,script,design,game,desktop,cli,other',
            'content_type' => 'required|string|in:gallery,video,text,link',
            'description' => 'nullable|string|max:2000',
            'content_text' => 'nullable|string|max:20000',
            'project_url' => 'nullable|url|max:500',
            'github_url' => 'nullable|url|max:500',
            'video_url' => 'nullable|url|max:500',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'advanced_course_id' => 'nullable|exists:advanced_courses,id',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|max:'.config('upload_limits.max_upload_kb'),
        ], [
            'title.required' => 'عنوان المشروع مطلوب',
            'project_url.url' => 'رابط المشروع يجب أن يكون رابطاً صحيحاً',
            'github_url.url' => 'رابط GitHub يجب أن يكون رابطاً صحيحاً',
            'video_url.url' => 'رابط الفيديو يجب أن يكون رابطاً صحيحاً',
            'images.max' => 'حد أقصى 5 صور للمشروع',
            'images.*.image' => 'يجب أن يكون الملف صورة',
            'images.*.max' => 'كل صورة حد أقصى 2 ميجابايت',
        ]);

        $data = [
            'user_id' => auth()->id(),
            'title' => $request->title,
            'project_type' => $request->project_type ?: null,
            'content_type' => $request->content_type ?: PortfolioProject::CONTENT_GALLERY,
            'description' => $request->description,
            'content_text' => $request->content_text,
            'project_url' => $request->project_url,
            'github_url' => $request->github_url,
            'video_url' => $request->video_url,
            'academic_year_id' => $request->academic_year_id ?: null,
            'advanced_course_id' => $request->advanced_course_id ?: null,
            'status' => PortfolioProject::STATUS_PENDING_REVIEW,
        ];

        $project = PortfolioProject::create($data);

        $dir = public_path('portfolio-images');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if ($request->content_type === PortfolioProject::CONTENT_GALLERY && $request->hasFile('images')) {
            $sortOrder = 0;
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $file->move($dir, $name);
                    $path = 'portfolio-images/' . $name;
                    PortfolioProjectImage::create([
                        'portfolio_project_id' => $project->id,
                        'image_path' => $path,
                        'sort_order' => $sortOrder++,
                    ]);
                    if ($sortOrder === 1) {
                        $project->update(['image_path' => $path]);
                    }
                }
            }
        }

        return redirect()->route('student.portfolio.index')->with('success', 'تم رفع المشروع بنجاح. سيتم مراجعته من المدرب ثم النشر في البورتفوليو.');
    }

    public function show(PortfolioProject $project)
    {
        $user = auth()->user();
        $this->ensureTeacherProfileSubscription($user);
        abort_unless((int) $project->user_id === (int) $user->id, 403);

        $project->load(['academicYear', 'advancedCourse', 'images', 'reviewer:id,name']);

        return view('student.portfolio.show', compact('project'));
    }

    public function edit(PortfolioProject $project)
    {
        $user = auth()->user();
        $this->ensureTeacherProfileSubscription($user);
        abort_unless((int) $project->user_id === (int) $user->id, 403);

        if (in_array($project->status, [PortfolioProject::STATUS_APPROVED, PortfolioProject::STATUS_PUBLISHED], true)) {
            return redirect()->route('student.portfolio.show', $project)->with('error', 'لا يمكن تعديل مشروع معتمد/منشور. يمكنك رفع مشروع جديد.');
        }

        $project->load(['images']);
        $contentTypeLabels = PortfolioProject::contentTypeLabels();

        return view('student.portfolio.edit', compact('project', 'contentTypeLabels'));
    }

    public function update(Request $request, PortfolioProject $project)
    {
        $user = auth()->user();
        $this->ensureTeacherProfileSubscription($user);
        abort_unless((int) $project->user_id === (int) $user->id, 403);

        if (in_array($project->status, [PortfolioProject::STATUS_APPROVED, PortfolioProject::STATUS_PUBLISHED], true)) {
            return redirect()->route('student.portfolio.show', $project)->with('error', 'لا يمكن تعديل مشروع معتمد/منشور.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'project_type' => 'nullable|string|in:web_app,mobile_app,api,library,script,design,game,desktop,cli,other',
            'content_type' => 'required|string|in:gallery,video,text,link',
            'description' => 'nullable|string|max:2000',
            'content_text' => 'nullable|string|max:20000',
            'project_url' => 'nullable|url|max:500',
            'github_url' => 'nullable|url|max:500',
            'video_url' => 'nullable|url|max:500',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'advanced_course_id' => 'nullable|exists:advanced_courses,id',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|max:'.config('upload_limits.max_upload_kb'),
        ]);

        $project->update([
            'title' => $request->title,
            'project_type' => $request->project_type ?: null,
            'content_type' => $request->content_type ?: PortfolioProject::CONTENT_GALLERY,
            'description' => $request->description,
            'content_text' => $request->content_text,
            'project_url' => $request->project_url,
            'github_url' => $request->github_url,
            'video_url' => $request->video_url,
            'academic_year_id' => $request->academic_year_id ?: null,
            'advanced_course_id' => $request->advanced_course_id ?: null,
        ]);

        // إذا كان مرفوضاً، إعادة إرسال للمراجعة
        if ($project->status === PortfolioProject::STATUS_REJECTED) {
            $project->update([
                'status' => PortfolioProject::STATUS_PENDING_REVIEW,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejected_reason' => null,
            ]);
        }

        $dir = public_path('portfolio-images');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if ($request->content_type === PortfolioProject::CONTENT_GALLERY && $request->hasFile('images')) {
            $currentCount = (int) $project->images()->count();
            $remaining = max(0, 5 - $currentCount);
            $files = array_slice($request->file('images') ?? [], 0, $remaining);
            $sortOrder = (int) ($project->images()->max('sort_order') ?? -1) + 1;
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $file->move($dir, $name);
                    $path = 'portfolio-images/' . $name;
                    PortfolioProjectImage::create([
                        'portfolio_project_id' => $project->id,
                        'image_path' => $path,
                        'sort_order' => $sortOrder++,
                    ]);
                    if (!$project->image_path) {
                        $project->update(['image_path' => $path]);
                    }
                }
            }
        }

        return redirect()->route('student.portfolio.show', $project)->with('success', 'تم حفظ التعديلات.');
    }

    public function destroy(PortfolioProject $project)
    {
        $user = auth()->user();
        $this->ensureTeacherProfileSubscription($user);
        abort_unless((int) $project->user_id === (int) $user->id, 403);

        if ($project->status === PortfolioProject::STATUS_PUBLISHED) {
            return back()->with('error', 'لا يمكن حذف مشروع منشور.');
        }

        // delete images from disk
        foreach ($project->images as $img) {
            $path = public_path($img->image_path);
            if (File::isFile($path)) {
                @unlink($path);
            }
        }
        if ($project->image_path) {
            $path = public_path($project->image_path);
            if (File::isFile($path)) {
                @unlink($path);
            }
        }

        $project->delete();

        return redirect()->route('student.portfolio.index')->with('success', 'تم حذف المشروع.');
    }

    public function destroyImage(PortfolioProject $project, PortfolioProjectImage $image)
    {
        $user = auth()->user();
        $this->ensureTeacherProfileSubscription($user);
        abort_unless((int) $project->user_id === (int) $user->id, 403);
        abort_unless((int) $image->portfolio_project_id === (int) $project->id, 404);

        if (in_array($project->status, [PortfolioProject::STATUS_APPROVED, PortfolioProject::STATUS_PUBLISHED], true)) {
            return back()->with('error', 'لا يمكن تعديل الصور لمشروع معتمد/منشور.');
        }

        $path = public_path($image->image_path);
        if (File::isFile($path)) {
            @unlink($path);
        }
        $image->delete();

        // ensure preview image
        $newPreview = $project->fresh()->preview_image_path;
        $project->update(['image_path' => $newPreview]);

        return back()->with('success', 'تم حذف الصورة.');
    }
}
