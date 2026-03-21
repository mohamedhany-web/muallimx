<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademyOpportunityApplication;
use App\Models\Notification;
use App\Models\AcademyOpportunity;
use App\Models\InstructorProfile;
use App\Services\InstructorMarketingRankingService;
use App\Models\User;
use Illuminate\Http\Request;

class AcademyOpportunityController extends Controller
{
    public function index()
    {
        $opportunities = AcademyOpportunity::query()
            ->withCount('applications')
            ->latest()
            ->paginate(20);

        return view('admin.academy-opportunities.index', compact('opportunities'));
    }

    public function create()
    {
        return view('admin.academy-opportunities.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'organization_name' => ['required', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:180'],
            'specialization' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'work_mode' => ['required', 'in:remote,onsite,hybrid'],
            'status' => ['required', 'in:active,paused,closed'],
            'is_featured' => ['nullable', 'boolean'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            'apply_until' => ['nullable', 'date'],
        ]);

        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $data['created_by'] = auth()->id();
        AcademyOpportunity::create($data);

        return redirect()->route('admin.academy-opportunities.index')->with('success', 'تم إنشاء الفرصة بنجاح.');
    }

    public function edit(AcademyOpportunity $academy_opportunity)
    {
        return view('admin.academy-opportunities.edit', ['opportunity' => $academy_opportunity]);
    }

    public function update(Request $request, AcademyOpportunity $academy_opportunity)
    {
        $data = $request->validate([
            'organization_name' => ['required', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:180'],
            'specialization' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'work_mode' => ['required', 'in:remote,onsite,hybrid'],
            'status' => ['required', 'in:active,paused,closed'],
            'is_featured' => ['nullable', 'boolean'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            'apply_until' => ['nullable', 'date'],
        ]);

        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $academy_opportunity->update($data);

        return redirect()->route('admin.academy-opportunities.index')->with('success', 'تم تحديث الفرصة بنجاح.');
    }

    public function destroy(AcademyOpportunity $academy_opportunity)
    {
        $academy_opportunity->delete();

        return back()->with('success', 'تم حذف الفرصة.');
    }

    public function applications(Request $request, AcademyOpportunity $academy_opportunity)
    {
        $status = (string) $request->get('status', 'all');

        $applications = AcademyOpportunityApplication::query()
            ->where('academy_opportunity_id', $academy_opportunity->id)
            ->with(['user'])
            ->latest('applied_at')
            ->get();

        if (in_array($status, ['submitted', 'reviewing', 'accepted', 'rejected'], true)) {
            $applications = $applications->where('status', $status)->values();
        }

        $userIds = $applications->pluck('user_id')->unique()->values();
        $profiles = InstructorProfile::approved()->whereIn('user_id', $userIds)->with('user')->get();
        $rankedProfiles = InstructorMarketingRankingService::rankProfilesCollection($profiles);
        $scoreByUser = $rankedProfiles->mapWithKeys(function ($p) {
            return [(int) $p->user_id => (int) ($p->ranking_score ?? 0)];
        });

        $applications = $applications
            ->map(function ($app) use ($scoreByUser) {
                $app->ranking_score = (int) ($scoreByUser[(int) $app->user_id] ?? 0);
                return $app;
            })
            ->sortByDesc('ranking_score')
            ->values();

        $perPage = 20;
        $page = max(1, (int) $request->integer('page', 1));
        $total = $applications->count();
        $items = $applications->slice(($page - 1) * $perPage, $perPage)->values();
        $applications = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.academy-opportunities.applications', [
            'opportunity' => $academy_opportunity,
            'applications' => $applications,
            'status' => $status,
        ]);
    }

    public function updateApplicationStatus(Request $request, AcademyOpportunity $academy_opportunity, AcademyOpportunityApplication $application)
    {
        abort_unless((int) $application->academy_opportunity_id === (int) $academy_opportunity->id, 404);

        $data = $request->validate([
            'status' => ['required', 'in:submitted,reviewing,accepted,rejected'],
        ]);

        $application->update(['status' => $data['status']]);

        $statusAr = [
            'submitted' => 'تم استلام الطلب',
            'reviewing' => 'قيد المراجعة',
            'accepted' => 'مقبول',
            'rejected' => 'مرفوض',
        ];

        Notification::sendToUser($application->user_id, [
            'sender_id' => auth()->id(),
            'title' => 'تحديث حالة طلب فرصة أكاديمية',
            'message' => 'تم تحديث حالة طلبك على فرصة "' . $academy_opportunity->title . '" إلى: ' . ($statusAr[$data['status']] ?? $data['status']),
            'type' => 'announcement',
            'action_url' => route('student.academies.visibility'),
            'action_text' => 'عرض التفاصيل',
            'priority' => in_array($data['status'], ['accepted', 'rejected'], true) ? 'high' : 'normal',
            'audience' => 'student',
            'is_read' => false,
        ]);

        $adminIds = User::query()
            ->whereIn('role', ['admin', 'super_admin'])
            ->pluck('id');
        Notification::sendToUsers($adminIds, [
            'sender_id' => auth()->id(),
            'title' => 'تحديث حالة طلب فرصة أكاديمية',
            'message' => 'تم تحديث طلب "' . ($application->user->name ?? 'معلم') . '" في فرصة "' . $academy_opportunity->title . '" إلى: ' . ($statusAr[$data['status']] ?? $data['status']),
            'type' => 'announcement',
            'action_url' => route('admin.academy-opportunities.applications', $academy_opportunity),
            'action_text' => 'عرض الطلبات',
            'priority' => 'normal',
            'audience' => 'admin',
            'is_read' => false,
        ]);

        return back()->with('success', 'تم تحديث حالة الطلب وإرسال إشعار للمعلم.');
    }
}

