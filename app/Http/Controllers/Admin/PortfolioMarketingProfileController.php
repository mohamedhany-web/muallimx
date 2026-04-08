<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PortfolioMarketingProfileController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending_review');
        $allowed = ['pending_review', 'rejected', 'approved', 'all'];
        if (! in_array($status, $allowed, true)) {
            $status = 'pending_review';
        }

        $query = User::query()
            ->where('role', 'student')
            ->with(['portfolioProfileReviewedBy:id,name']);

        if ($status === 'pending_review') {
            $query->where('portfolio_profile_status', User::PORTFOLIO_PROFILE_PENDING);
        } elseif ($status === 'rejected') {
            $query->where('portfolio_profile_status', User::PORTFOLIO_PROFILE_REJECTED);
        } elseif ($status === 'approved') {
            $query->where('portfolio_profile_status', User::PORTFOLIO_PROFILE_APPROVED);
        } elseif ($status === 'all') {
            $query->whereNotNull('portfolio_profile_status');
        }

        $users = $query
            ->orderByDesc('portfolio_profile_submitted_at')
            ->orderByDesc('updated_at')
            ->paginate(25)
            ->withQueryString();

        $pendingCount = User::where('role', 'student')
            ->where('portfolio_profile_status', User::PORTFOLIO_PROFILE_PENDING)
            ->count();

        return view('admin.portfolio-marketing.index', compact('users', 'status', 'pendingCount'));
    }

    public function show(User $user)
    {
        abort_unless($user->role === 'student', 404);
        abort_unless($user->portfolio_profile_status !== null, 404);

        $user->load(['portfolioProfileReviewedBy:id,name']);

        return view('admin.portfolio-marketing.show', compact('user'));
    }

    public function approve(Request $request, User $user)
    {
        abort_unless($user->role === 'student', 404);
        if ($user->portfolio_profile_status !== User::PORTFOLIO_PROFILE_PENDING) {
            return back()->with('error', 'هذا الملف ليس قيد المراجعة.');
        }

        $user->update([
            'portfolio_marketing_published' => $user->snapshotPortfolioMarketingForPublish(),
            'portfolio_profile_status' => User::PORTFOLIO_PROFILE_APPROVED,
            'portfolio_profile_reviewed_at' => now(),
            'portfolio_profile_reviewed_by' => $request->user()->id,
            'portfolio_profile_rejected_reason' => null,
        ]);

        return redirect()
            ->route('admin.portfolio-marketing-profiles.index', ['status' => 'pending_review'])
            ->with('success', 'تم اعتماد الملف التعريفي التسويقي للطالب.');
    }

    public function reject(Request $request, User $user)
    {
        abort_unless($user->role === 'student', 404);
        if ($user->portfolio_profile_status !== User::PORTFOLIO_PROFILE_PENDING) {
            return back()->with('error', 'هذا الملف ليس قيد المراجعة.');
        }

        $request->validate([
            'portfolio_profile_rejected_reason' => 'nullable|string|max:800',
        ]);

        $user->update([
            'portfolio_profile_status' => User::PORTFOLIO_PROFILE_REJECTED,
            'portfolio_profile_reviewed_at' => now(),
            'portfolio_profile_reviewed_by' => $request->user()->id,
            'portfolio_profile_rejected_reason' => $request->portfolio_profile_rejected_reason,
        ]);

        return redirect()
            ->route('admin.portfolio-marketing-profiles.index', ['status' => 'pending_review'])
            ->with('success', 'تم رفض الملف التعريفي. يمكن للطالب التعديل وإعادة الإرسال.');
    }
}
