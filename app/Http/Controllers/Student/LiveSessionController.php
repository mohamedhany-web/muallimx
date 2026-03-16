<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Models\LiveSetting;
use App\Models\SessionAttendance;
use Illuminate\Http\Request;

class LiveSessionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $enrolledCourseIds = \DB::table('online_enrollments')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('advanced_course_id');

        $query = LiveSession::with(['course', 'instructor'])
            ->where(function ($q) use ($enrolledCourseIds) {
                $q->whereIn('course_id', $enrolledCourseIds)
                  ->orWhere('require_enrollment', false)
                  ->orWhereNull('course_id');
            })
            ->whereIn('status', ['scheduled', 'live']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sessions = $query->orderByRaw("FIELD(status, 'live', 'scheduled')")
            ->orderBy('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        $liveSessions = LiveSession::where('status', 'live')
            ->where(function ($q) use ($enrolledCourseIds) {
                $q->whereIn('course_id', $enrolledCourseIds)
                  ->orWhere('require_enrollment', false)
                  ->orWhereNull('course_id');
            })
            ->with(['instructor', 'course'])
            ->get();

        return view('student.live-sessions.index', compact('sessions', 'liveSessions'));
    }

    public function show(LiveSession $liveSession)
    {
        if (!$liveSession->canUserJoin(auth()->user())) {
            abort(403, 'ليس لديك صلاحية دخول هذه الجلسة');
        }

        $liveSession->load(['course', 'instructor']);

        return view('student.live-sessions.show', compact('liveSession'));
    }

    public function join(LiveSession $liveSession)
    {
        $user = auth()->user();

        if (!$liveSession->canUserJoin($user)) {
            return back()->with('error', 'ليس لديك صلاحية دخول هذه الجلسة — تأكد من تسجيلك في الكورس');
        }
        if (!$liveSession->isLive()) {
            return back()->with('error', 'الجلسة ليست في وضع البث حالياً');
        }

        $existing = SessionAttendance::where('session_id', $liveSession->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if (!$existing) {
            SessionAttendance::create([
                'session_id'      => $liveSession->id,
                'user_id'         => $user->id,
                'joined_at'       => now(),
                'ip_address'      => request()->ip(),
                'user_agent'      => request()->userAgent(),
                'role_in_session' => 'student',
            ]);
        }

        $jitsiDomain = $liveSession->server?->domain ?? LiveSetting::get('jitsi_domain', 'meet.jit.si');

        return view('student.live-sessions.room', compact('liveSession', 'jitsiDomain', 'user'));
    }

    public function leave(LiveSession $liveSession)
    {
        $attendance = SessionAttendance::where('session_id', $liveSession->id)
            ->where('user_id', auth()->id())
            ->whereNull('left_at')
            ->first();

        $attendance?->markLeft();

        return redirect()->route('student.live-sessions.index')
            ->with('success', 'تم تسجيل خروجك من الجلسة');
    }
}
