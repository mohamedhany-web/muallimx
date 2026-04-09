<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassroomMeeting;
use App\Models\LiveSetting;
use App\Services\SubscriptionLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    /**
     * Muallimx Whiteboard — صفحة لوحة كاملة منفصلة (خارج غرفة الاجتماع).
     */
    public function whiteboardStandalone()
    {
        $user = Auth::user();
        $this->ensureClassroomAccess($user);

        return view('student.classroom.whiteboard-standalone');
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
        $roomName = 'Muallimx-' . $code;
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
            'title' => $request->input('title') ?: 'غرفة Muallimx - ' . now()->format('H:i'),
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

        if ($meeting->ended_at) {
            if (request()->routeIs('instructor.*')) {
                if ($meeting->consultation_request_id) {
                    return redirect()->route('instructor.consultations.show', $meeting->consultation_request_id)
                        ->with('error', 'انتهى هذا الاجتماع ولا يمكن إعادة فتح الغرفة.');
                }

                return redirect()->route('instructor.consultations.index')
                    ->with('error', 'انتهى هذا الاجتماع ولا يمكن إعادة فتح الغرفة.');
            }

            return redirect()->route('student.classroom.show', $meeting)
                ->with('error', 'انتهى هذا الاجتماع ولا يمكن إعادة فتح الغرفة.');
        }

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

    public function uploadRecording(Request $request, ClassroomMeeting $meeting)
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $user = Auth::user();
        $this->ensureMeetingOwnership($meeting, $user);
        $this->ensureClassroomAccess($user, $meeting);

        if (!$meeting->started_at) {
            return response()->json(['message' => 'لا يمكن رفع تسجيل لاجتماع لم يبدأ بعد.'], 422);
        }

        try {
            $validated = $request->validate([
                // max بالكيلوبايت في Laravel — 1048576 ≈ 1 جيجابايت
                'recording' => ['required', 'file', 'max:1048576'],
                'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:43200'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'فشل التحقق من الملف المرفوع.',
                'errors' => $e->errors(),
            ], 422);
        }

        $file = $validated['recording'];

        $ext = strtolower((string) $file->getClientOriginalExtension());
        if ($ext === '') {
            $ext = strtolower((string) pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        }
        if (! in_array($ext, ['webm', 'mp4', 'ogg', 'mkv'], true)) {
            return response()->json([
                'message' => 'امتداد الملف غير مدعوم. يُتوقع تسجيل المتصفح بصيغة webm.',
            ], 422);
        }

        if ($file->getSize() <= 0) {
            return response()->json([
                'message' => 'الملف المرفوع فارغ. أعد المحاولة من Chrome أو Edge، واضغط «إيقاف التسجيل» قبل إغلاق مشاركة الشاشة.',
            ], 422);
        }

        $mime = strtolower((string) $file->getMimeType());
        $allowedMimes = [
            'video/webm', 'video/mp4', 'video/quicktime', 'video/x-matroska',
            'audio/webm', 'audio/ogg', 'application/octet-stream', 'binary/octet-stream',
        ];
        if ($mime !== '' && ! in_array($mime, $allowedMimes, true)) {
            return response()->json([
                'message' => 'نوع الملف غير متوقع ('.$mime.'). إن استمر ذلك، جرّب متصفحاً آخر.',
            ], 422);
        }

        $disk = Storage::disk('live_recordings_r2');
        $directory = 'classroom-recordings/'.now()->format('Y/m');
        $fileName = sprintf('meeting-%d-%s.%s', $meeting->id, now()->format('Ymd-His'), $ext ?: 'webm');
        $newPath = $directory.'/'.$fileName;

        $oldPath = ($meeting->recording_disk === 'live_recordings_r2') ? $meeting->recording_path : null;

        try {
            $disk->putFileAs($directory, $file, $fileName);
        } catch (\Throwable $e) {
            \Log::error('Classroom recording upload failed', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'تعذر حفظ التسجيل على التخزين السحابي. تحقق من إعدادات R2 أو حاول لاحقاً.',
            ], 500);
        }

        if ($oldPath && $oldPath !== $newPath) {
            try {
                $disk->delete($oldPath);
            } catch (\Throwable $e) {
                // تجاهل فشل حذف التسجيل القديم حتى لا يتعطل حفظ الجديد.
            }
        }

        $meeting->update([
            'recording_disk' => 'live_recordings_r2',
            'recording_path' => $newPath,
            'recording_mime_type' => $file->getMimeType(),
            'recording_size' => $file->getSize(),
            'recording_duration_seconds' => (int) ($validated['duration_seconds'] ?? 0),
            'recording_uploaded_at' => now(),
        ]);

        return response()->json([
            'message' => 'تم رفع تسجيل المحاضرة إلى Cloudflare بنجاح.',
            'download_url' => $meeting->fresh()->recording_download_url,
        ]);
    }

    /**
     * رابط موقّع لرفع التسجيل مباشرة من المتصفح إلى R2/S3 (يتجاوز حدود PHP و nginx لحجم الطلب).
     */
    public function presignRecordingUpload(Request $request, ClassroomMeeting $meeting)
    {
        @set_time_limit(120);

        $user = Auth::user();
        $this->ensureMeetingOwnership($meeting, $user);
        $this->ensureClassroomAccess($user, $meeting);

        if (! $meeting->started_at) {
            return response()->json(['message' => 'لا يمكن رفع تسجيل لاجتماع لم يبدأ بعد.'], 422);
        }

        $disk = Storage::disk('live_recordings_r2');
        if (! $disk->providesTemporaryUploadUrls()) {
            return response()->json([
                'direct_upload' => false,
                'message' => 'التخزين الحالي لا يدعم الرفع المباشر؛ سيتم الرفع عبر الخادم.',
            ]);
        }

        $validated = $request->validate([
            'content_type' => ['nullable', 'string', 'max:191'],
        ]);

        $mime = $this->normalizeRecordingMime((string) ($validated['content_type'] ?? 'video/webm'));
        $ext = $this->mimeToRecordingExt($mime);

        $directory = 'classroom-recordings/'.now()->format('Y/m');
        $fileName = sprintf(
            'meeting-%d-%s-%s.%s',
            $meeting->id,
            now()->format('Ymd-His'),
            Str::lower(Str::random(8)),
            $ext
        );
        $newPath = $directory.'/'.$fileName;

        $token = Str::random(64);
        Cache::put(
            'classroom_recording_presign:'.$token,
            [
                'path' => $newPath,
                'meeting_id' => $meeting->id,
                'user_id' => $user->id,
                'mime' => $mime,
            ],
            now()->addMinutes(90)
        );

        try {
            $signed = $disk->temporaryUploadUrl(
                $newPath,
                now()->addMinutes(75),
                [
                    'ContentType' => $mime,
                ]
            );
        } catch (\Throwable $e) {
            Cache::forget('classroom_recording_presign:'.$token);
            \Log::error('Classroom recording presign failed', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'direct_upload' => false,
                'message' => 'تعذر تجهيز رابط الرفع إلى التخزين السحابي. تحقق من إعدادات R2 في .env.',
            ], 503);
        }

        return response()->json([
            'direct_upload' => true,
            'upload_url' => $signed['url'],
            'upload_token' => $token,
            'content_type' => $mime,
            'headers' => $signed['headers'] ?? [],
        ]);
    }

    /**
     * بعد PUT الناجح إلى R2: ربط الملف بالاجتماع (طلب JSON صغير لا يتأثر بحدود رفع الملف).
     */
    public function completeDirectRecordingUpload(Request $request, ClassroomMeeting $meeting)
    {
        @set_time_limit(120);

        $user = Auth::user();
        $this->ensureMeetingOwnership($meeting, $user);
        $this->ensureClassroomAccess($user, $meeting);

        if (! $meeting->started_at) {
            return response()->json(['message' => 'لا يمكن رفع تسجيل لاجتماع لم يبدأ بعد.'], 422);
        }

        $validated = $request->validate([
            'upload_token' => ['required', 'string', 'size:64'],
            'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:43200'],
        ]);

        $cacheKey = 'classroom_recording_presign:'.$validated['upload_token'];
        $payload = Cache::pull($cacheKey);
        if (! is_array($payload)
            || (int) ($payload['meeting_id'] ?? 0) !== (int) $meeting->id
            || (int) ($payload['user_id'] ?? 0) !== (int) $user->id) {
            return response()->json([
                'message' => 'انتهت صلاحية رابط الرفع أو أنه غير صالح. أعد محاولة الرفع.',
            ], 422);
        }

        $path = (string) ($payload['path'] ?? '');
        $mime = (string) ($payload['mime'] ?? 'video/webm');
        if ($path === '' || str_contains($path, '..')) {
            return response()->json(['message' => 'مسار التخزين غير صالح.'], 422);
        }

        $disk = Storage::disk('live_recordings_r2');
        if (! $disk->exists($path)) {
            return response()->json([
                'message' => 'الملف غير ظاهر على التخزين بعد. انتظر ثانية ثم أعد تأكيد الرفع، أو أعد الرفع من جديد.',
            ], 422);
        }

        $size = (int) $disk->size($path);
        $maxBytes = 2147483648;

        if ($size <= 0) {
            return response()->json(['message' => 'الملف المرفوع فارغ.'], 422);
        }

        if ($size > $maxBytes) {
            try {
                $disk->delete($path);
            } catch (\Throwable $e) {
            }

            return response()->json(['message' => 'حجم التسجيل يتجاوز الحد المسموح (٢ جيجابايت).'], 422);
        }

        $oldPath = ($meeting->recording_disk === 'live_recordings_r2') ? $meeting->recording_path : null;

        if ($oldPath && $oldPath !== $path) {
            try {
                $disk->delete($oldPath);
            } catch (\Throwable $e) {
            }
        }

        $meeting->update([
            'recording_disk' => 'live_recordings_r2',
            'recording_path' => $path,
            'recording_mime_type' => $mime,
            'recording_size' => $size,
            'recording_duration_seconds' => (int) ($validated['duration_seconds'] ?? 0),
            'recording_uploaded_at' => now(),
        ]);

        return response()->json([
            'message' => 'تم رفع تسجيل المحاضرة إلى Cloudflare بنجاح.',
            'download_url' => $meeting->fresh()->recording_download_url,
        ]);
    }

    /**
     * رابط موقّع لرفع ملف الصوت المنفصل مباشرة إلى R2/S3.
     */
    public function presignAudioUpload(Request $request, ClassroomMeeting $meeting)
    {
        @set_time_limit(120);

        $user = Auth::user();
        $this->ensureMeetingOwnership($meeting, $user);
        $this->ensureClassroomAccess($user, $meeting);

        if (! $meeting->started_at) {
            return response()->json(['message' => 'لا يمكن رفع تسجيل صوتي لاجتماع لم يبدأ بعد.'], 422);
        }

        $disk = Storage::disk('live_recordings_r2');
        if (! $disk->providesTemporaryUploadUrls()) {
            return response()->json([
                'direct_upload' => false,
                'message' => 'التخزين الحالي لا يدعم الرفع المباشر.',
            ]);
        }

        $validated = $request->validate([
            'content_type' => ['nullable', 'string', 'max:191'],
        ]);

        $mime = $this->normalizeAudioMime((string) ($validated['content_type'] ?? 'audio/webm'));
        $ext = $this->mimeToAudioExt($mime);
        $directory = 'classroom-recordings-audio/'.now()->format('Y/m');
        $fileName = sprintf(
            'meeting-%d-audio-%s-%s.%s',
            $meeting->id,
            now()->format('Ymd-His'),
            Str::lower(Str::random(8)),
            $ext
        );
        $newPath = $directory.'/'.$fileName;

        $token = Str::random(64);
        Cache::put(
            'classroom_audio_presign:'.$token,
            [
                'path' => $newPath,
                'meeting_id' => $meeting->id,
                'user_id' => $user->id,
                'mime' => $mime,
            ],
            now()->addMinutes(90)
        );

        try {
            $signed = $disk->temporaryUploadUrl(
                $newPath,
                now()->addMinutes(75),
                [
                    'ContentType' => $mime,
                ]
            );
        } catch (\Throwable $e) {
            Cache::forget('classroom_audio_presign:'.$token);
            \Log::error('Classroom audio presign failed', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'direct_upload' => false,
                'message' => 'تعذر تجهيز رابط رفع الملف الصوتي إلى التخزين السحابي.',
            ], 503);
        }

        return response()->json([
            'direct_upload' => true,
            'upload_url' => $signed['url'],
            'upload_token' => $token,
            'content_type' => $mime,
            'headers' => $signed['headers'] ?? [],
        ]);
    }

    /**
     * رفع ملف الصوت عبر السيرفر (fallback عند عدم دعم direct upload).
     */
    public function uploadAudioRecording(Request $request, ClassroomMeeting $meeting)
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $user = Auth::user();
        $this->ensureMeetingOwnership($meeting, $user);
        $this->ensureClassroomAccess($user, $meeting);

        if (! $meeting->started_at) {
            return response()->json(['message' => 'لا يمكن رفع تسجيل صوتي لاجتماع لم يبدأ بعد.'], 422);
        }

        try {
            $validated = $request->validate([
                'recording_audio' => ['required', 'file', 'max:1048576'],
                'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:43200'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'فشل التحقق من الملف الصوتي المرفوع.',
                'errors' => $e->errors(),
            ], 422);
        }

        $file = $validated['recording_audio'];
        $ext = strtolower((string) $file->getClientOriginalExtension());
        if ($ext === '') {
            $ext = strtolower((string) pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        }
        if (! in_array($ext, ['webm', 'ogg', 'm4a', 'mp3', 'mp4'], true)) {
            return response()->json(['message' => 'امتداد الصوت غير مدعوم.'], 422);
        }

        if ($file->getSize() <= 0) {
            return response()->json(['message' => 'الملف الصوتي فارغ.'], 422);
        }

        $disk = Storage::disk('live_recordings_r2');
        $directory = 'classroom-recordings-audio/'.now()->format('Y/m');
        $fileName = sprintf('meeting-%d-audio-%s.%s', $meeting->id, now()->format('Ymd-His'), $ext ?: 'webm');
        $newPath = $directory.'/'.$fileName;
        $oldAudioPath = ($meeting->recording_disk === 'live_recordings_r2') ? $meeting->recording_audio_path : null;

        try {
            $disk->putFileAs($directory, $file, $fileName);
        } catch (\Throwable $e) {
            \Log::error('Classroom audio upload failed', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'تعذر رفع ملف الصوت إلى التخزين السحابي.',
            ], 500);
        }

        if ($oldAudioPath && $oldAudioPath !== $newPath) {
            try {
                $disk->delete($oldAudioPath);
            } catch (\Throwable $e) {
            }
        }

        $meeting->update([
            'recording_disk' => 'live_recordings_r2',
            'recording_audio_path' => $newPath,
            'recording_audio_mime_type' => $file->getMimeType(),
            'recording_audio_size' => $file->getSize(),
            'recording_audio_duration_seconds' => (int) ($validated['duration_seconds'] ?? 0),
        ]);

        return response()->json([
            'message' => 'تم رفع التسجيل الصوتي بنجاح.',
            'audio_download_url' => $meeting->fresh()->recording_audio_download_url,
        ]);
    }

    /**
     * بعد PUT الناجح للصوت: ربط ملف الصوت بالاجتماع.
     */
    public function completeDirectAudioUpload(Request $request, ClassroomMeeting $meeting)
    {
        @set_time_limit(120);

        $user = Auth::user();
        $this->ensureMeetingOwnership($meeting, $user);
        $this->ensureClassroomAccess($user, $meeting);

        if (! $meeting->started_at) {
            return response()->json(['message' => 'لا يمكن رفع تسجيل صوتي لاجتماع لم يبدأ بعد.'], 422);
        }

        $validated = $request->validate([
            'upload_token' => ['required', 'string', 'size:64'],
            'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:43200'],
        ]);

        $cacheKey = 'classroom_audio_presign:'.$validated['upload_token'];
        $payload = Cache::pull($cacheKey);
        if (! is_array($payload)
            || (int) ($payload['meeting_id'] ?? 0) !== (int) $meeting->id
            || (int) ($payload['user_id'] ?? 0) !== (int) $user->id) {
            return response()->json([
                'message' => 'انتهت صلاحية رابط رفع الصوت أو أنه غير صالح.',
            ], 422);
        }

        $path = (string) ($payload['path'] ?? '');
        $mime = (string) ($payload['mime'] ?? 'audio/webm');
        if ($path === '' || str_contains($path, '..')) {
            return response()->json(['message' => 'مسار التخزين غير صالح.'], 422);
        }

        $disk = Storage::disk('live_recordings_r2');
        if (! $disk->exists($path)) {
            return response()->json([
                'message' => 'ملف الصوت غير ظاهر على التخزين بعد. انتظر ثانية ثم أعد التأكيد.',
            ], 422);
        }

        $size = (int) $disk->size($path);
        if ($size <= 0) {
            return response()->json(['message' => 'ملف الصوت المرفوع فارغ.'], 422);
        }

        $maxBytes = 2147483648;
        if ($size > $maxBytes) {
            try {
                $disk->delete($path);
            } catch (\Throwable $e) {
            }

            return response()->json(['message' => 'حجم ملف الصوت يتجاوز الحد المسموح (٢ جيجابايت).'], 422);
        }

        $oldAudioPath = ($meeting->recording_disk === 'live_recordings_r2') ? $meeting->recording_audio_path : null;
        if ($oldAudioPath && $oldAudioPath !== $path) {
            try {
                $disk->delete($oldAudioPath);
            } catch (\Throwable $e) {
            }
        }

        $meeting->update([
            'recording_disk' => 'live_recordings_r2',
            'recording_audio_path' => $path,
            'recording_audio_mime_type' => $mime,
            'recording_audio_size' => $size,
            'recording_audio_duration_seconds' => (int) ($validated['duration_seconds'] ?? 0),
        ]);

        return response()->json([
            'message' => 'تم رفع التسجيل الصوتي إلى Cloudflare بنجاح.',
            'audio_download_url' => $meeting->fresh()->recording_audio_download_url,
        ]);
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
            abort(403, 'ميزة Muallimx Classroom غير مفعلة في اشتراكك. يمكنك ترقية الباقة من صفحة التسعير.');
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

    private function normalizeRecordingMime(string $mime): string
    {
        $mime = strtolower(trim($mime));
        $allowed = [
            'video/webm', 'video/mp4', 'video/quicktime', 'video/x-matroska',
            'audio/webm', 'audio/ogg', 'application/octet-stream', 'binary/octet-stream',
        ];
        if ($mime !== '' && in_array($mime, $allowed, true)) {
            return $mime;
        }

        return 'video/webm';
    }

    private function mimeToRecordingExt(string $mime): string
    {
        return match ($mime) {
            'video/mp4', 'video/quicktime' => 'mp4',
            'video/x-matroska' => 'mkv',
            'audio/ogg', 'video/ogg' => 'ogg',
            default => 'webm',
        };
    }

    private function normalizeAudioMime(string $mime): string
    {
        $mime = strtolower(trim($mime));
        $allowed = [
            'audio/webm', 'audio/ogg', 'audio/mp4', 'audio/mpeg',
            'application/octet-stream', 'binary/octet-stream',
        ];
        if ($mime !== '' && in_array($mime, $allowed, true)) {
            return $mime;
        }

        return 'audio/webm';
    }

    private function mimeToAudioExt(string $mime): string
    {
        return match ($mime) {
            'audio/ogg' => 'ogg',
            'audio/mp4' => 'm4a',
            'audio/mpeg' => 'mp3',
            default => 'webm',
        };
    }
}
