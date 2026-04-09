<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\PortfolioProject;
use App\Models\User;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    /**
     * عرض صفحة البورتفوليو (بطاقات معلّمين + تصفية حسب المسار).
     */
    public function index(Request $request)
    {
        $learningPaths = AcademicYear::where('is_active', true)
            ->ordered()
            ->get(['id', 'name']);

        $categoryId = $request->get('path'); // تصفية حسب المسار (academic_year_id)
        $search = trim((string) $request->get('q', ''));
        $sort = (string) $request->get('sort', 'most_projects');

        $projectScope = fn ($q) => $q->published()
            ->when($categoryId, fn ($qq) => $qq->where('academic_year_id', $categoryId));

        $teachersQuery = User::query()
            ->select(['id', 'name', 'profile_image', 'bio', 'portfolio_headline', 'portfolio_about', 'portfolio_skills', 'portfolio_intro_video_url', 'portfolio_marketing_published', 'portfolio_profile_status', 'portfolio_profile_reviewed_at', 'updated_at'])
            ->whereHas('portfolioProjects', $projectScope)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('portfolio_headline', 'like', "%{$search}%")
                        ->orWhere('portfolio_about', 'like', "%{$search}%")
                        ->orWhere('bio', 'like', "%{$search}%");
                });
            })
            ->withCount(['portfolioProjects as published_projects_count' => $projectScope])
            ->with([
                'portfolioProjects' => fn ($q) => $q->published()
                    ->when($categoryId, fn ($qq) => $qq->where('academic_year_id', $categoryId))
                    ->with(['academicYear:id,name', 'advancedCourse:id,title', 'images'])
                    ->latest('published_at'),
            ]);

        if ($sort === 'name') {
            $teachersQuery->orderBy('name');
        } elseif ($sort === 'recent') {
            $teachersQuery->orderByDesc('updated_at');
        } else {
            $teachersQuery->orderByDesc('published_projects_count');
        }

        $teachersCount = (clone $teachersQuery)->count();

        $teachers = $teachersQuery
            ->paginate(12)
            ->withQueryString();

        return view('public.portfolio.index', compact('teachers', 'learningPaths', 'categoryId', 'teachersCount', 'search', 'sort'));
    }

    /**
     * عرض الملف العام للمعلّم وكل مشاريعه المنشورة.
     */
    public function showTeacher($id, Request $request)
    {
        $categoryId = $request->get('path');

        $teacher = User::query()
            ->select(['id', 'name', 'profile_image', 'bio', 'portfolio_headline', 'portfolio_about', 'portfolio_skills', 'portfolio_intro_video_url', 'portfolio_marketing_published', 'portfolio_profile_status', 'portfolio_profile_reviewed_at', 'updated_at'])
            ->where('id', $id)
            ->whereHas('portfolioProjects', function ($q) use ($categoryId) {
                $q->published()->when($categoryId, fn ($qq) => $qq->where('academic_year_id', $categoryId));
            })
            ->firstOrFail();

        $projects = PortfolioProject::published()
            ->where('user_id', $teacher->id)
            ->when($categoryId, fn ($q) => $q->where('academic_year_id', $categoryId))
            ->with(['academicYear:id,name', 'advancedCourse:id,title', 'images'])
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('public.portfolio.teacher', compact('teacher', 'projects', 'categoryId'));
    }

    /**
     * عرض مشروع واحد
     */
    public function show($id)
    {
        $project = PortfolioProject::published()
            ->with([
                'user:id,name,profile_image,bio,portfolio_headline,portfolio_about,portfolio_skills,portfolio_intro_video_url,portfolio_marketing_published,portfolio_profile_status,portfolio_profile_reviewed_at,updated_at',
                'academicYear:id,name',
                'advancedCourse:id,title',
                'images',
            ])
            ->findOrFail($id);

        $related = PortfolioProject::published()
            ->where('id', '!=', $project->id)
            ->where('academic_year_id', $project->academic_year_id)
            ->with(['user:id,name,profile_image,portfolio_marketing_published,portfolio_profile_status,portfolio_profile_reviewed_at,updated_at', 'images'])
            ->latest('published_at')
            ->take(4)
            ->get();

        return view('public.portfolio.show', compact('project', 'related'));
    }
}
