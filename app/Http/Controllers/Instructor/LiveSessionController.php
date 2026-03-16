<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Models\LiveSetting;
use App\Models\LiveServer;
use App\Models\SessionAttendance;
use App\Models\AdvancedCourse;
use Illuminate\Http\Request;

class LiveSessionController extends Controller
{
    public function index(Request $request)
    {
        $instructorId = auth()->id();

        $query = LiveSession::forInstructor($instructorId)
            ->with(['course'])
            ->withCount('attendance');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sessions = $query->latest('scheduled_at')->paginate(15)->withQueryString();

        $stats = [
            'total'     => LiveSession::forInstructor($instructorId)->count(),
            'live'      => LiveSession::forInstructor($instructorId)->where('status', 'live')->count(),
            'scheduled' => LiveSession::forInstructor($instructorId)->where('status', 'scheduled')->count(),
            'ended'     => LiveSession::forInstructor($instructorId)->where('status', 'ended')->count(),
        ];

        return view('instructor.live-sessions.index', compact('sessions', 'stats'));
    }

    public function create()
    {
        $courses = AdvancedCourse::whereHas('enrollments', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->orWhere('instructor_id', auth()->id())
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        if ($courses->isEmpty()) {
            $courses = AdvancedCourse::select('id', 'title')->orderBy('title')->get();
        }

        return view('instructor.live-sessions.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'course_id'        => 'nullable|exists:advanced_courses,id',
            'scheduled_at'     => 'required|date|after:now',
            'max_participants' => 'nullable|integer|min:2|max:500',
            'is_recorded'      => 'boolean',
            'allow_chat'       => 'boolean',
            'password'         => 'nullable|string|max:50',
        ]);

        $validated['instructor_id'] = auth()->id();
        $validated['is_recorded'] = $request->boolean('is_recorded');
        $validated['allow_chat'] = $request->boolean('allow_chat', true);
        $validated['allow_screen_share'] = true;
        $validated['require_enrollment'] = LiveSetting::get('require_enrollment', true);
        $validated['mute_on_join'] = LiveSetting::get('mute_students_on_join', true);
        $validated['video_off_on_join'] = LiveSetting::get('video_off_students_on_join', true);
        $validated['status'] = 'scheduled';

        $defaultServer = LiveServer::where('status', 'active')->first();
        if ($defaultServer) {
            $validated['server_id'] = $defaultServer->id;
        }

        $session = LiveSession::create($validated);

        return redirect()->route('instructor.live-sessions.show', $session)
            ->with('success', 'تم إنشاء جلسة البث بنجاح — يمكنك بدء البث في الموعد المحدد');
    }

    public function show(LiveSession $liveSession)
    {
        if ($liveSession->instructor_id !== auth()->id()) {
            abort(403);
        }

        $liveSession->load(['course', 'recordings']);
        $attendees = $liveSession->attendance()->with('user')->orderByDesc('joined_at')->get();

        return view('instructor.live-sessions.show', compact('liveSession', 'attendees'));
    }

    public function start(LiveSession $liveSession)
    {
        if ($liveSession->instructor_id !== auth()->id()) {
            abort(403);
        }
        if (!$liveSession->isScheduled()) {
            return back()->with('error', 'لا يمكن بدء هذه الجلسة — الحالة الحالية: ' . $liveSession->status);
        }

        $liveSession->start();

        SessionAttendance::create([
            'session_id'      => $liveSession->id,
            'user_id'         => auth()->id(),
            'joined_at'       => now(),
            'ip_address'      => request()->ip(),
            'user_agent'      => request()->userAgent(),
            'role_in_session'  => 'instructor',
        ]);

        return redirect()->route('instructor.live-sessions.room', $liveSession);
    }

    public function room(LiveSession $liveSession)
    {
        if ($liveSession->instructor_id !== auth()->id()) {
            abort(403);
        }
        if (!$liveSession->isLive()) {
            return redirect()->route('instructor.live-sessions.show', $liveSession)
                ->with('info', 'الجلسة ليست في وضع البث');
        }

        $jitsiDomain = $liveSession->server?->domain ?? LiveSetting::get('jitsi_domain', 'meet.jit.si');
        $user = auth()->user();

        return view('instructor.live-sessions.room', compact('liveSession', 'jitsiDomain', 'user'));
    }

    public function end(LiveSession $liveSession)
    {
        if ($liveSession->instructor_id !== auth()->id()) {
            abort(403);
        }

        $attendance = SessionAttendance::where('session_id', $liveSession->id)
            ->where('user_id', auth()->id())
            ->whereNull('left_at')
            ->first();
        $attendance?->markLeft();

        $liveSession->end();

        return redirect()->route('instructor.live-sessions.show', $liveSession)
            ->with('success', 'تم إنهاء جلسة البث');
    }
}
