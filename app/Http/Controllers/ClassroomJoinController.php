<?php

namespace App\Http\Controllers;

use App\Models\ClassroomMeeting;
use App\Models\ClassroomMeetingParticipant;
use App\Models\LiveSetting;
use App\Models\User;
use App\Services\ClassroomWhiteboardSceneService;
use App\Services\SubscriptionLimitService;
use App\Support\ShareAnnotationSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ClassroomJoinController extends Controller
{
    /**
     * لوبي الرابط الثابت للمعلم — /classroom/join/t/{slug}
     */
    public function showTeacher(string $slug)
    {
        $teacher = $this->findTeacherBySlug($slug);
        if (! $teacher) {
            abort(404, 'رابط الفصل غير موجود.');
        }

        $serviceAvailable = $teacher->hasActiveTeacherSubscription()
            && $teacher->hasSubscriptionFeature('classroom_access');

        $activeMeeting = null;
        if ($serviceAvailable) {
            $activeMeeting = ClassroomMeeting::query()
                ->where('user_id', $teacher->id)
                ->live()
                ->orderByDesc('started_at')
                ->first();
        }

        $fixedUrl = url('classroom/join/t/'.$teacher->classroom_slug);
        $jitsiDomain = LiveSetting::getJitsiDomain();

        return view('classroom.join-fixed', [
            'teacher' => $teacher,
            'serviceAvailable' => $serviceAvailable,
            'activeMeeting' => $activeMeeting,
            'fixedUrl' => $fixedUrl,
            'jitsiDomain' => $jitsiDomain,
            'statusUrl' => route('classroom.join.teacher.status', $teacher->classroom_slug),
            'enterUrl' => route('classroom.join.teacher.enter', $teacher->classroom_slug),
        ]);
    }

    /**
     * حالة الجلسة النشطة (للاستطلاع من صفحة الانتظار).
     */
    public function teacherStatus(string $slug)
    {
        $teacher = $this->findTeacherBySlug($slug);
        if (! $teacher) {
            return response()->json(['ok' => false, 'message' => 'رابط غير موجود'], 404);
        }

        $serviceAvailable = $teacher->hasActiveTeacherSubscription()
            && $teacher->hasSubscriptionFeature('classroom_access');

        if (! $serviceAvailable) {
            return response()->json([
                'ok' => true,
                'service_available' => false,
                'live' => false,
                'message' => 'خدمة Classroom غير متاحة حالياً لهذا المعلم.',
            ]);
        }

        $meeting = ClassroomMeeting::query()
            ->where('user_id', $teacher->id)
            ->live()
            ->orderByDesc('started_at')
            ->first();

        if ($meeting && SubscriptionLimitService::expireMeetingIfPastDuration($meeting)) {
            $meeting = null;
        }

        if (! $meeting) {
            return response()->json([
                'ok' => true,
                'service_available' => true,
                'live' => false,
                'message' => 'المعلم لم يبدأ الجلسة بعد.',
            ]);
        }

        return response()->json([
            'ok' => true,
            'service_available' => true,
            'live' => true,
            'code' => $meeting->code,
            'title' => $meeting->title,
            'join_url' => url('classroom/join/'.$meeting->code),
            'max_participants' => (int) ($meeting->max_participants ?: 25),
        ]);
    }

    /**
     * دخول الغرفة النشطة عبر الرابط الثابت (نفس منطق enter بالكود).
     */
    public function enterTeacher(Request $request, string $slug)
    {
        $teacher = $this->findTeacherBySlug($slug);
        if (! $teacher) {
            return response()->json(['ok' => false, 'message' => 'رابط غير موجود'], 404);
        }

        if (! $teacher->hasActiveTeacherSubscription() || ! $teacher->hasSubscriptionFeature('classroom_access')) {
            return response()->json([
                'ok' => false,
                'message' => 'خدمة Classroom غير متاحة حالياً.',
            ], 422);
        }

        $meeting = ClassroomMeeting::query()
            ->where('user_id', $teacher->id)
            ->live()
            ->orderByDesc('started_at')
            ->first();

        if ($meeting && SubscriptionLimitService::expireMeetingIfPastDuration($meeting)) {
            $meeting = null;
        }

        if (! $meeting) {
            return response()->json([
                'ok' => false,
                'message' => 'المعلم لم يبدأ الجلسة بعد. انتظر قليلاً ثم أعد المحاولة.',
                'waiting' => true,
            ], 422);
        }

        return $this->enter($request, $meeting->code);
    }

    private function findTeacherBySlug(string $slug): ?User
    {
        $slug = strtolower(trim($slug));
        if ($slug === '' || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return null;
        }

        return User::query()->where('classroom_slug', $slug)->first();
    }

    /**
     * صفحة الدخول كضيف — لا تتطلب تسجيل دخول.
     * الرابط يُشارك من المعلم: /classroom/join/{code}
     */
    public function show(string $code)
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
        if (strlen($code) < 4) {
            abort(404, 'كود الغرفة غير صالح.');
        }

        $roomName = 'Muallimx-'.$code;
        $meeting = ClassroomMeeting::where('code', $code)->first();
        $jitsiDomain = LiveSetting::getJitsiDomain();
        $joinUrl = url('classroom/join/'.$code);
        $maxParticipants = (int) ($meeting?->max_participants ?? 25);
        $meetingEnded = (bool) ($meeting && $meeting->ended_at);

        return view('classroom.join', compact('code', 'roomName', 'meeting', 'jitsiDomain', 'joinUrl', 'maxParticipants', 'meetingEnded'));
    }

    public function enter(Request $request, string $code)
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
        $meeting = ClassroomMeeting::where('code', $code)->firstOrFail();

        if ($meeting->ended_at) {
            return response()->json([
                'ok' => false,
                'message' => 'هذا الاجتماع تم إنهاؤه من المعلم.',
            ], 422);
        }

        if (! $meeting->started_at) {
            return response()->json([
                'ok' => false,
                'message' => 'المعلم لم يبدأ الجلسة بعد.',
                'waiting' => true,
            ], 422);
        }

        if (SubscriptionLimitService::expireMeetingIfPastDuration($meeting)) {
            return response()->json([
                'ok' => false,
                'message' => 'انتهت مدة هذا الاجتماع حسب قيود الباقة.',
            ], 422);
        }

        $owner = $meeting->user;

        $maxParticipants = (int) ($meeting->max_participants ?: 25);
        if ($owner && ! $meeting->consultation_request_id) {
            $limits = SubscriptionLimitService::limitsForUser($owner);
            $maxParticipants = min($maxParticipants, (int) $limits['classroom_max_participants']);
        }
        $activeParticipants = $this->activeParticipantsCount($meeting->id);
        if ($activeParticipants >= $maxParticipants) {
            return response()->json([
                'ok' => false,
                'message' => 'تم الوصول للحد الأقصى للطلاب في هذا الاجتماع.',
            ], 422);
        }

        $displayName = trim((string) $request->input('display_name', 'ضيف'));
        if ($displayName === '') {
            $displayName = 'ضيف';
        }
        $displayName = mb_substr($displayName, 0, 120);

        $token = Str::random(48);
        ClassroomMeetingParticipant::create([
            'classroom_meeting_id' => $meeting->id,
            'token' => $token,
            'display_name' => $displayName,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'joined_at' => now(),
            'last_seen_at' => now(),
        ]);

        $newCount = $this->activeParticipantsCount($meeting->id);
        if ($newCount > (int) ($meeting->participants_peak ?? 0)) {
            $meeting->update(['participants_peak' => $newCount]);
        }

        return response()->json([
            'ok' => true,
            'token' => $token,
            'active_participants' => $newCount,
            'max_participants' => $maxParticipants,
            'allow_participant_whiteboard' => $meeting->allowsParticipantWhiteboard(),
        ]);
    }

    public function heartbeat(Request $request, string $code)
    {
        $token = (string) $request->input('token');
        if ($token === '') {
            return response()->json(['ok' => false], 422);
        }

        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
        $meeting = ClassroomMeeting::where('code', $code)->firstOrFail();
        $participant = ClassroomMeetingParticipant::where('classroom_meeting_id', $meeting->id)
            ->where('token', $token)
            ->first();

        if (! $participant || $participant->left_at) {
            return response()->json(['ok' => false], 404);
        }

        $participant->update(['last_seen_at' => now()]);
        $meeting->refresh();

        if ($meeting->ended_at) {
            return response()->json([
                'ok' => false,
                'ended' => true,
                'message' => 'تم إنهاء الاجتماع.',
            ], 422);
        }

        if (SubscriptionLimitService::expireMeetingIfPastDuration($meeting)) {
            return response()->json([
                'ok' => false,
                'ended' => true,
                'message' => 'انتهت مدة الاجتماع حسب الباقة.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'active_participants' => $this->activeParticipantsCount($meeting->id),
            'max_participants' => (int) ($meeting->max_participants ?: 25),
            'allow_participant_whiteboard' => $meeting->allowsParticipantWhiteboard(),
        ]);
    }

    public function leave(Request $request, string $code)
    {
        $token = (string) $request->input('token');
        if ($token === '') {
            return response()->json(['ok' => false], 422);
        }

        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
        $meeting = ClassroomMeeting::where('code', $code)->firstOrFail();
        ClassroomMeetingParticipant::where('classroom_meeting_id', $meeting->id)
            ->where('token', $token)
            ->whereNull('left_at')
            ->update(['left_at' => now(), 'last_seen_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function pushShareAnnotation(Request $request, string $code)
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
        $meeting = ClassroomMeeting::where('code', $code)->firstOrFail();

        if (! $meeting->allowsParticipantWhiteboard() || ! $meeting->started_at || $meeting->ended_at) {
            return response()->json(['message' => 'غير مسموح'], 422);
        }

        $token = (string) $request->input('token');
        if ($token === '') {
            return response()->json(['message' => 'رمز غير صالح'], 422);
        }

        $participant = ClassroomMeetingParticipant::where('classroom_meeting_id', $meeting->id)
            ->where('token', $token)
            ->whereNull('left_at')
            ->first();

        if (! $participant) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $clean = ShareAnnotationSanitizer::polylines($request->input('polylines'));
        $key = 'mx_share_ann_classroom_'.$meeting->id;
        $all = Cache::get($key, []);
        $layerKey = 'g_'.substr(hash('sha256', $token), 0, 24);
        $all[$layerKey] = [
            'name' => $participant->display_name,
            'polylines' => $clean,
            'ts' => now()->timestamp,
        ];
        Cache::put($key, $all, now()->addHours(6));

        return response()->json(['ok' => true]);
    }

    /**
     * جلب مشهد الوايت بورد المشترك (للضيف عند تفعيل الكتابة).
     */
    public function whiteboardScene(Request $request, string $code)
    {
        $meeting = $this->resolveLiveMeetingForGuestWb($request, $code);
        if ($meeting instanceof \Illuminate\Http\JsonResponse) {
            return $meeting;
        }

        $scene = ClassroomWhiteboardSceneService::get($meeting);

        return response()->json([
            'ok' => true,
            'version' => $scene['version'],
            'elements' => $scene['elements'],
            'updated_by' => $scene['updated_by'],
            'ts' => $scene['ts'],
            'allow_write' => true,
        ]);
    }

    /**
     * حفظ مشهد الوايت بورد من الضيف (يكتب على اللوح وليس فوق فيديو الاجتماع).
     */
    public function pushWhiteboardScene(Request $request, string $code)
    {
        $meeting = $this->resolveLiveMeetingForGuestWb($request, $code);
        if ($meeting instanceof \Illuminate\Http\JsonResponse) {
            return $meeting;
        }

        $token = (string) $request->input('token');
        $participant = ClassroomMeetingParticipant::where('classroom_meeting_id', $meeting->id)
            ->where('token', $token)
            ->whereNull('left_at')
            ->first();

        $scene = ClassroomWhiteboardSceneService::put(
            $meeting,
            $request->input('elements'),
            'guest:'.($participant->display_name ?? 'ضيف')
        );

        return response()->json([
            'ok' => true,
            'version' => $scene['version'],
            'ts' => $scene['ts'],
        ]);
    }

    /**
     * @return ClassroomMeeting|\Illuminate\Http\JsonResponse
     */
    private function resolveLiveMeetingForGuestWb(Request $request, string $code)
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
        $meeting = ClassroomMeeting::where('code', $code)->first();
        if (! $meeting) {
            return response()->json(['ok' => false, 'message' => 'الغرفة غير موجودة'], 404);
        }

        if (! $meeting->started_at || $meeting->ended_at) {
            return response()->json(['ok' => false, 'message' => 'الاجتماع غير نشط'], 422);
        }

        if (SubscriptionLimitService::expireMeetingIfPastDuration($meeting)) {
            return response()->json(['ok' => false, 'message' => 'انتهت مدة الاجتماع', 'ended' => true], 422);
        }

        if (! $meeting->allowsParticipantWhiteboard()) {
            return response()->json(['ok' => false, 'message' => 'المعلم لم يُتح الكتابة على الوايت بورد بعد'], 422);
        }

        $token = (string) $request->input('token', $request->query('token', ''));
        if ($token === '') {
            return response()->json(['ok' => false, 'message' => 'رمز غير صالح'], 422);
        }

        $participant = ClassroomMeetingParticipant::where('classroom_meeting_id', $meeting->id)
            ->where('token', $token)
            ->whereNull('left_at')
            ->first();

        if (! $participant) {
            return response()->json(['ok' => false, 'message' => 'غير مصرح'], 403);
        }

        $participant->update(['last_seen_at' => now()]);

        return $meeting;
    }

    private function activeParticipantsCount(int $meetingId): int
    {
        return ClassroomMeetingParticipant::query()
            ->where('classroom_meeting_id', $meetingId)
            ->whereNull('left_at')
            ->where('last_seen_at', '>=', now()->subMinutes(2))
            ->count();
    }
}
