<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AdvancedCourse;
use App\Models\PortfolioProject;
use App\Models\PortfolioProjectImage;
use App\Models\User;
use App\Services\PortfolioImageStorage;
use App\Services\SubscriptionLimitService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PortfolioProjectController extends Controller
{
    private function ensureTeacherProfileSubscription(User $user): void
    {
        abort_unless($user->hasSubscriptionFeature('teacher_profile'), 403, 'ميزة التسويق الشخصي ومعرض المحتوى غير مفعّلة في اشتراكك. يمكنك الترقية من صفحة التسعير.');
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
        $projectTypeLabels = PortfolioProject::projectTypeLabels();
        $academicYears = AcademicYear::query()->orderByDesc('id')->get();
        $advancedCourses = AdvancedCourse::query()->orderBy('title')->limit(500)->get();

        return view('student.portfolio.create', compact('contentTypeLabels', 'projectTypeLabels', 'academicYears', 'advancedCourses'));
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
            'video_url' => ['nullable', 'url', 'max:500', Rule::requiredIf(fn () => $request->input('content_type') === PortfolioProject::CONTENT_VIDEO)],
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'advanced_course_id' => 'nullable|exists:advanced_courses,id',
            'images' => [
                'nullable',
                'array',
                'max:5',
                Rule::requiredIf(fn () => $request->input('content_type') === PortfolioProject::CONTENT_GALLERY),
            ],
            'images.*' => 'image|max:'.config('upload_limits.max_upload_kb'),
        ], [
            'title.required' => __('student.portfolio_marketing.validation.title_required'),
            'project_url.url' => __('student.portfolio_marketing.validation.project_url_url'),
            'github_url.url' => __('student.portfolio_marketing.validation.github_url_url'),
            'video_url.url' => __('student.portfolio_marketing.validation.video_url_url'),
            'video_url.required_if' => __('student.portfolio_marketing.validation.video_required_if'),
            'images.required' => __('student.portfolio_marketing.validation.images_required_gallery'),
            'images.required_if' => __('student.portfolio_marketing.validation.images_required_gallery'),
            'images.max' => __('student.portfolio_marketing.validation.images_max'),
            'images.*.image' => __('student.portfolio_marketing.validation.images_must_be_image'),
            'images.*.max' => __('student.portfolio_marketing.validation.images_size'),
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

        if ($request->content_type === PortfolioProject::CONTENT_GALLERY && $request->hasFile('images')) {
            $sortOrder = 0;
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $path = PortfolioImageStorage::store($file);
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

        return redirect()->route('student.portfolio.index')->with('success', __('student.portfolio_marketing.flash.store_success'));
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
            return redirect()->route('student.portfolio.show', $project)->with('error', __('student.portfolio_marketing.flash.cannot_edit_locked'));
        }

        $project->load(['images']);
        $contentTypeLabels = PortfolioProject::contentTypeLabels();
        $projectTypeLabels = PortfolioProject::projectTypeLabels();
        $academicYears = AcademicYear::query()->orderByDesc('id')->get();
        $advancedCourses = AdvancedCourse::query()->orderBy('title')->limit(500)->get();

        return view('student.portfolio.edit', compact('project', 'contentTypeLabels', 'projectTypeLabels', 'academicYears', 'advancedCourses'));
    }

    public function update(Request $request, PortfolioProject $project)
    {
        $user = auth()->user();
        $this->ensureTeacherProfileSubscription($user);
        abort_unless((int) $project->user_id === (int) $user->id, 403);

        if (in_array($project->status, [PortfolioProject::STATUS_APPROVED, PortfolioProject::STATUS_PUBLISHED], true)) {
            return redirect()->route('student.portfolio.show', $project)->with('error', __('student.portfolio_marketing.flash.cannot_edit_locked'));
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'project_type' => 'nullable|string|in:web_app,mobile_app,api,library,script,design,game,desktop,cli,other',
            'content_type' => 'required|string|in:gallery,video,text,link',
            'description' => 'nullable|string|max:2000',
            'content_text' => 'nullable|string|max:20000',
            'project_url' => 'nullable|url|max:500',
            'github_url' => 'nullable|url|max:500',
            'video_url' => ['nullable', 'url', 'max:500', Rule::requiredIf(fn () => $request->input('content_type') === PortfolioProject::CONTENT_VIDEO)],
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'advanced_course_id' => 'nullable|exists:advanced_courses,id',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|max:'.config('upload_limits.max_upload_kb'),
        ], [
            'title.required' => __('student.portfolio_marketing.validation.title_required'),
            'project_url.url' => __('student.portfolio_marketing.validation.project_url_url'),
            'github_url.url' => __('student.portfolio_marketing.validation.github_url_url'),
            'video_url.required_if' => __('student.portfolio_marketing.validation.video_required_if'),
            'video_url.url' => __('student.portfolio_marketing.validation.video_url_url'),
            'images.max' => __('student.portfolio_marketing.validation.images_max'),
            'images.*.image' => __('student.portfolio_marketing.validation.images_must_be_image'),
            'images.*.max' => __('student.portfolio_marketing.validation.images_size'),
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

        // إذا كان مرفوضاً، إعادة إرسال لمراجعة الإدارة
        if ($project->status === PortfolioProject::STATUS_REJECTED) {
            $project->update([
                'status' => PortfolioProject::STATUS_PENDING_REVIEW,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejected_reason' => null,
            ]);
        }

        if ($request->content_type === PortfolioProject::CONTENT_GALLERY && $request->hasFile('images')) {
            $currentCount = (int) $project->images()->count();
            $remaining = max(0, 5 - $currentCount);
            $files = array_slice($request->file('images') ?? [], 0, $remaining);
            $sortOrder = (int) ($project->images()->max('sort_order') ?? -1) + 1;
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $path = PortfolioImageStorage::store($file);
                    PortfolioProjectImage::create([
                        'portfolio_project_id' => $project->id,
                        'image_path' => $path,
                        'sort_order' => $sortOrder++,
                    ]);
                    if (! $project->image_path) {
                        $project->update(['image_path' => $path]);
                    }
                }
            }
        }

        return redirect()->route('student.portfolio.show', $project)->with('success', __('student.portfolio_marketing.flash.update_success'));
    }

    public function destroy(PortfolioProject $project)
    {
        $user = auth()->user();
        $this->ensureTeacherProfileSubscription($user);
        abort_unless((int) $project->user_id === (int) $user->id, 403);

        if ($project->status === PortfolioProject::STATUS_PUBLISHED) {
            return back()->with('error', __('student.portfolio_marketing.flash.cannot_delete_published'));
        }

        $project->loadMissing('images');
        foreach ($project->images as $img) {
            PortfolioImageStorage::delete($img->image_path);
        }
        PortfolioImageStorage::delete($project->image_path);

        $project->delete();

        return redirect()->route('student.portfolio.index')->with('success', __('student.portfolio_marketing.flash.destroy_success'));
    }

    public function destroyImage(PortfolioProject $project, PortfolioProjectImage $image)
    {
        $user = auth()->user();
        $this->ensureTeacherProfileSubscription($user);
        abort_unless((int) $project->user_id === (int) $user->id, 403);
        abort_unless((int) $image->portfolio_project_id === (int) $project->id, 404);

        if (in_array($project->status, [PortfolioProject::STATUS_APPROVED, PortfolioProject::STATUS_PUBLISHED], true)) {
            return back()->with('error', __('student.portfolio_marketing.flash.cannot_edit_images_locked'));
        }

        PortfolioImageStorage::delete($image->image_path);
        $image->delete();

        // ensure preview image
        $newPreview = $project->fresh()->preview_image_path;
        $project->update(['image_path' => $newPreview]);

        return back()->with('success', __('student.portfolio_marketing.flash.destroy_image_success'));
    }
}
