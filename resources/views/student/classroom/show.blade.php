@extends('layouts.app')

@section('title', 'تفاصيل الاجتماع')
@section('header', 'تفاصيل الاجتماع')

@php
    $rp = ($useInstructorRoutes ?? false) ? 'instructor.' : 'student.';
@endphp
@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="rounded-xl bg-sky-50 border border-sky-200 text-sky-800 px-4 py-3 text-sm font-medium">{{ session('info') }}</div>
    @endif

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">{{ $meeting->title }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">الكود: <span class="font-mono font-bold">{{ $meeting->code }}</span></p>
            </div>
            <div class="flex items-center gap-2">
                @if(!$meeting->consultation_request_id && !($useInstructorRoutes ?? false) && !$meeting->isLive() && !$meeting->ended_at)
                <a href="{{ route('student.classroom.edit', $meeting) }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold">تعديل</a>
                @endif
                @if(!$meeting->started_at && !$meeting->ended_at)
                    <form action="{{ route($rp.'classroom.start-meeting', $meeting) }}" method="POST">@csrf<button class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">بدء الآن</button></form>
                @elseif($meeting->isLive())
                    <a href="{{ route($rp.'classroom.room', $meeting) }}" class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">دخول الغرفة</a>
                    <form method="POST" action="{{ route($rp.'classroom.end', $meeting) }}" onsubmit="return confirm('إنهاء الاجتماع؟');">@csrf<button class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold">إنهاء</button></form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">الحالة</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white">
                    {{ $meeting->isLive() ? 'مباشر' : (!$meeting->started_at ? 'مجدول' : 'منتهي') }}
                </p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">الموعد المحدد</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ optional($meeting->scheduled_for)->format('Y-m-d H:i') ?? 'غير محدد' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">مدة الاجتماع</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ (int) ($meeting->planned_duration_minutes ?? $limits['classroom_max_duration_minutes']) }} دقيقة</p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">الحد الأقصى للمشاركين</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ (int) ($meeting->max_participants ?? 25) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">أعلى ذروة مشاركين</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ (int) ($meeting->participants_peak ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">إجمالي المشاركين المسجلين</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ (int) ($meeting->participants_count ?? 0) }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-3 space-y-3">
            @if(!empty($fixedJoinUrl) && !($useInstructorRoutes ?? false))
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs text-slate-600 dark:text-slate-300">الرابط الثابت (يُفضَّل مشاركته دائماً):</div>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ $fixedJoinUrl }}" class="w-[340px] max-w-[60vw] px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-xs" dir="ltr">
                    <button type="button" onclick="navigator.clipboard.writeText(@json($fixedJoinUrl))" class="px-3 py-2 rounded-lg bg-[#FB5607]/15 text-[#FB5607] text-xs font-semibold">نسخ</button>
                </div>
            </div>
            @endif
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs text-slate-600 dark:text-slate-300">رابط هذه الجلسة فقط:</div>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ $joinUrl }}" class="w-[340px] max-w-[60vw] px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-xs" dir="ltr">
                    <button type="button" onclick="navigator.clipboard.writeText(@json($joinUrl))" class="px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-semibold">نسخ</button>
                </div>
            </div>
        </div>

        @if($meeting->ended_at || $meeting->isRecordingProcessing() || $meeting->recording_path || $meeting->recording_audio_path)
            @php
                $hasVideo = (bool) $meeting->recording_path;
                $hasAudio = (bool) $meeting->recording_audio_path;
                $hasAnyMedia = $hasVideo || $hasAudio;
                $isProcessing = $meeting->isRecordingProcessing();
                $cloudflareOk = $hasVideo ? $meeting->recordingExistsOnCloudflare() : false;
                $recordingStatusUrl = route($rp.'classroom.recording.status', $meeting);
            @endphp
            <div id="mx-recording-panel" class="rounded-xl border border-sky-200 dark:border-sky-800 bg-sky-50/70 dark:bg-sky-900/10 p-4 space-y-3"
                 data-status-url="{{ $recordingStatusUrl }}"
                 data-initial-status="{{ $isProcessing ? 'processing' : ($cloudflareOk ? 'ready' : ($hasVideo ? 'pending_cloud' : ($hasAnyMedia ? 'ready' : 'none'))) }}">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-100">التسجيل والتقرير الصوتي</p>
                        <p id="mx-rec-status-text" class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                            @if($isProcessing)
                                جاري رفع/حفظ التسجيل على Cloudflare… حدّث الصفحة أو انتظر التحديث التلقائي.
                            @elseif($hasVideo && $cloudflareOk)
                                تم حفظ تسجيل المحاضرة على Cloudflare.
                                @if($meeting->recording_uploaded_at)
                                    وقت الرفع: {{ $meeting->recording_uploaded_at->format('Y-m-d H:i') }}
                                @endif
                            @elseif($hasVideo && ! $cloudflareOk)
                                المسار مسجّل لكن التحقق من Cloudflare لم ينجح بعد — أعد التحديث بعد لحظات.
                            @elseif($hasAudio)
                                تم حفظ التقرير الصوتي (الفويس) لهذا الاجتماع.
                            @elseif($meeting->ended_at)
                                لا يوجد تسجيل مرفوع بعد. إن كنت سجّلت المحاضرة، انتظر اكتمال الرفع من السيرفر.
                            @else
                                لا يوجد تسجيل أو تقرير صوتي مرفوع لهذا الاجتماع.
                            @endif
                        </p>
                        @if($hasAudio && $hasVideo)
                            <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-1">تم حفظ التقرير الصوتي أيضاً.</p>
                        @elseif($hasAudio && ! $hasVideo)
                            <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-1">تم حفظ التقرير الصوتي (الفويس) لهذا الاجتماع.</p>
                        @endif
                    </div>
                    <div id="mx-rec-actions" class="flex flex-wrap items-center gap-2">
                        @if($isProcessing)
                            <span id="mx-rec-processing-badge" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-100 text-amber-800 text-xs font-semibold">
                                <span class="inline-block h-3 w-3 rounded-full border-2 border-amber-500/40 border-t-amber-600 animate-spin"></span>
                                جاري الرفع…
                            </span>
                        @endif
                        @if($cloudflareOk)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-semibold">
                                <i class="fas fa-cloud"></i> Cloudflare جاهز
                            </span>
                        @endif
                        @if($meeting->recording_download_url && $cloudflareOk)
                            <a href="{{ $meeting->recording_download_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">
                                <i class="fas fa-download"></i>
                                تحميل تسجيل المحاضرة
                            </a>
                        @endif
                        @if($meeting->recording_audio_download_url)
                            <a href="{{ $meeting->recording_audio_download_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold">
                                <i class="fas fa-music"></i>
                                تحميل التقرير الصوتي
                            </a>
                            <audio controls preload="none" class="max-w-full md:max-w-sm h-9 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800">
                                <source src="{{ $meeting->recording_audio_download_url }}" type="{{ $meeting->recording_audio_mime_type ?: 'audio/webm' }}">
                            </audio>
                        @elseif($meeting->recording_audio_path)
                            <span class="text-xs text-amber-700 dark:text-amber-300">التقرير الصوتي موجود لكن رابط التحميل غير متاح حالياً.</span>
                        @endif
                        @if(!$meeting->recording_download_url && !$meeting->recording_audio_download_url && $hasAnyMedia && ! $isProcessing)
                            <span class="text-xs text-amber-700 dark:text-amber-300">الملف موجود ولكن رابط التحميل غير متاح حالياً.</span>
                        @endif
                    </div>
                </div>
                @if($isProcessing || ($hasVideo && ! $cloudflareOk))
                    <div id="mx-rec-progress-wrap" class="rounded-lg border border-sky-200/80 bg-white/70 dark:bg-slate-900/40 px-3 py-3">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">حالة الرفع إلى Cloudflare</span>
                            <span id="mx-rec-poll-hint" class="text-[11px] text-slate-500">تحديث تلقائي كل ٥ ثوانٍ</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
                            <div id="mx-rec-indet-bar" class="h-full w-1/3 bg-sky-500 animate-pulse"></div>
                        </div>
                    </div>
                @endif
            </div>

            @if($meeting->ended_at)
            @php
                $canGenerateReport = (bool) ($meeting->recording_audio_download_url || ($meeting->recording_download_url && $cloudflareOk));
            @endphp
            <div class="rounded-xl border border-violet-200 dark:border-violet-900 bg-violet-50/60 dark:bg-violet-950/20 p-4 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-100">تقرير المحاضرة النصي (بالذكاء الاصطناعي)</p>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                            يُرسل التسجيل أو التقرير الصوتي إلى خدمة المعالجة، ثم يُحدَّث هذا القسم تلقائياً عند اكتمال إنشاء التقرير.
                        </p>
                    </div>
                    @if($canGenerateReport && !($activeAiReport ?? null))
                        <form method="POST" action="{{ route($rp.'classroom.ai-report', $meeting) }}" class="shrink-0" onsubmit="return confirm('سيتم إرسال رابط التسجيل/التقرير الصوتي لإنشاء تقرير نصي عن المحاضرة. هل تريد المتابعة؟');">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold">
                                <i class="fas fa-robot"></i>
                                إنشاء التقرير النصي
                            </button>
                        </form>
                    @elseif(! $canGenerateReport)
                        <p class="text-xs text-amber-800 dark:text-amber-200 shrink-0 max-w-xs">ارفع التسجيل أو التقرير الصوتي أولاً حتى يظهر زر إنشاء التقرير.</p>
                    @endif
                </div>
                @if($activeAiReport ?? null)
                    <div class="rounded-lg border border-violet-200/80 dark:border-violet-800 bg-white/80 dark:bg-slate-900/40 px-3 py-2 text-xs text-violet-900 dark:text-violet-100">
                        <span class="font-semibold">حالة الطلب:</span>
                        @if($activeAiReport->status === 'pending') في انتظار المعالجة
                        @else جاري المعالجة…
                        @endif
                        <span class="text-slate-500 dark:text-slate-400">(يمكنك تحديث الصفحة لاحقاً)</span>
                    </div>
                @endif
                @if(($latestCompletedAiReport ?? null) && $latestCompletedAiReport->summary)
                    <div>
                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">آخر تقرير مكتمل:</p>
                        <div class="rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-3 text-sm text-slate-800 dark:text-slate-100 whitespace-pre-wrap leading-relaxed">{{ $latestCompletedAiReport->summary }}</div>
                    </div>
                @endif
            </div>
            @endif
        @endif

        <div class="flex items-center justify-between">
            @if($meeting->consultation_request_id && ($useInstructorRoutes ?? false))
                <a href="{{ route('instructor.consultations.show', $meeting->consultation_request_id) }}" class="text-sm text-sky-600 hover:underline">العودة لتفاصيل الاستشارة</a>
            @elseif($useInstructorRoutes ?? false)
                <a href="{{ route('instructor.consultations.index') }}" class="text-sm text-sky-600 hover:underline">العودة لطلبات الاستشارة</a>
            @else
                <a href="{{ route('student.classroom.index') }}" class="text-sm text-sky-600 hover:underline">العودة لقائمة الاجتماعات</a>
            @endif
            @if(!($useInstructorRoutes ?? false) && !$meeting->consultation_request_id)
            <form action="{{ route('student.classroom.destroy', $meeting) }}" method="POST" onsubmit="return confirm('حذف الاجتماع نهائياً؟');">
                @csrf
                @method('DELETE')
                <button class="text-sm text-rose-600 hover:underline">حذف الاجتماع</button>
            </form>
            @endif
        </div>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var panel = document.getElementById('mx-recording-panel');
    if (!panel) return;
    var url = panel.getAttribute('data-status-url');
    var initial = panel.getAttribute('data-initial-status') || 'none';
    if (!url || (initial !== 'processing' && initial !== 'pending_cloud')) return;

    var statusEl = document.getElementById('mx-rec-status-text');
    var tries = 0;
    var maxTries = 72; // ~6 دقائق

    function poll() {
        tries += 1;
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) return;
                if (statusEl && data.message) statusEl.textContent = data.message;
                if (data.status === 'ready' && data.cloudflare_ok) {
                    window.location.reload();
                    return;
                }
                if (tries < maxTries) {
                    setTimeout(poll, 5000);
                } else if (statusEl) {
                    statusEl.textContent = 'ما زال الرفع قيد الانتظار. حدّث الصفحة لاحقاً أو تحقق من إعدادات التسجيل.';
                }
            })
            .catch(function () {
                if (tries < maxTries) setTimeout(poll, 8000);
            });
    }

    setTimeout(poll, 3000);
})();
</script>
@endpush
@endsection

