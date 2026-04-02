<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Models\LiveSetting;
use App\Models\LiveServer;
use App\Models\SessionAttendance;
use App\Models\AdvancedCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\LiveRecording;

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

        $jitsiDomain = $liveSession->server?->normalized_domain ?: LiveSetting::getJitsiDomain();
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

    /**
     * تجهيز رابط رفع مباشر للتسجيل الصوتي المنفصل إلى Cloudflare R2.
     */
    public function presignAudioUpload(Request $request, LiveSession $liveSession)
    {
        if ($liveSession->instructor_id !== auth()->id()) {
            abort(403);
        }

        if (! $liveSession->isLive()) {
            return response()->json(['message' => 'الجلسة ليست في وضع البث.'], 422);
        }

        $disk = Storage::disk('live_recordings_r2');
        if (! $disk->providesTemporaryUploadUrls()) {
            return response()->json([
                'direct_upload' => false,
                'message' => 'التخزين الحالي لا يدعم الرفع المباشر. تحقق من إعدادات R2.',
            ], 503);
        }

        $validated = $request->validate([
            'content_type' => ['nullable', 'string', 'max:191'],
        ]);

        $mime = $this->normalizeAudioMime((string) ($validated['content_type'] ?? 'audio/webm'));
        $ext = $this->mimeToAudioExt($mime);
        $directory = 'live-session-audio/'.now()->format('Y/m');
        $fileName = sprintf(
            'session-%d-audio-%s-%s.%s',
            $liveSession->id,
            now()->format('Ymd-His'),
            Str::lower(Str::random(8)),
            $ext
        );
        $path = $directory.'/'.$fileName;

        $token = Str::random(64);
        Cache::put(
            'live_session_audio_presign:'.$token,
            [
                'path' => $path,
                'session_id' => $liveSession->id,
                'user_id' => auth()->id(),
                'mime' => $mime,
            ],
            now()->addMinutes(90)
        );

        try {
            $signed = $disk->temporaryUploadUrl(
                $path,
                now()->addMinutes(75),
                ['ContentType' => $mime]
            );
        } catch (\Throwable $e) {
            Cache::forget('live_session_audio_presign:'.$token);

            return response()->json([
                'direct_upload' => false,
                'message' => 'تعذر تجهيز رابط الرفع إلى التخزين السحابي.',
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
     * إنهاء رفع التسجيل الصوتي وإنشاء سجل منفصل في live_recordings.
     */
    public function completeAudioUpload(Request $request, LiveSession $liveSession)
    {
        if ($liveSession->instructor_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'upload_token' => ['required', 'string', 'size:64'],
            'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:43200'],
        ]);

        $payload = Cache::pull('live_session_audio_presign:'.$validated['upload_token']);
        if (! is_array($payload)
            || (int) ($payload['session_id'] ?? 0) !== (int) $liveSession->id
            || (int) ($payload['user_id'] ?? 0) !== (int) auth()->id()) {
            return response()->json([
                'message' => 'انتهت صلاحية رابط الرفع أو أنه غير صالح.',
            ], 422);
        }

        $path = (string) ($payload['path'] ?? '');
        if ($path === '' || str_contains($path, '..')) {
            return response()->json(['message' => 'مسار التخزين غير صالح.'], 422);
        }

        $disk = Storage::disk('live_recordings_r2');
        if (! $disk->exists($path)) {
            return response()->json([
                'message' => 'الملف غير ظاهر على التخزين بعد. أعد المحاولة بعد ثوانٍ.',
            ], 422);
        }

        $size = (int) $disk->size($path);
        if ($size <= 0) {
            return response()->json(['message' => 'ملف الصوت فارغ.'], 422);
        }

        $maxBytes = 2147483648;
        if ($size > $maxBytes) {
            try {
                $disk->delete($path);
            } catch (\Throwable $e) {
            }

            return response()->json(['message' => 'حجم الملف يتجاوز الحد المسموح (٢ جيجابايت).'], 422);
        }

        $recording = LiveRecording::firstOrNew([
            'session_id' => $liveSession->id,
            'file_path' => $path,
            'storage_disk' => 'r2',
        ]);

        if (! $recording->exists) {
            $recording->title = 'تسجيل صوتي منفصل - '.$liveSession->title;
            $recording->status = 'ready';
            $recording->is_published = false;
        }

        $recording->file_size = $size;
        $recording->duration_seconds = (int) ($validated['duration_seconds'] ?? 0);
        $recording->save();

        return response()->json([
            'success' => true,
            'recording_id' => $recording->id,
            'message' => 'تم رفع التسجيل الصوتي المنفصل بنجاح.',
        ]);
    }

    private function normalizeAudioMime(string $mime): string
    {
        $mime = strtolower(trim($mime));
        $allowed = [
            'audio/webm',
            'audio/ogg',
            'audio/mp4',
            'audio/mpeg',
            'application/octet-stream',
            'binary/octet-stream',
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
