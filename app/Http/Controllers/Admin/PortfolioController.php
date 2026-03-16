<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortfolioProject;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    /**
     * عرض كل مشاريع البورتفوليو — مراجعة من الأدمن فقط عند الإرسال
     */
    public function index(Request $request)
    {
        $query = PortfolioProject::with(['user:id,name,profile_image,email', 'academicYear:id,name', 'advancedCourse:id,title', 'reviewer:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('visible')) {
            if ($request->visible === '1') {
                $query->where('is_visible', true);
            } else {
                $query->where('is_visible', false);
            }
        }

        $projects = $query->latest()->paginate(20);
        return view('admin.portfolio.index', compact('projects'));
    }

    public function show(PortfolioProject $project)
    {
        $project->load(['user', 'academicYear', 'advancedCourse', 'reviewer']);
        return view('admin.portfolio.show', compact('project'));
    }

    /**
     * اعتماد المشروع (مراجعة الأدمن فقط)
     */
    public function approve(Request $request, PortfolioProject $project)
    {
        if ($project->status !== PortfolioProject::STATUS_PENDING_REVIEW) {
            return back()->with('error', 'المشروع تمت مراجعته مسبقاً.');
        }
        $project->update([
            'status' => PortfolioProject::STATUS_APPROVED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'instructor_notes' => $request->instructor_notes,
            'rejected_reason' => null,
        ]);
        return back()->with('success', 'تم اعتماد المشروع. يمكنك نشره في البورتفوليو عند الاستعداد.');
    }

    /**
     * رفض المشروع (مراجعة الأدمن فقط)
     */
    public function reject(Request $request, PortfolioProject $project)
    {
        if ($project->status !== PortfolioProject::STATUS_PENDING_REVIEW) {
            return back()->with('error', 'المشروع تمت مراجعته مسبقاً.');
        }
        $request->validate(['rejected_reason' => 'nullable|string|max:500']);
        $project->update([
            'status' => PortfolioProject::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejected_reason' => $request->rejected_reason,
        ]);
        return back()->with('success', 'تم رفض المشروع.');
    }

    /**
     * نشر المشروع في البورتفوليو (بعد الاعتماد)
     */
    public function publish(PortfolioProject $project)
    {
        if ($project->status !== PortfolioProject::STATUS_APPROVED) {
            return back()->with('error', 'يجب اعتماد المشروع أولاً قبل النشر.');
        }
        $project->update([
            'status' => PortfolioProject::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        return back()->with('success', 'تم نشر المشروع في البورتفوليو.');
    }

    /**
     * إظهار/إخفاء مشروع من البورتفوليو العام (الرقابة)
     */
    public function toggleVisibility(PortfolioProject $project)
    {
        $project->update(['is_visible' => !$project->is_visible]);
        $message = $project->is_visible ? 'تم إظهار المشروع في المعرض.' : 'تم إخفاء المشروع من المعرض.';
        return back()->with('success', $message);
    }
}
