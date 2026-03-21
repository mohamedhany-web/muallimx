<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademyOpportunity;
use App\Models\AcademyOpportunityApplication;
use App\Models\InstructorProfile;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class TeachingOpportunityController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless($user->hasSubscriptionFeature('can_apply_opportunities'), 403, 'ميزة التقديم على الفرص غير مفعلة في باقتك.');

        $profile = InstructorProfile::where('user_id', $user->id)->first();
        $keywords = $this->extractMatchingKeywords($profile);

        $opportunities = AcademyOpportunity::query()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('apply_until')->orWhere('apply_until', '>=', now()->toDateString());
            })
            ->limit(100)
            ->get()
            ->sortByDesc(function ($op) use ($keywords) {
                $score = 0;
                if ($op->is_featured) {
                    $score += 15;
                }
                $haystack = mb_strtolower(trim(($op->title ?? '') . ' ' . ($op->specialization ?? '') . ' ' . ($op->requirements ?? '')));
                foreach ($keywords as $kw) {
                    if ($kw !== '' && mb_strpos($haystack, $kw) !== false) {
                        $score += 10;
                    }
                }
                if ($op->apply_until && $op->apply_until->isFuture()) {
                    $days = now()->diffInDays($op->apply_until, false);
                    if ($days <= 7) {
                        $score += 4;
                    }
                }
                return $score;
            })
            ->values();

        $myApplications = AcademyOpportunityApplication::query()
            ->where('user_id', $user->id)
            ->with('opportunity')
            ->latest('applied_at')
            ->limit(30)
            ->get();

        $myApplicationsMap = $myApplications->pluck('status', 'academy_opportunity_id');

        return view('student.features.can-apply-opportunities', compact(
            'profile',
            'opportunities',
            'myApplications',
            'myApplicationsMap'
        ));
    }

    public function apply(Request $request, AcademyOpportunity $opportunity)
    {
        $user = auth()->user();
        abort_unless($user->hasSubscriptionFeature('can_apply_opportunities'), 403, 'ميزة التقديم على الفرص غير مفعلة في باقتك.');
        abort_if($opportunity->status !== 'active', 422, 'هذه الفرصة غير متاحة حالياً.');
        abort_if($opportunity->apply_until && $opportunity->apply_until->isPast(), 422, 'انتهت فترة التقديم على هذه الفرصة.');

        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:1500'],
        ]);

        AcademyOpportunityApplication::updateOrCreate(
            [
                'academy_opportunity_id' => $opportunity->id,
                'user_id' => $user->id,
            ],
            [
                'status' => 'submitted',
                'message' => $data['message'] ?? null,
                'applied_at' => now(),
            ]
        );

        $adminIds = User::query()
            ->whereIn('role', ['admin', 'super_admin'])
            ->pluck('id');
        Notification::sendToUsers($adminIds, [
            'sender_id' => $user->id,
            'title' => 'طلب جديد على فرصة تدريس',
            'message' => 'المعلم "' . $user->name . '" قدّم على فرصة "' . $opportunity->title . '".',
            'type' => 'announcement',
            'action_url' => route('admin.academy-opportunities.applications', $opportunity),
            'action_text' => 'مراجعة الطلبات',
            'priority' => 'high',
            'audience' => 'admin',
            'is_read' => false,
        ]);

        return back()->with('success', 'تم تقديم طلبك بنجاح.');
    }

    private function extractMatchingKeywords(?InstructorProfile $profile): array
    {
        if (!$profile) {
            return [];
        }
        $raw = collect([
            $profile->headline ?? '',
            $profile->skills ?? '',
            $profile->bio ?? '',
        ])->implode(' ');

        $tokens = preg_split('/[\s,\.\-\_\n\r\t،\/\\\\]+/u', mb_strtolower($raw), -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($tokens)) {
            return [];
        }

        $stop = ['في', 'من', 'على', 'الى', 'and', 'the', 'for', 'with', 'teacher', 'معلم'];
        return collect($tokens)
            ->map(fn ($t) => trim($t))
            ->filter(fn ($t) => mb_strlen($t) >= 3 && !in_array($t, $stop, true))
            ->unique()
            ->take(40)
            ->values()
            ->all();
    }
}

