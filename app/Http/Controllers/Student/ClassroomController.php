<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassroomMeeting;
use App\Models\LiveSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        if (!$user->hasSubscriptionFeature('classroom_access')) {
            abort(403, 'ميزة MuallimX Classroom غير مفعلة في اشتراكك. يمكنك ترقية الباقة من صفحة التسعير.');
        }

        $meetings = ClassroomMeeting::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $jitsiDomain = LiveSetting::getJitsiDomain();
        $joinBaseUrl = url('classroom/join');

        return view('student.classroom.index', compact('meetings', 'jitsiDomain', 'joinBaseUrl'));
    }

    public function start(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasSubscriptionFeature('classroom_access')) {
            abort(403, 'ميزة MuallimX Classroom غير مفعلة في اشتراكك.');
        }

        $code = ClassroomMeeting::generateCode();
        $roomName = 'MuallimX-' . $code;

        $meeting = ClassroomMeeting::create([
            'user_id' => $user->id,
            'code' => $code,
            'room_name' => $roomName,
            'title' => $request->input('title') ?: 'غرفة MuallimX - ' . now()->format('H:i'),
            'started_at' => now(),
        ]);

        return redirect()->route('student.classroom.room', $meeting);
    }

    public function room(ClassroomMeeting $meeting)
    {
        $user = Auth::user();
        if ($meeting->user_id !== $user->id) {
            abort(403);
        }
        if (!$user->hasSubscriptionFeature('classroom_access')) {
            abort(403);
        }

        $jitsiDomain = LiveSetting::getJitsiDomain();
        $isDemoJitsi = (strpos($jitsiDomain, 'meet.jit.si') !== false);

        return view('student.classroom.room', compact('meeting', 'jitsiDomain', 'user', 'isDemoJitsi'));
    }

    public function end(ClassroomMeeting $meeting)
    {
        $user = Auth::user();
        if ($meeting->user_id !== $user->id) {
            abort(403);
        }
        $meeting->update(['ended_at' => now()]);
        return redirect()->route('student.classroom.index')->with('success', 'تم إنهاء الاجتماع.');
    }
}
