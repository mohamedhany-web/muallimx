<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\PortfolioProject;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    /**
     * عرض صفحة البورتفوليو (مع تصنيفات = المسارات التعليمية)
     */
    public function index(Request $request)
    {
        $learningPaths = AcademicYear::where('is_active', true)
            ->ordered()
            ->get(['id', 'name']);

        $categoryId = $request->get('path'); // تصفية حسب المسار (academic_year_id)

        $query = PortfolioProject::published()
            ->with(['user:id,name,profile_image', 'academicYear:id,name', 'advancedCourse:id,title', 'images']);

        if ($categoryId) {
            $query->where('academic_year_id', $categoryId);
        }

        $projects = $query->latest('published_at')->paginate(12);

        return view('public.portfolio.index', compact('projects', 'learningPaths', 'categoryId'));
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
