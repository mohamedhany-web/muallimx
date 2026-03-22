<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassroomMeeting;
use App\Models\LiveSetting;
use App\Services\SubscriptionLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClassroomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $this->ensureClassroomAccess($user);

        $status = (string) $request->get('status', 'all');
        if (!in_array($status, ['all', 'live', 'scheduled', 'ended'], true)) {
            $status = 'all';
        }

        $meetingsQuery = ClassroomMeeting::query()->where('user_id', $user->id)->withCount('participants');
        if ($status === 'live') {
            $meetingsQuery->whereNotNull('started_at')->whereNull('ended_at');
        } elseif ($status === 'scheduled') {
            $meetingsQuery->whereNull('started_at');
        } elseif ($status === 'ended') {
            $meetingsQuery->whereNotNull('ended_at');
        }

        $meetings = $meetingsQuery
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $limits = SubscriptionLimitService::limitsForUser($user);
        $usedMeetingsThisMonth = SubscriptionLimitService::monthlyClassroomUsage($user);
        $remainingMeetingsThisMonth = max(0, $limits['classroom_meetings_per_month'] - $usedMeetingsThisMonth);
        $joinBaseUrl = url('classroom/join');
        $stats = [
            'total' => ClassroomMeeting::where('user_id', $user->id)->count(),
            'live' => ClassroomMeeting::where('user_id', $user->id)->whereNotNull('started_at')->whereNull('ended_at')->count(),
            'ended' => ClassroomMeeting::where('user_id', $user->id)->whereNotNull('ended_at')->count(),
        ];

        return view('student.classroom.index', compact(
            'meetings',
            'joinBaseUrl',
            'limits',
            'usedMeetingsThisMonth',
            'remainingMeetingsThisMonth',
            'stats',
            'status'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        $this->ensureClassroomAccess($user);

        $limits = SubscriptionLimitService::limitsForUser($user);
        $usedMeetingsThisMonth = SubscriptionLimitService::monthlyClassroomUsage($user);
        $remainingMeetingsThisMonth = max(0, $limits['classroom_meetings_per_month'] - $usedMeetingsThisMonth);

        return view('student.classroom.create', compact('limits', 'usedMeetingsThisMonth', 'remainingMeetingsThisMonth'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $this->ensureClassroomAccess($user);

        $limits = SubscriptionLimitService::limitsForUser($user);
        $usedThisMonth = SubscriptionLimitService::monthlyClassroomUsage($user);
        if ($usedThisMonth >= $limits['classroom_meetings_per_month']) {
            return redirect()->route('student.classroom.index')
                ->with('error', 'وصلت للحد الشهري المسموح لعدد الميتينج في باقتك. يمكنك ترقية الباقة لزيادة الحد.');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'max_participants' => ['required', 'integer', 'min:2', 'max:' . $limits['classroom_max_participants']],
            'start_now' => ['nullable', Rule::in(['0', '1'])],
            'scheduled_for' => ['nullable', 'date'],
            'planned_duration_minutes' => ['nullable', 'integer', 'min:15', 'max:' . $limits['classroom_max_duration_minutes']],
        ]);

        $code = ClassroomMeeting::generateCode();
        $roomName = 'MuallimX-' . $code;
        $startNow = (string) ($data['start_now'] ?? '1') === '1';

        $meeting = ClassroomMeeting::create([
            'user_id' => $user->id,
            'code' => $code,
            'room_name' => $roomName,
            'title' => $data['title'],
            'scheduled_for' => $startNow ? null : ($data['scheduled_for'] ?? null),
            'planned_duration_minutes' => (int) ($data['planned_duration_minutes'] ?? $limits['classroom_default_duration_minutes']),
            'max_participants' => (int) $data['max_participants'],
            'started_at' => $startNow ? now() : null,
        ]);

        if ($startNow) {
            return redirect()->route('student.classroom.room', $meeting);
        }

        return redirect()->route('student.classroom.show', $meeting)
            ->with('success', 'تم إنشاء الاجتماع بنجاح. يمكنك بدءه متى شئت.');
    }

    public function start(Request $request)
    {
        $request->merge([
            'title' => $request->input('title') ?: 'غرفة MuallimX - ' . now()->format('H:i'),
            'max_participants' => (string) (SubscriptionLimitService::limitsForUser(Auth::user())['classroom_max_participants'] ?? 25),
            'planned_duration_minutes' => (string) (SubscriptionLimitService::limitsForUser(Auth::user())['classroom_default_duration_minutes'] ?? 60),
            'start_now' => '1',
        ]);

        return $this->store($request);
    }

    public function show(ClassroomMeeting $meeting)
    {
        $user = Auth::user();
        $this->ensureMeetingOwnership($meeting, $user);
        $this->ensureClassroomAccess($user, $meeting);

        $meeting->loadCount('participants');
        $joinUrl = url('classroom/join/' . $meeting->code);
        $limits = SubscriptionLimitService::limitsForUser($user);
        $useInstructorRoutes = request()->routeIs('instructor.*');

        return view('student.classroom.show', compact('meeting', 'joinUrl', 'limits', 'useInstructorRoutes'));
    }

    public function edit(ClassroomMeeting $meeting)
    {
        $user = Auth::user();
        $this->ensureMeetingOwnership($meeting, $user);
        $this->ensureClassroomAccess($user);
        $limits = SubscriptionLimitService::limitsForUser($user);

        return view('student.classroom.edit', compact('meeting', 'limits'));
    }

    public function update(Request $request, ClassroomMeeting $meeting)
    {
        $user = Auth::user();
        $this->ensureMeetingOwnership($meeting, $user);
        $this->ensureClassroomAccess($user);
        $limits = SubscriptionLimitService::limitsForUser($user);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'max_participants' => ['required', 'integer', 'min:2', 'max:' . $limits['classroom_max_participants']],
            'scheduled_for' => ['nullable', 'date'],
            'planned_duration_minutes' => ['nullable', 'integer', 'min:15', 'max:' . $limits['classroom_max_duration_minutes']],
        ]);

        $meeting->update([
            'title' => $data['title'],
            'scheduled_for' => $data['scheduled_for'] ?? null,
            'planned_duration_minutes' => (int) ($data['planned_duration_minutes'] ?? $limits['classroom_default_duration_minutes']),
            'max_participants' => (int) $data['max_participants'],
        ]);

        return redirect()->route('student.classroom.show', $meeting)->with('success', 'تم تحديث إعدادات الاجتماع.');
    }

    public function startMeeting(ClassroomMeeting $meeting)
    {
        $user = Auth::user();
        $this->ensureMeetingOwnership($meeting, $user);
        $this->ensureClassroomAccess($user, $meeting);

        if ($meeting->ended_at) {
            return back()->with('error', 'لا يمكن بدء اجتماع منتهي.');
        }
        if (!$meeting->started_at) {
            $meeting->update(['started_at' => now()]);
        }

        return redirect()->to($this->classroomRoomUrl($meeting));
    }

    public function room(ClassroomMeeting $meeting)
    {
        $user = Auth::user();
        $this->ensureMeetingOwnership($meeting, $user);
        $this->ensureClassroomAccess($user, $meeting);

        $limits = SubscriptionLimitService::limitsForUser($user);
        if ($meeting->consultation_request_id) {
            $effectiveDurationMinutes = (int) ($meeting->planned_duration_minutes ?: 60);
            $maxDurationMinutes = max(480, $effectiveDurationMinutes);
        } else {
            $maxDurationMinutes = (int) $limits['classroom_max_duration_minutes'];
            $effectiveDurationMinutes = (int) ($meeting->planned_duration_minutes ?: $maxDurationMinutes);
            if ($effectiveDurationMinutes > $maxDurationMinutes) {
                $effectiveDurationMinutes = $maxDurationMinutes;
            }
        }
        if ($meeting->started_at && $meeting->started_at->copy()->addMinutes($effectiveDurationMinutes)->isPast()) {
            if (!$meeting->ended_at) {
                $meeting->update(['ended_at' => now()]);
            }
            $back = request()->routeIs('instructor.*')
                ? route('instructor.consultations.index')
                : route('student.classroom.index');

            return redirect()->to($back)
                ->with('error', $meeting->consultation_request_id
                    ? 'انتهت مدة جلسة الاستشارة.'
                    : 'انتهت مدة الاجتماع المسموح بها حسب باقتك. يمكنك ترقية الباقة لزيادة مدة الميتينج.');
        }

        $jitsiDomain = LiveSetting::getJitsiDomain();
        $isDemoJitsi = (strpos($jitsiDomain, 'meet.jit.si') !== false);
        $meetingEndsAt = $meeting->started_at ? $meeting->started_at->copy()->addMinutes($effectiveDurationMinutes) : null;
        $useInstructorRoutes = request()->routeIs('instructor.*');

        return view('student.classroom.room', compact('meeting', 'jitsiDomain', 'user', 'isDemoJitsi', 'maxDurationMinutes', 'effectiveDurationMinutes', 'meetingEndsAt', 'useInstructorRoutes'));
    }

    public function end(ClassroomMeeting $meeting)
    {
        $user = Auth::user();
        $this->ensureMeetingOwnership($meeting, $user);
        $meeting->update(['ended_at' => now()]);

        if (request()->routeIs('instructor.*')) {
            if ($meeting->consultation_request_id) {
                return redirect()->route('instructor.consultations.show', $meeting->consultation_request_id)
                    ->with('success', 'تم إنهاء جلسة الاستشارة.');
            }

            return redirect()->route('instructor.consultations.index')->with('success', 'تم إنهاء الاجتماع.');
        }

        return redirect()->route('student.classroom.show', $meeting)->with('success', 'تم إنهاء الاجتماع.');
    }

    public function destroy(ClassroomMeeting $meeting)
    {
        $user = Auth::user();
        $this->ensureMeetingOwnership($meeting, $user);
        $this->ensureClassroomAccess($user);

        if ($meeting->isLive()) {
            return back()->with('error', 'لا يمكن حذف اجتماع مباشر. قم بإنهائه أولاً.');
        }

        $meeting->delete();

        return redirect()->route('student.classroom.index')->with('success', 'تم حذف الاجتماع.');
    }

    private function ensureClassroomAccess($user, ?ClassroomMeeting $meeting = null): void
    {
        if ($meeting && $meeting->consultation_request_id && (int) $meeting->user_id === (int) $user->id) {
            return;
        }
        if (!$user->hasSubscriptionFeature('classroom_access')) {
            abort(403, 'ميزة MuallimX Classroom غير مفعلة في اشتراكك. يمكنك ترقية الباقة من صفحة التسعير.');
        }
    }

    private function classroomRoomUrl(ClassroomMeeting $meeting): string
    {
        if (request()->routeIs('instructor.*')) {
            return route('instructor.classroom.room', $meeting);
        }

        return route('student.classroom.room', $meeting);
    }

    private function ensureMeetingOwnership(ClassroomMeeting $meeting, $user): void
    {
        if ((int) $meeting->user_id !== (int) $user->id) {
            abort(403);
        }
    }
}
