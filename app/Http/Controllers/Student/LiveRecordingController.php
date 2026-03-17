<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LiveRecording;
use App\Models\LiveSession;
use Illuminate\Http\Request;

class LiveRecordingController extends Controller
{
    /**
     * قائمة التسجيلات المنشورة للجلسات التي يمكن للطالب الوصول إليها.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $enrolledCourseIds = \DB::table('online_enrollments')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('advanced_course_id');

        $sessionIds = LiveSession::where('status', 'ended')
            ->where(function ($q) use ($enrolledCourseIds) {
                $q->whereIn('course_id', $enrolledCourseIds)
                    ->orWhere('require_enrollment', false)
                    ->orWhereNull('course_id');
            })
            ->pluck('id');

        $recordings = LiveRecording::with(['session.course', 'session.instructor'])
            ->whereIn('session_id', $sessionIds)
            ->where('status', 'ready')
            ->where('is_published', true)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('student.live-recordings.index', compact('recordings'));
    }

    /**
     * مشاهدة تسجيل (مع تحقق الصلاحية).
     */
    public function show(LiveRecording $liveRecording)
    {
        $liveRecording->load('session');
        $session = $liveRecording->session;

        if (!$session || !$session->canUserJoin(auth()->user())) {
            abort(403, 'ليس لديك صلاحية مشاهدة هذا التسجيل');
        }
        if ($liveRecording->status !== 'ready' || !$liveRecording->is_published) {
            abort(404);
        }

        $url = $liveRecording->getUrl();
        if (!$url) {
            abort(404, 'رابط التسجيل غير متوفر حالياً');
        }

        return view('student.live-recordings.show', compact('liveRecording', 'url'));
    }
}
